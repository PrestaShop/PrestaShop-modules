<?php
/**
* 2013 NetReviews
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    NetReviews SAS <contact@avis-verifies.com>
*  @copyright 2013 NetReviews SAS
*  @version   Release: $Revision: 7 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of NetReviews SAS
*/

if (!defined('_PS_VERSION_'))
	exit;



require_once _PS_MODULE_DIR_.'netreviews/models/NetReviewsModel.php';

class NetReviews extends Module
{
	public $id_lang;
	public $iso_lang;
	public $stats_product;

	public function __construct()
	{
		$this->name = 'netreviews';
		$this->tab = 'advertising_marketing';
		$this->author = 'NetReviews';
		$this->need_instance = 0;
		$this->module_key = 'a65tt6ygert4azer34ru523re4rryuvt';
		$this->displayName = $this->l('Verified Reviews');
		$this->description = $this->l('Collect service and product reviews with Verified Reviews. Display reviews on your shop and win the trust of your visitors, to increase your revenue.');

		if (self::isInstalled($this->name))
		{
			$this->id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
			$this->iso_lang = pSQL(Language::getIsoById($this->id_lang));
		}

		$this->version = '7.1';
		parent::__construct();
		// Retrocompatibility
		$this->initContext();
	}

	/* Retrocompatibility 1.4/1.5 */
	private function initContext()
	{
		if (class_exists('Context'))
			$this->context = Context::getContext();
		else
		{
			global $smarty, $cookie;
			$this->context = new StdClass();
			$this->context->smarty = $smarty;
			$this->context->cookie = $cookie;
		}
	}


	public function install()
	{
		if (!$this->installDatabase() || !parent::install())
		{
			$this->context->controller->errors[] = $this->l('Installation error / Database configuration error');
			return false;
		}

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			//PS < 1.5 Hooks
			$this->registerHook('productTabContent');
			$this->registerHook('productTab');
			$this->registerHook('extraRight');
			$this->registerHook('extraLeft');
			$this->registerHook('rightColumn');
			$this->registerHook('leftColumn');
			$this->registerHook('header');
			$this->registerHook('orderConfirmation');

			if (version_compare(_PS_VERSION_, '1.4', '<'))//PS < 1.4 Hook
				$this->registerHook('updateOrderStatus');
			else
				$this->registerHook('postUpdateOrderStatus');
		}
		else
		{
			//PS >=1.5 Hooks
			$this->registerHook('displayProductTabContent');
			$this->registerHook('displayProductTab');
			$this->registerHook('displayRightColumnProduct');
			$this->registerHook('displayLeftColumnProduct');
			$this->registerHook('displayHeader');
			$this->registerHook('displayRightColumn');
			$this->registerHook('displayLeftColumn');
			$this->registerHook('displayOrderConfirmation');
			$this->registerHook('actionOrderStatusPostUpdate');
		}

		// Create PS configuration variable
		Configuration::updateValue('AVISVERIFIES_IDWEBSITE', '');
		Configuration::updateValue('AVISVERIFIES_CLESECRETE', '');
		Configuration::updateValue('AVISVERIFIES_PROCESSINIT', '');
		Configuration::updateValue('AVISVERIFIES_ORDERSTATESCHOOSEN', '');
		Configuration::updateValue('AVISVERIFIES_DELAY', '');

		Configuration::updateValue('AVISVERIFIES_GETPRODREVIEWS', '');
		Configuration::updateValue('AVISVERIFIES_DISPLAYPRODREVIEWS', '');

		Configuration::updateValue('AVISVERIFIES_CSVFILENAME', 'Export_NetReviews_01-01-1970-default.csv');

		Configuration::updateValue('AVISVERIFIES_SCRIPTFLOAT', '');
		Configuration::updateValue('AVISVERIFIES_SCRIPTFLOAT_ALLOWED', '');

		Configuration::updateValue('AVISVERIFIES_SCRIPTFIXE', '');
		Configuration::updateValue('AVISVERIFIES_SCRIPTFIXE_ALLOWED', '');

		Configuration::updateValue('AVISVERIFIES_URLCERTIFICAT', '');
		Configuration::updateValue('AVISVERIFIES_FORBIDDEN_EMAIL', '');

		Configuration::updateValue('AVISVERIFIES_CODE_LANG', '');

