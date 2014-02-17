<?php
/**
* 2007-2014 PrestaShop
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
* @author    Feedaty <info@feedaty.com>
* @copyright 2012-2014 Feedaty
* @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* @version   Release: 1.1.135 $
*/

if (!defined('_PS_VERSION_'))
	exit;

class Feedaty extends Module
{
	public function __construct()
	{
		$this->name = 'feedaty';
		$this->tab = 'front_office_features';
		$this->version = '1.1.3';
		$this->author = 'Feedaty.com';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Feedaty');
		$this->description = $this->l('Add the Feedaty review system into your PrestaShop');

		/* Old PrestaShop version, load the backward compatibility */
	    require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		$this->feedaty_current_language = $this->context->language->iso_code;
	}

	public function install()
	{
		/* Create (if not exists) the feedaty cache table */
		Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'feedaty_cache` (
			`id_key` VARCHAR(250) NULL,
			`value` TEXT NULL,
			`expiration` INT NULL,
			UNIQUE INDEX `id_key` (`id_key`)
		)
		ENGINE='._MYSQL_ENGINE_);

		/* Install also all hock */
		return (parent::install()
			&& $this->registerHook('productTab')
			&& $this->registerHook('productTabContent')
			&& $this->registerHook('extraLeft')
			&& $this->registerHook('extraRight')
			&& $this->registerHook('productActions')
			&& $this->registerHook('productOutOfStock')
			&& $this->registerHook('productfooter')
			&& $this->registerHook('header')
			&& $this->registerHook('top')
			&& $this->registerHook('leftColumn')
			&& $this->registerHook('rightColumn')
			&& $this->registerHook('footer')
			&& $this->registerHook('home')
			&& $this->registerHook('updateOrderStatus')
			&& $this->registerHook('BackOfficeHeader')
		);
	}
	/* Install hooks */
	public function hookBackOfficeHeader()
	{
		/* Add the css on feedaty backend page */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return '<link rel="stylesheet" type="text/css" href="'._MODULE_DIR_.$this->name.'/css/ps_style.css" />';
		elseif (method_exists($this->context->controller, 'addCSS'))
			$this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/css/ps_style.css');
		else
			Tools::addCSS(_MODULE_DIR_.$this->name.'/css/ps_style.css');
	}
	/* All hocks return to internal function to avoid duplicate code */
	public function hookextraLeft()
	{
		return $this->fdGenerateProductWidget('extraLeft');
	}
	public function hookextraRight()
	{
		return $this->fdGenerateProductWidget('extraRight');
	}
	public function hookproductActions()
	{
		return $this->fdGenerateProductWidget('productActions');
	}
	public function hookproductOutOfStock()
	{
		return $this->fdGenerateProductWidget('productOutOfStock');
	}
	public function hookproductfooter()
	{
		return $this->fdGenerateProductWidget('productfooter');
	}
	public function hookheader()
	{
		return $this->fdGenerateStoreWidget('header');
	}
	public function hookhome()
	{
		return $this->fdGenerateStoreWidget('home');
	}
	public function hookfooter()
	{
		return $this->fdGenerateStoreWidget('footer');
	}
	public function hooktop()
	{
		return $this->fdGenerateStoreWidget('top');
	}
	public function hookleftColumn()
	{
		return $this->fdGenerateStoreWidget('leftColumn');
	}
	public function hookrightColumn()
	{
		return $this->fdGenerateStoreWidget('rightColumn');
	}

	/* hookUpdateOrderStatus is used to send order when status will be reach to feedaty api service */
	public function hookUpdateOrderStatus($var1)
	{
		/* Configuration status is reached */
		if ($var1['newOrderStatus']->id == Configuration::get('feedaty_status_request'))
		{
			/* Load the order */
			$order = new Order($var1['id_order']);

			/* Gets all products on order */
			$products = $order->getProducts();
			$final_products = array();

			/* For each product we get picture, id, name, url */
			foreach ($products as $product)
			{
				$tmp = array();
				$id_image = Product::getCover($product['product_id']);
				if (count($id_image) > 0)
				{
					$image = new Image($id_image['id_image']);
					$tmp['ImageUrl'] = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().'.jpg';
				}
				$tmp['Id'] = $product['product_id'];
				$tmp['Name'] = $product['product_name'];
				$tmp['Brand'] = '';
				$link = new Link();
				$tmp['Url'] = $link->getProductLink((int)$product['product_id']);

				$final_products[] = $tmp;
			}

			/* Gets information about customer who made the order */
			$customer = new Customer((int)$order->id_customer);

			/* Retrive also order date, customer email, id order, prestashop version and plugin version */
			$tmp_order = array();
			$tmp_order['OrderId'] = $var1['id_order'];
			$tmp_order['OrderDate'] = $order->date_add;
			$tmp_order['CustomerEmail'] = $customer->email;
			$tmp_order['CustomerId'] = $customer->email;
			$tmp_order['Platform'] = 'PrestaShop '._PS_VERSION_.' / '.$this->version;
			$tmp_order['Products'] = $final_products;

			$js_data = array();
			$js_data['merchantCode'] = Configuration::get('feedaty_code');
			$js_data['orders'][] = $tmp_order;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://'.'www.zoorate.com/ws/feedatyapi.svc/SubmitOrders');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, '60');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($js_data));
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Expect:'));
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_exec($ch);
			curl_close($ch);
			/* We don't need any responce, if worked ok so otherwise no problem */
		}
	}

	/* getContent regards the backend configuration page of feedaty plugin */
	public function getContent()
	{
		$this->smarty->cache = false;
		$this->smarty->force_compile = true;
		$html = '';

		/* This div is added to add some css rules on old prestashop versions */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$html .= '<div class="ps14">';

		/* Internal cache system will be used for feedaty, this function delete expired cache */
		$this->_delete_feedaty_cache();

		if (Tools::getValue('export') == 'csv')
			/* Export of csv with last 3 months orders */
			$html .= $this->fdExportCsv();

		$feedaty = array();
		$feedaty['msg'] = '';

		if (Tools::getValue('act') == 'requesttrial')
		{
			/* Send information to Feedaty for requesting a free trial account */
			$inputerror = 0;
			if (Tools::strlen(Tools::getValue('feedaty_email')) == 0 || Tools::strlen(Tools::getValue('feedaty_password')) == 0)
				$inputerror = 1;
			elseif (!Validate::isEmail(Tools::getValue('feedaty_email')))
				$inputerror = 1;

			if ($inputerror == 0)
			{
				/* We collect from input: email and password;
				from prestashop: name of store, url, (for statistical purposes) server os, prestashop version, plugin version and user ip */
				$data_post = array();
				$data_post['feedaty_email'] = (string)Tools::getValue('feedaty_email');
				$data_post['feedaty_password'] = (string)Tools::getValue('feedaty_password');
				$data_post['name'] = (string)Configuration::get('PS_SHOP_NAME');
				$data_post['url'] = (string)_PS_BASE_URL_.__PS_BASE_URI__;
				$data_post['os'] = (string)PHP_OS;
				$data_post['platform'] = (string)'PrestaShop '._PS_VERSION_;
				$data_post['pv'] = (string)$this->version;
				$data_post['ip'] = (string)$_SERVER['REMOTE_ADDR'];

				/* Request is sent by curl */
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'http://'.'widget.stage.zoorate.com/plugin/install-files/plugin/prestashop/');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, '60');
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_post);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
				$content = trim(curl_exec($ch));
				curl_close($ch);

				$content = Tools::jsonDecode($content, true);
			}

			/* We use ajax or simply post for old browsers or errors so we sent msg if it is ok or create json for ajax */
			if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			{
				$return = array();
				if ($inputerror == 1)
					$return['success'] = -1;
				elseif ($content['success'] == 1)
					$return['success'] = 1;
				else
					$return['success'] = 0;

				echo Tools::jsonEncode($return);
				exit;
			}
			else
				if ($content['success'] == 1)
					$feedaty['msg'] = 1;
				else
					$feedaty['msg'] = 2;
		}
		/* Update settings */
		if (Tools::isSubmit('submitModule') && Tools::getValue('export') != 'csv')
		{
			/* Save merchant code */
			if (Tools::getValue('code'))
				Configuration::updateValue('feedaty_code', Tools::getValue('code'));

			if (Tools::getValue('template_store'))
				Configuration::updateValue('feedaty_store_template', Tools::getValue('template_store'));

			/* Save widget store position */
			if (Tools::getValue('store_position'))
			{
				Configuration::updateValue('feedaty_store_position', Tools::getValue('store_position'));
				Configuration::updateValue('feedaty_widget_store_enabled', Tools::getValue('widget_store_enabled'));
			}

			/* Save widget product position */
			if (Tools::getValue('product_template'))
				Configuration::updateValue('feedaty_product_template', Tools::getValue('product_template'));
			if (Tools::getValue('product_position'))
			{
				Configuration::updateValue('feedaty_product_position', Tools::getValue('product_position'));
				Configuration::updateValue('feedaty_widget_product_enabled', Tools::getValue('widget_product_enabled'));
			}

			/* Save n. of review on product page */
			if (Tools::getValue('count_review'))
			{
				Configuration::updateValue('feedaty_product_review_enabled', Tools::getValue('product_review_enabled'));
				Configuration::updateValue('feedaty_count_review', Tools::getValue('count_review'));
			}

			/* Save status to reach for send order to us */
			if (Tools::getValue('status_request'))
				Configuration::updateValue('feedaty_status_request', Tools::getValue('status_request'));

			$this->fdSendInstallationInfo();

			$html .= '<div class="conf confirm">'.$this->l('Configuration updated').'</div>';
		}

		/* Some var for smarty used on template */
		$feedaty['email_default'] = Configuration::get('PS_SHOP_EMAIL');
		$feedaty['data']['code'] = (Tools::safeOutput(Configuration::get('feedaty_code')) != '') ? Configuration::get('feedaty_code') : '';

		/* We pass vars to smarty */
		$this->smarty->assign(
			$feedaty
		);

		/* If account is not configured we show landing page */
		if (Tools::strlen(Configuration::get('feedaty_code')) == 0)
			/* Call template landing.tpl */
			$html .= $this->fetchTemplate('/views/templates/admin/landing.tpl');
		/* ... otherwise standard page with all settings for widget */
		else {
			/* Vars used on template */
			$feedaty['data']['merchant'] = $this->fdGetTemplate('merchant');
			$feedaty['data']['product'] = $this->fdGetTemplate('product');
			$feedaty['data']['product_template'] = Configuration::get('feedaty_product_template');
			$feedaty['data']['merchant_template'] = Configuration::get('feedaty_store_template');
			foreach (array('header','top','leftColumn','rightColumn','footer','home') as $v)
				$feedaty['data']['merchant_position'][$v] = $this->l('Position '.$v);
			foreach (array('extraLeft','extraRight','productActions','productOutOfStock','productfooter') as $v)
				$feedaty['data']['product_position'][$v] = $this->l('Position '.$v);
			$feedaty['data']['merchant_default_position'] = Configuration::get('feedaty_store_position');
			$feedaty['data']['product_default_position'] = Configuration::get('feedaty_product_position');

			$feedaty['data']['widget_product_enabled'] = Configuration::get('feedaty_widget_product_enabled');
			$feedaty['data']['widget_store_enabled'] = Configuration::get('feedaty_widget_store_enabled');
			$feedaty['data']['status_list'] = OrderState::getOrderStates($this->context->language->id);
	        $feedaty['data']['status'] = Configuration::get('feedaty_status_request');
	        if (Tools::strlen($feedaty['data']['status']) == 0)
	            $feedaty['data']['status'] = 5;
			$feedaty['data']['product_review_enabled'] = Configuration::get('feedaty_product_review_enabled');
			$feedaty['data']['count_review'] = (Tools::safeOutput(Configuration::get('feedaty_count_review')) != '') ? Configuration::get('feedaty_count_review') : '10';
			if (version_compare(_PS_VERSION_, 1.4, '<'))
				$feedaty['data']['old_version'] = 1;
			else
				$feedaty['data']['old_version'] = 0;

			$this->smarty->assign(
				$feedaty
			);

			/* Call template backoffice.tpl */
			$html .= $this->fetchTemplate('/views/templates/admin/backoffice.tpl');
		}
		/* Div added to add some css rules on old prestashop versions is now closed */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$html .= '</div>';
		return $html;
	}

	/* fdGetData is used to retrive widget informations (as html, name, preview) from feedaty api service */
	private function fdGetData()
	{
		/* If is cached we don't ask for that again */
		$content = $this->fdGetCache('FeedatyData'.Configuration::get('feedaty_code').$this->feedaty_current_language);

		if (!$content)
		{
			/* Otherwise we ask for it by a simply and quick curl request */
			$ch = curl_init();

			if (Tools::strlen($this->feedaty_current_language) > 0) $lang_url_part = '&lang='.$this->feedaty_current_language;
			curl_setopt($ch, CURLOPT_URL,
				'http://'.'widget.zoorate.com/go.php?function=feed_be&action=widget_list&merchant_code='.Configuration::get('feedaty_code').$lang_url_part);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, '60');
			$content = trim(curl_exec($ch));
			curl_close($ch);

			/* If everything is gone fine we can save it on cache */
			if (Tools::strlen($content) > 50)
				$this->fdSetCache('FeedatyData'.Configuration::get('feedaty_code').$this->feedaty_current_language,
					$content, time() + (5 * 24 * 60 * 60)); // 24 hours of cache
		}
		/* No matter if it's retrieved by cache or by curl, we need to decode json */
		$data = Tools::jsonDecode($content, true);
		return $data;
	}

	/* fdGetTemplate is used to get data of a kind of widget only */
	private function fdGetTemplate($what)
	{
		$data = $this->fdGetData();
		$return = array();

		foreach ($data as $k => $v)
			if ($v['type'] == $what)
				$return[$k] = $v;

		return $return;
	}

	/* Creation of store widget */
	private function fdGenerateStoreWidget($position)
	{
		/* Creation of store widget only if current position is what set from settings */
		if ($position == Configuration::get('feedaty_store_position'))
		{
			/* Expire old stuff */
			$this->fdRemoveExpiredCache();
			/* If plugin is enabled */
			$plugin_enabled = Configuration::get('feedaty_widget_store_enabled');

			if ($plugin_enabled != 0)
			{
				/* Get widget data */
				$data = $this->fdGetData();

				/* Return html embeded */
				return $data[Configuration::get('feedaty_store_template')]['html_embed'];
			}
		}
	}

	/* Creation of product widget */
	private function fdGenerateProductWidget($position)
	{
		/* Creation of product widget only if current position is what set from settings */
		if ($position == Configuration::get('feedaty_product_position'))
		{
			/* Expire old stuff */
			$this->fdRemoveExpiredCache();
			/* If plugin is enabled */
			$plugin_enabled = Configuration::get('feedaty_widget_product_enabled');

			if ($plugin_enabled != 0)
			{
				/* Get product id */
				$product = Tools::getValue('id_product');
				/* Get widget data */
				$data = $this->fdGetData();

				/* Return html embeded replacing product id */
				return str_replace('__insert_ID__', $product, $data[Configuration::get('feedaty_product_template')]['html_embed']);
			}
		}
	}

	/* Add new tab for feedaty reviews */
	public function hookProductTab()
	{
		if (Configuration::get('feedaty_product_review_enabled') == 1)
			return $this->fetchTemplate('/views/templates/front/productTab.tpl');
	}

	/* Content for tab on product page */
	public function hookProductTabContent()
	{
		/* If reviews on product pages are enabled */
		if (Configuration::get('feedaty_product_review_enabled') == 1)
		{
			$toview = array();
			/* Get id of product */
			$id_pro = Tools::getValue('id_product');
			/* Get product informations */
			$toview['data_review'] = $this->fdGetProductData($id_pro);
			/* Send n. of reviews to show to smarty */
			$toview['count_review'] = Configuration::get('feedaty_count_review');

			/* Create html of link */
			$toview['link'] = '<a href="'.$toview['data_review']['Product']['Url'].'">'.$this->l('Read all reviews').'</a>';

			/* Generate stars img */
			if (is_array($toview['data_review']['Feedbacks']))
				foreach ($toview['data_review']['Feedbacks'] as $k => $v)
					$toview['data_review']['Feedbacks'][$k]['stars_html'] = $this->fdGenerateStars($v['ProductRating']);

			/* Send vars to smarty */
			$this->smarty->assign('data_review', $toview['data_review']);
			$this->smarty->assign('count_review', $toview['count_review']);
			$this->smarty->assign('feedaty_link', $toview['link']);
			/* Finally retrive template */
			return $this->fetchTemplate('/views/templates/front/productTabContent.tpl');
		}
	}

	/* fdRemoveExpiredCache delete expired cache */
	private function fdRemoveExpiredCache()
	{
		$q = 'DELETE FROM `'._DB_PREFIX_.'feedaty_cache` WHERE expiration < '.time();
		Db::getInstance()->execute($q);
	}

	/* _delete_feedaty_cache reset all cache */
	private function _delete_feedaty_cache()
	{
		$q = 'DELETE FROM `'._DB_PREFIX_.'feedaty_cache`';
		Db::getInstance()->execute($q);
	}

	/* fdGetCache gets cache if available or false if it isn't */
	private function fdGetCache($id)
	{
		$q = 'SELECT * FROM `'._DB_PREFIX_.'feedaty_cache` WHERE id_key = "'.pSQL((string)$id,true).'"';
		$cache = Db::getInstance()->getRow($q, false);

		if (isset($cache['value']) && Tools::strlen($cache['value']) > 0)
			return $cache['value'];
		else
			return false;
	}

	/* fdSetCache is used for save to cache a value */
	private function fdSetCache($id, $value, $expiration = null)
	{
		/* If expiration it's null se set a default 7 days */
		if (is_null($expiration))
			$expiration = (7 * 24 * 60 * 60) + time();

		/* First of all we remove expired cache */
		$this->fdRemoveExpiredCache();

		/* Check if there is already a value saved */
		$q = 'SELECT COUNT(*) FROM `'._DB_PREFIX_.'feedaty_cache` WHERE id_key = "'.pSQL((string)$id).'"';
		$count = Db::getInstance()->getValue($q, false);

		/* If isn't we add a new row */
		if ($count == 0)
			$q = 'INSERT INTO `'._DB_PREFIX_.'feedaty_cache` (`id_key`, `value`, `expiration`) values (\''.pSQL((string)$id).'\',\''.pSQL((string)$value,true).'\',\''.pSQL((int)$expiration).'\') ';
		/* If there is already a value we update its content */
		else
			$q = 'UPDATE `'._DB_PREFIX_.'feedaty_cache` SET `value` = \''.pSQL((string)$value,true).'\', `expiration` = \''.pSQL((int)$expiration).'\' WHERE `id_key` = \''.pSQL((string)$id).'\'';
		Db::getInstance()->Execute($q);
		return true;
	}

	/* fdGetProductData is used to retrive a single product reviews from feedaty api service */
	public function fdGetProductData($id)
	{
		/* If is cached we don't ask for that again */
		$content = $this->fdGetCache('feedaty_product_data_'.$id);

		if (!$content || Tools::strlen($content) < 20)
		{
			/* Otherwise we ask for it by a simply and quick curl request */
			$ch = curl_init();
			$url = 'http://'.'widget.zoorate.com/go.php?function=feed&action=ws&task=product&merchant_code='.Configuration::get('feedaty_code').'&ProductID='.$id;

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, '3');
			$content = trim(curl_exec($ch));
			curl_close($ch);

			/* If everything is gone fine we can save it on cache */
			if (Tools::strlen($content) > 0)
				$this->fdSetCache('feedaty_product_data_'.$id, $content, time() + (24 * 60 * 60));
		}

		/* No matter if it's retrieved by cache or by curl, we need to decode json */
		$data = Tools::jsonDecode($content, true);

		return $data;
	}

	/* Generate a single star img html code */
	private function fdGenerateStars($data)
	{
		if (!isset($data)) $data = 0;
		return '<img src="http://'.'www.feedaty.com/rating/rate-small-'.(int)$data.'.png" height="15">';
	}

	public function fetchTemplate($name)
	{
		if (version_compare(_PS_VERSION_, '1.4', '<'))
			$this->smarty->currentTemplate = $name;
		elseif (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			if (filemtime(dirname(__FILE__).'/'.$name))
				return $this->display(__FILE__, $name);
		}

		return $this->display(__FILE__, $name);
	}

	/* Export csv function */
	public function fdExportCsv()
	{
		/* File will be generated by an external script called download.php called by adding an iframe inside backend */
		$idEmployee = (int)Context::getContext()->employee->id;
		$timeGenerated = time();
		$cryptToken = md5($idEmployee . _COOKIE_KEY_ . $timeGenerated);
		$url = '../modules/feedaty/download.php?cryptToken='.$cryptToken.'&idEmployee='.(int)$idEmployee .'&timeGenerated='.$timeGenerated;
		$html = '<iframe src="'.$url.'" height="0" width="0" border="0" frameBorder="0"></iframe>';
		$html .= '<div class="conf confirm">'.$this->l('Download in progress').'<br>
				<a href="'.$url.'">'.$this->l('If you do not start automatically, click here').'</a></div>';
		return $html;
	}

	/* Send some informations about plugin configuration for debugging potential errors and statistics */
	public function fdSendInstallationInfo()
	{
		$fdata = array();
		/* Platform (obviously PrestaShop) and version */
		$fdata['keyValuePairs'][] = array('Key' => 'Platform', 'Value' => 'PrestaShop '._PS_VERSION_);
		/* Plugin version */
		$fdata['keyValuePairs'][] = array('Key' => 'Version', 'Value' => $this->version);
		/* Base store url */
		$fdata['keyValuePairs'][] = array('Key' => 'Url', 'Value' => _PS_BASE_URL_.__PS_BASE_URI__);
		/* Server os */
		$fdata['keyValuePairs'][] = array('Key' => 'Os', 'Value' => PHP_OS);
		/* Php version */
		$fdata['keyValuePairs'][] = array('Key' => 'Php Version', 'Value' => phpversion());
		/* Store name */
		$fdata['keyValuePairs'][] = array('Key' => 'Name', 'Value' => Configuration::get('PS_SHOP_NAME'));
		/* Widget configuration and positions */
		$fdata['keyValuePairs'][] = array('Key' => 'Widget_Store', 'Value' => (string)Configuration::get('feedaty_widget_store_enabled'));
		$fdata['keyValuePairs'][] = array('Key' => 'Widget_Product', 'Value' => (string)Configuration::get('feedaty_widget_product_enabled'));
		$fdata['keyValuePairs'][] = array('Key' => 'Widget_Store_Position', 'Value' => (string)Configuration::get('feedaty_store_position'));
		$fdata['keyValuePairs'][] = array('Key' => 'Widget_Product_Position', 'Value' => (string)Configuration::get('feedaty_product_position'));
		$fdata['keyValuePairs'][] = array('Key' => 'Status', 'Value' => (string)Configuration::get('feedaty_status_request'));
		/* Current server date */
		$fdata['keyValuePairs'][] = array('Key' => 'Date', 'Value' => date('c'));
		/* Feedaty Merchant code */
		$fdata['merchantCode'] = Configuration::get('feedaty_code');

		/* All is sent by curl */
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://'.'www.zoorate.com/ws/feedatyapi.svc/SetKeyValue');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, '60');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($fdata));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Expect:'));
		curl_exec($ch);
		curl_close($ch);
		/* We don't care about response */
	}
}
