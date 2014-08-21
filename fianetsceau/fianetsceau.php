<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once(_PS_MODULE_DIR_.'fianetsceau/lib/includes/includes.inc.php');

class FianetSceau extends Module
{

	/**
	 * Table of differents order statuses 
	 * @var Array 
	 */
	private $fianetsceau_order_statuses = array(
		1 => 'waiting payment',
		2 => 'sent',
		3 => 'error'
	);

	/**
	 * Table of differents status 
	 * @var Array 
	 */
	private $fianetsceau_statuses = array(
		'test',
		'prod',
	);

	/**
	 * Table of differents payment type 
	 * @var Array 
	 */
	private $payment_types = array(
		1 => 'Carte',
		2 => 'Chèque',
		3 => 'Contre remboursement',
		4 => 'Virement',
		5 => 'CB en n fois',
		6 => 'Paypal',
		7 => '1euro.com',
		8 => 'Kwixo',
	);

	/**
	 * Table of differents logo and widget position 
	 * @var Array 
	 */
	private $fianetsceau_positions = array('en' =>
		array(
			'nothing' => 'Select a position',
			'top' => 'Top of page',
			'bottom' => 'Bottom of page',
			'left' => 'In the left column',
			'right' => 'In the right column'
		),
		'fr' => array(
			'nothing' => 'Sélectionnez une position',
			'top' => 'Haut de page',
			'bottom' => 'Bas de page',
			'left' => 'Colonne de gauche',
			'right' => 'Colonne de droite'
		)
	);

	/**
	 * Table of differents logo size 
	 * @var Array 
	 */
	private $fianetsceau_logo_sizes = array(
		'120' => 'logo_120.png',
		'150' => 'logo_150.png',
		'170' => 'logo_170.png',
		'190' => 'logo_190.png',
	);

	/**
	 * Table of differents widgets type 
	 * @var Array 
	 */
	private $fianetsceau_widgets = array(
		1 => array('background' => 'blanc', 'shape' => 'squared'),
		2 => array('background' => 'transparent', 'shape' => 'squared'),
		3 => array('background' => 'blanc', 'shape' => 'circle'),
		4 => array('background' => 'transparent', 'shape' => 'circle'),
		5 => array('background' => 'blanc', 'shape' => 'comment'),
		6 => array('background' => 'transparent', 'shape' => 'comment'),
	);
	private $fianetsceau_categories = array();
	private $fianetsceau_subcategories = array();

	const SCEAU_ORDER_TABLE_NAME = 'fianetsceau_order';
	const SCEAU_STATE_TABLE_NAME = 'fianetsceau_state';
	const SCEAU_CATEGORY_TABLE_NAME = 'fianetsceau_category';
	const SCEAU_PRODUCT_COMMENTS_TABLE_NAME = 'fianetsceau_product_comments';
	const SCEAU_COMMENTS_TABLE_NAME = 'fianetsceau_comments';
	const SCEAU_MAX_STAR = 5;
	const SCEAU_MAX_COMMENTS_PER_PAGE = 4;