		return true;
	}


	private function postProcess()
	{
		if (Tools::isSubmit('submit_export'))
		{
			try {
				$o_av = new NetReviewsModel;

				if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1)
				{
					//Do not use simple quote for \r\n
					$header_colums = 'id_order;email;lastname;firstname;date_order;delay;id_product;category;description;product_url;id_order_state;id_shop'."\r\n";
					$return_export = $o_av->export($this->context->shop->getContextShopID(), $header_colums);
				}
				else
				{
					//Do not use simple quote for \r\n
					$header_colums = 'id_order;email;lastname;firstname;date_order;delay;id_product;category;description;product_url;id_order_state'."\r\n";
					$return_export = $o_av->export(null, $header_colums);
				}

				if (file_exists($return_export[2]))
					$this->_html .= $this->displayConfirmation(sprintf($this->l('%s orders have been exported.'), $return_export[1]).
										'<a href="../modules/netreviews/Export_NetReviews_'.$return_export[0].'"> '.$this->l('Click here to download the file').'</a>');
				else
					$this->_html .= $this->displayError($this->l('Writing on the server is not allowed. Please assign write permissions to the folder netreviews').$return_export[2]);

			} catch (Exception $e) {
				$this->_html .= $this->displayError($e->getMessage());
			}

		}

		if (Tools::isSubmit('submit_configuration'))
		{
			Configuration::updateValue('AVISVERIFIES_IDWEBSITE', Tools::getValue('avisverifies_idwebsite'));
			Configuration::updateValue('AVISVERIFIES_CLESECRETE', Tools::getValue('avisverifies_clesecrete'));
			$this->_html .= $this->displayConfirmation($this->l('The informations have been registered'));
		}
	}

	public function getContent()
	{
		global $currentIndex;

		if (!empty($_POST))
			$this->postProcess();

		// There are 3 kinds of shop context : shop, group shop and general
		//CONTEXT_SHOP = 1;
		//CONTEXT_GROUP = 2;
		//CONTEXT_ALL = 4;

		if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1 &&
			(Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP))
				$this->_html .= $this->displayError($this->l('Multistore feature is enabled. Please choose above the store to configure.'));


		$this->context->smarty->assign(array(
				'current_avisverifies_urlapi' => Configuration::get('AVISVERIFIES_URLAPI'),
				'current_avisverifies_idwebsite' => Configuration::get('AVISVERIFIES_IDWEBSITE'),
				'current_avisverifies_clesecrete' => Configuration::get('AVISVERIFIES_CLESECRETE'),
				'version' => $this->version,
				'av_path' => $this->_path,
				'url_back' => Tools::safeOutput($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'))
		));

		$this->_html .= $this->display(__FILE__, 'views/templates/hook/avisverifies-backoffice.tpl');

		return $this->_html;

	}

	public function hookHeader()
	{
		$widget_flottant_code = '';

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{

			Tools::addCSS(($this->_path).'css/avisverifies-style-front.css', 'all');
			Tools::addJS(($this->_path).'js/avisverifies.js', 'all');

			if (Configuration::get('AVISVERIFIES_SCRIPTFLOAT_ALLOWED') != 'yes')
				return '';

			if (Configuration::get('AVISVERIFIES_SCRIPTFLOAT'))
				$widget_flottant_code .= "\n".Tools::stripslashes(html_entity_decode(Configuration::get('AVISVERIFIES_SCRIPTFLOAT')));
		}
		else
		{
			$this->context->controller->addCSS(($this->_path).'css/avisverifies-style-front.css', 'all');
			$this->context->controller->addJS(($this->_path).'js/avisverifies.js', 'all');

			if (Configuration::get('AVISVERIFIES_SCRIPTFLOAT_ALLOWED', null, null, $this->context->shop->getContextShopID()) != 'yes')
				return '';

			if (Configuration::get('AVISVERIFIES_SCRIPTFLOAT'))
				$widget_flottant_code .= "\n".Tools::stripslashes(html_entity_decode(
					Configuration::get('AVISVERIFIES_SCRIPTFLOAT', null, null, $this->context->shop->getContextShopID())));

		}

		return $widget_flottant_code;
	}


	public function hookProductTab()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$display_prod_reviews = Configuration::get('AVISVERIFIES_DISPLAYPRODREVIEWS');
		else
			$display_prod_reviews = Configuration::get('AVISVERIFIES_DISPLAYPRODREVIEWS', null, null, $this->context->shop->getContextShopID());

		$o_av = new NetReviewsModel();

		$this->stats_product = $o_av->getStatsProduct((int)Tools::getValue('id_product'));

		if ($this->stats_product['nb_reviews'] < 1 || $display_prod_reviews != 'yes') return ''; //Si Aucun avis, on retourne vide

		$this->context->smarty->assign(array('count_reviews' => $this->stats_product['nb_reviews']));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return ($this->display(__FILE__, '/views/templates/hook/avisverifies-tab.tpl'));
		else
			return ($this->display(__FILE__, 'avisverifies-tab.tpl'));
	}


	/* WARNING : Modifications below need to be copy in ajax-load.php*/

	public function hookProductTabContent()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$display_prod_reviews = Configuration::get('AVISVERIFIES_DISPLAYPRODREVIEWS');
			$url_certificat = Configuration::get('AVISVERIFIES_URLCERTIFICAT');
		}
		else
		{
			$display_prod_reviews = configuration::get('AVISVERIFIES_DISPLAYPRODREVIEWS', null, null, $this->context->shop->getContextShopID());
			$url_certificat = Configuration::get('AVISVERIFIES_URLCERTIFICAT', null, null, $this->context->shop->getContextShopID());
		}

		$shop_name = Configuration::get('PS_SHOP_NAME');
		$id_product = (int)Tools::getValue('id_product');

		$o_av = new NetReviewsModel();

		$stats_product = (!isset($this->stats_product) || empty($this->stats_product)) ? $o_av->getStatsProduct($id_product) : $this->stats_product;

		if ($stats_product['nb_reviews'] < 1 || $display_prod_reviews != 'yes') return ''; /* if no reviews, return empty */

		$reviews = $o_av->getProductReviews($id_product, false, 0);
		$reviews_list = array(); //Create array with all reviews data
		$my_review = array(); //Create array with each reviews data

		foreach ($reviews as $review)
		{
			//Create variable for template engine
			$my_review['ref_produit'] = $review['ref_product'];
			$my_review['id_product_av'] = $review['id_product_av'];
			$my_review['rate'] = $review['rate'];
			$my_review['avis'] = urldecode($review['review']);
			$my_review['horodate'] = date('d/m/Y', $review['horodate']);
			$my_review['customer_name'] = urldecode($review['customer_name']);
			$my_review['discussion'] = '';

			$unserialized_discussion = json_decode(NetReviewsModel::acDecodeBase64($review['discussion']),true);

			if ($unserialized_discussion)
			{
				foreach ($unserialized_discussion as $k_discussion => $each_discussion)
				{
					$my_review['discussion'][$k_discussion]['commentaire'] = $each_discussion['commentaire'];
					$my_review['discussion'][$k_discussion]['horodate'] = date('d/m/Y', time($each_discussion['horodate']));

					if ($each_discussion['origine'] == 'ecommercant')
						$my_review['discussion'][$k_discussion]['origine'] = $shop_name;
					elseif ($each_discussion['origine'] == 'internaute')
						$my_review['discussion'][$k_discussion]['origine'] = $my_review['customer_name'];
					else
						$my_review['discussion'][$k_discussion]['origine'] = $this->l('Moderator');
				}
			}

			array_push($reviews_list, $my_review);
		}

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$controller = new FrontController();
			$this->context->controller = $controller;
		}

		$this->context->controller->pagination((int)$stats_product['nb_reviews']);

		$this->context->smarty->assign(array(
			'current_url' =>  $_SERVER['REQUEST_URI'],
			'reviews' => $reviews_list,
			'count_reviews' => $stats_product['nb_reviews'],
			'average_rate' => round($stats_product['rate'], 1),
			'average_rate_percent' => $stats_product['rate'] * 20,
			'url_certificat' => $url_certificat
		));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return ($this->display(__FILE__, '/views/templates/hook/avisverifies-tab-content.tpl'));
		else
			return ($this->display(__FILE__, 'avisverifies-tab-content.tpl'));
	}

	public function hookPostUpdateOrderStatus($params)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$process_init = Configuration::get('AVISVERIFIES_PROCESSINIT');
			$order_status_choosen = Configuration::get('AVISVERIFIES_ORDERSTATESCHOOSEN');
			$id_website = configuration::get('AVISVERIFIES_IDWEBSITE');
			$secret_key = configuration::get('AVISVERIFIES_CLESECRETE');
		}
		else
		{
			$process_init = Configuration::get('AVISVERIFIES_PROCESSINIT', null, null, $this->context->shop->getContextShopID());
			$order_status_choosen = Configuration::get('AVISVERIFIES_ORDERSTATESCHOOSEN', null, null, $this->context->shop->getContextShopID());
			$id_website = configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $this->context->shop->getContextShopID());
			$secret_key = configuration::get('AVISVERIFIES_CLESECRETE', null, null, $this->context->shop->getContextShopID());
		}

		if (empty($id_website) || empty($secret_key))
			return;

		if (empty($process_init) || empty($order_status_choosen) || $process_init != 'onorderstatuschange')
			return;

		if (!Validate::isLoadedObject($params['newOrderStatus']))
			die($this->displayName.' -> Missing parameters');

		$new_order_status = $params['newOrderStatus'];
		$order = new Order((int)$params['id_order']);

		if ($order && !Validate::isLoadedObject($order))
			die($this->displayName.' -> Incorrect Order object.');

		if ($process_init == 'onorderstatuschange' && in_array($new_order_status->id, explode(';', $order_status_choosen)))
		{
			$o_av = new NetReviewsModel();
			$o_av->id_order = (int)$params['id_order'];
			$o_av->id_order_state = $new_order_status->id;
			$o_av->id_shop = (!empty($order->id_shop)) ? $order->id_shop : null;
			$o_av->id_lang_order = $order->id_lang;
			$o_av->saveOrderToRequest();
		}

		return true;
	}

	public function hookOrderConfirmation($params)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$process_init = Configuration::get('AVISVERIFIES_PROCESSINIT');
			$id_website = configuration::get('AVISVERIFIES_IDWEBSITE');
			$secret_key = configuration::get('AVISVERIFIES_CLESECRETE');
			$code_lang = configuration::get('AVISVERIFIES_CODE_LANG');
		}
		else
		{
			$process_init = Configuration::get('AVISVERIFIES_PROCESSINIT');
			$id_website = configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $this->context->shop->getContextShopID());
			$secret_key = configuration::get('AVISVERIFIES_CLESECRETE', null, null, $this->context->shop->getContextShopID());
			$code_lang = configuration::get('AVISVERIFIES_CODE_LANG', null, null, $this->context->shop->getContextShopID());
		}

		if (empty($id_website) || empty($secret_key))
			return;

		$code_lang = (!empty($code_lang)) ? $code_lang : 'undef';

		$o_order = $params['objOrder'];

		$id_order = Tools::getValue('id_order');

		if (!empty($o_order) && !empty($id_order))
		{
			$o_av = new NetReviewsModel();
			$o_av->id_order = (int)$id_order;

			if (!empty($o_order->current_state))
				$o_av->id_order_state = $o_order->current_state;

			if (!empty($o_order->id_shop))
				$o_av->id_shop = $o_order->id_shop;

			$o_av->id_lang_order = $o_order->id_lang;

			if ($process_init == 'onorder')
				$o_av->saveOrderToRequest();

			$order_total = ($o_order->total_paid) ? (100 * $o_order->total_paid) : 0;
			return "<img height='1' hspace='0' 
			src='//www.netreviews.eu/index.php?action=act_order&idWebsite=$id_website&langue=$code_lang&refCommande=$id_order&montant=$order_total' />";
		}

	}

	public function hookRightColumn()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$av_scriptfixe_allowed = Configuration::get('AVISVERIFIES_SCRIPTFIXE_ALLOWED');
			$av_scriptfixe_position = Configuration::get('AVISVERIFIES_SCRIPTFIXE_POSITION');
			$av_scriptfixe = Configuration::get('AVISVERIFIES_SCRIPTFIXE');
		}
		else
		{
			$av_scriptfixe_allowed = Configuration::get('AVISVERIFIES_SCRIPTFIXE_ALLOWED', null, null, $this->context->shop->getContextShopID());
			$av_scriptfixe_position = Configuration::get('AVISVERIFIES_SCRIPTFIXE_POSITION', null, null, $this->context->shop->getContextShopID());
			$av_scriptfixe = Configuration::get('AVISVERIFIES_SCRIPTFIXE', null, null, $this->context->shop->getContextShopID());
		}
		if ($av_scriptfixe_allowed != 'yes' || $av_scriptfixe_position != 'right')
			return;

		if ($av_scriptfixe)
			return "\n\n<div align='center'>".Tools::stripslashes(html_entity_decode($av_scriptfixe))."</div><br clear='left'/><br />";
	}



	public function hookLeftColumn()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$av_scriptfixe_allowed = Configuration::get('AVISVERIFIES_SCRIPTFIXE_ALLOWED');
			$av_scriptfixe_position = Configuration::get('AVISVERIFIES_SCRIPTFIXE_POSITION');
			$av_scriptfixe = Configuration::get('AVISVERIFIES_SCRIPTFIXE');
		}
		else
		{
			$av_scriptfixe_allowed = Configuration::get('AVISVERIFIES_SCRIPTFIXE_ALLOWED', null, null, $this->context->shop->getContextShopID());
			$av_scriptfixe_position = Configuration::get('AVISVERIFIES_SCRIPTFIXE_POSITION', null, null, $this->context->shop->getContextShopID());
			$av_scriptfixe = Configuration::get('AVISVERIFIES_SCRIPTFIXE', null, null, $this->context->shop->getContextShopID());
		}
		if ($av_scriptfixe_allowed != 'yes' || $av_scriptfixe_position != 'left')
			return;

		if ($av_scriptfixe)
			return "\n\n<div align='center'>".Tools::stripslashes(html_entity_decode($av_scriptfixe))."</div><br clear='left'/><br />";
	}

	public function hookExtraRight()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$display_prod_reviews = configuration::get('AVISVERIFIES_DISPLAYPRODREVIEWS');
		else
			$display_prod_reviews = configuration::get('AVISVERIFIES_DISPLAYPRODREVIEWS', null, null, $this->context->shop->getContextShopID());

		$id_product = (int)Tools::getValue('id_product');

		$o = new NetReviewsModel();
		$reviews = $o->getStatsProduct($id_product);

		if ($reviews['nb_reviews'] < 1 || $display_prod_reviews != 'yes') return ''; //Si Aucun avis, on retourne vide

		$this->context->smarty->assign(array(
						'av_nb_reviews' => $reviews['nb_reviews'],
						'av_rate' =>  $reviews['rate'],
						'av_rate_percent' =>  $reviews['rate'] * 20,
					));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return $this->display(__FILE__, 'views/templates/hook/avisverifies-extraright.tpl');
		else
			return $this->display(__FILE__, 'avisverifies-extraright.tpl');
	}

	public function uninstall()
	{
		Configuration::deleteByName('AVISVERIFIES_IDWEBSITE');
		Configuration::deleteByName('AVISVERIFIES_CLESECRETE');
		Configuration::deleteByName('AVISVERIFIES_PROCESSINIT');
		Configuration::deleteByName('AVISVERIFIES_ORDERSTATESCHOOSEN');
		Configuration::deleteByName('AVISVERIFIES_DELAY');
		Configuration::deleteByName('AVISVERIFIES_GETPRODREVIEWS');
		Configuration::deleteByName('AVISVERIFIES_DISPLAYPRODREVIEWS');
		Configuration::deleteByName('AVISVERIFIES_CSVFILENAME');
		Configuration::deleteByName('AVISVERIFIES_SCRIPTFLOAT');
		Configuration::deleteByName('AVISVERIFIES_SCRIPTFLOAT_ALLOWED');
		Configuration::deleteByName('AVISVERIFIES_SCRIPTFIXE');
		Configuration::deleteByName('AVISVERIFIES_SCRIPTFIXE_POSITION');
		Configuration::deleteByName('AVISVERIFIES_SCRIPTFIXE_ALLOWED');
		Configuration::deleteByName('AVISVERIFIES_URLCERTIFICAT');
		Configuration::deleteByName('AVISVERIFIES_FORBIDDEN_EMAIL');
		Configuration::deleteByName('AVISVERIFIES_CODE_LANG');

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			//PS < 1.5 Hooks
			$this->unregisterHook('productTabContent');
			$this->unregisterHook('productTab');
			$this->unregisterHook('extraRight');
			$this->unregisterHook('extraLeft');
			$this->unregisterHook('rightColumn');
			$this->unregisterHook('leftColumn');
			$this->unregisterHook('header');
			$this->unregisterHook('orderConfirmation');

			if (version_compare(_PS_VERSION_, '1.4', '<'))	//PS < 1.4 Hook
				$this->unregisterHook('updateOrderStatus');
			else
				$this->unregisterHook('postUpdateOrderStatus');
		}
		else
		{
			//PS >=1.5 Hooks
			$this->unregisterHook('displayProductTabContent');
			$this->unregisterHook('displayProductTab');
			$this->unregisterHook('displayRightColumnProduct');
			$this->unregisterHook('displayLeftColumnProduct');
			$this->unregisterHook('displayHeader');
			$this->unregisterHook('displayRightColumn');
			$this->unregisterHook('displayLeftColumn');
			$this->unregisterHook('displayOrderConfirmation');
			$this->unregisterHook('actionOrderStatusPostUpdate');
		}

		if (!parent::uninstall() || !$this->uninstallDatabase())
		{
			$this->context->controller->errors[] = $this->l('Table couldn\'t be deleted');
			return false;
		}

		return true;
	}

	/**
	 * Create tables
	 * @return boolean if succeed
	 */

	public function installDatabase()
	{
		$query = array();
		$query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'av_products_reviews;';
		$query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'av_products_average;';
		$query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'av_orders;';
		$query[] = '
					CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'av_products_reviews (
					  `id_product_av` varchar(36) NOT NULL,
					  `ref_product` varchar(20) NOT NULL,
					  `rate` varchar(5) NOT NULL,
					  `review` text NOT NULL,
					  `customer_name` varchar(30) NOT NULL,
					  `horodate` text NOT NULL,
					  `discussion` text,
					  `lang` varchar(5),
					  PRIMARY KEY (`id_product_av`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		$query[] = '
					CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'av_products_average` (
					  `id_product_av` varchar(36) NOT NULL,
					  `ref_product` varchar(20) NOT NULL,
					  `rate` varchar(5) NOT NULL,
					  `nb_reviews` int(10) NOT NULL,
					  `horodate_update` text NOT NULL,
					  `id_lang` varchar(5) DEFAULT NULL,
					  PRIMARY KEY (`ref_product`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		$query[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'av_orders` (
					  `id_order` int(11) NOT NULL,
					  `id_shop` int(2) DEFAULT NULL,
					  `flag_get` int(2) DEFAULT NULL,
					  `horodate_get` varchar(25) DEFAULT NULL,
					  `id_order_state` int(5) DEFAULT NULL,
					  `id_lang_order` int(5) DEFAULT NULL,
					  `horodate_now` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
					  PRIMARY KEY (`id_order`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		foreach ($query as $sql)
		{
			$error = false;
			if (!Db::getInstance()->Execute($sql))
			{
				$this->context->controller->errors[] = sprintf($this->l('SQL ERROR : %s | Query can\'t be executed. Maybe, check SQL user permissions.'),$sql);
				$error = true;
			}
		}

		if (Db::getInstance()->ExecuteS("SHOW tables LIKE '"._DB_PREFIX_."av_orders'"))
		{
			if (!Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'av_orders(id_order, flag_get) SELECT id_order,1 FROM '._DB_PREFIX_.'orders WHERE date_add > DATE_SUB(NOW(), INTERVAL 2 MONTH)'))
			{
				$this->context->controller->errors[] = $this->l('SQL ERROR : Inserting already getted orders | Query can\'t be executed. Maybe, check SQL user permissions.');
				$error = true;
			}
				
		}
		else
		{
			$this->context->controller->errors[] = $this->l('SQL ERROR : Table av_orders doest not exist |');
			$error = true;
		}

		return !$error;
	}

	/**
	 * Drop tables
	 * @return boolean if succeed
	 */

	public function uninstallDatabase()
	{
		$query = array();
		$query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'av_products_reviews';
		$query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'av_products_average';
		$query[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'av_orders';

		foreach ($query as $sql)
		{
			$error = false;
			if (!Db::getInstance()->Execute($sql))
			{
				$this->context->controller->errors[] =  sprintf($this->l('SQL ERROR : %s | Query can\'t be executed. Maybe, check SQL user permissions.'),$sql);
				$error = true;
			}
		}

		return !$error;
	}
}	
