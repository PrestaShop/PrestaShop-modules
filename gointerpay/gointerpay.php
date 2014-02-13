<?php
/*
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @version  Release: $Revision: 1.7.4 $
 *
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
	exit;

define('_GOINTERPAY_MAIN_URL_', Configuration::get('GOINTERPAY_CHECKOUT_URL'));
define('_GOINTERPAY_API_URL_', _GOINTERPAY_MAIN_URL_.'iglobalstores/services/OrderRestService/v1.02');
define('_GOINTERPAY_API_UUID_URL_', _GOINTERPAY_MAIN_URL_.'iglobalstores/services/TempCartService');
define('_GOINTERPAY_RATES_URL_', Configuration::get('GOINTERPAY_RATES_FEED_API'));
	
class GoInterpay extends PaymentModule
{
	private $_postErrors = array();

	public function __construct()
	{	
		$this->name = 'gointerpay';
		$this->tab = 'payments_gateways';
		$this->version = '1.7.6';
		$this->author = 'PrestaShop';

		parent::__construct();

		$this->displayName = $this->l('GoInterpay');
		$this->description = $this->l('GoInterpay is the easiest way for merchants to sell and ship globally.');

		/* Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
		
		$this->context->smarty->assign('base_dir', __PS_BASE_URI__);
		
		if (!function_exists('curl_version'))
			$this->warning = $this->l('cURL librairy is not available.');
	}

	public function install()
	{
		if (file_exists(dirname(__FILE__).'/GeoLiteCity.dat') === false)
		{
			$geocities = Tools::file_get_contents('http://api.prestashop.com/maxmind/GeoLiteCity.dat');
	
			if ($geocities == '')
				return false;
			else
				file_put_contents(dirname(__FILE__).'/GeoLiteCity.dat', $geocities);
		}

		/* Create RateOfferID Field */
		if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'currency` ADD `rateoffer_id` VARCHAR( 50 ) NULL;'))
			return false;
		
		/* Create Expiry Field */
		if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'currency` ADD `expiry` DATETIME NULL;'))
			return false;
				
		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gointerpay_cache_currency` (
			  `last_update` datetime NOT NULL
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'))
			return false;

		/* Create and activat currencies */
		$this->_addCurrencies();

		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('adminOrder') || !$this->registerHook('header') || !$this->registerHook('top') || !$this->registerHook('backOfficeTop') || !$this->registerHook('extraRight') || !Configuration::updateValue('GOINTERPAY_RECIPIENT_FIRSTNAME', 'iGlobal') || !Configuration::updateValue('GOINTERPAY_RECIPIENT_LASTNAME', 'GoInterpay') || !$this->registerHook('productActions') || !$this->registerHook('rightColumn'))
			return false;
		if (!is_dir(dirname(__FILE__).'/../../override/classes/'))
			mkdir(dirname(__FILE__).'/../../override/classes/', 0777, true);
		if (file_exists(dirname(__FILE__).'/../../override/classes/Currency.php'))
			rename(dirname(__FILE__).'/../../override/classes/Currency.php', dirname(__FILE__).'/../../override/classes/Currency.origin.php');
		if (!copy(dirname(__FILE__).'/override/classes/Currency.php', dirname(__FILE__).'/../../override/classes/Currency.php'))
			return false;

		$this->registerHook('orderDetailDisplayed');
		if (!Configuration::get('GOINTERPAY_PAYMENT_PENDING'))
			Configuration::updateValue('GOINTERPAY_PAYMENT_PENDING', $this->_addState('Go Interpay: Payment pending', '#DDEEFF'));

		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gointerpay_order_id` (
			`id_cart` int(10) NOT NULL,
			`orderId` varchar(20) DEFAULT NULL,
			`shipping` decimal(17,2) DEFAULT NULL,
			`shipping_orig` decimal(17,2) DEFAULT NULL,
			`taxes` decimal(17,2) DEFAULT NULL,
			`total` decimal(17,2) DEFAULT NULL,
			`products` decimal(17,2) DEFAULT NULL,
			`status` varchar(20) DEFAULT NULL,
			PRIMARY KEY (`id_cart`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'))
			return false;

		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gointerpay_countries` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`country_code` varchar(2) DEFAULT NULL,
			`country_name` varchar(30) DEFAULT NULL,
			`currency_code` varchar(3) DEFAULT NULL,
			`currency_name` varchar(20) DEFAULT NULL,
			`posx` int(10),
			`posy` int(10),		  
			PRIMARY KEY (`id`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'))
			return false;

		if (!Db::getInstance()->Execute('
		INSERT INTO '._DB_PREFIX_.'gointerpay_countries (country_code, country_name, currency_code, currency_name, posx, posy) 
		VALUES ("AD","Andorra","EUR","Euro","632","362"),("AT","Austria","EUR","Euro","695","412"),("BE","Belgium","EUR","Euro","695","462"),
		("CY","Cyprus","EUR","Euro","320","714"),("EE","Estonia","EUR","Euro","253","814"),("FI","Finland","EUR","Euro","126","864"),
		("FR","France","EUR","Euro","190","864"),("DE","Germany","EUR","Euro","63","7"),("GR","Greece","EUR","Euro","253","7"),
		("IE","Ireland, Republic of","EUR","Euro","0","156"),("IT","Italy","EUR","Euro","190","156"),("LU","Luxembourg","EUR","Euro","632","665"),
		("MT","Malta","EUR","Euro","632","715"),("MC","Monaco","EUR","Euro","632","765"),("NL","Netherlands - Holand","EUR","Euro","948","814"),
		("PT","Portugal","EUR","Euro","759","54"),("SM","San Marino","EUR","Euro","822","154"),("SK","Slovakia","EUR","Euro","505","254"),
		("SI","Slovenia","EUR","Euro","569","254"),("ES","Spain","EUR","Euro","63","312"),("AL","Albania","USD","US Dollar","822","313"),
		("AS","American Samoa","USD","US Dollar","569","362"),("AO","Angola","USD","US Dollar","695","362"),("AI","Anguilla","USD","US Dollar","759","362"),
		("AG","Antigua","USD","US Dollar","885","362"),("AM","Armenia","USD","US Dollar","505","412"),("AW","Aruba","USD","US Dollar","569","412"),
		("AZ","Azerbaijan","USD","US Dollar","759","412"),("BS","Bahamas","USD","US Dollar","822","412"),("BH","Bahrain","USD","US Dollar","885","412"),
		("BD","Bangladesh","USD","US Dollar","948","412"),("BB","Barbados","USD","US Dollar","505","452"),("BY","Belarus","USD","US Dollar","632","462"),
		("BZ","Belize","USD","US Dollar","759","462"),("BJ","Benin","USD","US Dollar","822","462"),("BM","Bermuda","USD","US Dollar","885","462"),
		("BT","Bhutan","USD","US Dollar","948","462"),("BO","Bolivia","USD","US Dollar","569","512"),("BW","Botswana","USD","US Dollar","759","512"),
		("BN","Brunei","USD","US Dollar","569","512"),("BG","Bulgaria","USD","US Dollar","632","512"),("BF","Burkina Faso","USD","US Dollar","695","512"),
		("BI","Burundi","USD","US Dollar","759","512"),("KH","Cambodia","USD","US Dollar","885","512"),("CM","Cameroon","USD","US Dollar","948","560"),
		("IC","Canary Islands","USD","US Dollar","190","615"),("CV","Cape Verde","USD","US Dollar","63","615"),("KY","Cayman Islands","USD","US Dollar","126","615"),
		("TD","Chad","USD","US Dollar","253","615"),("CO","Colombia","USD","US Dollar","190","664"),("KM","Comoros","USD","US Dollar","253","664"),
		("CG","Congo","USD","US Dollar","380","664"),("CK","Cook Islands","USD","US Dollar","443","664"),("CR","Costa Rica","USD","US Dollar","0","714"),
		("HR","Croatia","USD","US Dollar","190","714"),("CW","Curacao","USD","US Dollar","253","714"),("DJ","Djibouti","USD","US Dollar","63","764"),
		("DM","Dominica","USD","US Dollar","126","764"),("DO","Dominican Republic","USD","US Dollar","190","764"),("EC","Ecuador","USD","US Dollar","380","764"),
		("EG","Egypt","USD","US Dollar","443","764"),("SV","El Salvador","USD","US Dollar","0","814"),("GQ","Equatorial Guinea","USD","US Dollar","126","814"),
		("ER","Eritrea","USD","US Dollar","190","814"),("ET","Ethiopia","USD","US Dollar","320","814"),("FK","Falkland Islands","USD","US Dollar","443","814"),
		("FO","Faroe Islands - Denmark","USD","US Dollar","0","864"),("FJ","Fiji","USD","US Dollar","63","864"),("GF","French Guiana","USD","US Dollar","253","864"),
		("GA","Gabon","USD","US Dollar","380","864"),("GM","Gambia","USD","US Dollar","443","864"),("GI","Gibraltar","USD","US Dollar","190","7"),
		("GL","Greenland","USD","US Dollar","320","7"),("GD","Grenada","USD","US Dollar","380","7"),("GP","Guadeloupe","USD","US Dollar","190","864"),
		("GU","Guam","USD","US Dollar","443","7"),("GT","Guatemala","USD","US Dollar","0","56"),("GG","Guernsev","USD","US Dollar","63","56"),
		("GN","Guinea","USD","US Dollar","190","56"),("GY","Guyana","USD","US Dollar","253","56"),("HT","Haiti","USD","US Dollar","320","56"),
		("HN","Honduras","USD","US Dollar","443","56"),("IS","Iceland","USD","US Dollar","126","106"),("ID","Indonesia","USD","US Dollar","253","106"),
		("JM","Jamaica","USD","US Dollar","253","156"),("JE","Jersey","USD","US Dollar","443","156"),("JO","Jordan","USD","US Dollar","0","206"),
		("KZ","Kazakhstan","USD","US Dollar","380","206"),("KE","Kenya","USD","US Dollar","43","206"),("KI","Kiribati","USD","US Dollar","63","256"),
		("KR","Korea - Republic of","USD","US Dollar","253","256"),("KG","Kyrgyzstan","USD","US Dollar","443","256"),("LA","Laos","USD","US Dollar","569","615"),
		("LV","Latvia","USD","US Dollar","632","615"),("LS","Lesotho","USD","US Dollar","759","615"),("LR","Liberia","USD","US Dollar","822","615"),
		("LI","Liechtenstein","USD","US Dollar","948","615"),("LT","Lithuania","USD","US Dollar","505","665"),("MO","Macau","USD","US Dollar","695","665"),
		("MG","Madagascar","USD","US Dollar","822","665"),("MW","Malawi","USD","US Dollar","885","665"),("MV","Maldives","USD","US Dollar","505","715"),
		("ML","Mali","USD","US Dollar","569","715"),("MH","Marshall Islands","USD","US Dollar","759","715"),("MQ","Martinique","USD","US Dollar","695","715"),
		("MR","Mauritania","USD","US Dollar","822","715"),("MU","Mauritius","USD","US Dollar","885","715"),("FM","Micronesia","USD","US Dollar","505","765"),
		("MD","Moldova","USD","US Dollar","569","765"),("MN","Mongolia","USD","US Dollar","695","765"),("MS","Montserrat","USD","US Dollar","822","765"),
		("MA","Morocco","USD","US Dollar","948","765"),("MZ","Mozambique","USD","US Dollar","505","814"),("NP","Nepal","USD","US Dollar","822","814"),
		("NC","New Caledonia","USD","US Dollar","885","814"),("NI","Nicaragua","USD","US Dollar","569","864"),("NE","Niger","USD","US Dollar","632","864"),
		("NG","Nigeria","USD","US Dollar","695","864"),("MP","Northern Mariana Island","USD","US Dollar","948","864"),("OM","Oman","USD","US Dollar","569","5"),
		("PK","Pakistan","USD","US Dollar","632","5"),("PW","Palau","USD","US Dollar","695","5"),("PA","Panama","USD","US Dollar","822","5"),
		("PG","Papua New Guinea","USD","US Dollar","885","5"),("PY","Paraguay","USD","US Dollar","948","5"),("PE","Peru","USD","US Dollar","505","54"),
		("PH","Philippines","USD","US Dollar","569","54"),("PL","Poland","USD","US Dollar","695","54"),("PR","Puerto Rico","USD","US Dollar","822","54"),
		("QA","Qatar","USD","US Dollar","885","54"),("RO","Romania","USD","US Dollar","505","104"),("RW","Rwanda","USD","US Dollar","632","104"),
		("ST","Sao Tome & Principe","USD","US Dollar","885","154"),("SA","Saudi Arabia","USD","US Dollar","505","204"),("SN","Senegal","USD","US Dollar","632","204"),
		("SC","Seychelles","USD","US Dollar","759","204"),("ZA","South Africa","USD","US Dollar","822","254"),("SS","South Sudan","USD","US Dollar","759","254"),
		("LK","Sri Lanka","USD","US Dollar","126","312"),("BL","St. Barthelemy","USD","US Dollar","759","104"),("KN","St. Kitts and Nevis","USD","US Dollar","885","104"),
		("LC","St. Lucia","USD","US Dollar","948","104"),("MF","St. Maarten","USD","US Dollar","505","154"),("VC","St. Vincent","USD","US Dollar","632","154"),
		("SD","Sudan","USD","US Dollar","190","312"),("SR","Suriname","USD","US Dollar","253","312"),("SZ","Swaziland","USD","US Dollar","320","312"),
		("PF","Tahiti","USD","US Dollar","0","312"),("TW","Taiwan","USD","US Dollar","63","360"),("TJ","Tajikistan","USD","US Dollar","126","360"),
		("TZ","Tanzania","USD","US Dollar","190","360"),("TH","Thailand","USD","US Dollar","320","360"),("TL","Timor-Leste","USD","US Dollar","253","360"),
		("TG","Togo","USD","US Dollar","443","360"),("TO","Tonga","USD","US Dollar","63","410"),("TT","Trinidad and Tobago","USD","US Dollar","190","410"),
		("TN","Tunisia","USD","US Dollar","320","410"),("TR","Turkey","USD","US Dollar","380","410"),("TM","Turkmenistan","USD","US Dollar","0","458"),
		("TC","Turks and Caicos Islands","USD","US Dollar","63","458"),("UA","Ukraine","USD","US Dollar","380","458"),("US","United States","USD","US Dollar","63","507"),
		("UY","Uruguay","USD","US Dollar","126","507"),("UZ","Uzbekistan","USD","US Dollar","190","507"),("VU","Vanuatu","USD","US Dollar","253","507"),
		("VE","Venezuela","USD","US Dollar","380","507"),("VN","Vietnam","USD","US Dollar","443","507"),("VG","Virgins Islands - British","USD","US Dollar","0","558"),
		("VI","Virgin Islands - U.S","USD","US Dollar","63","558"),("WS","Western Samoa","USD","US Dollar","190","558"),("YE","Yemen","USD","US Dollar","253","558"),
		("ZM","Zambia","USD","US Dollar","320","558"),("ZW","Zimbabwe","USD","US Dollar","380","558"),("AR","Argentina","ARS","Argentina Peso","948","362"),
		("AU","Australia","AUD","Australian Dollar","632","412"),("BR","Brazil","BRL","Brazil Real","822","512"),("CA","Canada","CAD","Canadian Dollar","0","615"),
		("CL","Chile","CLP","Chile Peso","443","615"),("CN","China","CNY","China Yuan Renminbi","0","664"),("CZ","Czech Republic","CZK","Czech Koruna","380","714"),
		("DK","Denmark","DKK","Danish Krone","0","764"),("HK","Hong Kong","HKD","Hong Kong Dollar","0","106"),("HU","Hungary","HUF","Hungary Forint","63","106"),
		("IN","India","INR","India Rupee","190","106"),("IL","Israel","ILS","Israel Shekel","126","156"),("JP","Japan","JPY","Japanese Yen","380","156"),
		("KW","Kuwait","KWD","Kuwait Dinar","380","256"),("MY","Malaysia","MYR","Malaysia Ringgit","948","665"),("MX","Mexico","MXN","Mexican Peso","948","715"),
		("NZ","New Zealand","NZD","New Zealand Dollar","505","864"),("NO","Norway","NOK","Norwegian Krone","505","5"),("RU","Rusia","RUB","Russia Rubble","569","104"),
		("SG","Singapore","SGD","Singapure Dollar","948","204"),("SE","Sweden","SEK","Swedish Krona","380","312"),("CH","Switzerland","CHF","Swiss Franc","443","312"),
		("AE","United Arab Emirates","AED","UA Emirates Dirham","443","458"),("GB","United Kingdom","GBP","Pound","0","507")'))
			return false;

		/* Default values for the Warehouse address */
		Configuration::updateValue('GOINTERPAY_RECIPIENT_ADDRESS', '14572 South 790 West, Suite A200');
		Configuration::updateValue('GOINTERPAY_RECIPIENT_ZIPCODE', '84065');
		Configuration::updateValue('GOINTERPAY_RECIPIENT_CITY', 'Bluffdale');
		$id_country_usa = (int)Country::getByISO('US');
		Configuration::updateValue('GOINTERPAY_RECIPIENT_COUNTRY', (int)$id_country_usa);
		Configuration::updateValue('GOINTERPAY_RECIPIENT_STATE', (int)State::getIdByIso('UT', (int)$id_country_usa));
		Configuration::updateValue('PS_GEOLOCATION_ENABLED', 1);
		@copy(dirname(__FILE__).'/GeoLiteCity.dat', dirname(__FILE__).'/../../tools/geoip/GeoLiteCity.dat');
		
		/* Create a dummy product for displaying Taxes and Duties on invoices and order details */
		Configuration::updateValue('GOINTERPAY_ID_TAXES_TDUTIES', (int)$this->_addTaxesAndDutiesProduct());

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		@unlink(dirname(__FILE__).'/../../override/classes/Currency.php');

		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'gointerpay_order_id`');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'gointerpay_cache_currency`');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'gointerpay_countries`');
		Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'currency` DROP `rateoffer_id`');
		Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'currency` DROP `expiry`');
		Configuration::deleteByName('GOINTERPAY_RECIPIENT_FIRSTNAME');
		Configuration::deleteByName('GOINTERPAY_RECIPIENT_LASTNAME');
		Configuration::deleteByName('GOINTERPAY_RECIPIENT_ADDRESS');
		Configuration::deleteByName('GOINTERPAY_RECIPIENT_ZIPCODE');
		Configuration::deleteByName('GOINTERPAY_RECIPIENT_CITY');
		Configuration::deleteByName('GOINTERPAY_RECIPIENT_COUNTRY');
		Configuration::deleteByName('GOINTERPAY_RECIPIENT_STATE');
		Configuration::deleteByName('GOINTERPAY_STORE');
		Configuration::deleteByName('GOINTERPAY_SECRET');
		Configuration::deleteByName('GOINTERPAY_ID_TAXES_TDUTIES');
		Configuration::deleteByName('GOINTERPAY_SHIPPING_ADDRESS_ID');
		Configuration::deleteByName('GOINTERPAY_EXPORT_PRODUCT');
		Configuration::deleteByName('GOINTERPAY_MERCHANT_ID');
		Configuration::deleteByName('GOINTERPAY_CHECKOUT_URL');
		Configuration::deleteByName('GOINTERPAY_RATES_FEED_API');
		
		$product = new Product((int)Configuration::get('GOINTERPAY_ID_TAXES_TDUTIES'));
		if (Validate::isLoadedObject($product))
			$product->delete();

		return true;
	}
	
	private function _addTaxesAndDutiesProduct()
	{
		$product = new Product();
		$product->id_shop_default = (int)$this->context->shop->id;
		$product->id_manufacturer = 0;
		$product->id_supplier = 0;
		$product->reference = 'TAXDUTIES';
		$product->supplier_reference = '';
		$product->location = '';
		$product->width = 0;
		$product->height = 0;
		$product->depth = 0;
		$product->weight = 0;
		$product->quantity_discount = false;
		$product->ean13 = '';
		$product->upc = '';
		$product->is_virtual = false;
		$product->id_category_default = 2;
		$product->id_tax_rules_group = 1;
		$product->on_sale = 0;
		$product->online_only = 0;
		$product->ecotax = 0;
		$product->minimal_quantity = 1;
		$product->price = 0;
		$product->wholesale_price = 0;
		$product->unity = '';
		$product->unit_price_ratio = 1;
		$product->additional_shipping_cost = 0;
		$product->customizable = 0;
		$product->active = 1;
		$product->condition = 'new';
		$product->show_price = false;
		$product->visibility = 'none';
		$product->date_add = date('Y-m-d H:i:s');
		$product->date_upd = date('Y-m-d H:i:s');
		$product->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $this->l('Taxes and Duties');
		$product->link_rewrite[(int)Configuration::get('PS_LANG_DEFAULT')] = 'taxes-and-duties';
		$result = $product->add();
		
		/* Allowed to be ordered even if stock = 0 */
		if ($result)
		{
			StockAvailable::setProductOutOfStock((int)$product->id, 1);
			StockAvailable::setProductDependsOnStock((int)$product->id, false);
			StockAvailable::setQuantity((int)$product->id, 0, 1000000);
		}
		
		return $result ? (int)$product->id : 0;
	}

	/* Add currencies and activate them in the Back-office */
	private function _addCurrencies()
	{
		$currencies = array(
			'AUD' => array('name' => $this->l('Australian Dollar'), 'iso_code_num' => 36, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'CAD' => array('name' => $this->l('Canadian Dollar'), 'iso_code_num' => 124, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'DKK' => array('name' => $this->l('Danish Krone'), 'iso_code_num' => 208, 'sign' => 'kr', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'EUR' => array('name' => $this->l('Euro'), 'iso_code_num' => 978, 'sign' => '€', 'blank' => true, 'format' => 2, 'decimals' => 2),
			'GBP' => array('name' => $this->l('Pound'), 'iso_code_num' => 826, 'sign' => '£', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'JPY' => array('name' => $this->l('Japanese Yen'), 'iso_code_num' => 392, 'sign' => '¥', 'blank' => true, 'format' => 1, 'decimals' => 0),
			'NOK' => array('name' => $this->l('Norwegian Krone'), 'iso_code_num' => 578, 'sign' => 'kr', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'NZD' => array('name' => $this->l('New Zealand Dollar'), 'iso_code_num' => 554, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'SEK' => array('name' => $this->l('Swedish Krona'), 'iso_code_num' => 752, 'sign' => 'kr', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'USD' => array('name' => $this->l('US Dollar'), 'iso_code_num' => 840, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'AED' => array('name' => $this->l('UA Emirates Dirham'), 'iso_code_num' => 784, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'BRL' => array('name' => $this->l('Brazil Real'), 'iso_code_num' => 986, 'sign' => 'R$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'ARS' => array('name' => $this->l('Argentina Peso'), 'iso_code_num' => 32, 'sign' => '$a', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'CHF' => array('name' => $this->l('Swiss Franc'), 'iso_code_num' => 756, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'CLP' => array('name' => $this->l('Chile Peso'), 'iso_code_num' => 152, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'CNY' => array('name' => $this->l('China Yuan Renminbi'), 'iso_code_num' => 156, 'sign' => '元', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'HKD' => array('name' => $this->l('Hong Kong Dollar'), 'iso_code_num' => 344, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'HUF' => array('name' => $this->l('Hungary Forint'), 'iso_code_num' => 348, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'ILS' => array('name' => $this->l('Israel Shekel'), 'iso_code_num' => 376, 'sign' => '₪', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'INR' => array('name' => $this->l('Indian Rupee'), 'iso_code_num' => 356, 'sign' => 'Rs', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'KWD' => array('name' => $this->l('Kuwait Dinar'), 'iso_code_num' => 414, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'MYR' => array('name' => $this->l('Malasya Ringgit'), 'iso_code_num' => 458, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'RUB' => array('name' => $this->l('Russia Ruble'), 'iso_code_num' => 643, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'SGD' => array('name' => $this->l('Singapure Dollar'), 'iso_code_num' => 702, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'THB' => array('name' => $this->l('Thailand Baht'), 'iso_code_num' => 764, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'TWD' => array('name' => $this->l('Taiwan New Dollar'), 'iso_code_num' => 901, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'MXN' => array('name' => $this->l('Mexican Peso'), 'iso_code_num' => 484, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'CZK' => array('name' => $this->l('Czech Koruna'), 'iso_code_num' => 203, 'sign' => 'Kr', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'PHP' => array('name' => $this->l('Philipine Peso'), 'iso_code_num' => 608, 'sign' => '₱', 'blank' => true, 'format' => 1, 'decimals' => 2),
			'PLN' => array('name' => $this->l('Polish Zloty'), 'iso_code_num' => 985, 'sign' => '$', 'blank' => true, 'format' => 1, 'decimals' => 2));

		$iso_codes = '';
		foreach ($currencies as $k => $currency)
			$iso_codes .= '"'.pSQL($k).'",';
		$iso_codes = rtrim($iso_codes, ',');

		$current_currencies = Db::getInstance()->ExecuteS('
		SELECT c.id_currency, c.iso_code
		FROM '._DB_PREFIX_.'currency c
		WHERE c.iso_code IN ('.$iso_codes.') AND deleted = 0');

		$currencies_list = array();
		foreach ($current_currencies as $currency)
			$currencies_list[] = $currency['iso_code'];

		$currencies_to_add = array_diff(array_keys($currencies), $currencies_list);

		foreach ($currencies_to_add as $currency_to_add)
		{
			$currency = new Currency();
			$currency->iso_code = $currency_to_add;
			$currency->name = $currencies[$currency_to_add]['name'];
			$currency->active = 1;
			$currency->deleted = 0;
			$currency->conversion_rate = 1;
			$currency->iso_code_num = $currencies[$currency_to_add]['iso_code_num'];
			$currency->sign = $currencies[$currency_to_add]['sign'];
			$currency->blank = $currencies[$currency_to_add]['blank'];
			$currency->decimals = $currencies[$currency_to_add]['decimals'] ? 1 : 0;
			$currency->format = $currencies[$currency_to_add]['format'];
			$currency->add();
		}

		$current_currencies = Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'currency SET active = 1 WHERE iso_code IN ('.$iso_codes.')');
		Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'currency SET name = \'US Dollar\' WHERE name = \'Dollar\'');
	}

	private function _addState($en, $color)
	{
		$orderState = new OrderState();
		$orderState->name = array();
		foreach (Language::getLanguages() as $language)
			$orderState->name[$language['id_lang']] = $en;
		$orderState->send_email = false;
		$orderState->color = $color;
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = true;
		if ($orderState->add())
			copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif');
		return $orderState->id;
	}

	/**
	 * @brief Main Form Method
	 *
	 * @return Rendered form
	 */
	public function getContent()
	{
		$this->updateCurrencies();

		$html = '';
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$this->context->controller->addJQueryPlugin('fancybox');
		else
			$html .= '
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
		  	<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';

		/* Check system requirements */
		if (version_compare(PHP_VERSION, '5.3.3') < 0)
			$html .= '<div class="warning warn">'.$this->l('This module requires your hosting to run PHP 5.3.3 or higher. It appears that you are currently running and older version, please get in touch with your hosting provider to resolve that issue.').'</div>';
		if (!extension_loaded('mbstring'))
			$html .= '<div class="warning warn">'.$this->l('This module requires the "mbstring" PHP extension. It appears that this extension is not available on your hosting, please get in touch with your hosting provider to resolve that issue.').'</div>';
			
		if (!empty($_POST) && (Tools::isSubmit('submit') || Tools::isSubmit('submitShipping')))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
			{
				$this->_postProcess();
				if (!count($this->_postErrors))
					$html .= $this->_displayValidation();
				else
					$html .= $this->_displayErrors();				
			}
			else
				$html .= $this->_displayErrors();
		}

		if (Tools::getValue('ajaxCall'))
			die($this->_displayProductListToSelect());

		// Process to select or unselect all products
		if (Tools::getValue('allprodS'))
		{
			$product_to_export = Tools::getValue('id_product');

			if (is_array($product_to_export))
			{
				$product_to_export = array_unique($product_to_export);
				Configuration::updateValue('GOINTERPAY_EXPORT_PRODUCT', Tools::jsonEncode($product_to_export, JSON_NUMERIC_CHECK));
				die($this->l('CHECKED PRODUCTS WERE SELECTED'));
			}
			else
			{
				Configuration::updateValue('GOINTERPAY_EXPORT_PRODUCT', '');
				die($this->l('ALL PRODUCTS WERE UNSELECTED'));
			}
		}

		return $html.$this->_displayAdminTpl();
	}

	private function _displayProductListToSelect()
	{
		$this->context->controller->addJS(array($this->_path.'js/jquery.lightbox_me.js'));

		$sqlprod = Db::getInstance()->ExecuteS('
		SELECT cl.`id_category`, cl.`name` category, pl.`id_product`, pl.`name`
		FROM `'._DB_PREFIX_.'category` c
		INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.id_category = cl.id_category)
		INNER JOIN `'._DB_PREFIX_.'category_product` cp ON (cl.`id_category` = cp.`id_category`	AND cl.`id_lang` = '.(int)$this->context->cookie->id_lang.'	AND cl.`id_shop` = '.(int)$this->context->shop->id.')
		INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (cp.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$this->context->cookie->id_lang.' AND pl.`id_shop` = '.(int)$this->context->shop->id.')
		INNER JOIN `'._DB_PREFIX_.'product_shop` ps ON (pl.id_product = ps.id_product	AND ps.id_shop = '.(int)$this->context->shop->id.')
		WHERE c.is_root_category = 0
		ORDER BY category, pl.`name` ASC');

        foreach ($sqlprod as $row)
        {
            $category = $row['category'];
            $row['value'] = Tools::jsonEncode(array((int)$row['id_category'], (int)$row['id_product']), JSON_NUMERIC_CHECK);
            $gointerpay_export_product = Tools::jsonDecode(Configuration::get('GOINTERPAY_EXPORT_PRODUCT'));
            $row['checked'] = (in_array('['.(int)$row['id_category'].','.(int)$row['id_product'].']', (is_array($gointerpay_export_product)) ? $gointerpay_export_product : array()));
            unset($row['category'], $row['id_category']);

            $gointerpay_category_products_list[$category][] = $row;
        }

		$this->context->smarty->assign('category_products', $gointerpay_category_products_list);

		return $this->display(__FILE__, 'tpl/product_select.tpl');
	}

	private function _displayAdminTpl()
	{
		$this->context->smarty->assign(array(
			'tab' => array(
				'credential' => array(
					'title' => $this->l('Credentials'),
					'content' => $this->_displayCredentialTpl(),
					'icon' => '../modules/'.$this->name.'/img/icon-credential.png',
					'tab' => 1,
					'selected' => !Tools::isSubmit('submitShipping'),
				),
				'shipping' => array(
					'title' => $this->l('Shipping'),
					'content' => $this->_displayShippingTpl(),
					'icon' => '../modules/'.$this->name.'/img/icon-shipping.gif',
					'tab' => 2,
					'selected' => Tools::isSubmit('submitShipping'),
				),
			),
			'logo' => '../modules/'.$this->name.'/img/logo-interpay.png',
			'css' => '../modules/'.$this->name.'/css/'.$this->name.'.css',
			'prestashop_version' => _PS_VERSION_ < 1.5 ? '1.4' : '1.5'));

		if (Tools::isSubmit('allprodS'))
			$msg = 'ALL PRODUCTS WERE SELECTED';
		elseif (Tools::isSubmit('allprodU'))
			$msg = 'ALL PRODUCTS WERE UNSELECTED';
		if (isset($msg))
			$this->context->smarty->assign('msg', $msg);
		$this->context->smarty->assign('product_to_select_template', $this->_displayProductListToSelect());
		$this->context->smarty->assign('interpay_configured', Configuration::get('GOINTERPAY_STORE') != '' && Configuration::get('GOINTERPAY_SECRET') != '');

		return $this->display(__FILE__, 'tpl/admin.tpl');
	}

	private function _displayCredentialTpl()
	{
$env = array();
$env[0][1] = 'test';
$env[0][2] = 'test';
		 	$this->context->smarty->assign(array(
			'formCredential' => './index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name='.$this->name.'&submit',
			'formTool' => './index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name='.$this->name,
			'credentialTitle' => $this->l('Authentication'),
			'credentialText' => $this->l('In order to use the GoInterpay module, please complete the form below.'),
			'credentialInputVar' => array(
				'store' => array(
					'name' => 'store',
					'required' => true,
					'value' => Configuration::get('GOINTERPAY_STORE'),
					'type' => 'text',
					'label' => $this->l('Store'),
					'desc' => $this->l(''),
				),
				'secret' => array(
					'name' => 'secret',
					'required' => true,
					'value' => Configuration::get('GOINTERPAY_SECRET'),
					'type' => 'password',
					'label' => $this->l('Secret'),
					'desc' => $this->l(''),
				),
				'merchantid' => array(
					'name' => 'merchantid',
					'required' => true,
					'value' => Configuration::get('GOINTERPAY_MERCHANT_ID'),
					'type' => 'text',
					'label' => $this->l('Merchant ID'),
					'desc' => $this->l(''),
				),
				'checkouturl' => array(
					'name' => 'checkouturl',
					'required' => true,
					'value' => Configuration::get('GOINTERPAY_CHECKOUT_URL'),
					'type' => 'text',
					'label' => $this->l('Checkout URL (GoInterpay)'),
					'desc' => $this->l(''),
				),
				'ratesfeeapi' => array(
					'name' => 'ratesfeedapi',
					'required' => true,
					'value' => Configuration::get('GOINTERPAY_RATES_FEED_API'),
					'type' => 'text',
					'label' => $this->l('Rates Feed API'),
					'desc' => $this->l(''),
				),
		)));
 
		return $this->display(__FILE__, 'tpl/credential.tpl');
	}

	private function _displayShippingTpl()
	{
		$country = Country::getCountries(Language::getIdByIso('en'));
		$countries = array('0' => array('text' => $this->l('Select a country'), 'states' => null));

		$statesSender = array();
		$statesRecipient = array();

		foreach ($country as $value)
		{
			$countries[$value['id_country']]['text'] = $value['name'];
			$countries[$value['id_country']]['states'] = isset($value['states']) ? $value['states'] : null;

			if ($value['id_country'] == Configuration::get('GOINTERPAY_RECIPIENT_COUNTRY') && isset($value['states']))
			{
				foreach ($value['states'] as $state)
					$statesRecipient[$state['id_state']]['text'] = $state['name'];
			}
			if ($value['id_country'] == Configuration::get('GOINTERPAY_SENDER_COUNTRY') && isset($value['states']))
				foreach ($value['states'] as $state)
					$statesSender[$state['id_state']]['text'] = $state['name'];
		}

		$this->context->smarty->assign(array(
			'formShipping' => './index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name='.$this->name.'&submitShipping',
			'shippingTitle' => $this->l('GoInterpay Warehouse Address'),
			'shippingText' => $this->l('Please fill out the form below with the closest GoInterpay warehouse address that was provided when you created your account.'),
			'shippingInputVar' => array(
				'lastname' => array(
					'name' => 'LastName',
					'required' => true,
					'value' => (isset($_POST['recipientLastName']) ? Tools::safeOutput($_POST['recipientLastName']) : Configuration::get('GOINTERPAY_RECIPIENT_LASTNAME')),
					'type' => 'text',
					'label' => $this->l('Last Name or Company Name'),
					'desc' => '',
				),
				'firstname' => array(
					'name' => 'FirstName',
					'required' => false,
					'value' => (isset($_POST['recipientFirstName']) ? Tools::safeOutput($_POST['recipientFirstName']) : Configuration::get('GOINTERPAY_RECIPIENT_FIRSTNAME')),
					'type' => 'text',
					'label' => $this->l('First Name'),
					'desc' => '',
				),
				'address' => array(
					'name' => 'Address',
					'required' => true,
					'value' => (isset($_POST['recipientAddress']) ? Tools::safeOutput($_POST['recipientAddress']) : Configuration::get('GOINTERPAY_RECIPIENT_ADDRESS')),
					'type' => 'text',
					'label' => $this->l('Address'),
					'desc' => '',
				),
				'zipcode' => array(
					'name' => 'ZipCode',
					'required' => true,
					'value' => (isset($_POST['recipientZipCode']) ? Tools::safeOutput($_POST['recipientZipCode']) : Configuration::get('GOINTERPAY_RECIPIENT_ZIPCODE')),
					'type' => 'text',
					'label' => $this->l('Zip Code'),
					'desc' => '',
				),
				'city' => array(
					'name' => 'City',
					'required' => true,
					'value' => (isset($_POST['recipientCity']) ? Tools::safeOutput($_POST['recipientCity']) : Configuration::get('GOINTERPAY_RECIPIENT_CITY')),
					'type' => 'text',
					'label' => $this->l('City'),
					'desc' => '',
				),
				'country' => array(
					'name' => 'Country',
					'required' => true,
					'defaultValue' => Configuration::get('GOINTERPAY_RECIPIENT_COUNTRY'),
					'value' => $countries,
					'type' => 'select',
					'label' => $this->l('Country'),
					'desc' => '',
				),
				'states' => array(
					'name' => 'State',
					'required' => true,
					'defaultValue' => Configuration::get('GOINTERPAY_RECIPIENT_STATE'),
					'value' => $statesRecipient,
					'type' => 'select',
					'label' => $this->l('State'),
					'desc' => '',
					'hidden' => sizeof($statesRecipient) ? false : true,
				),
		)));

		return $this->display(__FILE__, 'tpl/shipping.tpl');
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('submit'))
			$this->_postValidationCredential();
		elseif (Tools::isSubmit('submitShipping'))
			$this->_postValidationShipping();
	}

	private function _postValidationShipping()
	{
		if (!isset($_POST['recipientLastName']) || $_POST['recipientLastName'] == '')
			$this->_postErrors[] = $this->l('Recipient Last name is missing');
		if (!isset($_POST['recipientAddress']) || $_POST['recipientAddress'] == '')
			$this->_postErrors[] = $this->l('Recipient Address is missing');
		if (!isset($_POST['recipientZipCode']) || $_POST['recipientZipCode'] == '')
			$this->_postErrors[] = $this->l('Recipient Zip Code is missing');
		if (!isset($_POST['recipientCity']) || $_POST['recipientCity'] == '')
			$this->_postErrors[] = $this->l('Recipient City is missing');
		if (!isset($_POST['recipientCountry']) || $_POST['recipientCountry'] == '')
			$this->_postErrors[] = $this->l('Recipient Country is missing');
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('submit'))
		{
			$this->_postProcessCredential();
			
			include_once(_PS_MODULE_DIR_.'gointerpay/Rest.php');			
			$rest = new Rest(Configuration::get('GOINTERPAY_STORE'), Configuration::get('GOINTERPAY_SECRET'));			
			if (!$rest->checkCredentials('store='.Configuration::get('GOINTERPAY_STORE').'&secret='.Configuration::get('GOINTERPAY_SECRET').'&test=true'))			
				$this->_postErrors[] = $this->l('Invalid credentials, please double-check you store ID and secret. If this issue persists, please contact GoInterpay.');		
		}
		else
			$this->_postProcessShipping();
	}

	private function _postValidationCredential()
	{
		if (!isset($_POST['store']) || $_POST['store'] == '')
			$this->_postErrors[] = $this->l('Recipient Store is missing');
		if (!isset($_POST['secret']) || $_POST['secret'] == '')
			$this->_postErrors[] = $this->l('Recipient Secret is missing');
	}

	private function _postProcessCredential()
	{
		Configuration::updateValue('GOINTERPAY_STORE', pSQL(Tools::getValue('store')));
		Configuration::updateValue('GOINTERPAY_SECRET', pSQL(Tools::getValue('secret')));
		Configuration::updateValue('GOINTERPAY_MERCHANT_ID', pSQL(Tools::getValue('merchantid')));
		Configuration::updateValue('GOINTERPAY_CHECKOUT_URL', pSQL(Tools::getValue('checkouturl')));
		Configuration::updateValue('GOINTERPAY_RATES_FEED_API', pSQL(Tools::getValue('ratesfeedapi')));
	}

	private function _postProcessShipping()
	{
		Configuration::updateValue('GOINTERPAY_RECIPIENT_LASTNAME', pSQL(Tools::getValue('recipientLastName')));
		Configuration::updateValue('GOINTERPAY_RECIPIENT_FIRSTNAME', pSQL(Tools::getValue('recipientFirstName')));
		Configuration::updateValue('GOINTERPAY_RECIPIENT_ADDRESS', pSQL(Tools::getValue('recipientAddress')));
		Configuration::updateValue('GOINTERPAY_RECIPIENT_ZIPCODE', pSQL(Tools::getValue('recipientZipCode')));
		Configuration::updateValue('GOINTERPAY_RECIPIENT_CITY', pSQL(Tools::getValue('recipientCity')));
		Configuration::updateValue('GOINTERPAY_RECIPIENT_COUNTRY', (int)Tools::getValue('recipientCountry'));
		Configuration::updateValue('GOINTERPAY_RECIPIENT_STATE', (!isset($_POST['recipientState']) ? 0 : (int)Tools::getValue('recipientState')));

		if (Configuration::get('GOINTERPAY_SHIPPING_ADDRESS_ID'))
		{
			$address = new Address((int)Configuration::get('GOINTERPAY_SHIPPING_ADDRESS_ID'));
			$address->id_country = (int)$_POST['recipientCountry'];
			if (isset($_POST['recipientState']))
				$address->id_state = (int)$_POST['recipientState'];
			else
				$address->id_state = 0;
			$address->alias = 'gointerpay';
			$address->lastname = pSQL(Tools::getValue('recipientLastName'));
			$address->firstname = isset($_POST['recipientFirstName']) && $_POST['recipientFirstName'] != '' ? pSQL(Tools::getValue('recipientFirstName')) : 'Go Interpay';
			$address->address1 = pSQL(Tools::getValue('recipientAddress'));
			$address->city = pSQL(Tools::getValue('recipientCity'));
			$address->postcode = pSQL(Tools::getValue('recipientZipCode'));
			$address->update();
		}
		else
		{
			$address = new Address();
			$address->id_country = (int)$_POST['recipientCountry'];
			if (Tools::getValue('recipientState'))
				$address->id_state = (int)$_POST['recipientState'];
			else
				$address->id_state = 0;
			$address->alias = 'gointerpay';
			$address->lastname = pSQL(Tools::getValue('recipientLastName'));
			$address->firstname = isset($_POST['recipientFirstName']) && $_POST['recipientFirstName'] != '' ? pSQL(Tools::getValue('recipientFirstName')) : 'Go Interpay';
			$address->address1 = pSQL(Tools::getValue('recipientAddress'));
			$address->city = pSQL(Tools::getValue('recipientCity'));
			$address->postcode = pSQL(Tools::getValue('recipientZipCode'));
			if ($address->add())
				Configuration::updateValue('GOINTERPAY_SHIPPING_ADDRESS_ID', (int)$address->id);
		}
	}

	private function _displayErrors()
	{
		$this->context->smarty->assign('postErrors', $this->_postErrors);
		return $this->display(__FILE__, 'tpl/error.tpl');
	}

	private function _displayValidation()
	{
		$this->context->smarty->assign('postValidation', array($this->l('Updated successfully')));
		return $this->display(__FILE__, 'tpl/validation.tpl');
	}

	public function hookorderDetailDisplayed($params)
	{
		if ($params['order']->module != 'gointerpay')
			return false;
		if (!$this->active || !Configuration::get('GOINTERPAY_STORE') || !Configuration::get('GOINTERPAY_SECRET'))
			return false;

		$gointerpayOrder = Db::getInstance()->getValue('SELECT `orderId` FROM `'._DB_PREFIX_.'gointerpay_order_id` WHERE `id_cart` = '.(int)$params['order']->id_cart);

		if ($gointerpayOrder == null)
			return false;
		include_once(_PS_MODULE_DIR_.'gointerpay/Rest.php');
		$rest = new Rest(Configuration::get('GOINTERPAY_STORE'), Configuration::get('GOINTERPAY_SECRET'));
		$this->context->smarty->assign(array('interpay_link' => $rest->getOrderStatusLink($gointerpayOrder), 'interpay_order' => $gointerpayOrder));

		return $this->display(__FILE__, 'tpl/orderDetail.tpl');
	}
	
	public function getAllProductsNotForExport()
	{
		preg_match_all('/\[[0-9]+\,([0-9]+)\]/', Configuration::get('GOINTERPAY_EXPORT_PRODUCT'), $products_for_export);
		$products_for_export = array_flip($products_for_export[1]);
		
		$products = Db::getInstance()->ExecuteS('
		SELECT p.id_product
		FROM `'._DB_PREFIX_.'product` p
		INNER JOIN `'._DB_PREFIX_.'product_shop` ps ON (p.`id_product` = ps.`id_product`)
		WHERE p.`active` = 1 AND ps.`id_shop` = '.(int)$this->context->shop->id);
		
		$not_for_export = array();
		foreach ($products as $product)
			if (!isset($products_for_export[$product['id_product']]))
				$not_for_export[] = (int)$product['id_product'];

		return $not_for_export;
	}

	private function _isExportProduct($idProduct, $id_category = null)
	{
		$products = Tools::jsonDecode(Configuration::get('GOINTERPAY_EXPORT_PRODUCT'));
		if (!is_array($products))
			return false;
		if (!$id_category)
			$product = new Product((int) $idProduct);
		
		$id_category = ($id_category) ? $id_category : $product->getDefaultCategory();
		if (in_array("[$id_category,$idProduct]", $products))
			return true;
		return false;
	}

	public function hookPayment($params)
	{
		if (!$this->active || !Configuration::get('GOINTERPAY_STORE') || !Configuration::get('GOINTERPAY_SECRET'))
			return false;

		$this->context->smarty->assign(array('pathSsl' => (_PS_VERSION_ >= 1.4 ? Tools::getShopDomainSsl(true, true) : '' ).__PS_BASE_URI__.'modules/gointerpay/', 'modulePath' => $this->_path));

		return $this->display(__FILE__, 'tpl/payment.tpl');
	}

	public function hookBackOfficeTop($params)
	{
		if (!$this->active)
			return false;
		$idProduct = (int)Tools::getValue('id_product');
		if (!$idProduct || !(((isset($_GET['tab']) && $_GET['tab'] == 'AdminCatalog')) || (isset($_GET['controller']) && $_GET['controller'] == 'AdminProducts')))
			return false;

		$product_categories = Db::getInstance()->executeS('
		SELECT cl.`name` , cl.`id_category` , cp.`id_product`
		FROM `'._DB_PREFIX_.'category_product` cp
		INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (cp.`id_category` = cl.`id_category` AND cl.`id_lang` = '.(int)$this->context->cookie->id_lang.' AND cl.`id_shop` = '.(int)$this->context->shop->id.')
		INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (cp.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$this->context->cookie->id_lang.' AND pl.`id_shop` = '.(int)$this->context->shop->id.')
		INNER JOIN `'._DB_PREFIX_.'product_shop` ps ON (cp.`id_product` = ps.`id_product` AND ps.`id_shop` = '.(int)$this->context->shop->id.')
		AND cp.`id_product` = '.(int)$idProduct);

		$export = array();

		foreach ($product_categories as $export_product)
		{
			$category = $export_product['name'];
			if ($category == 'Root')
				continue;
			$export[$category] = array('value' => Tools::jsonEncode(array($export_product['id_category'], $idProduct), JSON_NUMERIC_CHECK), 'checked' => $this->_isExportProduct($idProduct, $export_product['id_category']));
		}

		if (Tools::isSubmit('submitAddproduct') || Tools::isSubmit('submitAddproductAndStay'))
		{
			if (isset($_POST['gointerpay_export_product']))
				$gointerpay_export_product = array();

			$gointerpay_export_product = Tools::jsonDecode(Configuration::get('GOINTERPAY_EXPORT_PRODUCT'), 1);

			if (is_array($gointerpay_export_product))
				foreach ($gointerpay_export_product as $key => $row)
					foreach ($export as $cat)
						if ($cat['value'] == $row)
							unset($gointerpay_export_product[$key]);

			if (is_array(Tools::getValue('gointerpay_export')))
				foreach (Tools::getValue('gointerpay_export') as $category_product)
					$gointerpay_export_product[] = $category_product;

			Configuration::updateValue('GOINTERPAY_EXPORT_PRODUCT', Tools::jsonEncode(array_values($gointerpay_export_product), JSON_NUMERIC_CHECK));
		}

		$this->context->smarty->assign('bullet_common_field', '<img src="themes/'.$this->context->employee->bo_theme.'/img/bullet_orange.png" alt="" style="vertical-align: bottom" />');
		$this->context->smarty->assign('interpay_export', $export);

		return $this->display(__FILE__, 'tpl/admin-product.tpl');
	}

	public function hookadminOrder($params)
	{
		if (!$this->active)
			return false;
		$order = new Order($params['id_order']);
		if ($order->module != $this->name)
			return false;

		$cart = new Cart((int)$order->id_cart);

		$interpay_order = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'gointerpay_order_id` WHERE `id_cart` = '.(int)$order->id_cart);
		include_once(_PS_MODULE_DIR_.'gointerpay/Rest.php');
		$rest = new Rest(Configuration::get('GOINTERPAY_STORE'), Configuration::get('GOINTERPAY_SECRET'));

		$result = $rest->orderDetail(Tools::safeOutput($interpay_order['orderId']));

		if ($order->getCurrentState() == Configuration::get('PS_OS_CANCELED') && $interpay_order['status'] != 'Cancel')
		{
			$result = $rest->updateOrderStatus($interpay_order['orderId'], 'VENDOR_CANCELLATION_REQUEST');
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'gointerpay_order_id` SET `status` = \'Cancel\' WHERE `id_cart` = '.(int)$order->id_cart);
			$interpay_order['status'] = 'Cancel';
			$this->context->smarty->assign('interpay_validate', $this->l('Request sent to IGlobal to cancel the order'));
		}
		else if ($order->getCurrentState() == Configuration::get('PS_OS_PAYMENT') && $interpay_order['status'] != 'Accepted')
		{
			$result = $rest->updateOrderStatus($interpay_order['orderId'], 'VENDOR_PREPARING_ORDER');
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'gointerpay_order_id` SET `status` = \'Accepted\' WHERE `id_cart` = '.(int)$order->id_cart);
			$interpay_order['status'] = 'Accepted';
			$this->context->smarty->assign('interpay_validate', $this->l('Order accepted in Gointerpay'));
		}
		else if ($order->getCurrentState() == Configuration::get('PS_OS_SHIPPING') && $interpay_order['status'] != 'Shipped')
		{
			$result = $rest->updateOrderStatus($interpay_order['orderId'], '400', true);
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'gointerpay_order_id` SET `status` = \'Shipped\' WHERE `id_cart` = '.(int)$order->id_cart);
			$interpay_order['status'] = 'Shipped';
			$this->context->smarty->assign('interpay_validate', $this->l('Order updated in Gointerpay'));
		}

		$message = array();
		if ($interpay_order['status'] == 'Pending')
		{
			$message[] = $this->l('You can accept this order by updating the status to "Payment accepted" or cancel it by updating the status to "Canceled".');
			$interpay_status[] = (int)Configuration::get('PS_OS_PAYMENT');
			$interpay_status[] = (int)Configuration::get('PS_OS_CANCELED');
		}
		elseif ($interpay_order['status'] == 'Accepted')
		{
			$message[] = $this->l('Once this order has been shipped, please update the order status to "Shipped".');
			$message[] = $this->l('You can also cancel the order by updating the status to "Canceled".');
			$interpay_status[] = (int)Configuration::get('PS_OS_SHIPPING');
			$interpay_status[] = (int)Configuration::get('PS_OS_CANCELED');
		}
		elseif ($interpay_order['status'] == 'Cancel')
			$message[] = $this->l('This order has been marked as cancelled.');
		elseif ($interpay_order['status'] == 'Shipped')
			$message[] = $this->l('This order has been marked as shipped.');

		$interpay_status[] = Configuration::get('PS_OS_REFUND');

		$this->context->smarty->assign(array(
			'interpay_message' => $message,
			'interpay_order' => $interpay_order,
			'interpay_link' => $rest->getOrderStatusLink(Tools::safeOutput($interpay_order['orderId'])),
			'interpay_status' => Tools::jsonEncode(array('available' => $interpay_status))
		));

		return $this->display(__FILE__, 'tpl/order.tpl');
	}

	public function hookTop($params)
	{
		$this->context->controller->addCSS(($this->_path).'css/gointerpay.css', 'all');

		if (!$this->active)
			return false;

		$html = '';
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$this->context->controller->addJQueryPlugin('fancybox');
		else
			$html = '
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
		  	<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';

		if (isset($params['cart']) && !empty($params['cart']) && $this->_isInternational($params['cart']))
			$this->context->smarty->assign(array('shippingRemove' => true));
		$currencies = Currency::getCurrencies();
		if (!count($currencies))
			return '';

		$interpay_show_popup = 0;
		if (!isset($this->context->cookie->interpay_country_code) || !isset($this->context->cookie->interpay_currency_code))
		{
			$interpay_show_popup = 1;
			$this->context->cookie->interpay_popup = 1;
			if (isset($this->context->country->iso_code))
				$this->context->cookie->interpay_country_code = $this->context->country->iso_code;
		}

		if (Tools::isSubmit('SubmitInterpay'))
		{
			$this->context->cookie->interpay_country_code = Tools::getValue('interpay_country_code');
			$this->context->cookie->interpay_currency_code = Tools::getValue('interpay_currency_code');
			$this->context->cookie->id_currency = (int)Currency::getIdByIsoCode($this->context->cookie->interpay_currency_code);
			Tools::redirect($_SERVER['REQUEST_URI']);
			exit;
		}

		$this->context->smarty->assign('gointerpay_country_name', Db::getInstance()->getValue('
		SELECT cl.name
		FROM '._DB_PREFIX_.'country c
		LEFT JOIN '._DB_PREFIX_.'country_lang cl ON (cl.id_country = c.id_country)
		WHERE iso_code = \''.pSQL($this->context->cookie->interpay_country_code).'\' AND id_lang = '.(int)$this->context->cookie->id_lang));
		$currency = new Currency((int)$this->context->cookie->id_currency);
		$this->context->smarty->assign('gointerpay_defaultCurrency', $currency->name);
		$this->context->smarty->assign('interpay_show_popup', (int)$interpay_show_popup);
		$this->context->smarty->assign('sqlcurrencies', Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'currency ORDER BY name'));
		$this->context->smarty->assign('sqlcountries', Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'gointerpay_countries ORDER BY country_name'));

		$this->context->smarty->assign('interpay_not_for_export', $this->getAllProductsNotForExport());		
		
		return $html.$this->display(__FILE__, 'tpl/top.tpl');
	}

	private function _isInternational($cart, $no_currency_test = false)
	{
		if (isset($cart) && $cart->id_address_delivery != 0)
		{
			$delivery = new Address((int)$cart->id_address_delivery);
			$country = new Country((int)$delivery->id_country);

			return $country->iso_code != 'US';
		}

		return false;
	}

	public function hookExtraRight($params)
	{
		if (!$this->active || !Configuration::get('GOINTERPAY_STORE') || !Configuration::get('GOINTERPAY_SECRET'))
			return false;

		$storeCountry_isoCode = Db::getInstance()->getValue('SELECT iso_code FROM '._DB_PREFIX_.'country WHERE id_country = '.(int)Configuration::get('PS_COUNTRY_DEFAULT'));
		if ($this->context->cookie->interpay_country_code == $storeCountry_isoCode)
			return false;
		if (!$this->_isExportProduct((int)$_GET['id_product']))
			$this->context->smarty->assign('alert', $this->l('This product is not available for shipping to the country you selected.'));
		else
			return false;

		return $this->display(__FILE__, 'tpl/product.tpl');
	}

	public function hookHeader($params)
	{
		if (!$this->active || !Configuration::get('GOINTERPAY_STORE') || !Configuration::get('GOINTERPAY_SECRET'))
			return false;

		$html = '';
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$this->context->controller->addJQueryPlugin('fancybox');
		else
			$html .= '
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
		  	<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';

		if (Tools::getValue('controller') == 'category')
		{
			$disable_add_to_cart = $this->_checkProuctForExportOnCategoryPage();
			$this->context->smarty->assign('disable_add_to_cart', $disable_add_to_cart);
		}		

		$this->updateCurrencies();

		$details = Db::getInstance()->ExecuteS('SELECT i.`orderId`, o.`id_cart`, o.`id_order`'.(_PS_VERSION_ >= 1.5 ? ', o.`reference`' : '').' FROM `'._DB_PREFIX_.'orders` as o, `'._DB_PREFIX_.'gointerpay_order_id` as i WHERE o.`id_cart` = i.`id_cart` AND o.`id_customer` = '.(int)$this->context->customer->id);
		$link = array();
		include_once(_PS_MODULE_DIR_.'gointerpay/Rest.php');
		$rest = new Rest(Configuration::get('GOINTERPAY_STORE'), Configuration::get('GOINTERPAY_SECRET'));

		if (count($details))
		{
			if (version_compare(_PS_VERSION_, 1.5, '<'))
				foreach ($details as $detail)
					$link[(int)$detail['id_order']] = trim(Tools::safeOutput($rest->getOrderStatusLink($detail['orderId'])));
			else
				foreach ($details as $detail)
					$link[Tools::safeOutput($detail['reference'])] = trim(Tools::safeOutput($rest->getOrderStatusLink($detail['orderId'])));
		}

		$this->context->smarty->assign(array('pathSsl' => (_PS_VERSION_ >= 1.4 ? Tools::getShopDomainSsl(true, true) : '' ).__PS_BASE_URI__.'modules/gointerpay/', 'modulePath' => $this->_path, 'links' => $link, 'version15' => (_PS_VERSION_ >= 1.5 ? true : false)));

		$button = true;
		if (!$this->_isInternational($params['cart']))
			$button = false;

		if ($button)
		{
			$alert = array();
			foreach ($params['cart']->getProducts() as $val)
				if (!$this->_isExportProduct((int)$val['id_product']))
					$alert[] = $this->l(Tools::safeOutput($val['name']).' is not a product that can be exported.');
			if (count($alert))
				$this->context->smarty->assign('alert', implode('\n', $alert));
		}

		if (Tools::getValue('step') < 1)
			$button = false;
			
		/* Check that the selected ship to country is available with Interpay */
		if (isset($this->context->cart->id_address_delivery) && $this->context->cart->id_address_delivery && Tools::getValue('ajax') == '')
		{
			$ship_to_address = new Address((int)$this->context->cart->id_address_delivery);
			if (Validate::isLoadedObject($ship_to_address))
			{
				$ship_to_iso_code = Country::getIsoById((int)$ship_to_address->id_country);
				$ship_to_supported_by_interpay = Db::getInstance()->getValue('SELECT country_code FROM '._DB_PREFIX_.'gointerpay_countries WHERE country_code = \''.pSQL($ship_to_iso_code).'\'');
				if ($ship_to_supported_by_interpay != $ship_to_iso_code)
				{
					$this->context->smarty->assign('interpay_not_supported', true);
					$button = false;
				}
			}
		}

		$this->context->smarty->assign('button', $button);		

		return $this->display(__FILE__, 'tpl/header.tpl');
	}

	private function _checkProuctForExportOnCategoryPage()
	{
		$gointerpay_products = Db::getInstance()->executeS('
		SELECT DISTINCT cp.`id_category`, p.`id_product`
		FROM `'._DB_PREFIX_.'product` p
		INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
		INNER JOIN `'._DB_PREFIX_.'category_product` cp ON (p.`id_product` = cp.`id_product`)
		INNER JOIN `'._DB_PREFIX_.'product_shop` ps ON (p.id_product = ps.id_product)
		WHERE cp.`id_category` ='.(int)Tools::getValue('id_category').' AND pl.`id_lang` = '.(int)$this->context->cookie->id_lang.' AND  ps.id_shop = pl.id_shop = '.(int)$this->context->shop->id);

		$javascript_code = 'var div = \'<div class="warning" style="margin: 5px 3px;"><small>'.$this->l('This product is not available for shipping to the country you selected.').'</small></div>\';';
		foreach ($gointerpay_products as $product_link)
		{
			if (!$this->_isExportProduct((int)$product_link['id_product'],(int)$product_link['id_category']))
			{
				$element_id = Configuration::get('PS_REWRITING_SETTINGS') ? 'car?add=1&id_product=' : 'controller=cart&add=1&id_product=';
				$javascript_code .= '$(div).appendTo($(\'a[href*="'.$element_id.(int)$product_link['id_product'].'"]\').parent());'."\n";
				$javascript_code .= '$(\'a[href*="'.$element_id.(int)$product_link['id_product'].'"]\').remove();'."\n";
			}
		}
		return $javascript_code;
	}

	public function updateCurrencies()
	{
		$date = Db::getInstance()->getValue('SELECT `last_update` FROM `'._DB_PREFIX_.'gointerpay_cache_currency`');
		if ($date == null)
		{
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gointerpay_cache_currency` (`last_update`) VALUES (NOW())');
			Currency::refreshCurrencies();
		}
		else
		{
			$hours = floor(abs(strtotime($date) - strtotime('now')) / 3600);
			$expiry = Db::getInstance()->getValue('SELECT c.expiry FROM '._DB_PREFIX_.'currency c WHERE c.iso_code =\''.$this->context->cookie->interpay_currency_code.'\' AND deleted = 0');			
			if ($hours > 1 || (strtotime($expiry) < strtotime('now')))
			{
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'gointerpay_cache_currency` SET `last_update` = NOW()');
				Currency::refreshCurrencies();
			}
		}
	}

	public function validation()
	{
		if (!$this->active || !Configuration::get('GOINTERPAY_STORE') || !Configuration::get('GOINTERPAY_SECRET'))
			return false;

		if (!isset($_GET['orderId']))
			return false;

		include_once(_PS_MODULE_DIR_.'gointerpay/Rest.php');
		$rest = new Rest(Configuration::get('GOINTERPAY_STORE'), Configuration::get('GOINTERPAY_SECRET'));
		$result = $rest->orderDetail(Tools::safeOutput(Tools::getValue('orderId')));
				
		$cart = new Cart((int)$result['cartId']);
		$original_total = Tools::ps_round((float)$cart->getOrderTotal(true, Cart::BOTH), 2);
		
		/* Check the currency code */
		$id_currency_new = (int)Currency::getIdByIsoCode($result['foreignCurrencyCode']);
		if ($id_currency_new)
		{
			$cart->id_currency = (int)$id_currency_new;
			$cart->save();
		}
		else
			die('Sorry, we were not able to accept orders in the following currency: '.Tools::safeOutput($result['foreignCurrencyCode']));
		$name = explode(" ", $result['delivery_address']['name']);
		$lastname =" - ";
		if (isset($name[1])){$lastname=$name[1];}
		/* Update the delivery and billing address */
		$delivery_address = new Address((int)$cart->id_address_delivery);
		$delivery_address->firstname = $name[0];
		$delivery_address->lastname = $lastname;
		$delivery_address->company = $result['delivery_address']['company'];
		$delivery_address->phone = $result['delivery_address']['phone'];
		$delivery_address->phone_mobile = $result['delivery_address']['altPhone'];
		$delivery_address->id_country = (int)Country::getByIso($result['delivery_address']['countryCode']);
		$delivery_address->id_state = (int)State::getIdByIso($result['delivery_address']['state'], (int)$delivery_address->id_country);
		$delivery_address->address1 = $result['delivery_address']['address1'];
		$delivery_address->address2 = $result['delivery_address']['address2'];
		$delivery_address->city = $result['delivery_address']['city'];
		$delivery_address->postcode = $result['delivery_address']['zip'];		
		$delivery_address->save();
		
		/* If no billing address specified, use the delivery address */
		if ($result['invoice_address']['address1'] != '' || $result['invoice_address']['city'] != '')
		{
			$invoice_name = explode(" ", $result['invoice_address']['name']);
			$invoice_lastname =" - ";
			if (isset($invoice_name[1])){$invoice_lastname=$invoice_name[1];}
			$invoice_address = new Address((int)$cart->id_address_invoice);
			$invoice_address->firstname = $invoice_name[0];
			$invoice_address->lastname = $invoice_lastname;
			$invoice_address->company = $result['invoice_address']['company'];
			$invoice_address->phone = $result['invoice_address']['phone'];
			$invoice_address->phone_mobile = $result['invoice_address']['altPhone'];
			$invoice_address->id_country = (int)Country::getByIso($result['invoice_address']['countryCode']);
			$invoice_address->id_state = (int)State::getIdByIso($result['invoice_address']['state'], (int)$invoice_address->id_country);
			$invoice_address->address1 = $result['invoice_address']['address1'];
			$invoice_address->address2 = $result['invoice_address']['address2'];
			$invoice_address->city = $result['invoice_address']['city'];
			$invoice_address->postcode = $result['invoice_address']['zip'];
			if ($cart->id_address_delivery == $cart->id_address_invoice)
			{
				$invoice_address->add();
				$cart->id_address_invoice = (int)$invoice_address->id;
				$cart->save();
			}
			else
				$invoice_address->save();
		}

		/* Store the Order ID and Shipping cost */
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'gointerpay_order_id` (`id_cart`, `orderId`, `shipping`, `shipping_orig`, `taxes`, `total`, `products`, `status`)
		VALUES (\''.(int)$cart->id.'\', \''.pSQL(Tools::getValue('orderId')).'\', \''.(float)$result['shippingTotal'].'\', \''.(float)$result['shippingTotalForeign'].'\', \''.(float)$result['quotedDutyTaxes'].'\', \''.(float)$result['grandTotal'].'\', \''.(float)$result['itemsTotal'].'\', \'Init\')');
	
		/* Add the duties and taxes */
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'specific_price` WHERE id_customer = '.(int)$cart->id_customer.' AND id_product = '.(int)Configuration::get('GOINTERPAY_ID_TAXES_TDUTIES'));

		$specific_price = new SpecificPrice();
		$specific_price->id_product = (int)Configuration::get('GOINTERPAY_ID_TAXES_TDUTIES');
		$specific_price->id_shop = 0;
		$specific_price->id_country = 0;
		$specific_price->id_group = 0;
		$specific_price->id_cart = (int)$cart->id;
		$specific_price->id_product_attribute = 0;
		$specific_price->id_currency = $cart->id_currency;
		$specific_price->id_customer = $cart->id_customer;
		$specific_price->price = (float)$result['quotedDutyTaxesForeign'];
		$specific_price->from_quantity = 1;
		$specific_price->reduction = 0;
		$specific_price->reduction_type = 'percentage';
		$specific_price->from = date('Y-m-d H:i:s');
		$specific_price->to = strftime('%Y-%m-%d %H:%M:%S', (time()+10));
		$specific_price->add();
		
		if (Validate::isLoadedObject($specific_price))
			$cart->updateQty(1, (int)Configuration::get('GOINTERPAY_ID_TAXES_TDUTIES'));

		$result['status'] = 'Pending';
		$total = Tools::ps_round((float)$cart->getOrderTotal(true, Cart::BOTH), 2);

		$message = '
		Total paid on Interpay: '.(float)$result['grandTotalForeign'].' '.(string)$result['foreignCurrencyCode'].'
		Duties and taxes on Interpay: '.(float)$result['quotedDutyTaxesForeign'].' '.(string)$result['foreignCurrencyCode'].'
		Shipping on Interpay: '.(float)$result['shippingTotalForeign'].' '.(string)$result['foreignCurrencyCode'].'
		Currency: '.$result['foreignCurrencyCode'];

		if ($result['status'] == 'Pending')
		{
			$this->context->cart->id = (int)$result['cartId'];
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'gointerpay_order_id` SET `status` = \'Pending\' WHERE `id_cart` = '.(int)$cart->id);

			$order_status = Configuration::get('GOINTERPAY_PAYMENT_PENDING');
			$price_difference = ((abs($original_total - ((float)$result['grandTotalForeign'] - (float)$result['quotedDutyTaxesForeign'] - (float)$result['shippingTotalForeign'])) * 100) / $original_total);

			if ($price_difference > 1)
			{		
				/* Uncomment this line if you would like to decline orders with a too high price difference */
				// $order_status = Configuration::get('PS_OS_ERROR');

				/*$message .= '
				
				Warning: The difference between the price paid and the price to pay was higher than 1% ('.number_format($price_difference, 2, '.', '').'%)
				However, the payment was processed by Interpay, you should get in touch with the customer and Interpay to resolve that matter.';*/
			}
			
			$this->validateOrder((int)$cart->id, (int)$order_status, $total, $this->displayName, $message, array(), NULL, false, $cart->secure_key);

			Tools::redirectLink(__PS_BASE_URI__.'history.php');
		}
		else
			die('Order was not found or cannot be validated at this time, please contact us.');
	}
}
