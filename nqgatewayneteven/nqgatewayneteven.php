<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

if (strpos(dirname(__FILE__), 'nqgatewayneteven') !== false)
	include_once(dirname(__FILE__).'/classes/Gateway.php');

class NqGatewayNeteven extends Module
{
	private $html = '';
	private $gateway = null;

	public function __construct()
	{
		$this->name = 'nqgatewayneteven';

        $tab_name = 'Tools';

		if (constant('_PS_VERSION_') >= 1.4)
            $tab_name = 'market_place';

        $this->tab = $tab_name;
		
		$this->version = '2.7';
		$this->author = 'NetEven';
		
		parent::__construct();

		$this->displayName = $this->l('NetEven');
		$this->description = $this->l('Vendez sur toutes les marketplaces depuis votre PrestaShop');

		$this->feature_url = '/script/set-neteven-categories.php?token='.Tools::encrypt(Configuration::get('PS_SHOP_NAME'));
		$this->order_url = '/script/import-order.php?token='.Tools::encrypt(Configuration::get('PS_SHOP_NAME')).'&active=1';
		$this->product_url = '/script/update-product.php?token='.Tools::encrypt(Configuration::get('PS_SHOP_NAME')).'&active=1';
		
		if (!$this->getSOAP())
			$this->warning = $this->l('SOAP should be installed for this module');

        if (version_compare(_PS_VERSION_, '1.5', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

        if (Module::isInstalled($this->name))
        {
            $this->unInstallHookByVersion();
            $this->installHookByVersion();
            $this->installCarrier();
        }
    }

	public function install()
	{
		if (!parent::install() ||
			!$this->registerHook('updateOrderStatus') ||
            !$this->registerHook('updateCarrier') ||
			!$this->installDB() ||
			!$this->installConfig())
			return false;
		return true;
	}

	public function uninstall()
	{
		// Removing the order status Neteven
		$order_state = new OrderState((int)Gateway::getConfig('ID_ORDER_STATE_NETEVEN'));
		if (!$order_state->delete())
			return false;
		
		// Uninstalling the module
		if (!Configuration::deleteByName('neteven_date_export_product') || !$this->uninstallDB() || !parent::uninstall())
			return false;
		
		return true;
	}

	public function installConfig()
	{
        $rand_letters = range('a','z');
        shuffle($rand_letters);

		// Creation of employee NetEven
		$new_employe = new Employee();
		$new_employe->lastname = 'Employee';
		$new_employe->firstname = 'NetEven';
		$new_employe->id_lang = (int)$this->context->language->id;
		$new_employe->email = 'empl'.rand(0, 100).$rand_letters[0].rand(0, 100).'@neteven.com';
		$new_employe->passwd = $rand_letters[0].'$&-$&-$&-$&'.rand(0, 1000);
		$new_employe->id_profile = 3;
        $new_employe->active = 0;
		$new_employe->add();
		Gateway::updateConfig('ID_EMPLOYEE_NETEVEN', (int)$new_employe->id);

		// Creation of customer Neteven
		$new_customer = new Customer();
		$new_customer->lastname	= 'Client';
		$new_customer->firstname = 'NetEven';
		$new_customer->passwd = $rand_letters[0].'$&-$&-$&-$&'.rand(0, 1000);
		$new_customer->email = 'cust'.rand(0, 100).$rand_letters[0].rand(0, 100).'@neteven.com';
        $new_customer->newsletter = 0;
        $new_customer->optin = 0;
		$new_customer->add();
		Gateway::updateConfig('ID_CUSTOMER_NETEVEN', (int)$new_customer->id);

		// Creation of order status Neteven
		$order_state = new OrderState();
		$order_state->name = array();
		foreach (Language::getLanguages() as $language)
			$order_state->name[(int)$language['id_lang']] = $this->l('Statut NetEven');
		
		$order_state->send_email = false;
		$order_state->color = '#7d204d';
		$order_state->hidden = false;
		$order_state->delivery = false;
		$order_state->logable = false;
		$order_state->invoice = false;
		if ($order_state->add())
		{
			$source = dirname(__FILE__).'/img/os.gif';
			$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
			copy($source, $destination);
		}
		
		// Set the configuration
		Gateway::updateConfig('ID_ORDER_STATE_NETEVEN', $order_state->id);
		Gateway::updateConfig('SHIPPING_DELAY', 3);
		Gateway::updateConfig('COMMENT', $this->l('Livraison rapide et soignée'));
		Gateway::updateConfig('DEFAULT_BRAND', Configuration::get('PS_SHOP_NAME'));
		Gateway::updateConfig('COUNTRY_DEFAULT', 8);
		Gateway::updateConfig('PASSWORD_DEFAULT', 'password');
		Gateway::updateConfig('NETEVEN_URL', 'http://ws.neteven.com/NWS');
		Gateway::updateConfig('NETEVEN_NS', 'urn:NWS:examples');
		Gateway::updateConfig('SYNCHRONISATION_ORDER', 0);
		Gateway::updateConfig('SYNCHRONISATION_PRODUCT', 0);
		Gateway::updateConfig('MAIL_ACTIVE', 1);
		Gateway::updateConfig('SEND_SHIPPING_PRICE', 0);
		Gateway::updateConfig('SHIPPING_BY_PRODUCT', 0);
		Gateway::updateConfig('SHIPPING_BY_PRODUCT_FIELDNAME', 'additional_shipping_cost');
		Gateway::updateConfig('IMAGE_TYPE_NAME', '');

        $this->installCarrier();

		return true;
	}


    private function installCarrier(){

        $id_carrier_neteven = Gateway::getConfig('CARRIER_NETEVEN');
        if (!empty($id_carrier_neteven))
            return;

        $id_carrier = $this->addCarrier('NetEven carrier');
        Gateway::updateConfig('CARRIER_NETEVEN', $id_carrier);
    }

    private function installHookByVersion(){
        if ($this->version < 2)
            return;

        $is_unregister = Gateway::getConfig('REGISTER_HOOK');
        if (!empty($is_unregister))
            return;

        $this->registerHook('updateCarrier');

        Gateway::updateConfig('REGISTER_HOOK', 1);
    }


    private function addCarrier($name, $delay = 'fast')
    {
        $ret = false;

        if (($carrier = new Carrier()))
        {
            $delay_lang = array();
            foreach (Language::getLanguages(false) as $lang)
                $delay_lang[$lang['id_lang']] = $delay;
            $carrier->name = $name;
            $carrier->active = 0;
            $carrier->range_behavior = 1;
            $carrier->need_range = 1;
            $carrier->external_module_name = 'nqgatewayneteven';
            $carrier->shipping_method = 1;
            $carrier->delay = $delay_lang;
            $carrier->is_module = (_PS_VERSION_ < '1.4') ? 0 : 1;

            $ret = $carrier->add();
        }
        return $ret ? $carrier->id : false;
    }
	
	public function installDB()
	{
		// Creation of the tables in a database
		$result = true;
		$queries = array();
		
		$queries[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'orders_gateway_order_state` (
			`id_order_gateway_order_state` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_order` INT UNSIGNED NOT NULL,
			`id_order_state` INT UNSIGNED NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
			PRIMARY KEY (`id_order_gateway_order_state`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		
		$queries[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'orders_gateway` (
			`id_order_gateway` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_order_neteven` INT UNSIGNED NOT NULL,
			`id_order_detail_neteven` INT UNSIGNED NOT NULL,
			`id_order` INT UNSIGNED NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
			PRIMARY KEY (`id_order_gateway`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		
		$queries[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'orders_gateway_customer` (
			`id_order_gateway_customer` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_customer_neteven` INT UNSIGNED,
			`id_customer` INT UNSIGNED NOT NULL,
			`mail_customer_neteven` VARCHAR(255),
			`date_add` DATETIME NOT NULL,
			PRIMARY KEY (`id_order_gateway_customer`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		
		$queries[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'orders_gateway_configuration` (
			`id_order_gateway_configuration` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(255),
			`value` VARCHAR(255),
			PRIMARY KEY (`id_order_gateway_configuration`),
			KEY `name` (`name`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		
		$queries[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'orders_gateway_feature_link` (
			`id_order_gateway_feature_link` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_order_gateway_feature` INT UNSIGNED NOT NULL,
			`id_feature` INT UNSIGNED,
			`id_attribute_group` INT UNSIGNED,
			PRIMARY KEY (`id_order_gateway_feature_link`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
		
		$queries[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'orders_gateway_feature` (
			`id_order_gateway_feature` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(255),
			`value` VARCHAR(255),
			`category` VARCHAR(255),
			PRIMARY KEY (`id_order_gateway_feature`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		foreach ($queries as $query)
			$result &= Db::getInstance()->Execute($query);

		if (!$result)
			return false;

		// Update table `orders_gateway_feature`
		ToolBox::setNetEvenCategories();
		
		return true;
	}
	
	public function uninstallDB()
	{
		// Delete NetEven employee
		$employee = new Employee((int)Gateway::getConfig('ID_EMPLOYEE_NETEVEN'));
		$employee->delete();
		
		// Delete NetEven customer
		$customer = new Customer((int)Gateway::getConfig('ID_CUSTOMER_NETEVEN'));
		$customer->delete();
		
		// Removing tables from the database
		$result = Db::getInstance()->Execute('
			DROP TABLE IF EXISTS
				`'._DB_PREFIX_.'orders_gateway`,
				`'._DB_PREFIX_.'orders_gateway_order_state`,
				`'._DB_PREFIX_.'orders_gateway_customer`,
				`'._DB_PREFIX_.'orders_gateway_configuration`,
				`'._DB_PREFIX_.'orders_gateway_feature_link`,
				`'._DB_PREFIX_.'orders_gateway_feature`
		');
		
		if (!$result)
			return false;
		return true;
	}

    public function unInstallHookByVersion(){
        if ($this->version < 2)
            return;

        $is_unregister = Gateway::getConfig('UNREGISTER_HOOK');
        if (!empty($is_unregister))
            return;

        $this->unregisterHook('addProduct');
        $this->unregisterHook('updateProduct');
        $this->unregisterHook('updateQuantity');
        $this->unregisterHook('updateProductAttribute');

        Gateway::updateConfig('UNREGISTER_HOOK', 1);
    }

    public function hookUpdateCarrier($params)
    {
        if ((int)($params['id_carrier']) != (int)($params['carrier']->id))
        {
            $id_carrier_neteven = Gateway::getConfig('CARRIER_NETEVEN');
            if ($params['id_carrier'] != $id_carrier_neteven)
                return;

            Gateway::updateConfig('CARRIER_NETEVEN', $params['carrier']->id);
        }
    }

	public function hookUpdateOrderStatus($params)
	{
		// If SOAP is not installed
		if (!$this->getSOAP())
			return;
		
		// if synchronization order is not active
		if (!Gateway::getConfig('SYNCHRONISATION_ORDER'))
			return;

		GatewayOrder::getInstance()->setOrderNetEven($params);		
	}


	public function getSOAP()
	{
		if (!class_exists('SoapClient'))
			return false;
		return true;
	}

	public function getContent()
	{
		$this->html = '';

		if (Tools::isSubmit('submitNetEven'))
		{
			if (Tools::getValue('NETEVEN_LOGIN')&& Tools::getValue('NETEVEN_PASSWORD'))
			{
				Gateway::updateConfig('NETEVEN_LOGIN', Tools::getValue('NETEVEN_LOGIN'));
				Gateway::updateConfig('NETEVEN_PASSWORD', Tools::getValue('NETEVEN_PASSWORD'));
				Gateway::updateConfig('COMMENT', Tools::getValue('COMMENT'));
				Gateway::updateConfig('DEFAULT_BRAND', Tools::getValue('DEFAULT_BRAND'));
				Gateway::updateConfig('IMAGE_TYPE_NAME', Tools::getValue('IMAGE_TYPE_NAME'));
				Gateway::updateConfig('SYNCHRONISATION_ORDER', (int)Tools::getValue('SYNCHRONISATION_ORDER'));
				Gateway::updateConfig('SYNCHRONISATION_PRODUCT', (int)Tools::getValue('SYNCHRONISATION_PRODUCT'));
                Gateway::updateConfig('TYPE_SKU', (string)Tools::getValue('TYPE_SKU'));
				
				$this->html .= $this->displayConfirmation($this->l('Les paramètres ont bien été mis à jour'));
			}
			else
				$this->html .= $this->displayError($this->l('Les login et mot de passe NetEven sont obligatoire'));

		}
		elseif (Tools::isSubmit('submitNetEvenShipping'))
		{
			Gateway::updateConfig('SHIPPING_DELAY', Tools::getValue('SHIPPING_DELAY'));
			Gateway::updateConfig('SHIPPING_PRICE_LOCAL', Tools::getValue('SHIPPING_PRICE_LOCAL'));
			Gateway::updateConfig('SHIPPING_PRICE_INTERNATIONAL', Tools::getValue('SHIPPING_PRICE_INTERNATIONAL'));
			Gateway::updateConfig('SHIPPING_BY_PRODUCT', (int)Tools::getValue('SHIPPING_BY_PRODUCT'));
			Gateway::updateConfig('SHIPPING_BY_PRODUCT_FIELDNAME', Tools::getValue('SHIPPING_BY_PRODUCT_FIELDNAME'));

			Gateway::updateConfig('SHIPPING_CARRIER_FRANCE', Tools::getValue('SHIPPING_CARRIER_FRANCE'));
			Gateway::updateConfig('SHIPPING_ZONE_FRANCE', Tools::getValue('SHIPPING_ZONE_FRANCE'));
			Gateway::updateConfig('SHIPPING_CARRIER_INTERNATIONAL', Tools::getValue('SHIPPING_CARRIER_INTERNATIONAL'));
			Gateway::updateConfig('SHIPPING_ZONE_INTERNATIONAL', Tools::getValue('SHIPPING_ZONE_INTERNATIONAL'));


			$this->html .= $this->displayConfirmation($this->l('Les paramètres de livraison ont bien été mis à jour'));
		}
		elseif (Tools::isSubmit('submitDev'))
		{
			Gateway::updateConfig('NETEVEN_URL', Tools::getValue('NETEVEN_URL'));
			Gateway::updateConfig('NETEVEN_NS', Tools::getValue('NETEVEN_NS'));
			Gateway::updateConfig('MAIL_LIST_ALERT', Tools::getValue('MAIL_LIST_ALERT'));
			Gateway::updateConfig('DEBUG', (int)Tools::getValue('DEBUG'));
			Gateway::updateConfig('SEND_REQUEST_BY_EMAIL', (int)Tools::getValue('SEND_REQUEST_BY_EMAIL'));
			
			$this->html .= $this->displayConfirmation($this->l('Les paramètres de maintenance ont bien été mis à jour'));
		}
		elseif (Tools::isSubmit('submitCustomizableFeilds'))
		{
			$customizable_field_name = Tools::getValue('customizable_field_name');
			$customizable_field_value = Tools::getValue('customizable_field_value');
			
			$customizable_string = '';
			foreach ($customizable_field_name as $key => $value)
			{
				if (!$customizable_field_name[$key] || !$customizable_field_value[$key])
					continue;
				
				if ($customizable_string)
					$customizable_string .= '¤';
				
				$customizable_string .= $customizable_field_name[$key].'|'.$customizable_field_value[$key];
			}	
			
			Gateway::updateConfig('CUSTOMIZABLE_FIELDS', $customizable_string);
		}

		// Lists of order status
		$order_states = OrderState::getOrderStates((int)$this->context->cookie->id_lang);

		// Lists of features
		$features = Feature::getFeatures((int)$this->context->cookie->id_lang);
		
		// Lists of attribute groups
		$attribute_groups = AttributeGroup::getAttributesGroups((int)$this->context->cookie->id_lang);

		$neteven_features = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'orders_gateway_feature`');
		$neteven_feature_categories = array();
		foreach ($neteven_features as $neteven_feature)
		{
			if (!isset($neteven_feature_categories[$neteven_feature['category']]))
				$neteven_feature_categories[$neteven_feature['category']] = array();

			$neteven_feature_categories[$neteven_feature['category']][] = $neteven_feature;
		}
		
		if ($this->getSOAP())
			$this->html .= $this->displayForm($order_states, $features, $attribute_groups, $neteven_feature_categories);
		else
			$this->html .= $this->displayError($this->l('This module requires the SOAP extension to run'));

		return $this->html;
	}

	public function displayForm($order_states, $features, $attribute_groups, $neteven_feature_categories)
	{
		$customizable_fields = array();
		if (Gateway::getConfig('CUSTOMIZABLE_FIELDS'))
			foreach (explode('¤', Gateway::getConfig('CUSTOMIZABLE_FIELDS')) as $customizable_field)
				$customizable_fields[] = explode('|', $customizable_field);

		$carriers = Carrier::getCarriers((int)$this->context->cookie->id_lang);

		$this->context->smarty->assign(array(
			'SHIPPING_CARRIER_FRANCE' => Tools::safeOutput(Tools::getValue('SHIPPING_CARRIER_FRANCE', Gateway::getConfig('SHIPPING_CARRIER_FRANCE'))),
			'SHIPPING_ZONE_FRANCE' => Tools::safeOutput(Tools::getValue('SHIPPING_ZONE_FRANCE', Gateway::getConfig('SHIPPING_ZONE_FRANCE'))),
			'SHIPPING_CARRIER_INTERNATIONAL' => Tools::safeOutput(Tools::getValue('SHIPPING_CARRIER_INTERNATIONAL', Gateway::getConfig('SHIPPING_CARRIER_INTERNATIONAL'))),
			'SHIPPING_ZONE_INTERNATIONAL' => Tools::safeOutput(Tools::getValue('SHIPPING_ZONE_INTERNATIONAL', Gateway::getConfig('SHIPPING_ZONE_INTERNATIONAL'))),
			'carriers' => $carriers,
			'order_states' => $order_states,
			'features' => $features,
			'module_path' => $this->_path,
			'module_display_name' => $this->displayName,
			'attribute_groups' => $attribute_groups,
			'neteven_feature_categories' => $neteven_feature_categories,
			'default_currency' => new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT')),
			'format_images' => ImageType::getImagesTypes('products'),
			'cron_feature_url' => Tools::getProtocol(Tools::usingSecureMode()).$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.$this->feature_url,
			'cron_order_url' => Tools::getProtocol(Tools::usingSecureMode()).$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.$this->order_url,
			'cron_product_url' => Tools::getProtocol(Tools::usingSecureMode()).$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.$this->product_url,
			'customizable_fields' => $customizable_fields,
			'neteven_token' => Tools::encrypt(Configuration::get('PS_SHOP_NAME')),
			'NETEVEN_LOGIN' => Tools::safeOutput(Tools::getValue('NETEVEN_LOGIN', Gateway::getConfig('NETEVEN_LOGIN'))),
			'NETEVEN_PASSWORD' => Tools::safeOutput(Tools::getValue('NETEVEN_PASSWORD', Gateway::getConfig('NETEVEN_PASSWORD'))),
			'SYNCHRONISATION_ORDER' => (int)Gateway::getConfig('SYNCHRONISATION_ORDER'),
			'SYNCHRONISATION_PRODUCT' => (int)Gateway::getConfig('SYNCHRONISATION_PRODUCT'),
			'DEFAULT_BRAND' => Tools::safeOutput(Tools::getValue('DEFAULT_BRAND', Gateway::getConfig('DEFAULT_BRAND'))),
			'SHIPPING_DELAY' => Tools::safeOutput(Tools::getValue('SHIPPING_DELAY', Gateway::getConfig('SHIPPING_DELAY'))),
			'IMAGE_TYPE_NAME' => Gateway::getConfig('IMAGE_TYPE_NAME'),
			'COMMENT' => Tools::safeOutput(Tools::getValue('COMMENT', Gateway::getConfig('COMMENT'))),
			'SHIPPING_PRICE_LOCAL' => Tools::safeOutput(Tools::getValue('SHIPPING_PRICE_LOCAL', Gateway::getConfig('SHIPPING_PRICE_LOCAL'))),
			'SHIPPING_PRICE_INTERNATIONAL' => Tools::safeOutput(Tools::getValue('SHIPPING_PRICE_INTERNATIONAL', Gateway::getConfig('SHIPPING_PRICE_INTERNATIONAL'))),
			'SHIPPING_BY_PRODUCT' => (int)Gateway::getConfig('SHIPPING_BY_PRODUCT'),
			'SHIPPING_BY_PRODUCT_FIELDNAME' => Tools::safeOutput(Tools::getValue('SHIPPING_BY_PRODUCT_FIELDNAME', Gateway::getConfig('SHIPPING_BY_PRODUCT_FIELDNAME'))),
			'ID_ORDER_STATE_NETEVEN' => (int)Gateway::getConfig('ID_ORDER_STATE_NETEVEN'),
			'NETEVEN_URL' => Tools::safeOutput(Tools::getValue('NETEVEN_URL', Gateway::getConfig('NETEVEN_URL'))),
			'NETEVEN_NS' => Tools::safeOutput(Tools::getValue('NETEVEN_NS', Gateway::getConfig('NETEVEN_NS'))),
			'MAIL_LIST_ALERT' => Tools::safeOutput(Tools::getValue('MAIL_LIST_ALERT', Gateway::getConfig('MAIL_LIST_ALERT'))),
			'DEBUG' => (int)Gateway::getConfig('DEBUG'),
			'SEND_REQUEST_BY_EMAIL' => (int)Gateway::getConfig('SEND_REQUEST_BY_EMAIL'),
            'TYPE_SKU' => (int)(Gateway::getConfig('TYPE_SKU') !== false)?Gateway::getConfig('TYPE_SKU'):'reference'
		));
			
		return $this->display(__FILE__, 'views/templates/admin/nqgatewayneteven.tpl');
	}
	
	public function getL($key = null)
	{
		$translations = array(
			'Send email to' => $this->l('Send email to'),
			'Start' => $this->l('Start'),
			'Total' => $this->l('Total'),
			'Customer' => $this->l('Customer'),
			'Address' => $this->l('Address'),
			'Order total' => $this->l('Order total'),
			'Order' => $this->l('Order'),
			'History' => $this->l('History'),
			'Order information' => $this->l('Order information'),
			'Order detail' => $this->l('Order detail'),
			'Cart product' => $this->l('Cart product'),
			'No product to send !' => $this->l('No product to send !'),
			'Sends data to NetEven' => $this->l('Sends data to NetEven'),
			'Failed to send data to Neteven' => $this->l('Failed to send data to Neteven'),
			'No EAN13 for product' => $this->l('No EAN13 for product'),
			'Treatment mode' => $this->l('Treatment mode'),
			'Display mode' => $this->l('Display mode'),
			'Quantity of recovered product' => $this->l('Quantity of recovered product'),
			'Quantity of recovered product after remove products without EAN code' => $this->l('Quantity of recovered product after remove products without EAN code'),
			'Problem with a secure key recovery for the customer / NetEven Order Id' => $this->l('Problem with a secure key recovery for the customer / NetEven Order Id'),
			'Failed for cart creation / NetEven Order Id' => $this->l('Failed for cart creation / NetEven Order Id'),
			'Failed for order creation / NetEven Order Id' => $this->l('Failed for order creation / NetEven Order Id'),
			'Add order Id' => $this->l('Add order Id'),
			'NetEven Order Id' => $this->l('NetEven Order Id'),
			'Save order state Id' => $this->l('Save order state Id'),
			'Failed for save export NetEven order Id' => $this->l('Failed for save export NetEven order Id'),
			'Save export NetEven order Id' => $this->l('Save export NetEven order Id'),
			'Get already exported order Id' => $this->l('Get already exported order Id'),
			'Failed for creation of order detail / NetEven Order Id' => $this->l('Failed for creation of order detail / NetEven Order Id'),
			'NetEven order detail id' => $this->l('NetEven order detail id'),
			'Creation of order detail for NetEven order Id' => $this->l('Creation of order detail for NetEven order Id'),
			'Failed for creation of order detail of NetEven order Id' => $this->l('Failed for creation of order detail of NetEven order Id'),
			'Product not found SKU' => $this->l('Product not found SKU'),
			'Creation of customer for NetEven order Id' => $this->l('Creation of customer for NetEven order Id'),
			'Failed for creation of customer of NetEven order Id' => $this->l('Failed for creation of customer of NetEven order Id'),
			'Get existing customer for NetEven Order Id' => $this->l('Get existing customer for NetEven Order Id'),
			'Problem with id_country on address' => $this->l('Problem with id_country on address'),
			'Get existing address for NetEven Order Id' => $this->l('Get existing address for NetEven Order Id'),
			'Creation of address of NetEven order Id' => $this->l('Creation of address of NetEven order Id'),
			'Failed for creation of address of NetEven order Id' => $this->l('Failed for creation of address of NetEven order Id'),
			'Order Id' => $this->l('Order Id'),
			'Failed for save export NetEven order state Id' => $this->l('Failed for save export NetEven order state Id'),
			'Save export of NetEven order state Id' => $this->l('Save export of NetEven order state Id'),
			'Product to update or create' => $this->l('Product to update or create'),
			'Number of product send to NetEven' => $this->l('Number of product send to NetEven'),
			'Debug - Control request' => $this->l('Debug - Control request getOrderNetEven'),
			'NetEven Order Detail' => $this->l('NetEven Order Detail'),
			'Order imported is empty' => $this->l('Order imported is empty'),
			'NetEven response' => $this->l('NetEven response')
		);
		
		if (!$key)
			return $translations;
		
		if (!isset($translations[$key]))
			return $key;
		
		return $translations[$key];
	}
}
