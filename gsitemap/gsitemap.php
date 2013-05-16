<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @version  Release: $Revision: 7515 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Gsitemap extends Module
{
	public $cron = false;

	public function __construct()
	{
		$this->name = 'gsitemap';
		$this->tab = 'seo';
		$this->version = '2.1.1';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Google sitemap');
		$this->description = $this->l('Generate your Google sitemap file');

	}

	public function install()
	{
		Configuration::updateValue('GSITEMAP_PRIORITY_HOME', 1.0);
		Configuration::updateValue('GSITEMAP_PRIORITY_PRODUCT', 0.9);
		Configuration::updateValue('GSITEMAP_PRIORITY_CATEGORY', 0.8);
		Configuration::updateValue('GSITEMAP_PRIORITY_MANUFACTURER', 0.7);
		Configuration::updateValue('GSITEMAP_PRIORITY_SUPPLIER', 0.6);
		Configuration::updateValue('GSITEMAP_PRIORITY_CMS', 0.5);
		Configuration::updateValue('GSITEMAP_FREQUENCY', 'weekly');
		Configuration::updateValue('GSITEMAP_LAST_EXPORT', false);

		return parent::install() && Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gsitemap_sitemap` (`link` varchar(255) DEFAULT NULL) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');
	}

	public function uninstall()
	{
		Configuration::deleteByName('GSITEMAP_PRIORITY_HOME');
		Configuration::deleteByName('GSITEMAP_PRIORITY_PRODUCT');
		Configuration::deleteByName('GSITEMAP_PRIORITY_CATEGORY');
		Configuration::deleteByName('GSITEMAP_PRIORITY_MANUFACTURER');
		Configuration::deleteByName('GSITEMAP_PRIORITY_SUPPLIER');
		Configuration::deleteByName('GSITEMAP_PRIORITY_CMS');
		Configuration::deleteByName('GSITEMAP_FREQUENCY');
		Configuration::deleteByName('GSITEMAP_LAST_EXPORT');
		return parent::uninstall() && $this->removeSitemap();
	}

	public function getContent()
	{
		if (Tools::isSubmit('SubmitGsitemap'))
		{
			Configuration::updateValue('GSITEMAP_FREQUENCY', pSQL(Tools::getValue('gsitemap_frequency')));
			if (Tools::getValue('gsitemap_meta'))
			{
				$meta = '';
				foreach (Tools::getValue('gsitemap_meta') as $val)
					$meta .= (int)$val.',';
				Configuration::updateValue('GSITEMAP_DISABLE_LINKS', $meta);
			}
			$this->removeSitemap();
			$this->createSitemap();
		}
		elseif (Tools::getValue('continue'))
			$this->createSitemap();

		/* Backward compatibility */
		if (_PS_VERSION_ < 1.5)
			require(_PS_MODULE_DIR_.'gsitemap/backward_compatibility/backward.php');

		$this->context->smarty->assign(array(
				'gsitemap_form' => './index.php?tab=AdminModules&configure=gsitemap&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=gsitemap',
				'gsitemap_cron' => _PS_BASE_URL_._MODULE_DIR_.'gsitemap/gsitemap-cron.php?token='.substr(Tools::encrypt('gsitemap/cron'),0,10),
				'gsitemap_feed_exists' => file_exists(dirname(__FILE__).'/../../index_sitemap.xml'),
				'gsitemap_last_export' => Configuration::get('GSITEMAP_LAST_EXPORT'),
				'gsitemap_frequency' => Configuration::get('GSITEMAP_FREQUENCY'),
				'gsitemap_store_url' => 'http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__,
				'gsitemap_links' => Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'gsitemap_sitemap`'),
				'store_metas' => Meta::getMetas(),
				'gsitemap_disable_metas' => explode(',', Configuration::get('GSITEMAP_DISABLE_LINKS')),
				'gsitemap_customer_limit' => array('max_exec_time' => (int)ini_get('max_execution_time'), 'memory_limit' => intval(ini_get('memory_limit'))),
				'prestashop_version' => _PS_VERSION_ >= 1.5 ? '1.5' : '1.4',
				'prestashop_ssl' => Configuration::get('PS_SSL_ENABLED'),
			));

		return $this->display(__FILE__, 'tpl/configuration.tpl');
	}

	public function removeSitemap()
	{
		$links = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'gsitemap_sitemap`');
		if ($links)
		{
			foreach ($links as $link)
				@unlink(dirname(__FILE__).'/../../'.$link['link']);
			return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'gsitemap_sitemap`');
		}
		return true;
	}

	public function _addLinkToSitemap(&$link_sitemap, $new_link, $lang, &$index, &$i, $id_obj)
	{
		if ($i <= 25000 && memory_get_usage() < 100000000)
		{
			$link_sitemap[] = $new_link;
			$i++;
			return true;
		}
		else
		{
			$this->_recursiveSitemapCreator($link_sitemap, $lang, $index);
			if ($index % 20 == 0 && !$this->cron)
			{
				$this->context->smarty->assign(array(
						'gsitemap_number' => (int)$index,
						'gsitemap_refresh_page' => './index.php?tab=AdminModules&configure=gsitemap&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=gsitemap&continue&type='.$new_link['type'].'&lang='.$lang.'&index='.$index.'&id='.($id_obj + 1)));
				return false;
			}
			else if ($index % 20 == 0 && $this->cron)
			{
				header('Refresh: 5; url=http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.'modules/gsitemap/gsitemap-cron.php?continue&token='.substr(Tools::encrypt('gsitemap/cron'),0,10).'&type='.$new_link['type'].'&lang='.$lang.'&index='.$index.'&id='.($id_obj + 1));
				die();
			}
			else
			{
				if ($this->cron)
					header('location: http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.'modules/gsitemap/gsitemap-cron.php?continue&token='.substr(Tools::encrypt('gsitemap/cron'),0,10).'&type='.$new_link['type'].'&lang='.$lang.'&index='.$index.'&id='.($id_obj + 1));
				else
				{
					$admin_folder = str_replace(_PS_ROOT_DIR_, '', _PS_ADMIN_DIR_);
					$admin_folder = substr($admin_folder, 1);
					header('location: http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.$admin_folder.'/index.php?tab=AdminModules&configure=gsitemap&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=gsitemap&continue&type='.$new_link['type'].'&lang='.$lang.'&index='.$index.'&id='.($id_obj + 1));
				}
				die();
			}
		}
	}

	private function _getHomeLink(&$link_sitemap, $lang, &$index, &$i)
	{
		return $this->_addLinkToSitemap($link_sitemap, array('type' => 'home', 'page' => 'home', 'link' => 'http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__, 'image' => false), $lang['iso_code'], $index, $i, -1);
	}

	private function _getMetaLink(&$link_sitemap, $lang, &$index, &$i, $id_meta = 0)
	{
		$link = new Link();

		$metas = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'meta` WHERE `id_meta` > '.(int)$id_meta.' ORDER BY `id_meta` ASC');
		foreach ($metas as $meta)
		{
			if (!in_array($meta['id_meta'], explode(',', Configuration::get('GSITEMAP_DISABLE_LINKS'))))
			{
				if (_PS_VERSION_ >= 1.5)
				{
					$url_rewrite = Db::getInstance()->getValue('SELECT url_rewrite FROM `'._DB_PREFIX_.'meta_lang` WHERE `id_meta` = '.(int)$meta['id_meta']);
					Dispatcher::getInstance()->addRoute($meta['page'], (isset($url_rewrite) ? $url_rewrite : $meta['page']), $meta['page'], $lang['id_lang']);
					$uri_path = Dispatcher::getInstance()->createUrl($meta['page'], $lang['id_lang'], array(), (bool)Configuration::get('PS_REWRITING_SETTINGS'));
					$url = (Configuration::get('PS_SSL_ENABLED') ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));
					$url .= __PS_BASE_URI__.(Language::isMultiLanguageActivated() ? $lang['iso_code'].'/' : '').ltrim($uri_path, '/');
				}
				else
					$url = $link->getPageLink($meta['page'].'.php', true, $lang['id_lang']);
				if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'meta', 'page' => $meta['page'], 'link' => $url, 'image' => false), $lang['iso_code'], $index, $i, $meta['id_meta']))
					return false;
			}
		}
		return true;
	}

	private function _getProductLink(&$link_sitemap, $lang, &$index, &$i, $id_product = 0)
	{
		$link = new Link();

		$products_id = Db::getInstance()->ExecuteS('SELECT `id_product` FROM `'._DB_PREFIX_.'product` WHERE `active` = 1 AND `id_product` > '.(int)$id_product.' ORDER BY `id_product` ASC');
		foreach ($products_id as $product_id)
		{
			$product = new Product((int)$product_id['id_product'], false, (int)$lang['id_lang']);

			if (_PS_VERSION_ >= 1.5)
				$url = $link->getProductLink($product, $product->link_rewrite, $product->category, $product->ean13, (int)$lang['id_lang'], null, 0, true);
			else
			{
				$category = new Category((int)$product->id_category_default, (int)$lang['id_lang']);
				$url = $link->getProductLink($product, $product->link_rewrite, $category->name, $product->ean13, (int)$lang['id_lang']);
			}

			$id_image = Product::getCover((int)$product_id['id_product']);
			if (isset($id_image['id_image']))
				$image_link = $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.(int)$id_image['id_image']);

			$file_headers = @get_headers($image_link);
			if (isset($image_link) && $file_headers[0] != 'HTTP/1.1 404 Not Found')
				if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'product', 'page' => 'product', 'link' => $image_link, 'image' => array('title_img' => Tools::safeOutput($product->name), 'caption' => Tools::safeOutput(strip_tags($product->description_short)))), $lang['iso_code'], $index, $i, $product_id['id_product']))
					return false;
			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'product', 'page' => 'product', 'link' => $url, 'image' => false), $lang['iso_code'], $index, $i, $product_id['id_product']))
				return false;
				
			unset($image_link);
		}
		return true;
	}

	private function _getCategoryLink(&$link_sitemap, $lang, &$index, &$i, $id_category = 0)
	{
		$link = new Link();

		$categories_id = Db::getInstance()->ExecuteS('SELECT `id_category` FROM `'._DB_PREFIX_.'category` WHERE `active` = 1 AND `id_category` != 1 AND `id_category` > '.(int)$id_category.'  ORDER BY `id_category` ASC');
		foreach ($categories_id as $category_id)
		{
			$category = new Category((int)$category_id['id_category'], (int)$lang['id_lang']);

			if (_PS_VERSION_ >= 1.5)
				$url = $link->getCategoryLink($category, null, null, null, (int)$lang['id_lang']);
			else
				$url = $link->getCategoryLink($category, $category->link_rewrite, (int)$lang['id_lang']);

			if ($category->id_image)
				$image_link = $this->context->link->getCatImageLink($category->link_rewrite, (int)$category->id_image, 'category_default');
			$file_headers = @get_headers($image_link);
			if (isset($image_link) && $file_headers[0] != 'HTTP/1.1 404 Not Found')
				if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'category', 'page' => 'category', 'link' => $image_link, 'image' => array('title_img' => Tools::safeOutput($category->name))), $lang['iso_code'], $index, $i, (int)$category_id['id_category']))
					return false;
			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'category', 'page' => 'category', 'link' => $url, 'image' => false), $lang['iso_code'], $index, $i, (int)$category_id['id_category']))
				return false;
				
			unset($image_link);
		}
		return true;
	}

	private function _getManufacturerLink(&$link_sitemap, $lang, &$index, &$i, $id_manufacturer = 0)
	{
		$link = new Link();

		$manufacturers_id = Db::getInstance()->ExecuteS('SELECT `id_manufacturer` FROM `'._DB_PREFIX_.'manufacturer` WHERE `active` = 1  AND `id_manufacturer` > '.(int)$id_manufacturer.' ORDER BY `id_manufacturer` ASC');
		foreach ($manufacturers_id as $manufacturer_id)
		{
			$manufacturer = new Manufacturer((int)$manufacturer_id['id_manufacturer'], $lang['id_lang']);
			$url = $link->getManufacturerLink($manufacturer, $manufacturer->link_rewrite, $lang['id_lang']);

			$image_link = 'http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getMediaServer(_THEME_MANU_DIR_)._THEME_MANU_DIR_.((!file_exists(_PS_MANU_IMG_DIR_.'/'.(int)$manufacturer->id.'-medium_default.jpg')) ? $lang['iso_code'].'-default' : (int)$manufacturer->id).'-medium_default.jpg';
			$file_headers = @get_headers($image_link);
			if ($file_headers[0] != 'HTTP/1.1 404 Not Found')
				if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'manufacturer', 'page' => 'manufacturer', 'link' => $image_link, 'image' => array('title_img' => $manufacturer->name, 'caption' => $manufacturer->short_description)), $lang['iso_code'], $index, $i, $manufacturer_id['id_manufacturer']))
					return false;
			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'manufacturer', 'page' => 'manufacturer', 'link' => $url, 'image' => false), $lang['iso_code'], $index, $i, $manufacturer_id['id_manufacturer']))
				return false;;
		}
		return true;
	}

	private function _getSupplierLink(&$link_sitemap, $lang, &$index, &$i, $id_supplier = 0)
	{
		$link = new Link();

		$suppliers_id = Db::getInstance()->ExecuteS('SELECT `id_supplier` FROM `'._DB_PREFIX_.'supplier` WHERE `active` = 1 AND `id_supplier` > '.(int)$id_supplier.' ORDER BY `id_supplier` ASC');
		foreach ($suppliers_id as $supplier_id)
		{
			$supplier = new Supplier((int)$supplier_id['id_supplier'], $lang['id_lang']);
			$url = $link->getSupplierLink($supplier, $supplier->link_rewrite, $lang['id_lang']);

			$image_link = 'http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getMediaServer(_THEME_SUP_DIR_)._THEME_SUP_DIR_.((!file_exists(_THEME_SUP_DIR_.'/'.(int)$supplier->id.'-medium_default.jpg')) ? $lang['iso_code'].'-default' : (int)$supplier->id).'-medium_default.jpg';

			$file_headers = @get_headers($image_link);
			if ($file_headers[0] != 'HTTP/1.1 404 Not Found')
				if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'supplier', 'page' => 'supplier', 'link' => 'http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getMediaServer(_THEME_SUP_DIR_)._THEME_SUP_DIR_.((!file_exists(_THEME_SUP_DIR_.'/'.(int)$supplier->id.'-medium_default.jpg')) ? $lang['iso_code'].'-default' : (int)$supplier->id).'-medium_default.jpg', 'image' => array('title_img' => $supplier->name)), $lang['iso_code'], $index, $i, $supplier_id['id_supplier']))
					return false;
			if (!$this->_addLinkToSitemap($link_sitemap, array('type' => 'supplier', 'page' => 'supplier', 'link' => $url, 'image' => false), $lang['iso_code'], $index, $i, $supplier_id['id_supplier']))
				return false;
		}
		return true;
	}

	private function _getCmsLink(&$link_sitemap, $lang, &$index, &$i, $id_cms = 0)
	{
		$link = new Link();

		$cmss_id = Db::getInstance()->ExecuteS('SELECT `id_cms` FROM `'._DB_PREFIX_.'cms` WHERE `active` = 1 AND id_cms > '.(int)$id_cms.' ORDER BY `id_cms` ASC');
		foreach ($cmss_id as $cms_id)
		{
			$cms = new CMS((int)$cms_id['id_cms'], $lang['id_lang']);
			if (!$this->_addLinkToSitemap($link_sitemap, array('page' => 'cms', 'link' => $link->getCmsLink($cms, null, null, null, $lang['id_lang']), 'image' => false), $lang['iso_code'], $index, $i, $cms_id['id_cms']))
				return false;
		}
		return true;
	}

	public function createSitemap()
	{
		$i = 0;

		/* Backward compatibility */
		if (_PS_VERSION_ < 1.5)
			require(_PS_MODULE_DIR_.'gsitemap/backward_compatibility/backward.php');

		$index = Tools::getValue('index') ? (int)Tools::getValue('index') : 0;
		$type =  Tools::getValue('type') ? Tools::getValue('type') : '';
		$languages = Language::getLanguages();
		$lang_stop = Tools::getValue('lang') ? true : false;
		$id_obj = Tools::getValue('id') ? (int)Tools::getValue('id') : 0;
		$type_array = array('home', 'meta', 'product', 'category', 'manufacturer', 'supplier', 'cms');
		foreach ($languages as $lang)
		{
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
					if (!$this->$function($link_sitemap, $lang, $index, $i, $id_obj))
						return false;
					$type = '';
					$id_obj = 0;
				}
			}
			$this->_recursiveSitemapCreator($link_sitemap, $lang['iso_code'], $index);
			$page = '';
		}
		$this->_createIndexSitemap();
		Configuration::updateValue('GSITEMAP_LAST_EXPORT', date('r'));

		fopen('http://www.google.com/webmasters/sitemaps/ping?sitemap='.urlencode('http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '').'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.'index_sitemap.xml'), 'r');

		if ($this->cron)
			return true;
		header('location: ./index.php?tab=AdminModules&configure=gsitemap&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=gsitemap&validation');
		die();
	}

	private function _saveSitemapLink($sitemap)
	{
		if ($sitemap)
		{
			$sql = 'INSERT INTO `'._DB_PREFIX_.'gsitemap_sitemap` (`link`) VALUES ("'.pSQL($sitemap).'")';
			Db::getInstance()->Execute($sql);
		}
	}

	private function _arraySearchStr($str, $ar)
	{
		foreach ($ar as $key => $val)
			if ($val == $str && is_string($val))
				return $key;
	}



	private function _recursiveSitemapCreator($link_sitemap, $lang, &$index)
	{
		if (!count($link_sitemap))
			return false;

		$writeFd = fopen(dirname(__FILE__).'/../../'.$lang.'_'.$index.'_sitemap.xml', 'w');

		fwrite($writeFd, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">');
		$nourl = true;
		foreach ($link_sitemap as $key => $file)
		{
			if ($file['image'])
			{
				fwrite($writeFd, '<url>');
				$this->_addSitemapNodeImage($writeFd, htmlspecialchars($file['link']), isset($file['image']['title_img']) ? htmlspecialchars($file['image']['title_img']) : '', isset($file['image']['caption']) ? htmlspecialchars($file['image']['caption']) : '');
				$nourl = false;
			}
			else
			{
				if ($nourl)
					fwrite($writeFd, '<url>');
				$this->_addSitemapNode($writeFd, htmlspecialchars($file['link']), $this->_getPriorityPage($file['page']), Configuration::get('GSITEMAP_FREQUENCY'), date('c'));
				fwrite($writeFd, '</url>');
				$nourl = true;
			}
		}
		fwrite($writeFd, '</urlset>');
		fclose($writeFd);
		$this->_saveSitemapLink($lang.'_'.$index.'_sitemap.xml');
		$index++;
	}

	private function _getPriorityPage($page)
	{
		return Configuration::get('GSITEMAP_PRIORITY_'.Tools::strtoupper($page)) ? Configuration::get('GSITEMAP_PRIORITY_'.Tools::strtoupper($page)) : 0.4;
	}

	private function _addSitemapNode($fd, $loc, $priority, $change_freq, $last_mod = NULL)
	{
		fwrite($fd, '<loc>'.$loc.'</loc><priority>'.number_format($priority,1,'.','').'</priority>'.($last_mod ? '<lastmod>'.$last_mod.'</lastmod>' : '').'<changefreq>'.$change_freq.'</changefreq>');
	}

	private function _addSitemapNodeImage($fd, $link, $title, $caption)
	{
		fwrite($fd, '<image:image><image:loc>'.$link.'</image:loc><image:caption>'.$caption.'</image:caption><image:title>'.$title.'</image:title></image:image>');
	}

	private function _createIndexSitemap()
	{
		$sitemaps = Db::getInstance()->ExecuteS('SELECT `link` FROM `'._DB_PREFIX_.'gsitemap_sitemap`');
		if (!$sitemaps)
			return false;

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"></sitemapindex>';
		$xml_feed = new SimpleXMLElement($xml);
		$http = 'http'.(Configuration::get('PS_SSL_ENABLED') ? 's' : '');

		foreach ($sitemaps as $link)
		{
			$sitemap = $xml_feed->addChild('sitemap');
			$sitemap->addChild('loc', $http.'://'.Tools::getShopDomain(false, true).__PS_BASE_URI__.$link['link']);
			$sitemap->addChild('lastmod', date('c'));
		}
		file_put_contents(dirname(__FILE__).'/../../index_sitemap.xml', $xml_feed->asXML());
	}
}
