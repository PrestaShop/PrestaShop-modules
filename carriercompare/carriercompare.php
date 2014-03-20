<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class CarrierCompare extends Module
{
	public $template_directory = '';
	public $smarty;
	
	public function __construct()
	{
		$this->name = 'carriercompare';
		$this->tab = 'shipping_logistics';
		$this->version = '2.1.0';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();	

		$this->displayName = $this->l('Shipping Estimate');
		$this->description = $this->l('Compares carrier choices before checkout.');
		$this->template_directory = dirname(__FILE__).'/template/';
		$this->initRetroCompatibilityVar();
	}
	
	// Retro-compatibiliy 1.4/1.5
	private function initRetroCompatibilityVar()
	{			
		if (class_exists('Context'))
			$smarty = Context::getContext()->smarty;
		else
			global $smarty;
		
		$this->smarty = $smarty;
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('shoppingCart') OR !$this->registerHook('header'))
			return false;
		return true;
	}
	
	public function getContent()
	{
		$output = '';
		if (Tools::isSubmit('setGlobalConfiguration'))
			if (Configuration::updateValue('SE_RERESH_METHOD', (int)Tools::getValue('SE_RERESH_METHOD')))
				$output .= $this->displayConfirmation('Configuration updated');
		
		return $output.$this->renderForm();
	}
	
	public function hookHeader($params)
	{
		if (!$this->isModuleAvailable() || !isset($this->context->controller->php_self) || $this->context->controller->php_self != 'order')
			return;

		if (isset($this->context->controller->step) && $this->context->controller->step == 0)
		{
			$this->context->controller->addCSS(($this->_path).'style.css', 'all');
			$this->context->controller->addJS(($this->_path).'carriercompare.js');
		}
	}

	/*
	 ** Hook Shopping Cart Process
	 */
	public function hookShoppingCart($params)
	{
		if (!$this->isModuleAvailable())
			return;
					
		if (!isset($this->context->cart) || $this->context->cart->getProducts() == 0)
			return;			
		
		$protocol = (Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) 
			&& strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
		
		$endURL = __PS_BASE_URI__.'modules/carriercompare/';
	
		if (method_exists('Tools', 'getShopDomainSsl'))
			$moduleURL = $protocol.Tools::getShopDomainSsl().$endURL;
		else
			$moduleURL = $protocol.$_SERVER['HTTP_HOST'].$endURL;
		
		$refresh_method = Configuration::get('SE_RERESH_METHOD');
		
		if(isset($this->context->cookie->id_country) && $this->context->cookie->id_country > 0)
			$id_country = (int)$this->context->cookie->id_country;
		if(!isset($id_country))
			$id_country = (isset($this->context->customer->geoloc_id_country) ? (int)$this->context->customer->geoloc_id_country : (int)Configuration::get('PS_COUNTRY_DEFAULT'));
		if (isset($this->context->customer->id) && $this->context->customer->id && isset($this->context->cart->id_address_delivery) && $this->context->cart->id_address_delivery)
		{
			$address = new Address((int)($this->context->cart->id_address_delivery));
			$id_country = (int)$address->id_country;
		}			
			
			
		if(isset($this->context->cookie->id_state) && $this->context->cookie->id_state > 0)
			$id_state = (int)$this->context->cookie->id_state;
		if(!isset($id_state))
			$id_state = (isset($this->context->customer->geoloc_id_state) ? (int)$this->context->customer->geoloc_id_state : 0);	
			
		if(isset($this->context->cookie->postcode) && $this->context->cookie->postcode > 0)
			$zipcode = Tools::safeOutput($this->context->cookie->postcode);
		if(!isset($zipcode))
			$zipcode = (isset($this->context->customer->geoloc_postcode) ? $this->context->customer->geoloc_postcode : '');

		$this->smarty->assign(array(
			'countries' => Country::getCountries((int)$this->context->cookie->id_lang, true),
			'id_carrier' => ($params['cart']->id_carrier ? $params['cart']->id_carrier : Configuration::get('PS_CARRIER_DEFAULT')),
			'id_country' => $id_country,
			'id_state' => $id_state,
			'zipcode' => $zipcode,
			'currencySign' => $this->context->currency->sign,
			'currencyRate' => $this->context->currency->conversion_rate,
			'currencyFormat' => $this->context->currency->format,
			'currencyBlank' => $this->context->currency->blank,
			'new_base_dir' => $moduleURL,
			'refresh_method' => ($refresh_method === false) ? 0 : $refresh_method
		));

		return $this->display(__FILE__, 'template/carriercompare.tpl');
	}

	/*
	** Get states by Country id, called by the ajax process
	** id_state allow to preselect the selection option
	*/
	public function getStatesByIdCountry($id_country, $id_state = '')
	{
		$states = State::getStatesByIdCountry($id_country);

		return (sizeof($states) ? $states : array());
	}

	/*
	** Get carriers by country id, called by the ajax process
	*/
	public function getCarriersListByIdZone($id_country, $id_state = 0, $zipcode = 0)
	{
		// cookie saving/updating
		$this->context->cookie->id_country = $id_country;
		if ($id_state != 0)
			$this->context->cookie->id_state = $id_state;
		if ($zipcode != 0)
			$this->context->cookie->postcode = $zipcode;

		$id_zone = 0;
		if ($id_state != 0)
			$id_zone = State::getIdZone($id_state);
		if (!$id_zone)
			$id_zone = Country::getIdZone($id_country);

		$carriers = CarrierCompare::getCarriersByCountry($id_country, $id_state, $zipcode, $this->context->cart, $this->context->customer->id);

		return (sizeof($carriers) ? $carriers : array());
	}

	/*
	 * Get all carriers available for this zon
	 */
	public static function getCarriersByCountry($id_country, $id_state, $zipcode, $exiting_cart, $id_customer)
	{
		// Create temporary Address
		$addr_temp = new Address();
		$addr_temp->id_customer = $id_customer;
		$addr_temp->id_country = $id_country;
		$addr_temp->id_state = $id_state;
		$addr_temp->postcode = $zipcode;

		// Populate required attributes
		// Note: Some carrier needs the whole address
		// the '.' will do the job
		$addr_temp->firstname = ".";
		$addr_temp->lastname = ".";
		$addr_temp->address1 = ".";
		$addr_temp->city = ".";
		$addr_temp->alias = "TEMPORARY_ADDRESS_TO_DELETE";
		$addr_temp->save();

		$cart = new Cart();
		$cart->id_currency = $exiting_cart->id_currency;
		$cart->id_lang = $exiting_cart->id_lang;
		$cart->id_address_delivery = $addr_temp->id;
		$cart->add();

		$products = $exiting_cart->getProducts();
		foreach ($products as $key => $product) {
			$cart->updateQty($product['quantity'], $product['id_product']);	
		}

		$carriers = $cart->simulateCarriersOutput(null, true);

		//delete temporary objects
		$addr_temp->delete();
		$cart->delete();

		return $carriers;
	}

	public function simulateSelection($price, $params)
	{
		$cart_data = array();
		$cart_data['price'] = (float)$price;
		$cart_data['order_total'] = (float)$this->context->cart->getOrderTotal() - (float)$this->context->cart->getTotalShippingCost();
		$cart_data['params'] = $params;

		return $cart_data;
	}

	/*
	** Check the validity of the zipcode format depending of the country
	*/
	private function checkZipcode($zipcode, $id_country)
	{
		$country = new Country((int)$id_country);
		if (!Validate::isLoadedObject($country))
			return true;
		$zipcodeFormat = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT `zip_code_format`
				FROM `'._DB_PREFIX_.'country`
				WHERE `id_country` = '.(int)$id_country);

		if (!$country->need_zip_code || !$country->zip_code_format)
			return true;

		$regxMask = str_replace(
				array('N', 'C', 'L'),
				array(
					'[0-9]',
					$country->iso_code,
					'[a-zA-Z]'),
				$country->zip_code_format);
		if (preg_match('/'.$regxMask.'/', $zipcode))
			return true;
		return false;
	}

	/**
	 * This module is shown on front office, in only some conditions
	 * @return bool
	 */
	private function isModuleAvailable()
	{
		$fileName = basename($_SERVER['SCRIPT_FILENAME']);
		/**
		 * This module is only available on standard order process because
		 * on One Page Checkout the carrier list is already available.
		 */
		if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
			return false;
		/**
		 * If visitor is logged, the module isn't available on Front office,
		 * we use the account informations for carrier selection and taxes.
		 */
		/*if (Context::getContext()->customer->id)
			return false;*/
		return true;
	}
	
	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'select',
						'label' => $this->l('How to refresh the carrier list?'),
						'name' => 'SE_RERESH_METHOD',
						'required' => false,
						'desc' => $this->l('This determines when the list of carriers presented to the customer is updated.'),
						'default_value' => (int)$this->context->country->id,
						'options' => array(
							'query' => array(
								array('id' => 0, 'name' => $this->l('Automatically with each field change')),
								array('id' => 1, 'name' => $this->l('When the customer clicks on the "Estimate Shipping Cost" button'))
								),
							'id' => 'id',
							'name' => 'name',
						),
					),
				),
			'submit' => array(
				'title' => $this->l('Save'))
			),
		);
		
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'setGlobalConfiguration';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}
	
	public function getConfigFieldsValues()
	{		
		return array(
			'SE_RERESH_METHOD' => Tools::getValue('SE_RERESH_METHOD', Configuration::get('SE_RERESH_METHOD')),
		);
	}

}

