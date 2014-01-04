<?php

/*
 *  2007-2013 PrestaShop
 * 
 * NOTICE OF LICENSE
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php* If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * 
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @version  Release: $Revision: 7515 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) exit;

class Gsitemap extends Module
{

	public $cron = false;
	private $sql_checks = array();

	public function __construct()
	{
		$this->name = 'gsitemap';
		$this->tab = 'seo';
		$this->version = '2.3.4';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;


		parent::__construct();

		$this->displayName = $this->l('Google sitemap');
		$this->description = $this->l('Generate your Google sitemap file');
	}

	/**
	 * Google Sitemap installation process:
	 *
	 * Step 1 - Pre-set Configuration option values
	 * Step 2 - Install the Addon and create a database table to store Sitemap files name by shop
	 *
	 * @return boolean Installation result
	 */
	public function install()
	{
		foreach (array('GSITEMAP_PRIORITY_HOME' => 1.0, 'GSITEMAP_PRIORITY_PRODUCT' => 0.9, 'GSITEMAP_PRIORITY_CATEGORY' => 0.8,
	'GSITEMAP_PRIORITY_MANUFACTURER' => 0.7, 'GSITEMAP_PRIORITY_SUPPLIER' => 0.6, 'GSITEMAP_PRIORITY_CMS' => 0.5,
	'GSITEMAP_FREQUENCY' => 'weekly', 'GSITEMAP_CHECK_IMAGE_FILE' => false, 'GSITEMAP_LAST_EXPORT' => false) as $key => $val) 
			if (!Configuration::updateValue($key, $val)) 
				return false;
		
		return parent::install() && Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.
						'gsitemap_sitemap` (`link` varchar(255) DEFAULT NULL, `id_shop` int(11) DEFAULT 0) ENGINE='._MYSQL_ENGINE_.
						' DEFAULT CHARSET=utf8;');
	}

	/**
	 * Google Sitemap uninstallation process:
	 *
	 * Step 1 - Remove Configuration option values from database
	 * Step 2 - Remove the database containing the generated Sitemap files names
	 * Step 3 - Uninstallation of the Addon itself
	 *
	 * @return boolean Uninstallation result
	 */
	public function uninstall()
	{
		foreach (array('GSITEMAP_PRIORITY_HOME' => '', 'GSITEMAP_PRIORITY_PRODUCT' => '', 'GSITEMAP_PRIORITY_CATEGORY' => '',
	'GSITEMAP_PRIORITY_MANUFACTURER' => '', 'GSITEMAP_PRIORITY_SUPPLIER' => '', 'GSITEMAP_PRIORITY_CMS' => '',
	'GSITEMAP_FREQUENCY' => '', 'GSITEMAP_CHECK_IMAGE_FILE' => '', 'GSITEMAP_LAST_EXPORT' => '') as $key => $val) 
			if (!Configuration::deleteByName($key)) 
				return false;
		
		return parent::uninstall() && $this->removeSitemap();
	}

	/**
	 * Delete all the generated Sitemap files  and drop the addon table.
	 * @return boolean
	 */
	public function removeSitemap()
	{
		$links = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'gsitemap_sitemap`');
		if ($links) 
			foreach ($links as $link) 
				if (!@unlink(dirname(__FILE__).'/../../'.$link['link'])) 
						return false;
		if (!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'gsitemap_sitemap`')) 
			return false;
		return true;
	}

	/* Google Sitemap configuration section
	 *
	 * @return HTML page (template) to use the addon 
	 */

	public function getContent()
	{
		/* Store the posted parameters and generate a new Google Sitemap files for the current Shop */
		if (Tools::isSubmit('SubmitGsitemap'))
		{
			Configuration::updateValue('GSITEMAP_FREQUENCY', pSQL(Tools::getValue('gsitemap_frequency')));
			Configuration::updateValue('GSITEMAP_INDEX_CHECK', '');
			Configuration::updateValue('GSITEMAP_CHECK_IMAGE_FILE', pSQL(Tools::getValue('gsitemap_check_image_file')));
			Configuration::updateValue('GSITEMAP_ENABLE_SSL_LINK', pSQL(Tools::getValue('gsitemap_enable_ssl_link')));
			$meta = '';
			if (Tools::getValue('gsitemap_meta')) 
				$meta .= implode(', ', Tools::getValue('gsitemap_meta'));
			Configuration::updateValue('GSITEMAP_DISABLE_LINKS', $meta);
			$this->emptySitemap();
			$this->createSitemap();
		}
		/* if no posted form and the variable [continue] is found in the HTTP request variable keep creating sitemap */
		elseif (Tools::getValue('continue')) 
			$this->createSitemap();

		/* Backward compatibility */
		if (_PS_VERSION_ < 1.5) 
			require(_PS_MODULE_DIR_.'gsitemap/backward_compatibility/backward.php');

		/* Empty the Shop domain cache */
		if (class_exists('ShopUrl') && method_exists('ShopUrl', 'resetMainDomainCache')) 
				ShopUrl::resetMainDomainCache();

		$gsitemap_db_links = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'gsitemap_sitemap` WHERE id_shop = '.(int)$this->context->shop->id);
		$languages = Language::getLanguages();
		foreach ($languages as $language)
		{
			foreach($gsitemap_db_links as $gsitemap_db_link)
				if (preg_match('(_'.$language['iso_code'].'_)', $gsitemap_db_link['link']))
					$gsitemap_links[$language['iso_code']][] = $gsitemap_db_link;	
		}
		
		$this->context->smarty->assign(array(
			'gsitemap_form' => './index.php?tab=AdminModules&configure=gsitemap&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=gsitemap',
			'gsitemap_cron' => Tools::getShopDomainSsl(true, true)._MODULE_DIR_.'gsitemap/gsitemap-cron.php?token='.substr(Tools::encrypt('gsitemap/cron'), 0, 10).'&id_shop='.$this->context->shop->id,
			'gsitemap_feed_exists' => file_exists(dirname(__FILE__).'/../../index_sitemap.xml'),
			'gsitemap_last_export' => Configuration::get('GSITEMAP_LAST_EXPORT'),
			'gsitemap_frequency' => Configuration::get('GSITEMAP_FREQUENCY'),
			'gsitemap_store_url' => $this->_getStoreUrl(),
			'gsitemap_links' => $gsitemap_links,
			'gsitemap_languages' => $languages,
			'store_metas' => Meta::getMetasByIdLang((int)$this->context->cookie->id_lang),
			'gsitemap_disable_metas' => explode(',', Configuration::get('GSITEMAP_DISABLE_LINKS')),
			'gsitemap_customer_limit' => array('max_exec_time' => (int)ini_get('max_execution_time'), 'memory_limit' => intval(ini_get('memory_limit'))),
			'prestashop_version' => _PS_VERSION_ >= 1.5 ? '1.5' : '1.4',
			'prestashop_ssl' => Configuration::get('PS_SSL_ENABLED'),
			'gsitemap_check_image_file' => Configuration::get('GSITEMAP_CHECK_IMAGE_FILE'),
			'gsitemap_enable_ssl_link' => Configuration::get('GSITEMAP_ENABLE_SSL_LINK'),
			'shop' => $this->context->shop
		));

		return $this->display(__FILE__, 'tpl/configuration.tpl');
	}

	/**
	 * Delete all the generated Sitemap files from the files system and the database.
	 * @param type $id_shop
	 * @return boolean
	 */
	public function emptySitemap($id_shop = 0)
	{
		if (!isset($this->context)) 
			$this->context = new Context();
		
		if ($id_shop != 0) 
			$this->context->shop = new Shop((int)$id_shop);
		
		$links = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'gsitemap_sitemap` WHERE id_shop = '.(int)$this->context->shop->id);
		
		if ($links)
		{
			foreach ($links as $link) 
				@unlink(dirname(__FILE__).'/../../'.$link['link']);
			return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gsitemap_sitemap` WHERE id_shop = '.(int)$this->context->shop->id);
		}
		return true;
	}

	/**
	 * Add new URL element to the $link_sitemap main array and handles the memory usage issue
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $new_link array containe the link elements
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file 
	 * @param type $i int the count of elements added to sitemap main array 
	 * @param type $id_obj int the identifier of the object of the link to be added to the Gogle Sitemap file
	 * @return boolean
	 */
	public function _addLinkToSitemap(&$link_sitemap, $new_link, $lang, &$index, &$i, $id_obj)
	{
		if ($i <= 250000 && memory_get_usage() < 100000000)
		{
			$link_sitemap[] = $new_link;
			$i++;
			return true;
		}
		else
		{
			$link_sitemap_keys = array_keys($link_sitemap);
			$last_obj = end($link_sitemap_keys);
			file_put_contents(dirname(__FILE__).'/log.txt', file_get_contents(dirname(__FILE__).'/log.txt')."\n".'id_obj: '.$id_obj.' index: '.$index.' elements count: '.$this->_getMaxSitmapElementsCount($link_sitemap, $lang, $index, $i, $id_obj));
			$this->_recursiveSitemapCreator($link_sitemap, $lang, $index);
			if ($index % 20 == 0 && !$this->cron)
			{
				$this->context->smarty->assign(array(
					'gsitemap_number' => (int)$index,
					'gsitemap_refresh_page' => './index.php?tab=AdminModules&configure=gsitemap&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=gsitemap&continue=1&type='.$new_link['type'].'&lang='.$lang.'&index='.$index.'&id='.($id_obj + 1).'&id_shop='.$this->context->shop->id));
				return false;
			}
			else if ($index % 20 == 0 && $this->cron)
			{
				header('Refresh: 5; url=http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.'modules/gsitemap/gsitemap-cron.php?continue=1&token='.substr(Tools::encrypt('gsitemap/cron'), 0, 10).'&type='.$new_link['type'].'&lang='.$lang.'&index='.$index.'&id='.($id_obj + 1).'&id_shop='.$this->context->shop->id);
				die();
			}
			else//if ($id_obj <= $this->_getMaxSitmapElementsCount($link_sitemap, $lang, $index, $i, $id_obj))
			{
				if ($this->cron) 
					header('Refresh: 5; url=http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.'modules/gsitemap/gsitemap-cron.php?continue=1&token='.substr(Tools::encrypt('gsitemap/cron'), 0, 10).'&type='.$new_link['type'].'&lang='.$lang.'&index='.$index.'&id='.($id_obj + 1).'&id_shop='.$this->context->shop->id);
				else 
					header('Refresh: 1; url=http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.$this->_getAdminFolder().'/index.php?tab=AdminModules&configure=gsitemap&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=gsitemap&continue=1&type='.$new_link['type'].'&lang='.$lang.'&index='.$index.'&id='.($id_obj + 1).'&id_shop='.$this->context->shop->id);

				die();
			}
		}
	}

	/**
	 * return the link elements for the home page
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file 
	 * @param type $i int the count of elements added to sitemap main array 
	 * @return type
	 */
	private function _getHomeLink(&$link_sitemap, $lang, &$index, &$i)//$this->domain_ssl
	{
		return $this->_addLinkToSitemap($link_sitemap, array('type' => 'home', 'page' => 'home', 'link' => $this->_getStoreUrl().(method_exists('Language', 'isMultiLanguageActivated') ? Language::isMultiLanguageActivated() ? $lang['iso_code'].'/' : ''  : ''), 'image' => false), $lang['iso_code'], $index, $i, -1);
	}

	/**
	 * return the link elements for the meta object
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file 
	 * @param type $i int the count of elements added to sitemap main array 
	 * @param type $id_meta int the meta object identifier 
	 * @return boolean
	 */
	private function _getMetaLink(&$link_sitemap, $lang, &$index, &$i, $id_meta = 0, $count = false)
	{
		if (class_exists('ShopUrl') && method_exists('ShopUrl', 'resetMainDomainCache')) ShopUrl::resetMainDomainCache();
		$link = new Link();
		$metas = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'meta` m
			INNER JOIN `'._DB_PREFIX_.'meta_lang` ml ON m.`id_meta` = ml.`id_meta` '.
			(!$count ? 'WHERE m.`id_meta` > '.(int)$id_meta : '').'
			'.(($this->tableColumnExists(_DB_PREFIX_.'meta_lang', 'id_shop')) ? ' AND ml.`id_shop` = '.(int)$this->context->shop->id : '').'
			ORDER BY m.`id_meta` ASC');
		
		if ($count) 
			return count($metas);
		
		foreach ($metas as $meta)
		{
			$url = '';
			if (!in_array($meta['id_meta'], explode(',', Configuration::get('GSITEMAP_DISABLE_LINKS'))))
			{
				if (_PS_VERSION_ >= 1.5)
				{
					$url_rewrite = Db::getInstance()->getValue('SELECT url_rewrite, id_shop FROM `'._DB_PREFIX_.'meta_lang` WHERE `id_meta` = '.(int)$meta['id_meta'].' AND `id_shop` ='.(int)$this->context->shop->id.' AND `id_lang` = '.(int)$lang['id_lang']);
					Dispatcher::getInstance()->addRoute($meta['page'], (isset($url_rewrite) ? $url_rewrite : $meta['page']), $meta['page'], $lang['id_lang']);
					$uri_path = Dispatcher::getInstance()->createUrl($meta['page'], $lang['id_lang'], array(), (bool)Configuration::get('PS_REWRITING_SETTINGS'));
					$url .= $this->_getStoreUrl().(Language::isMultiLanguageActivated() ? $lang['iso_code'].'/' : '').ltrim($uri_path, '/');
				}
				else 
					$url = $link->getPageLink($meta['page'].'.php', true, $lang['id_lang']);

				if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
					$url = str_replace('http://', 'https://', $url);
				else 
					$url = str_replace('https://', 'http://', $url);


				if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'meta', 'page' => $meta['page'], 'link' => $url, 'image' => false), $lang['iso_code'], $index, $i, $meta['id_meta'])) 
						return false;
			}
		}
		return true;
	}

	/**
	 * return the link elements for the product object
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file 
	 * @param type $i int the count of elements added to sitemap main array 
	 * @param type $id_product int the product object identifier 
	 * @return boolean
	 */
	private function _getProductLink(&$link_sitemap, $lang, &$index, &$i, $id_product = 0, $count = false)
	{

		$link = new Link();
		if (class_exists('ShopUrl') && method_exists('ShopUrl', 'resetMainDomainCache')) 
				ShopUrl::resetMainDomainCache();

		$products_id = Db::getInstance()->ExecuteS('SELECT p.`id_product` FROM `'._DB_PREFIX_.'product` p 
			INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON p.id_product = pl.id_product '.
				(($this->tableColumnExists(_DB_PREFIX_.'product_shop', 'id_shop')) ? 'INNER JOIN `'._DB_PREFIX_.'product_shop` ps ON pl.id_product = ps.id_product ' : '').'
			WHERE p.`active` = 1 '.(!$count ? ' AND p.`id_product` > '.(int)$id_product : '').
				(($this->tableColumnExists(_DB_PREFIX_.'product_lang', 'id_shop')) ? ' AND pl.`id_shop` = '.(int)$this->context->shop->id : '').
				(($this->tableColumnExists(_DB_PREFIX_.'product_shop', 'id_shop')) ? ' AND ps.`id_shop` = '.(int)$this->context->shop->id : '').' 
				AND pl.`id_lang` = '.(int)$lang['id_lang'].' 
			ORDER BY `id_product` ASC');
		
		if ($count) 
			return count($products_id);

		//if (!$count) d($products_id);
		foreach ($products_id as $product_id)
		{
			$product = new Product((int)$product_id['id_product'], false, (int)$lang['id_lang']);
			$category = new Category((int)$product->id_category_default, (int)$lang['id_lang']);

			if (_PS_VERSION_ >= 1.5) 
				$url = $link->getProductLink($product, $product->link_rewrite, htmlspecialchars(strip_tags($product->category)), $product->ean13, (int)$lang['id_lang'], (int)$this->context->shop->id, 0, true);
			else
				$url = $link->getProductLink($product, Configuration::get('PS_REWRITING_SETTINGS') ? $product->link_rewrite : false, htmlspecialchars(strip_tags($category->name)), $product->ean13, (int)$lang['id_lang']);

			if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
				$url = str_replace('http://', 'https://', $url);
			else 
				$url = str_replace('https://', 'http://', $url);

			$id_image = Product::getCover((int)$product_id['id_product']);

			if (isset($id_image['id_image']))
			{
				$image_link = $this->context->link->getImageLink($category->link_rewrite, (int)$id_image['id_image']);
				if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
					$image_link = str_replace('http://', 'https://', $image_link);
				else 
					$image_link = str_replace('https://', 'http://', $image_link);

				$image_link = (!(int)in_array(preg_replace('/[^A-Za-z0-9\-]/', '', Context::getContext()->shop->virtual_uri), explode('/', $image_link))) ?
						str_replace(array($this->_getStoreUrl(false), __PS_BASE_URI__), array($this->_getStoreUrl(), ''), $image_link) : $image_link;

				if (!_MEDIA_SERVER_1_ && !_MEDIA_SERVER_2_ && !_MEDIA_SERVER_3_ && preg_match("/http/", $image_link) <= 0) 
					$image_link = $this->_getStoreUrl().$image_link;
			}
			
			$file_headers = (Configuration::get('GSITEMAP_CHECK_IMAGE_FILE')) ? @get_headers($image_link) : true;
			$image_product = array();
			
			if (isset($image_link) && ($file_headers[0] != 'HTTP/1.1 404 Not Found' || $file_headers === true)) 
				$image_product = array('title_img' => htmlspecialchars(strip_tags($product->name)), 'caption' => htmlspecialchars(strip_tags($product->description_short)), 'link' => $image_link);
			
			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'product', 'page' => 'product', 'lastmod' => $product->date_upd, 'link' => $url, 'image' => $image_product), $lang['iso_code'], $index, $i, $product_id['id_product']))
				return false;

			unset($image_link);
		}
		return true;
	}

	/**
	 * return the link elements for the category object
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file 
	 * @param type $i int the count of elements added to sitemap main array 
	 * @param type $id_category int the category object identifier 
	 * @return boolean
	 */
	private function _getCategoryLink(&$link_sitemap, $lang, &$index, &$i, $id_category = 0, $count = false)
	{
		$link = new Link();
		if (class_exists('ShopUrl') && method_exists('ShopUrl', 'resetMainDomainCache')) ShopUrl::resetMainDomainCache();
		$categories_id = Db::getInstance()->ExecuteS('SELECT c.`id_category` FROM `'._DB_PREFIX_.'category` c 
			INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category` '.
				(($this->tableColumnExists(_DB_PREFIX_.'product_shop', 'id_shop')) ? 'INNER JOIN `'._DB_PREFIX_.'category_shop` cs ON cl.`id_category` = cs.`id_category` ' : '').'
			WHERE c.`active` = 1 AND c.`id_category` != 1 AND c.id_parent > 0 '.(!$count ? ' AND c.`id_category` > '.(int)$id_category : '').
				($this->tableColumnExists(_DB_PREFIX_.'category_lang', 'id_shop') ? ' AND cl.`id_shop` = '.(int)$this->context->shop->id : '').
				($this->tableColumnExists(_DB_PREFIX_.'category_shop', 'id_shop') ? ' AND cs.`id_shop` = '.(int)$this->context->shop->id : '').
				' AND cl.`id_lang` = '.(int)$lang['id_lang'].' ORDER BY c.`id_category` ASC');
		if ($count) 
			return count($categories_id);

		foreach ($categories_id as $category_id)
		{
			$category = new Category((int)$category_id['id_category'], (int)$lang['id_lang']);
			$url = $link->getCategoryLink($category, urlencode($category->link_rewrite), (int)$lang['id_lang']);
			if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
				$url = str_replace('http://', 'https://', $url);
			else 
				$url = str_replace('https://', 'http://', $url);

			if ($category->id_image)
			{
				$image_link = $this->context->link->getCatImageLink($category->link_rewrite, (int)$category->id_image, 'category_default');
				if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
					$image_link = str_replace('http://', 'https://', $image_link);
				else 
					$image_link = str_replace('https://', 'http://', $image_link);

				$image_link = (!in_array(preg_replace('/[^A-Za-z0-9\-]/', '', Context::getContext()->shop->virtual_uri), explode('/', $image_link))) ?
						str_replace(array($this->_getStoreUrl(false)), array($this->_getStoreUrl()), $image_link) : $image_link;
				if (!_MEDIA_SERVER_1_ && !_MEDIA_SERVER_2_ && !_MEDIA_SERVER_3_ && preg_match("/http/", $image_link) <= 0) 
					$image_link = $this->_getStoreUrl().$image_link;
			}
			$file_headers = (Configuration::get('GSITEMAP_CHECK_IMAGE_FILE')) ? @get_headers($image_link) : true;
			$image_category = array();
			if (isset($image_link) && ($file_headers[0] != 'HTTP/1.1 404 Not Found' || $file_headers === true)) 
				$image_category = array('title_img' => htmlspecialchars(strip_tags($category->name)), 'link' => $image_link);

			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'category', 'page' => 'category', 'lastmod' => $category->date_upd, 'link' => $url, 'image' => $image_category), $lang['iso_code'], $index, $i, (int)$category_id['id_category'])) 
				return false;

			unset($image_link);
		}
		return true;
	}

	/**
	 * return the link elements for the manifacturer object
	 * @param type $link_sitemap
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file 
	 * @param type $i int the count of elements added to sitemap main array 
	 * @param type $id_manufacturer int the manifacturer object identifier 
	 * @return boolean
	 */
	private function _getManufacturerLink(&$link_sitemap, $lang, &$index, &$i, $id_manufacturer = 0, $count = false)
	{
		$link = new Link();
		if (class_exists('ShopUrl') && method_exists('ShopUrl', 'resetMainDomainCache')) ShopUrl::resetMainDomainCache();
		$manufacturers_id = Db::getInstance()->ExecuteS('SELECT m.`id_manufacturer` FROM `'._DB_PREFIX_.'manufacturer` m 
			INNER JOIN `'._DB_PREFIX_.'manufacturer_lang` ml on m.`id_manufacturer` = ml.`id_manufacturer`'.
				($this->tableColumnExists(_DB_PREFIX_.'manufacturer_shop') ? ' INNER JOIN `'._DB_PREFIX_.'manufacturer_shop` ms ON m.`id_manufacturer` = ms.`id_manufacturer` ' : '').
				' WHERE m.`active` = 1 '.(!$count ? ' AND m.`id_manufacturer` > '.(int)$id_manufacturer : '').
				($this->tableColumnExists(_DB_PREFIX_.'manufacturer_shop') ? ' AND ms.`id_shop` = '.(int)$this->context->shop->id : '').
				' AND ml.`id_lang` = '.(int)$lang['id_lang'].
				' ORDER BY m.`id_manufacturer` ASC');
		if ($count) 
			return count($manufacturers_id);

		foreach ($manufacturers_id as $manufacturer_id)
		{
			$manufacturer = new Manufacturer((int)$manufacturer_id['id_manufacturer'], $lang['id_lang']);
			$url = $link->getManufacturerLink($manufacturer, $manufacturer->link_rewrite, $lang['id_lang']);

			if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) $url = str_replace('http://', 'https://', $url);

			$image_link = 'http://'.Tools::getMediaServer(_THEME_MANU_DIR_).str_replace('//', '/', Context::getContext()->shop->physical_uri._THEME_MANU_DIR_.((!file_exists(__DIR__.'/../..'._THEME_MANU_DIR_.(int)$manufacturer->id.'-medium_default.jpg')) ? $lang['iso_code'].'-default' : (int)$manufacturer->id).'-medium_default.jpg');
			if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
				$image_link = str_replace('http://', 'https://', $image_link);
			else $image_link = str_replace('https://', 'http://', $image_link);

			$image_link = (!in_array(preg_replace('/[^A-Za-z0-9\-]/', '', Context::getContext()->shop->virtual_uri), explode('/', $image_link))) ?
				str_replace(array($this->_getStoreUrl(false)), array($this->_getStoreUrl()), $image_link) : $image_link;
			if (!_MEDIA_SERVER_1_ && !_MEDIA_SERVER_2_ && !_MEDIA_SERVER_3_ && preg_match("/http/", $image_link) <= 0) $image_link = $this->_getStoreUrl().$image_link;

			$file_headers = (Configuration::get('GSITEMAP_CHECK_IMAGE_FILE')) ? @get_headers($image_link) : true;
			$manifacturer_image = array();
			
			if ($file_headers[0] != 'HTTP/1.1 404 Not Found' || $file_headers === true) 
				$manifacturer_image = array('title_img' => htmlspecialchars(strip_tags($manufacturer->name)), 'caption' => htmlspecialchars(strip_tags($manufacturer->short_description)), 'link' => $image_link);
			
			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'manufacturer', 'page' => 'manufacturer', 'lastmod' => $manufacturer->date_upd, 'link' => $url, 'image' => $manifacturer_image), $lang['iso_code'], $index, $i, $manufacturer_id['id_manufacturer'])) 
				return false;;
		}
		return true;
	}

	/**
	 * return the link elements for the supplier object
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file 
	 * @param type $i int the count of elements added to sitemap main array 
	 * @param type $id_supplier int the supplier object identifier 
	 * @return boolean
	 */
	private function _getSupplierLink(&$link_sitemap, $lang, &$index, &$i, $id_supplier = 0, $count = false)
	{
		$link = new Link();
		if (class_exists('ShopUrl') && method_exists('ShopUrl', 'resetMainDomainCache')) ShopUrl::resetMainDomainCache();
		$suppliers_id = Db::getInstance()->ExecuteS('SELECT s.`id_supplier` FROM `'._DB_PREFIX_.'supplier` s 
			INNER JOIN `'._DB_PREFIX_.'supplier_lang` sl ON s.`id_supplier` = sl.`id_supplier` '.
				($this->tableColumnExists(_DB_PREFIX_.'supplier_shop') ? 'INNER JOIN `'._DB_PREFIX_.'supplier_shop` ss ON s.`id_supplier` = ss.`id_supplier`' : '').' 
			WHERE s.`active` = 1 '.(!$count ? ' AND s.`id_supplier` > '.(int)$id_supplier : '').
				($this->tableColumnExists(_DB_PREFIX_.'supplier_shop') ? ' AND ss.`id_shop` = '.(int)$this->context->shop->id : '').' 
			AND sl.`id_lang` = '.(int)$lang['id_lang'].' 
			ORDER BY s.`id_supplier` ASC');
		if ($count) 
			return count($suppliers_id);
		foreach ($suppliers_id as $supplier_id)
		{
			$supplier = new Supplier((int)$supplier_id['id_supplier'], $lang['id_lang']);
			$url = $link->getSupplierLink($supplier, $supplier->link_rewrite, $lang['id_lang']);
			if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
				$url = str_replace('http://', 'https://', $url);
			else 
				$url = str_replace('httpS://', 'http://', $url);

			$image_link = 'http://'.Tools::getMediaServer(_THEME_SUP_DIR_).str_replace('//', '/', Context::getContext()->shop->physical_uri._THEME_SUP_DIR_.((!file_exists(__DIR__.'/../..'._THEME_SUP_DIR_.(int)$supplier->id.'-medium_default.jpg')) ? $lang['iso_code'].'-default' : (int)$supplier->id).'-medium_default.jpg');
			$image_link = str_replace('//', '/', $image_link);
			if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
				$image_link = str_replace('http://', 'https://', $image_link);
			else 
				$image_link = str_replace('https://', 'http://', $image_link);

			$image_link = (!in_array(preg_replace('/[^A-Za-z0-9\-]/', '', Context::getContext()->shop->virtual_uri), explode('/', $image_link))) ?
					str_replace(array($this->_getStoreUrl(false)), array($this->_getStoreUrl()), $image_link) : $image_link;
			if (!_MEDIA_SERVER_1_ && !_MEDIA_SERVER_2_ && !_MEDIA_SERVER_3_ && preg_match("/http/", $image_link) <= 0) $image_link = $this->_getStoreUrl().$image_link;

			$file_headers = (Configuration::get('GSITEMAP_CHECK_IMAGE_FILE')) ? @get_headers($image_link) : true;
			$supplier_image = array();
			if ($file_headers[0] != 'HTTP/1.1 404 Not Found' || $file_headers === true) 
				$supplier_image = array('title_img' => htmlspecialchars(strip_tags($supplier->name)), 'link' => $image_link);
			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'supplier', 'page' => 'supplier', 'lastmod' => $supplier->date_upd, 'link' => $url, 'image' => $supplier_image), $lang['iso_code'], $index, $i, $supplier_id['id_supplier']))
				return false;
		}
		return true;
	}

	/**
	 * return the link elements for the CMS object
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file 
	 * @param type $i int the count of elements added to sitemap main array 
	 * @param type $id_cms int the CMS object identifier 
	 * @return boolean
	 */
	private function _getCmsLink(&$link_sitemap, $lang, &$index, &$i, $id_cms = 0, $count=false)
	{
		$link = new Link();
		if (class_exists('ShopUrl') && method_exists('ShopUrl', 'resetMainDomainCache')) ShopUrl::resetMainDomainCache();
		$cmss_id = Db::getInstance()->ExecuteS('SELECT c.`id_cms` FROM `'._DB_PREFIX_.'cms` c INNER JOIN `'._DB_PREFIX_.'cms_lang` cl ON c.`id_cms` = cl.`id_cms` '.
				($this->tableColumnExists(_DB_PREFIX_.'supplier_shop') ? 'INNER JOIN `'._DB_PREFIX_.'cms_shop` cs ON c.`id_cms` = cs.`id_cms` ' : '').
				'INNER JOIN `'._DB_PREFIX_.'cms_category` cc ON c.id_cms_category = cc.id_cms_category AND cc.active = 1 
				WHERE c.`active` =1 '.(!$count ? 'AND c.`id_cms` > '.(int)$id_cms : '').
				($this->tableColumnExists(_DB_PREFIX_.'supplier_shop') ? ' AND cs.id_shop = '.(int)$this->context->shop->id : '').
				' AND cl.`id_lang` = '.(int)$lang['id_lang'].
				' ORDER BY c.`id_cms` ASC');
		if ($count) 
			return count($cmss_id);

		foreach ($cmss_id as $cms_id)
		{
			$cms = new CMS((int)$cms_id['id_cms'], $lang['id_lang']);
			$cms->link_rewrite = urlencode((is_array($cms->link_rewrite) ? $cms->link_rewrite[(int)$id_lang] : $cms->link_rewrite));
			$url = $link->getCMSLink($cms, null, null, $lang['id_lang']);
			if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
				$url = str_replace('http://', 'https://', $url);
			else 
				$url = str_replace('https://', 'http://', $url);

			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'cms', 'page' => 'cms', 'link' => $url, 'image' => false), $lang['iso_code'], $index, $i, $cms_id['id_cms'])) 
				return false;
		}
		return true;
	}

	/**
	 * Create the Google Sitemap by Shop
	 * @param type $id_shop int the Shop identifier
	 * @return boolean
	 */
	public function createSitemap($id_shop = 0)
	{
		if (@fopen(dirname(__FILE__).'/../../test.txt', 'w') == false)
		{
			$this->context->smarty->assign('google_maps_error', $this->l('An error occured while trying to check your file permissions. Please adjust your permissions to allow PrestaShop to write a file in your root directory.'));
			return false;
		}
		else 
			@unlink(dirname(__FILE__).'/../../test.txt');

		if ($id_shop != 0) 
			$this->context->shop = new Shop((int)$id_shop);

		/* Backward compatibility */
		if (_PS_VERSION_ < 1.5) 
			require(_PS_MODULE_DIR_.'gsitemap/backward_compatibility/backward.php');

		$type = Tools::getValue('type') ? Tools::getValue('type') : '';
		$languages = Language::getLanguages();
		$lang_stop = Tools::getValue('lang') ? true : false;
		$id_obj = Tools::getValue('id') ? (int)Tools::getValue('id') : 0;
		$type_array = array('home', 'meta', 'product', 'category', 'manufacturer', 'supplier', 'cms');
		foreach ($languages as $lang)
		{
			$i = 0;
			$index = (Tools::getValue('index') && Tools::getValue('lang') == $lang['iso_code']) ? (int)Tools::getValue('index') : 0;
			if ($lang_stop && $lang['iso_code'] != Tools::getValue('lang')) 
				continue;
			elseif ($lang_stop && $lang['iso_code'] == Tools::getValue('lang')) 
				$lang_stop = false;

			$link_sitemap = array();
			foreach ($type_array as $type_val)
			{
				if ($type == '' || $type == $type_val)
				{
					$function = '_get'.ucfirst($type_val).'Link';
					if (!$this->$function($link_sitemap, $lang, $index, $i, $id_obj)) return false;
					$type = '';
					$id_obj = 0;
				}
			}

			$this->_recursiveSitemapCreator($link_sitemap, $lang, $index);
			$page = '';
			$index = 0;
		}

		$this->_createIndexSitemap();
		Configuration::updateValue('GSITEMAP_LAST_EXPORT', date('r'));

		Tools::file_get_contents('http://www.google.com/webmasters/sitemaps/ping?sitemap='.urlencode('http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).$this->context->shop->physical_uri.$this->context->shop->virtual_uri.$this->context->shop->id.'_index_sitemap.xml'));

		if ($this->cron) 
			return true;
		header('location: ./index.php?tab=AdminModules&configure=gsitemap&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=gsitemap&validation');
		die();
	}

	/**
	 * Store the generated Sitemap file to the database
	 * @param type $sitemap string the name of the generated Google Sitemap file 
	 * @return type boolean
	 */
	private function _saveSitemapLink($sitemap)
	{
		if ($sitemap) 
			return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gsitemap_sitemap` (`link`, id_shop) VALUES (\''.pSQL($sitemap).'\', '.(int)$this->context->shop->id.')');
	}

	/**
	 * Create the Google Sitemap files
	 * @param type $link_sitemap array containe all the links for the Google Sitemap file to be generated
	 * @param type $lang string the language of link to add
	 * @param type $index int the index of the current Google Sitemap file
	 * @return boolean
	 */
	private function _recursiveSitemapCreator($link_sitemap, $lang, &$index)
	{
		if (!count($link_sitemap)) 
			return false;

		$sitemap_link = $this->context->shop->id.'_'.(is_array($lang) ? $lang['iso_code'] : $lang).'_'.$index.'_sitemap.xml';
		$writeFd = fopen(dirname(__FILE__).'/../../'.$sitemap_link, 'w');

		fwrite($writeFd, '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\r\n");
		foreach ($link_sitemap as $key => $file)
		{
			fwrite($writeFd, '<url>'."\r\n");
			$lastmod = ( isset($file['lastmod']) && !empty($file['lastmod']) ) ? date('c', strtotime($file['lastmod'])) : NULL;
			$this->_addSitemapNode($writeFd, htmlspecialchars(strip_tags($file['link'])), $this->_getPriorityPage($file['page']), Configuration::get('GSITEMAP_FREQUENCY'), $lastmod);
			if ($file['image'])
				$this->_addSitemapNodeImage($writeFd, htmlspecialchars(strip_tags($file['image']['link'])), isset($file['image']['title_img']) ? htmlspecialchars(str_replace(array("\r\n", "\r", "\n"), '', strip_tags($file['image']['title_img']))) : '', isset($file['image']['caption']) ? htmlspecialchars(str_replace(array("\r\n", "\r", "\n"), '', strip_tags($file['image']['caption']))) : '');

			fwrite($writeFd, '</url>'."\r\n");
		}
		fwrite($writeFd, '</urlset>'."\r\n");
		fclose($writeFd);
		$this->_saveSitemapLink($sitemap_link);
		$index++;
	}

	/**
	 * return the priority value set in the configuration parameters
	 * @param type $page string 
	 * @return type string
	 */
	private function _getPriorityPage($page)
	{
		return Configuration::get('GSITEMAP_PRIORITY_'.Tools::strtoupper($page)) ? Configuration::get('GSITEMAP_PRIORITY_'.Tools::strtoupper($page)) : 0.1;
	}

	/**
	 * Add a new line to the sitemap file  
	 * @param type $fd file system object ressource
	 * @param type $loc string the URL of the object page  
	 * @param type $priority string 
	 * @param type $change_freq string
	 * @param type $last_mod timestamp the last modification date/time
	 */
	private function _addSitemapNode($fd, $loc, $priority, $change_freq, $last_mod = NULL)
	{
		fwrite($fd, '<loc>'.(Configuration::get('PS_REWRITING_SETTINGS') ? '<![CDATA['.$loc.']]>' : $loc).'</loc>'."\r\n".'<priority>'."\r\n".number_format($priority, 1, '.', '').'</priority>'."\r\n".($last_mod ? '<lastmod>'.date('c', strtotime($last_mod)).'</lastmod>' : '')."\r\n".'<changefreq>'.$change_freq.'</changefreq>'."\r\n");
	}

	private function _addSitemapNodeImage($fd, $link, $title, $caption)
	{
		fwrite($fd, '<image:image>'."\r\n".'<image:loc>'.(Configuration::get('PS_REWRITING_SETTINGS') ? '<![CDATA['.$link.']]>' : $link).'</image:loc>'."\r\n".'<image:caption><![CDATA['.$caption.']]></image:caption>'."\r\n".'<image:title><![CDATA['.$title.']]></image:title>'."\r\n".'</image:image>'."\r\n");
	}

	/**
	 * Create the index file for all generated sitemaps
	 * @return boolean
	 */
	private function _createIndexSitemap()
	{
		$sitemaps = Db::getInstance()->ExecuteS('SELECT `link` FROM `'._DB_PREFIX_.'gsitemap_sitemap` WHERE id_shop = '.$this->context->shop->id);
		if (!$sitemaps) 
			return false;

		$xml = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>';
		$xml_feed = new SimpleXMLElement($xml);

		foreach ($sitemaps as $link)
		{
			$sitemap = $xml_feed->addChild('sitemap');
			$sitemap->addChild('loc', 'http://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.$link['link']);
			$sitemap->addChild('lastmod', date('c'));
		}
		file_put_contents(dirname(__FILE__).'/../../'.$this->context->shop->id.'_index_sitemap.xml', $xml_feed->asXML());
	}

	/**
	 * Cehck if the given table, column exists
	 * @param type $table_name
	 * @param type $column
	 * @return boolean
	 */
	public function tableColumnExists($table_name, $column = null)
	{

		//p(debug_backtrace(1));
		if (array_key_exists($table_name, $this->sql_checks)) if (!empty($column) && $this->sql_checks[$table_name]) 
			return $this->sql_checks[$table_name][$column];
		else 
			return $this->sql_checks[$table_name];

		$table = Db::getInstance()->ExecuteS('SHOW TABLES LIKE \''.$table_name.'\'');

		if (count($table) < 1) 
			return $this->sql_checks[$table_name] = 0;
		else 
			$this->sql_checks[$table_name] = 1;
		if (!empty($column))
		{
			$this->sql_checks[$table_name] = array();
			$table = Db::getInstance()->ExecuteS('SELECT * FROM `'.$table_name.'` LIMIT 1');
			return $this->sql_checks[$table_name][$column] = (int)array_key_exists($column, current($table));
		}
		return true;
	}

	private function _getStoreUrl($virtual_uri = true)
	{
		$shop = new Shop(Context::getContext()->shop->id);
		$url = Tools::getShopDomainSsl(true).__PS_BASE_URI__.($virtual_uri ? Context::getContext()->shop->virtual_uri : '');

		if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('GSITEMAP_ENABLE_SSL_LINK')) 
			$url = str_replace('http://', 'https://', $url);
		else 
			$url = str_replace('https://', 'http://', $url);

		return $url;
	}

	private function _getAdminFolder()
	{
		$gsitemap_tmp = explode("/", _PS_ADMIN_DIR_);
		return end($gsitemap_tmp);
	}

	private function _getMaxSitmapElementsCount($link_sitemap, $lang, $index, $i)
	{
		$type_array = array('meta', 'product', 'category', 'manufacturer', 'supplier', 'cms');
		$count = 1;
		$languages = Language::getLanguages();
		foreach ($type_array as $type_val)
		{
			$function = '_get'.ucfirst($type_val).'Link';
			$count = $count + $this->$function($link_sitemap, $languages[0], $index, $i, null, true);
		}
		return $count;
	}

}
