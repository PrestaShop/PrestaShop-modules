<?php
/*
* Shopgate GmbH
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file AFL_license.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to interfaces@shopgate.com so we can send you a copy immediately.
*
* @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
* @copyright  Shopgate GmbH
* @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
*/

include_once dirname(__FILE__).'/../vendors/shopgate_library/shopgate.php';

class ShopgateConfigPresta extends ShopgateConfig {
	/**
	 * request url
	 */
	const URL_TO_UPDATE_SHOPGATE = 'https://api.shopgate.com/log';

	const DEFAULT_SHOP_SYSTEM_ID = 102;

	protected $currency;
	protected $language;
	protected $use_stock;

	public function startup() {
		// overwrite some library defaults
		$this->plugin_name = 'prestashop';
		$this->enable_redirect_keyword_update = 24;
		$this->enable_ping = 1;
		$this->enable_add_order = 1;
		$this->enable_update_order = 1;
		$this->enable_get_orders = 0;
		$this->enable_get_customer = 1;
		$this->enable_get_items_csv = 1;
		$this->enable_get_categories_csv = 1;
		$this->enable_get_reviews_csv = 0;
		$this->enable_get_pages_csv = 0;
		$this->enable_get_log_file = 1;
		$this->enable_mobile_website = 1;
		$this->enable_cron = 0;
		$this->enable_clear_logfile = 1;
		$this->encoding = 'UTF-8';

		// initialize plugin specific stuff
		$this->use_stock = 1;
		$this->currency = 'EUR';
	}

	public function getLanguage() {
		return $this->language;
	}

	public function getCurrency() {
		return $this->currency;
	}

	public function getUseStock() {
		return $this->use_stock;
	}

	public function setLanguage($value) {
		$this->language = $value;
	}

	public function setCurrency($value) {
		$this->currency = $value;
	}

	public function setUseStock($value) {
		$this->use_stock = $value;
	}

	public function registerPlugin() {

		try {
			$data = array(
				'action' => 'interface_install',
				'uid' => $this->getUniqueId(),
				'url' => Configuration::get('PS_SHOP_DOMAIN') ? Configuration::get('PS_SHOP_DOMAIN') : $this->getShopUrl(),
				'plugin_version' => SHOPGATE_PLUGIN_VERSION,
				'shopping_system_id' => self::DEFAULT_SHOP_SYSTEM_ID,
				'contact_name' => '',
				'contact_phone' => Configuration::get('PS_SHOP_PHONE'),
				'contact_email' => Configuration::get('PS_SHOP_EMAIL'),
				'stats_items' => $this->getStatsItems(),
				'stats_categories' => $this->getStatsCategories(),
				'stats_orders' => $this->getStatsOrders(),
				'stats_currency' => $this->getStatsCurrency(),
				'stats_acs' => $this->getStatsAcs(),
				'stats_unique_visits' => $this->getStatsUniqueVisits(),
				'stats_mobile_visits' => 0);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, ShopgateConfigPresta::URL_TO_UPDATE_SHOPGATE);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_exec($ch);
			curl_close($ch);

		} catch (Exception $e) {

		}
	}

	protected function getShopUrl() {
		return $_SERVER['HTTP_HOST'];
	}

	protected function getUniqueId() {
		return defined(_RIJNDAEL_KEY_) ? md5(_RIJNDAEL_KEY_.self::DEFAULT_SHOP_SYSTEM_ID) : md5(_COOKIE_KEY_.self::DEFAULT_SHOP_SYSTEM_ID);
	}

	protected function getStatsUniqueVisits() {
		$sql = 'SELECT COUNT(DISTINCT(ip_address)) FROM '._DB_PREFIX_.'connections;';

		return Db::getInstance()->getValue($sql);
	}

	protected function getStatsAcs() {
		$sql = 'SELECT AVG(total_paid_tax_incl) FROM '._DB_PREFIX_.'orders';

		return Db::getInstance()->getValue($sql);
	}

	protected function getStatsItems() {
		$sql = 'SELECT COUNT(pa.`id_product_attribute`)
				FROM `'._DB_PREFIX_.'product_attribute` pa

				LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)Configuration::get('PS_LANG_DEFAULT').')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)Configuration::get('PS_LANG_DEFAULT').')
				ORDER BY pa.`id_product_attribute`';

		return Db::getInstance()->getValue($sql);

	}

	protected function getStatsCategories() {
		return count(Category::getSimpleCategories((int)Configuration::get('PS_LANG_DEFAULT')));
	}

	protected function getStatsOrders() {
		$sql = 'SELECT COUNT(id_order) FROM `'._DB_PREFIX_.'orders` WHERE `date_add` > "'.date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")."-1 months")).'"';

		return Db::getInstance()->getValue($sql);
	}

	protected function getStatsCurrency() {
		$currencyItem = Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'));

		return $currencyItem['iso_code'];
	}
}