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
}