	public function __construct()
	{
		include(_PS_MODULE_DIR_.'fianetsceau/lib/includes/fianetcategories.inc.php');
		$this->fianetsceau_categories = $fianetsceau_categories;
		$this->fianetsceau_subcategories = $fianetsceau_subcategories;
		
		$this->name = 'fianetsceau';
		$this->version = '2.8';
		$this->tab = 'payment_security';
		$this->author = 'Fia-Net';
		$this->displayName = $this->l('Fia-Net - Sceau de Confiance');
		$this->description = $this->l('Turn your visitors into buyers by creating confidence in your site.');
		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
		{
			$this->backward_error = $this->l('In order to work properly in PrestaShop v1.4, 
				the FIA-NET Sceau de confiance module requiers the backward compatibility module at least v0.3.').'<br />';
			$this->backward_error .= $this->l('You can download this module for free here: 
				http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');
			if (file_exists(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php'))
			{
				include(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php');
				$this->backward = true;
			}
			else
				$this->warning = $this->backward_error;
		}
		else
			$this->backward = true;

		parent::__construct();

		//check Sceau update
		$this->checkSceauUpdate();
	}

	public function install()
	{
		//create log file
		SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Création du fichier de log');

		/** database tables creation * */
		$sqlfile = dirname(__FILE__).'/install.sql';
		if (!file_exists($sqlfile) || !($sql = Tools::file_get_contents($sqlfile)))
			return false;

		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = str_replace('SCEAU_ORDER_TABLE_NAME', self::SCEAU_ORDER_TABLE_NAME, $sql);
		$sql = str_replace('SCEAU_STATE_TABLE_NAME', self::SCEAU_STATE_TABLE_NAME, $sql);
		$sql = str_replace('SCEAU_CATEGORY_TABLE_NAME', self::SCEAU_CATEGORY_TABLE_NAME, $sql);
		$sql = str_replace('SCEAU_COMMENTS_TABLE_NAME', self::SCEAU_COMMENTS_TABLE_NAME, $sql);
		$sql = str_replace('SCEAU_PRODUCT_COMMENTS_TABLE_NAME', self::SCEAU_PRODUCT_COMMENTS_TABLE_NAME, $sql);
		$queries = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($queries as $query)
			if (!Db::getInstance()->Execute(trim($query)))
			{
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Installation failed, database creation failed : '.Db::getInstance()->getMsgError());
				return false;
			}

		//Sceau order statuses insertion
		foreach ($this->fianetsceau_order_statuses as $id => $label)
		{
			$sql = 'INSERT INTO `'._DB_PREFIX_.self::SCEAU_STATE_TABLE_NAME."` (`id_fianetsceau_state`,`label`) 
				VALUES ('".(int)$id."','".pSQL((string)$label)."')";
			$insert = Db::getInstance()->execute($sql);
			if (!$insert)
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Insertion state'.$id.$label.' échouée : '.Db::getInstance()->getMsgError());
		}

		//tabs creation
		$tab_admin_id_order = Tab::getIdFromClassName('AdminOrders');

		//AdminSceauController registration
		$tab_controller_main = new Tab();
		$tab_controller_main->active = 1;
		$tab_controller_main->class_name = 'AdminSceau';
		foreach (Language::getLanguages() as $language)
			$tab_controller_main->name[$language['id_lang']] = 'Sceau de Confiance';
		$tab_controller_main->id_parent = $tab_admin_id_order;
		$tab_controller_main->module = $this->name;
		$tab_controller_main->add();
		$tab_controller_main->move($this->getNewLastPosition(0));

		//update kwixo version on configuration
		Configuration::updateValue('FIANETSCEAU_MODULE_VERSION', $this->version);

		//Hook register
		return (parent::install()
			&& $this->registerHook('newOrder')
			&& $this->registerHook('adminOrder')
			&& $this->registerHook('backOfficeHeader')
			&& $this->registerHook('top')
			&& $this->registerHook('footer')
			&& $this->registerHook('leftColumn')
			&& $this->registerHook('rightColumn')
			&& $this->registerHook('postUpdateOrderStatus')
			&& $this->registerHook('paymentTop')
			&& $this->registerHook('header')
			&& $this->registerHook('productfooter')

			);
	}
	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		else
		{
			//delete sceau tab
			$id = Tab::getIdFromClassName('AdminSceau');
			$tab = new Tab($id);
			$tab->delete();

			//drop sceau state table
			Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::SCEAU_STATE_TABLE_NAME.'`');

			//drop sceau category table
			Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::SCEAU_CATEGORY_TABLE_NAME.'`');

			return true;
		}
	}

	/**
	 * Load css and javascript file
	 * 
	 * 
	 * @return html 
	 */
	public function hookbackOfficeHeader()
	{
		if (_PS_VERSION_ < '1.5')
			Tools::addCSS($this->_path.'/css/toolbarAdmin.css', 'all');
		else
			$this->context->controller->addCSS($this->_path.'/css/toolbarAdmin.css', 'all');
	}
	/**
	 * insert info into fianetsceau table with fields in parameters
	 *
	 * @param array $fields
	 * @return boolean
	 */
	public function insertDBElement(array $fields, $table_name)
	{
		$fieldnames = '';
		$fieldvalues = '';
		foreach ($fields as $key_field => $elem)
		{
			$fieldnames .= '`'.$this->bqSQL($key_field).'`,';
			$fieldvalues .= "'".pSQL($elem)."',";
		}

		$fieldvalues = Tools::substr($fieldvalues, 0, -1);
		$fieldnames = Tools::substr($fieldnames, 0, -1);

		$sql = 'INSERT INTO `'.$table_name.'` ('.$fieldnames.') VALUES ('.$fieldvalues.')';

		$inserted = Db::getInstance()->execute($sql);
		if (!$inserted)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Insertion failed : '.Db::getInstance()->getMsgError());
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Insertion failed : '.$sql);
			return false;
		}

		return true;
	}

	/**
	 * Check if id_cart exists on fianetsceau_order table
	 * 
	 * @param int 
	 * @return bool 
	 */
	private function checkCartID($id_cart)
	{
		$sql = 'SELECT `id_cart` FROM `'._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME."` WHERE `id_cart` = '".(int)$id_cart."'";

		$query_result = Db::getInstance()->getRow($sql);

		if ($query_result != null)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'id_cart found :'.$id_cart);
			return true;
		}
		else
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'id_cart not found, id_cart added :'.$id_cart);
			return false;
		}
	}

	/**
	 * update fianetsceau_order table with fields in parameters
	 * 
	 * @param int $id_order
	 * @param array $fields
	 * @return boolean 
	 */
	private function updateOrder($id_order, array $fields, $update_by_id_cart = false)
	{
		$set_string = '';
		foreach ($fields as $fieldname => $fieldvalue)
			$set_string .= '`'.$this->bqSQL($fieldname)."` = '".pSQL($fieldvalue)."', ";
		$set_string = Tools::substr($set_string, 0, '-2');
		if (!$update_by_id_cart)
			$sql = 'UPDATE `'._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME.'` SET '.$set_string.' WHERE `id_order` = '.(int)$id_order;
		else
			$sql = 'UPDATE `'._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME.'` SET '.$set_string.' WHERE `id_cart` = '.(int)$id_order;
		SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, $sql);

		$updated = Db::getInstance()->execute($sql);
		if (!$updated)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Update $id_order failed : '.Db::getInstance()->getMsgError());
			return false;
		}
		return true;
	}

	/**
	 * returns the IP address of the customer who passed the order $id_order
	 *
	 * @param int $id_order
	 * @return string
	 */
	private function getCustomerIP($id_order)
	{
		$sql = 'SELECT `customer_ip_address` FROM `'._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME."` WHERE `id_order` = '".(int)$id_order."'";
		return (Db::getInstance()->getValue($sql));
	}

	/**
	 * return Fianet payment type used for payment
	 *
	 * @param int $id_order
	 * @return int
	 */
	private function getPaymentFianetType($id_order)
	{
		$order = new Order($id_order);
		$payments = $this->loadPaymentMethods();
		foreach ($payments as $element)
			if ($order->payment == $element['name'])
				$type = ($element['fianetsceau_type']);

		if ($type == null)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Order '.$id_order.' : payment defaut type = 1'.$type);
			return '1';
		}
		else
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Order '.$id_order.' : payment type = '.$type);
			return $type;
		}
	}

	/**
	 * build xml with customer's and order's data et send it to FIA-NET
	 * 
	 * @param int $id_order
	 * @return boolean 
	 */
	public function sendXML($id_order)
	{
		$order = new Order($id_order);
		$customer = new Customer($order->id_customer);
		if (_PS_VERSION_ < '1.5')
		{
			$civility = $customer->id_gender;
			$accepted_civility = array('1', '2', '3');
			if (!(in_array($civility, $accepted_civility)))
				$civility = '1';

			$id_shop = 1;
			$fianetsceau = new Sceau();
		}
		else
		{
			$gender = new Gender($customer->id_gender);
			$id_lang = Language::getIdByIso($this->context->language->iso_code);
			$civility = $gender->name[$id_lang] == 'Mr.' ? 1 : 2;

			$id_shop = (int)$order->id_shop;
			$fianetsceau = new Sceau($id_shop);
		}
		$token = Tools::getAdminToken($fianetsceau->getSiteid().$fianetsceau->getAuthkey().$fianetsceau->getLogin());
		$lang = Language::getIsoById($order->id_lang) == 'fr' ? 'fr' : 'uk';

		$sceaucontrol = new SceauControl();

		$sceaucontrol->createCustomer('', $civility, $customer->lastname, $customer->firstname, Tools::strtolower($customer->email));

		$order_details = $sceaucontrol->createOrderDetails($id_order, $fianetsceau->getSiteid(), $order->total_paid, 'EUR', $this->getCustomerIP($id_order),
			$order->date_add, $lang);

		//get default FIA-NET category
		$default_product_type = $this->getFianetSubCategoryId(0, $id_shop);

		$products = $order->getProducts();
		$link = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'],
				ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/fianetsceau/commentsmanager.php?token='.$token;

		$productsceau = $order_details->createOrderProducts();
		$productsceau->createUrlwebservice($link);

		foreach ($products as $product)
		{

			$product_categories = Product::getProductCategories((int)$product['product_id']);
			$product_category = array_pop($product_categories);

			$prod = new Product($product['product_id']);

			//gets the product FIA-NET category
			$product_type = $this->getFianetSubCategoryId($product_category, $id_shop);

			if (!empty($prod->ean13) && ($prod->ean13 != 0 || $prod->ean13 != ''))
				$codeean = $prod->ean13;
			else
				$codeean = null;

			$reference = $product['product_id'];

			if ($product_type)//if a FIA-NET category is set: the type attribute takes the product FIA-NET type value
				$fianet_type = $product_type;
			else //if FIA-NET category not set: the type attribute takes the default value
				$fianet_type = $default_product_type;
			//get product image
			$images = Product::getCover($product['product_id']);

			if (!empty($images))
			{
				$image = new Image($images['id_image']);
				if (method_exists($image, 'getExistingImgPath'))
					$image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().'.'.$image->image_format;
				else
					$image_url = null;
			}
			$image_url = null;
			$product_name = str_replace("'", '', $product['product_name']);
			$product_name = str_replace('&', '', $product_name);
			$productsceau->createProduct($codeean, $reference, $fianet_type, $product_name,
				(string)$product['product_price_wt'], $image_url);
		}

		$sceaucontrol->createPayment($this->getPaymentFianetType($id_order));

		$fianetsceau->addCrypt($sceaucontrol);
		$result = $fianetsceau->sendSendrating($sceaucontrol);

		if (isXMLstringSceau($result))
		{
			$resxml = new SceauSendratingResponse($result);

			if ($resxml->isValid())
			{
				//update fianetsceau_state 2:sent
				$this->updateOrder($id_order, array('id_fianetsceau_state' => '2'));
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Order '.$id_order.' sended');
				if (Configuration::get('FIANETSCEAU_CONFIGURATION_OK') === false)
					Configuration::updateValue('FIANETSCEAU_CONFIGURATION_OK', 1);
				return true;
			}
			else
			{
				//update fianetsceau_state 3:error
				$this->updateOrder($id_order, array('id_fianetsceau_state' => '3'));
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Order '.$id_order.' XML send error');
				return false;
			}
		}
		else
		{
			//result is empty
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Order '.$id_order.' XML result is empty');
			return false;
		}
	}

	/**
	 * Send XML data to FIA-NET when Kwixo payment is accepted with status : Kwixo paiement accepté - score vert
	 * 
	 * @param type $params 
	 */
	public function hookPostUpdateOrderStatus($params)
	{
		$order_state = $params['newOrderStatus'];
		$status_founded = strpos($order_state->name, 'score vert');
		if ($status_founded != false || $order_state->id == 2)
		{
			//send xml to FIA-NET when Kwixo payment is confirmed
			$id_order = $params['id_order'];
			$this->sendXML((int)$id_order);
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, $order_state->name);
		}
		else
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Statut de paiement non reconnu pour envoi : '.$order_state->name);
	}

	/**
	 * Show FIA-NET Sceau status on detail order if FIANETSCEAU_SHOW_STATUS_ORDER is enabled
	 * 
	 * 
	 * @param type $params
	 * @return type 
	 */
	public function hookAdminOrder($params)
	{
		//$this->sendXML($params['id_order']);
		//check if show status option is enabled in module settings
		if (!Configuration::get('FIANETSCEAU_SHOW_STATUS_ORDER'))
			return false;

		$id_order = $params['id_order'];

		//retrieve order's status label
		$sql = 'SELECT fs.`label` FROM `'._DB_PREFIX_.self::SCEAU_STATE_TABLE_NAME.'` fs INNER JOIN `'._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME.'` fo 
				ON fo.`id_fianetsceau_state`=fs.`id_fianetsceau_state` 
			WHERE  fo.`id_order`='.(int)$id_order;

		$order_state_label = Db::getInstance()->getValue($sql);

		//if label exist, we load the corresponding tpl
		if (!($order_state_label === false))
		{
			$tpl_name = preg_replace('#[^a-zA-Z0-9]#', '_', $order_state_label);

			if ($tpl_name == 'sent')
				$img = 'sent.gif';
			elseif ($tpl_name == 'waiting_payment')
				$img = 'waiting.gif';
			elseif ($tpl_name == 'error')
				$img = 'not_concerned.png';

			$link = 'index.php?tab=AdminSceau&action=ResendOrder&id_order='.(int)$id_order.'&token='.Tools::getAdminTokenLite('AdminSceau');

			$this->smarty->assign(array(
				'fianetsceau_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/'.$img,
				'link' => $link,
				'resend_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/sceauresend14.png',
				'logo_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/logo.gif',
			));

			return $this->display(__FILE__, '/views/templates/admin/'.$tpl_name.'.tpl');
		}
		else
		{
			//if label doesn't exist, we load nosend.tpl, order was sent before FIA-NET Sceau installation
			$img = 'error.gif';
			$this->smarty->assign(array(
				'fianetsceau_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/'.$img,
				'logo_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/logo.gif',
			));
			return $this->display(__FILE__, '/views/templates/admin/nosend.tpl');
		}
	}

	/**
	 * Insert id_cart on fianetsceau_order table when page payment is called
	 * 
	 * @param type Array 
	 */
	public function hookPaymentTop($params)
	{
		$id_cart = $params['cart']->id;
		if ($this->checkCartID($id_cart) == false)
			$this->insertDBElement(array('id_cart' => (int)$id_cart,
				'id_fianetsceau_state' => '0',
				'customer_ip_address' => Tools::getRemoteAddr(),
				'date' => date('Y-m-d H:i:s')), _DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME);
	}
	/**
	 * Insert a new order on id_fianetsceau_state table when a new order arrives
	 * 
	 * @param type Array 
	 */
	public function hookNewOrder($params)
	{
		//insert data into id_fianetsceau_order when new order arrives
		$order = $params['order'];
		$order->id_cart;
		$this->updateOrder((int)$order->id_cart,
			array('id_order' => (int)$order->id,
				'id_fianetsceau_state' => '1',
				'customer_ip_address' => Tools::getRemoteAddr(),
				'date' => $order->date_add), true);
	}
	/**
	 * Load logo or/and widget tpl with top position
	 * 
	 * @return hmtl 
	 */
	public function hookTop()
	{
		return $this->displayLogoWidget('top');
	}

	/**
	 * Load logo or/and widget tpl with bottom position
	 * 
	 * @return html 
	 */
	public function hookFooter()
	{
		return $this->displayLogoWidget('bottom');
	}

	/**
	 * Load logo or/and widget tpl with left position
	 * 
	 * @return html 
	 */
	public function hookLeftColumn()
	{
		return $this->displayLogoWidget('left');
	}

	/**
	 * Load logo or/and widget tpl with right position
	 * 
	 * @return html 
	 */
	public function hookRightColumn()
	{
		return $this->displayLogoWidget('right');
	}
	
	public function displayLogoWidget($position)
	{		
		$html = '';
		if (Configuration::get('FIANETSCEAU_LOGO_POSITION') == $position)
			$html = $this->loadLogoTPL();
		if (Configuration::get('FIANETSCEAU_WIDGET_POSITION') == $position)
			$html .= $this->loadWidgetTPL();
		
		return $html == '' ? false : $html;
	}

	/**
	 * load front-office logo's template
	 * 
	 * @return type 
	 */
	private function loadLogoTPL()
	{
		//retrieve logo size on database and load the corresponding image
		$this->smarty->assign(array(
			'siteid' => Configuration::get('FIANETSCEAU_SITEID'),
			'fianetsceau_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/'.$this->fianetsceau_logo_sizes[Configuration::get('FIANETSCEAU_LOGO_SIZE')]
		));

		return $this->display(__FILE__, '/views/templates/front/fianetsceau.tpl');
	}

	/**
	 * load front-office widget's template
	 *
	 * @return type 
	 */
	private function loadWidgetTPL()
	{
		//retrieve widget number on database and load the corresponding widget
		$host = $_SERVER['HTTP_HOST'];
		$url_site = 'http://www.'.$host;
		$string_site = explode('.', $host);
		$nom_site = $string_site[0];

		$this->smarty->assign(array(
			'siteid' => Configuration::get('FIANETSCEAU_SITEID'),
			'shape' => $this->fianetsceau_widgets[Configuration::get('FIANETSCEAU_WIDGET_NUMBER')]['shape'],
			'background' => $this->fianetsceau_widgets[Configuration::get('FIANETSCEAU_WIDGET_NUMBER')]['background'],
			'url_site' => $url_site,
			'nom_site' => $nom_site,
		));

		if ($this->fianetsceau_widgets[Configuration::get('FIANETSCEAU_WIDGET_NUMBER')]['shape'] == 'comment')
			return $this->display(__FILE__, '/views/templates/front/fianetsceau_widget_comments.tpl');
		else
			return $this->display(__FILE__, '/views/templates/front/fianetsceau_widget.tpl');
	}

	/**
	 * save all admin settings on database
	 *
	 * @return boolean 
	 */
	protected function processForm()
	{
		if (!$this->formIsValid())
			return false;

		//update configuration
		Configuration::updateValue('FIANETSCEAU_LOGIN', Tools::getValue('fianetsceau_login'));
		Configuration::updateValue('FIANETSCEAU_PASSWORD', Tools::getValue('fianetsceau_password'));
		Configuration::updateValue('FIANETSCEAU_SITEID', Tools::getValue('fianetsceau_siteid'));
		Configuration::updateValue('FIANETSCEAU_AUTHKEY', Tools::getValue('fianetsceau_authkey'));
		Configuration::updateValue('FIANETSCEAU_STATUS', Tools::getValue('fianetsceau_status'));
		Configuration::updateValue('FIANETSCEAU_LOGO_POSITION', Tools::getValue('fianetsceau_logo_position'));
		Configuration::updateValue('FIANETSCEAU_LOGO_SIZE', Tools::getValue('fianetsceau_logo_sizes'));
		Configuration::updateValue('FIANETSCEAU_WIDGET_POSITION', Tools::getValue('fianetsceau_widget_position'));
		Configuration::updateValue('FIANETSCEAU_WIDGET_NUMBER', Tools::getValue('fianetsceau_widget_number'));
		Configuration::updateValue('FIANETSCEAU_SHOW_STATUS_ORDER', ((int)Tools::getValue('fianetsceau_showstatus') == 1 ? '1' : '0'));
		if (_PS_VERSION_ < '1.5')
			$id_shop = 1;
		else
			$id_shop = Context::getContext()->shop->id;
		//update default category value
		$this->manageFianetSubCategory(0, Tools::getValue('fianetsceau_0_subcategory'), 1, $id_shop);
		/** categories configuration * */
		//lists all product categories and update FIA-NET categories
		$shop_categories = $this->loadProductCategories();
		foreach (array_keys($shop_categories) as $id)
			$this->manageFianetSubCategory($id, Tools::getValue('fianetsceau_'.$id.'_subcategory'), 0, $id_shop);

		/** update payment means settings * */
		//list of payment means of the shop
		$payment_modules = $this->loadPaymentMethods();
		foreach (array_keys($payment_modules) as $id)
			Configuration::updateValue('FIANETSCEAU_'.$id.'_PAYMENT_TYPE', Tools::getValue('fianetsceau_'.$id.'_payment_type'));

		SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Configuration module mise à jour');
		return true;
	}

	/**
	 * returns true if the form is valid, false otherwise
	 * 
	 * @return boolean
	 */
	private function formIsValid()
	{
		$iso_lang_current = Language::getIsoById($this->context->language->id);

		//check login
		if (Tools::strlen(Tools::getValue('fianetsceau_login')) < 1)
			$this->_errors[] = $this->l("Login can't be empty");

		//check password
		if (Tools::strlen(Tools::getValue('fianetsceau_password')) < 1)
			$this->_errors[] = $this->l("Password can't be empty");

		//check site ID
		if (Tools::strlen(Tools::getValue('fianetsceau_siteid')) < 1)
			$this->_errors[] = $this->l("Siteid can't be empty");

		if (!preg_match('#^[0-9]+$#', Tools::getValue('fianetsceau_siteid')))
			$this->_errors[] = $this->l('Siteid has to be integer. '.gettype(Tools::getValue('fianetsceau_siteid')).' given.');

		//check authkey
		if (Tools::strlen(Tools::getValue('fianetsceau_authkey')) < 1)
			$this->_errors[] = $this->l("Authkey can't be empty");

		//check status
		if (!in_array(Tools::getValue('fianetsceau_status'), $this->fianetsceau_statuses))
			$this->_errors[] = $this->l('You must give a correct status.');

		//check default product type
		if (!in_array(Tools::getValue('fianetsceau_0_subcategory'), array_keys($this->fianetsceau_subcategories)))
			$this->_errors[] = $this->l('You must configure a valid default product type');

		//check logo and widget position
		/*if (Tools::getValue('fianetsceau_logo_position') == 'nothing' && Tools::getValue('fianetsceau_widget_position') == 'nothing')
			$this->_errors[] = $this->l('You must select a logo or a widget position');*/

		//payment means check
		$shop_payments = $this->loadPaymentMethods();
		foreach ($shop_payments as $id => $shop_payment)
		{
			if (!in_array(Tools::getValue('fianetsceau_'.$id.'_payment_type'), array_keys($this->payment_types)))
				$this->_errors[] = $this->l("The payment type for '".$shop_payment['name']."' is not valid");
		}

		//check sceau show status
		if (!in_array(Tools::getValue('fianetsceau_showstatus'), array('0', '1')))
			$this->_errors[] = $this->l('You must give a correct FIA-NET show status value');

		//check logo position
		if (!in_array(Tools::getValue('fianetsceau_logo_position'), array_keys($this->fianetsceau_positions[$iso_lang_current])))
			$this->_errors[] = $this->l('You must give a correct logo position');

		//check widget position
		if (!in_array(Tools::getValue('fianetsceau_widget_position'), array_keys($this->fianetsceau_positions[$iso_lang_current])))
			$this->_errors[] = $this->l('You must give a correct widget position');

		//check logo size
		if (!in_array(Tools::getValue('fianetsceau_logo_sizes'), array_keys($this->fianetsceau_logo_sizes)))
			$this->_errors[] = $this->l('You must give a correct logo size');

		//check widget type
		if (!in_array(Tools::getValue('fianetsceau_widget_number'), array_keys($this->fianetsceau_widgets)))
			$this->_errors[] = $this->l('You must give a correct widget type');

		//categories check
		$shop_categories = $this->loadProductCategories();
		foreach ($shop_categories as $id => $shop_category)
			if (Tools::getValue('fianetsceau_'.$id.'_subcategory') != 0)
				if (!in_array(Tools::getValue('fianetsceau_'.$id.'_subcategory'), (array_keys($this->fianetsceau_subcategories))))
					$this->_errors[] = $this->l('Invalid product type for category:')." '".$shop_category['name']."'";

		return empty($this->_errors);
	}

	public function getContent()
	{
		$head_msg = '';
		//if some POST datas are found
		//Get log file
		$log_content = htmlentities(SceauLogger::getLogContent());

		if (_PS_VERSION_ < '1.5')
		{
			$id_shop = 1;
			$fianetsceau = new Sceau();
		}
		else
		{
			$id_shop = Context::getContext()->shop->id;
			$fianetsceau = new Sceau($id_shop);
		}

		if (Tools::isSubmit('submitSettings'))
		{
			//if the form is correctly saved
			if ($this->processForm())
			//adds a confirmation message
				$head_msg = $this->displayConfirmation($this->l('Configuration updated.'));
			else
			{ //if errors have been encountered while validating the form
				//add an error message informing about errors that have been encountered
				$error_msg = $this->l('Some errors have been encoutered while updating configuration.');
				$error_msg .= '<ul>';
				foreach ($this->_errors as $error_label)
					$error_msg .= '<li>'.$error_label.'</li>';

				$error_msg .= '</ul>';
				$head_msg = $this->displayError($error_msg);
			}
			//if submit form, we save data form
			$login = (Tools::isSubmit('fianetsceau_login') ? Tools::getValue('fianetsceau_login') : $fianetsceau->getLogin());
			$password = (Tools::isSubmit('fianetsceau_password') ? Tools::getValue('fianetsceau_password') : $fianetsceau->getPassword());
			$siteid = (Tools::isSubmit('fianetsceau_siteid') ? Tools::getValue('fianetsceau_siteid') : $fianetsceau->getSiteid());
			$authkey = (Tools::isSubmit('fianetsceau_authkey') ? Tools::getValue('fianetsceau_authkey') : $fianetsceau->getAuthkey());
			$status = (Tools::isSubmit('fianetsceau_status') ? Tools::getValue('fianetsceau_status') : $fianetsceau->getStatus());
			$fianetsceau_logo_position = Tools::getValue('fianetsceau_logo_position');
			$fianetsceau_widget_position = Tools::getValue('fianetsceau_widget_position');
			$fianetsceau_logo = Tools::getValue('fianetsceau_logo_sizes');
			$widget_number = Tools::getValue('fianetsceau_widget_number');
			$show_status = Tools::getValue('fianetsceau_showstatus');
			$fianetsceau_default_category = Tools::getValue('fianetsceau_0_category');
			$fianetsceau_default_subcategory = Tools::getValue('fianetsceau_0_subcategory');
		}
		else
		{
			//if no submit form and 0 value in database, we put a defaut value
			$login = (Configuration::get('FIANETSCEAU_LOGIN') === false ? '' : Configuration::get('FIANETSCEAU_LOGIN'));
			$password = (Configuration::get('FIANETSCEAU_PASSWORD') === false ? '' : Configuration::get('FIANETSCEAU_PASSWORD'));
			$siteid = (Configuration::get('FIANETSCEAU_SITEID') === false ? '' : Configuration::get('FIANETSCEAU_SITEID'));
			$authkey = (Configuration::get('FIANETSCEAU_AUTHKEY') === false ? '' : Configuration::get('FIANETSCEAU_AUTHKEY'));
			$status = (Configuration::get('FIANETSCEAU_STATUS') === false ? 'test' : Configuration::get('FIANETSCEAU_STATUS'));
			$fianetsceau_logo_position = (Configuration::get('FIANETSCEAU_LOGO_POSITION') === false ?
				'left' : Configuration::get('FIANETSCEAU_LOGO_POSITION'));
			$fianetsceau_widget_position = (Configuration::get('FIANETSCEAU_WIDGET_POSITION') === false ?
				'left' : Configuration::get('FIANETSCEAU_WIDGET_POSITION'));
			$fianetsceau_logo = (Configuration::get('FIANETSCEAU_LOGO_SIZE') === false ? '120' : Configuration::get('FIANETSCEAU_LOGO_SIZE'));
			$widget_number = (Configuration::get('FIANETSCEAU_WIDGET_NUMBER') === false ? '1' : Configuration::get('FIANETSCEAU_WIDGET_NUMBER'));
			$show_status = (Configuration::get('FIANETSCEAU_SHOW_STATUS_ORDER') === false ? '0' : Configuration::get('FIANETSCEAU_SHOW_STATUS_ORDER'));
			$fianetsceau_default_subcategory = $this->getFianetSubCategoryId(0, $id_shop);
			$fianetsceau_default_category = $this->getFianetCategoryId($fianetsceau_default_subcategory);
		}

		//token
		$token = Tools::getAdminToken($fianetsceau->getSiteid().$fianetsceau->getAuthkey().$fianetsceau->getLogin());

		//listing payment type of the shop
		$payment_modules = $this->loadPaymentMethods();

		//lists all categories
		$shop_categories = $this->loadProductCategories();

		$base_url = __PS_BASE_URI__;
		$logo_sizes = array();
		foreach ($this->fianetsceau_logo_sizes as $size => $img_name)
			$logo_sizes[$size] = $base_url.'modules/'.$this->name.'/img/'.$img_name;

		$iso_lang_current = $this->context->language->iso_code;

		//give smarty data to the view
		$this->smarty->assign(array(
			'head_message' => $head_msg,
			'image_path' => $base_url.'modules/'.$this->name.'/img/sceaudeconfiance.png',
			'fianetsceau_login' => Tools::safeOutput($login),
			'fianetsceau_password' => Tools::safeOutput($password),
			'fianetsceau_siteid' => Tools::safeOutput($siteid),
			'fianetsceau_authkey' => Tools::safeOutput($authkey),
			'fianetsceau_status' => Tools::safeOutput($status),
			'fianetsceau_statuses' => $this->fianetsceau_statuses,
			'payment_modules' => $payment_modules,
			'fianetsceau_payment_types' => $this->payment_types,
			'logo_account_path' => $base_url.'modules/'.$this->name.'/img/account.gif',
			'logo_payments_path' => $base_url.'modules/'.$this->name.'/img/payments.gif',
			'fianetsceau_logo_positions' => $this->fianetsceau_positions[$iso_lang_current],
			'fianetsceau_logo_position' => Tools::safeOutput($fianetsceau_logo_position),
			'fianetsceau_logo_sizes' => $logo_sizes,
			'fianetsceau_logo' => (int)$fianetsceau_logo,
			'fianetsceau_widget_positions' => $this->fianetsceau_positions[$iso_lang_current],
			'fianetsceau_widget_position' => Tools::safeOutput($fianetsceau_widget_position),
			'fianetsceau_widget_numbers' => array_keys($this->fianetsceau_widgets),
			'widget_number' => (int)$widget_number,
			'path_prefix' => $base_url.'modules/'.$this->name.'/img',
			'fianetsceaushow_status' => (int)$show_status,
			'log_content' => $log_content,
			'shop_categories' => $shop_categories,
			'fianetsceau_default_category' => Tools::safeOutput($fianetsceau_default_category),
			'fianetsceau_default_subcategory' => Tools::safeOutput($fianetsceau_default_subcategory),
			'logo_categories_path' => $base_url.'modules/'.$this->name.'/img/categories.gif',
			'fianetsceau_categories' => $this->fianetsceau_categories,
			'fianetsceau_subcategories' => $this->fianetsceau_subcategories,
			'token' => $token,
			'id_shop' => $id_shop,
		));

		//view selection by prestashop 1.4 or 1.5

		return $this->display(__FILE__, '/views/templates/admin/admin.tpl');
	}

	/**
	 * returns payment means of the shop in an array which have module ID in indice with module's name and FIA-NET payment type. 
	 * 
	 * @return array
	 */
	private function loadPaymentMethods()
	{
		if (_PS_VERSION_ >= '1.5')
			$payments = Module::getPaymentModules();
		else
			$payments = $this->getInstalledPaymentModules();

		$payment_modules = array();

		foreach ($payments as $payment)
		{
			$module = Module::getInstanceById($payment['id_module']);
			$payment_modules[$payment['id_module']] = array(
				'name' => $module->displayName,
				'fianetsceau_type' => Configuration::get('FIANETSCEAU_'.$payment['id_module'].'_PAYMENT_TYPE'),
			);
		}
		return $payment_modules;
	}

	/**
	 * List all installed and active payment modules
	 * @see Module::getPaymentModules() if you need a list of module related to the user context
	 *
	 * @since 1.4.5
	 * @return array module informations
	 */
	public static function getInstalledPaymentModules()
	{
		return Db::getInstance()->executeS('
			SELECT DISTINCT m.`id_module`, h.`id_hook`, m.`name`, hm.`position`
			FROM `'._DB_PREFIX_.'module` m
			LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
			LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
			WHERE h.`name` = \'payment\'
			AND m.`active` = 1
		');
	}

	/**
	 * For Prestashop < 1.4.5
	 * Return an available position in subtab for parent $id_parent
	 * 
	 * @param int $id_parent
	 * @return int 
	 */
	public function getNewLastPosition($id_parent)
	{
		return Db::getInstance()->getValue('SELECT IFNULL(MAX(position),0)+1 FROM `'._DB_PREFIX_.'tab` WHERE `id_parent` = '.(int)$id_parent);
	}

	/**
	 * For Prestashop < 1.4.5
	 * Protect SQL queries
	 */
	public function bqSQL($string)
	{
		return str_replace('`', '\`', pSQL($string));
	}

	/**
	 * returns the product categories list as an array indexed by category_id
	 * 
	 * @return array
	 */
	private function loadProductCategories()
	{
		$categories = Category::getSimpleCategories($this->context->language->id);

		if (_PS_VERSION_ < '1.5')
			$id_shop = 1;
		else
			$id_shop = Context::getContext()->shop->id;

		$shop_categories = array();
		foreach ($categories as $category)
		{
			$fianetsceau_type = Tools::isSubmit('fianetsceau_'.$category['id_category'].'_subcategory') ?
				Tools::getValue('fianetsceau_'.$category['id_category'].'_subcategory') : $this->getFianetSubCategoryId($category['id_category'], $id_shop);
			$parent_id = $this->getFianetCategoryId($fianetsceau_type);
			$shop_categories[$category['id_category']] = array(
				'name' => $category['name'],
				'fianetsceau_type' => $fianetsceau_type,
				'parent_id' => $parent_id
			);
		}
		return $shop_categories;
	}

	/**
	 * Load FIA-NET subcategory from category_id
	 * @param int $category_id
	 * @return array 
	 */
	public function loadFianetSubCategories($category_id)
	{
		$subcategories = $this->fianetsceau_subcategories;

		$subcategories_array = array();
		foreach ($subcategories as $key => $value)
		{

			if ($value['parent_id'] == $category_id)
				$subcategories_array[] = array('subcategory_id' => $key, 'label' => $value['label']);
		}

		return ($subcategories_array);
	}

	/**
	 * Get FIA-NET category from subcategory_id
	 * @param int $subcategory_id
	 * @return int 
	 */
	public function getFianetCategoryId($subcategory_id)
	{
		$subcategories = $this->fianetsceau_subcategories;

		foreach ($subcategories as $key => $value)
		{
			if ($key == $subcategory_id)
				return $value['parent_id'];
		}
	}

	/**
	 * Get FIA-NET subcategory configuration from category_id
	 * 
	 * @param int $category_id
	 * @return int 
	 */
	public function getFianetSubCategoryId($category_id, $id_shop)
	{
		$sql = 'SELECT `id_fianetsceau_subcategory` 
			FROM `'._DB_PREFIX_.self::SCEAU_CATEGORY_TABLE_NAME.'` 
				WHERE `id_category`= '.(int)$category_id.' 
					AND `id_shop`= '.(int)$id_shop;

		$query_result = Db::getInstance()->getValue($sql);
		if ($query_result == false)
			return false;
		else
			return $query_result['id_fianetsceau_subcategory'];
	}

	/**
	 * Insert or update FIA-NET subcategory configuration
	 * 
	 * @param int $category_id
	 * @param int $fianet_subcategory
	 * @param bool $is_default_subcategory 
	 */
	public function manageFianetSubCategory($category_id, $fianet_subcategory, $is_default_subcategory, $id_shop)
	{
		$sql = 'SELECT `id_category` 
			FROM `'._DB_PREFIX_.self::SCEAU_CATEGORY_TABLE_NAME.'` 
				WHERE `id_category`= '.(int)$category_id.' 
					AND id_shop = '.(int)$id_shop;

		$query_result = Db::getInstance()->getRow($sql);

		if ($query_result == false)
		{
			$sql = 'INSERT INTO `'._DB_PREFIX_.self::SCEAU_CATEGORY_TABLE_NAME."` (`id_category`, `id_fianetsceau_subcategory`, `default_category`, `id_shop`) 
				VALUES ('".pSQL($category_id)."', '".pSQL($fianet_subcategory)."',
					'".pSQL($is_default_subcategory)."', '".pSQL($id_shop)."')";

			$insert = Db::getInstance()->execute($sql);

			if (!$insert)
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'subcategory insertion failed $sql: '.Db::getInstance()->getMsgError());
			else
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'subcategory inserted '.$fianet_subcategory);
		}
		else
		{

			$sql = 'UPDATE `'._DB_PREFIX_.self::SCEAU_CATEGORY_TABLE_NAME.'` 
				SET `id_fianetsceau_subcategory` = '.pSQL($fianet_subcategory).', `default_category` = '.pSQL($is_default_subcategory).' 
				WHERE `id_category` = '.(int)$category_id.' AND `id_shop` = '.(int)$id_shop;

			$update = Db::getInstance()->execute($sql);

			if (!$update)
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'subcategory $fianet_subcategory : update failed $sql: '.Db::getInstance()->getMsgError());
			else
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'subcategory updated : '.$fianet_subcategory);
		}
	}

	/**
	 * For Prestashop 1.4, check if module is enabled, from Module::isEnabled($module_name)
	 * 
	 * @param string $module_name
	 * 
	 */
	public function checkModuleisEnabled($module_name)
	{
		return (bool)Db::getInstance()->getValue('SELECT `active` FROM `'._DB_PREFIX_.'module` WHERE `name` = \''.pSQL($module_name).'\'');
	}

	/**
	 * Get Kwixo module version saved on configuration
	 * @return int 
	 */
	public function getSceauModuleVersion()
	{
		$sceau_version = Configuration::get('FIANETSCEAU_MODULE_VERSION');

		if (!$sceau_version)
			return false;
		else
			return $sceau_version;
	}

	/**
	 * Check Sceau module version and reinstall it if version is too old
	 * 
	 */
	public function checkSceauUpdate()
	{
		if (_PS_VERSION_ >= '1.5')
		//check if kwixo is enabled on PS 1.5
			$sceau_is_enabled = Module::isEnabled('fianetsceau');
		else
		//check if kwixo is enabled on PS 1.4
			$sceau_is_enabled = $this->checkModuleisEnabled('fianetsceau');

		if (Module::isInstalled('fianetsceau') && $sceau_is_enabled)
		{
			$sceau_version = $this->getSceauModuleVersion();

			if (!$sceau_version || $sceau_version < $this->version)
			{
				SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Sceau module version < '.$this->version);
				if ($this->uninstall())
					SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Sceau module uninstalled');

				if ($this->install())
					SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Sceau module installed');
			}
		}
	}
	/**
	 * Return html notation
	 * 
	 */
	public function getStar($nb_star, $max_star)
	{
		$html = '';
		$entier = $this->getDecimalEntierNote($nb_star, 'entier');
		$decimal = $this->getDecimalEntierNote($nb_star, 'decimal');
		if (is_null($entier))
			$note = $nb_star;
		else
			$note = $entier;
		for ($i = 0; $i < $note; $i++)
			$html .= "<img src='".__PS_BASE_URI__.'modules/'.$this->name.'/img/star_on.png'."' alt='' />";
		if (!is_null($decimal))
		{
			if (in_array($decimal, array('4', '5', '6')))
			{
				$html .= "<img src='".__PS_BASE_URI__.'modules/'.$this->name.'/img/star_half.png'."' alt='' />";
				$complement = $max_star - ($note + 1);
			}
			elseif ($decimal > 6)
			{
				$html .= "<img src='".__PS_BASE_URI__.'modules/'.$this->name.'/img/star_on.png'."' alt='' />";
				$complement = $max_star - ($note + 1);
			}
			else
				$complement = $max_star - $note;
		}
		else
			$complement = $max_star - $note;
		for ($i = 0; $i < $complement; $i++)
			$html .= "<img src='".__PS_BASE_URI__.'modules/'.$this->name.'/img/star_off.png'."' alt='' />";
		return $html;
	}
	/**
	 * Load css and javascript files
	 * 
	 * @param type $params
	 */
	public function hookHeader()
	{
		if (_PS_VERSION_ < '1.5')
			Tools::addCSS($this->_path.'/css/fianetsceau.css', 'all');
		else
			$this->context->controller->addCSS($this->_path.'/css/fianetsceau.css', 'all');
	}
	public function hookProductFooter($params)
	{
		$product_comment = $this->getFianetProductComments($params['product']->id, 0, FianetSceau::SCEAU_MAX_COMMENTS_PER_PAGE);
		if ($product_comment == false)
			return false;
		$size_array = count($product_comment);
		$product_info = $this->getFianetProductNoteNbComments($params['product']->id);
		$global_note = $product_info['global_note'];
		$entier_note = $product_info['entier_note'];
		$decimal_note = $product_info['decimal_note'];
		$nb_comments = $this->getFianetProductNbComments($params['product']->id);
		$view_global_note = $this->getStar($global_note, FianetSceau::SCEAU_MAX_STAR);
		$nb_total_pages = Tools::ceilf($nb_comments / FianetSceau::SCEAU_MAX_COMMENTS_PER_PAGE);
		$fianetsceau = new Sceau();
		$token = Tools::getAdminToken($fianetsceau->getSiteid().$fianetsceau->getAuthkey().$fianetsceau->getLogin());
		$current_limit_min = 0;
		$logo_path = __PS_BASE_URI__.'modules/'.$this->name.'/img/logo_150.png';
		$script_path = __PS_BASE_URI__.'modules/'.$this->name.'/commentsmanager.php';
		$this->smarty->assign(array(
			'logo_path' => $logo_path,
			'script_path' => $script_path,
			'product_comment' => $product_comment,
			'global_note' => $global_note,
			'nb_comments' => $nb_comments,
			'view_global_note' => $view_global_note,
			'nb_total_pages' => $nb_total_pages,
			'token' => $token,
			'current_limit_min' => $current_limit_min,
			'nb_max_comments_per_page' => FianetSceau::SCEAU_MAX_COMMENTS_PER_PAGE,
			'product_id' => $params['product']->id,
			'size_array' => $size_array,
			'img_loader' => __PS_BASE_URI__.'modules/'.$this->name.'/img/fianetsceauloader.gif',
			'entier_note' => $entier_note,
			'decimal_note' => $decimal_note,
		));
		return $this->display(__FILE__, '/views/templates/front/fianetsceau_productcomments.tpl');
	}
	/**
	 * return all Fianet comments of the product
	 *  
	 * @param type $id_product
	 * @param type $limit_start
	 * @param type $limit_end
	 * @return array 
	 */
	public function getFianetProductComments($id_product, $limit_start = null, $limit_end = null)
	{
		$sql = 'SELECT * FROM `'._DB_PREFIX_.self::SCEAU_COMMENTS_TABLE_NAME.'` c 
			JOIN `'._DB_PREFIX_.self::SCEAU_PRODUCT_COMMENTS_TABLE_NAME."` pc 
				ON c.id_product = pc.id_product 
				WHERE c.`id_product` = '".(int)$id_product."' ORDER BY date DESC";
		if (!is_null($limit_start) && !is_null($limit_end))
			$sql .= ' LIMIT '.$limit_start.' ,'.$limit_end;
		$query_result = Db::getInstance()->executeS($sql);
		if ($query_result != null)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Comments founded for product : '.$id_product);
			$product_comments = array();
			foreach ($query_result as $value)
			{
				$product_comments[$value['id_comment']] = array(
				'id_product' => $value['id_product'],
				'note' => $value['note'],
				'comment' => $value['comment'],
				'firstname' => $value['firstname'],
				'name' => $value['name'],
				'state' => $value['state'],
				'date' => date('d/m/Y à H:m', strtotime($value['date'])),
				'global_note' => $value['global_note'],
				'nb_comments' => $value['nb_comments'],
				'view_note' => $this->getStar($value['note'], FianetSceau::SCEAU_MAX_STAR),
				'view_global_note' => $this->getStar($value['global_note'], FianetSceau::SCEAU_MAX_STAR));
			}
			return $product_comments;
		}
		else
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'No comments founded for product : '.$id_product);
			return false;
		}
	}
	/**
	 * return note and comment number of product
	 * 
	 * @param type $id_product
	 * @return type 
	 */
	public function getFianetProductNoteNbComments($id_product)
	{
		$sql = 'SELECT * FROM `'._DB_PREFIX_.self::SCEAU_PRODUCT_COMMENTS_TABLE_NAME."` c 
			WHERE `id_product` = '".(int)$id_product."'";
		$product_info = array();
		$query_result = Db::getInstance()->executeS($sql);
		if ($query_result != null)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Note and comments founded for product : '.$id_product);
			foreach ($query_result as $value)
			{
				$product_info['global_note'] = $value['global_note'];
				$product_info['nb_comments'] = $value['nb_comments'];
				$product_info['entier_note'] = $this->getDecimalEntierNote($value['global_note'], 'entier');
				$product_info['decimal_note'] = $this->getDecimalEntierNote($value['global_note'], 'decimal');
			}
			return $product_info;
		}
		else
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'No note and comments founded for product : '.$id_product);
			return false;
		}
	}
	/**
	 * return entier part or decimal part of the note
	 * 
	 * @param type $note
	 * @param type $part
	 * @return type 
	 */
	public function getDecimalEntierNote($note, $part)
	{
		if (strstr($note, '.'))
		{
			list($entier, $decimal) = explode('.', $note);
			if ($part == 'entier')
				if (!is_null($entier))
					return $entier;
				else
					return null;
			if ($part == 'decimal')
				if (!is_null($decimal))
					return $decimal;
				else
					return null;
		}
		else
			return null;
	}
	/**
	 * return Fianet product comments number
	 * 
	 * @param type $id_product
	 * @return nb comment calculated from DB 
	 */
	public function getFianetProductNbComments($id_product)
	{
		$sql = 'SELECT count(*) nb_comments FROM `'._DB_PREFIX_.self::SCEAU_COMMENTS_TABLE_NAME."`
			WHERE `id_product` = '".(int)$id_product."'";
		$nb_comments = Db::getInstance()->getValue($sql);
		SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, $nb_comments.' comments founded for product : '.$id_product);
		return $nb_comments;
	}
	/**
	 * return average note product
	 * 
	 * @param type $id_product
	 * @return average calculated from DB 
	 */
	public function getFianetProductAverageNote($id_product)
	{
		$sql = 'SELECT AVG(note) average FROM `'._DB_PREFIX_.self::SCEAU_COMMENTS_TABLE_NAME."`
			WHERE `id_product` = '".(int)$id_product."'";
		$average = Db::getInstance()->getValue($sql);
		SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, ' Average note for product '.$id_product.' : '.$average);
		return Tools::ps_round($average, 2);
	}
	/**
	 * delete Fianet comment on database
	 * 
	 * @param type $id_comment
	 * @return bool 
	 */
	public function deleteFianetComments($id_comment)
	{
		$sql = 'DELETE FROM `'._DB_PREFIX_.self::SCEAU_COMMENTS_TABLE_NAME."`
			WHERE `id_comment` = '".(int)$id_comment."'";
		$query_result = Db::getInstance()->executeS($sql);
		if ($query_result != null)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, $id_comment.' deleted');
			return true;
		}
		else
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, $id_comment.' not deleted : '.Db::getInstance()->getMsgError());
			return false;
		}
	}
	/**
	 * update fianetsceau_order table with fields in parameters
	 * 
	 * @param int $id_order
	 * @param array $fields
	 * @return boolean 
	 */
	public function updateFianetComments($id, array $fields, $table_name)
	{
		$set_string = '';
		foreach ($fields as $fieldname => $fieldvalue)
			$set_string .= '`'.$this->bqSQL($fieldname)."` = '".$this->bqSQL($fieldvalue)."', ";
		$set_string = Tools::substr($set_string, 0, '-2');
		if ($table_name == self::SCEAU_COMMENTS_TABLE_NAME)
			$sql = 'UPDATE `'._DB_PREFIX_.self::SCEAU_COMMENTS_TABLE_NAME.'` SET '.$set_string.' WHERE `id_comment` = '.(int)$id;
		if ($table_name == self::SCEAU_PRODUCT_COMMENTS_TABLE_NAME)
			$sql = 'UPDATE `'._DB_PREFIX_.self::SCEAU_PRODUCT_COMMENTS_TABLE_NAME.'` SET '.$set_string.' WHERE `id_product` = '.(int)$id;
		SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, $sql);
		$updated = Db::getInstance()->execute($sql);
		if (!$updated)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Update $id failed : '.Db::getInstance()->getMsgError());
			return false;
		}
		return true;
	}
	/**
	 *Check if fianet comment or id_product exists on database
	 * 
	 * @param type $id
	 * @param type $table_name
	 * @return bool
	 * 
	 * 
	 */
	public function checkFianetComments($id, $table_name)
	{
		if ($table_name == self::SCEAU_COMMENTS_TABLE_NAME)
			$sql = 'SELECT `id_comment` FROM `'._DB_PREFIX_.self::SCEAU_COMMENTS_TABLE_NAME."` WHERE `id_comment` = '".(int)$id."'";
		if ($table_name == self::SCEAU_PRODUCT_COMMENTS_TABLE_NAME)
			$sql = 'SELECT `id_product` FROM `'._DB_PREFIX_.self::SCEAU_PRODUCT_COMMENTS_TABLE_NAME."` WHERE `id_product` = '".(int)$id."'";

		$query_result = Db::getInstance()->getValue($sql);
		if ($query_result != null)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'id found :'.$id);
			return true;
		}
		else
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'id not found, id added :'.$id);
			return false;
		}
	}

}
