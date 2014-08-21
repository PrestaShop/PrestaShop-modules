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
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_')) exit;

if (!defined('_PS_VERSION_')) exit;

if (!class_exists('SeurLib')) include_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

class SeurCashOnDelivery extends PaymentModule{

	public function __construct(){

		$this->name = 'seurcashondelivery';
		$this->tab = 'payments_gateways';
		$this->version = '1.0';
		$this->author = 'www.lineagrafica.es';

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		parent::__construct();

		$this->displayName = $this->l('SEUR Cash on delivery');
		$this->description = $this->l('This module runs under SEUR module.');
		$this->path = __PS_BASE_URI__ . 'modules/' . $this->name . '/';
		$this->img_dir = $this->path.'img/';
		$this->css_dir = $this->path.'css/';
		$this->js_dir = $this->path.'js/';

		/** Backward compatibility 1.4 / 1.5 */
		if (version_compare(_PS_VERSION_, '1.5', '<')){ require_once(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php'); }

	}

	public function install()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			if (
				!parent::install()
				|| !$this->registerHook('payment')
				|| !$this->registerHook('paymentReturn')
				|| !$this->registerHook('orderDetailDisplayed')
				|| !$this->registerHook('adminOrder')
				|| !Configuration::updateValue('SEUR_REMCAR_TIPO_CARGO', 1)
				|| !Configuration::updateValue('SEUR_REMCAR_CARGO_MIN', 0)
				|| !Configuration::updateValue('SEUR_REMCAR_MIN_MOSTRAR', 100)
			) return false;
		}
		else{
			if (
				!parent::install()
				|| !$this->registerHook('payment')
				|| !$this->registerHook('paymentReturn')
				|| !Configuration::updateValue('SEUR_REMCAR_TIPO_CARGO', 1)
				|| !$this->registerHook('displayPDFInvoice')
				|| !$this->registerHook('displayOrderDetail')
				|| !$this->registerHook('displayAdminOrder')
			) return false;
		}
		if (!class_exists('SeurLib')){
			$this->warning = $this->l('Still has not configured their SEUR module.');
			return false;
		}
		if (version_compare(_PS_VERSION_, "1.5", ">=") && !Configuration::get('REEMBOLSO_OS_CARGO')){
			$orderState = new OrderState();
			$orderState->name = array();
			foreach(Language::getLanguages() as $language){
				if (Tools::strtolower($language['iso_code']) == 'fr') $orderState->name[$language['id_lang']] = 'SEUR Paiement de la restitution';
				elseif (Tools::strtolower($language['iso_code']) == 'en') $orderState->name[$language['id_lang']] = 'SEUR Refund payment';
				else $orderState->name[$language['id_lang']] = 'SEUR Pago reembolso';
			}
			$orderState->send_email = false;
			$orderState->color = '#7FA0D3';
			$orderState->hidden = false;
			$orderState->delivery = false;
			$orderState->logable = true;
			$orderState->invoice = true;
			$orderState->paid = false;
			$orderState->module_name = 'seurcashondelivery';
			$orderState->unremovable = true;
			$orderState->template = 'order_conf';
			if ($orderState->add()) copy(dirname(__FILE__).'/img/reembolsoestado.gif', _PS_ROOT_DIR_.'/img/os/'.(int)$orderState->id.'.gif');
			Configuration::updateValue('REEMBOLSO_OS_CARGO', (int)$orderState->id);
		}
		$id_modulo = Db::getInstance()->getValue('SELECT id_module FROM `'._DB_PREFIX_.'module` WHERE name="'.pSQL($this->name).'"');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_country` WHERE id_module ='.(int)$id_modulo);
		Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'module_country`
				(id_module, id_country)
			VALUES
				('.(int)$id_modulo.','.(int)Country::getByIso("ES").'),
				('.(int)$id_modulo.','.(int)Country::getByIso("PT").'),
				('.(int)$id_modulo.','.(int)Country::getByIso("AD").');
		');
		return true;
	}

	public function uninstall(){
		if (version_compare(_PS_VERSION_, "1.5", ">=")){
			if (
				!parent::uninstall()
				|| !$this->unregisterHook('payment')
				|| !$this->unregisterHook('paymentReturn')
				|| !Configuration::deleteByName('SEUR_REMCAR_TIPO_CARGO')
				|| !$this->unregisterHook('displayPDFInvoice')
				|| !$this->unregisterHook('displayOrderDetail')
				|| !$this->unregisterHook('displayAdminOrder')
			) return false;
			$orderState = new OrderState((int)Configuration::get('REEMBOLSO_OS_CARGO'));
			if (Validate::isLoadedObject($orderState))
				$orderState->delete();
			Configuration::deleteByName('REEMBOLSO_OS_CARGO');
			return true;
		}
		else{
			if (
				!parent::uninstall()
				|| !$this->unregisterHook('payment')
				|| !$this->unregisterHook('paymentReturn')
				|| !$this->unregisterHook('orderDetailDisplayed')
				|| !$this->unregisterHook('adminOrder')
				|| !Configuration::deleteByName('SEUR_REMCAR_TIPO_CARGO')
				|| !Configuration::deleteByName('SEUR_REMCAR_CARGO_MIN')
				|| !Configuration::deleteByName('SEUR_REMCAR_MIN_MOSTRAR')
			) return false;
			return true;
		}
	}

	public function getContent(){
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submit')){
			$configuracion = Configuration::getMultiple(array(
				'SEUR_REMCAR_CARGO',
				'SEUR_REMCAR_TIPO_CARGO',
				'SEUR_REMCAR_CARGO_MIN',
				'SEUR_REMCAR_MOSTRAR',
				'SEUR_REMCAR_MIN_MOSTRAR'
			));
			$cargo = Tools::getValue('cargo') ? Tools::getValue('cargo') : $configuracion['SEUR_REMCAR_CARGO'];
			$tipo = Tools::getValue('tipo') ? Tools::getValue('tipo') :  $configuracion['SEUR_REMCAR_TIPO_CARGO'];
			$minimo = Tools::getValue('minimo') ? Tools::getValue('minimo') : $configuracion['SEUR_REMCAR_CARGO_MIN'];
			$min = Tools::getValue('min') ? Tools::getValue('min') : $configuracion['SEUR_REMCAR_MIN_MOSTRAR'];
			Configuration::updateValue('SEUR_REMCAR_CARGO', (float)$cargo);
			Configuration::updateValue('SEUR_REMCAR_TIPO_CARGO', (float)$tipo);
			Configuration::updateValue('SEUR_REMCAR_CARGO_MIN', (int)$minimo);
			Configuration::updateValue('SEUR_REMCAR_MIN_MOSTRAR', $min); //@TODO unused variable?
			$output .= $this->displayConfirmation($this->l('Configuracion actualizada.'));
		}
		return $output.$this->displayForm();
	}

	private function displayForm(){
        return '<fieldset>
                    <div class="margin-form">
                        <p>'.$this->l(' This module runs under SEUR module.').'</P>
                    </div>
                    <div class="clear"></div>
                </fieldset>';
    }

	public function execPayment($cart)
	{
		if (!$this->active)
			return ;

		$coste = (float)(abs($cart->getOrderTotal(true, Cart::BOTH)));
		$cargo = number_format($this->getCargo($cart) , 2, '.', '');
		$vales = (float)(abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)));
		$total_con_cargo = $coste - $vales + $cargo;

		$this->context->smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => (int)$cart->id_currency,
			'currencies' => $this->getCurrency((int)$cart->id_currency),
			'coste' => $coste,
			'cargo' => $cargo,
			'total' => $total_con_cargo,
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
            return $this->display($this->path, 'views/templates/front/payment_execution.tpl');
        else
            return $this->display(__FILE__, 'views/templates/front/payment_execution.tpl');

	}

	public function hookPayment($params)
	{
		if (!$this->active || !SeurLib::getConfigurationField('seur_cod')) // Cash on delivery is disabled in Seur Configuration
			return;

		$address = new Address((int)$params['cart']->id_address_delivery);
		$country = new Country((int)$address->id_country);

		$seur_carrier_sen = SeurLib::getSeurCarrier('SEN');
		$seur_carrier_scn = SeurLib::getSeurCarrier('SCN');
		$seur_carrier_sce = SeurLib::getSeurCarrier('SCE');

		$cod_carriers = array($seur_carrier_scn['id'], $seur_carrier_sen['id'], $seur_carrier_sce['id']);

		if (($country->iso_code == 'ES' || $country->iso_code == 'PT' || $country->iso_code == 'AD') &&
		   in_array($params['cart']->id_carrier, $cod_carriers))
		{
			$cost = (float)(abs($params['cart']->getOrderTotal(true, Cart::BOTH)));
			$cargo = number_format($this->getCargo($params['cart']), 2, '.', '');
			$total_con_cargo = (float)($cost + $cargo);

			if (version_compare(_PS_VERSION_, "1.5", ">="))
			{
				$this->context->smarty->assign(array(
					'ruta' => $this->_path,
					'coste' => $cost,
					'cargo' => $cargo,
					'total' => $total_con_cargo,
					'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
					'enlace' => $this->context->link->getModuleLink('seurcashondelivery', 'validation', array(), true),
					'visible' => 1
				));

				$id_carrier = "";
				$delivery_options_array = Tools::getValue('delivery_option');

				if (is_array($delivery_options_array))
					foreach($delivery_options_array as $id_carrier)
						if ($seur_carrier_scn['id'] == (int)$id_carrier || $seur_carrier_sen['id'] == (int)$id_carrier || $seur_carrier_sce['id'] == (int)$id_carrier)
							return $this->display(__FILE__, 'views/templates/hook/payment.tpl');

				if ($id_carrier == "")
				{
					if (in_array(Configuration::get('PS_CARRIER_DEFAULT'), $cod_carriers))
						return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
					else
					{
						$this->context->smarty->assign('visible', 0);
						return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
					}
				}
			}
			else // PS < 1.5
			{
				$smarty = $this->context->smarty;

				$url = $this->getModuleLink('seurcashondelivery', 'payment.php');
				$smarty->assign(array(
					'ruta' => $this->_path,
					'coste' => $cost,
					'cargo' => $cargo,
					'total' => $total_con_cargo,
					'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
					'enlace' => $url,
					'visible' => 1
				));

				if (in_array(Tools::getValue('id_carrier'), $cod_carriers))
					return $this->display($this->path, 'views/templates/hook/payment.tpl');
				else // opc
				{
					$this->context->smarty->assign('visible', 0);
					return $this->display($this->path, 'views/templates/hook/payment.tpl');
				}
			}
		}

		return '';
	}

	public function hookPaymentReturn($params)
    {
            if (!$this->active)
                    return;

            $this->context->smarty->assign(array(
                    'id_order' => $params['objOrder']->id
            ));
			if (version_compare(_PS_VERSION_, '1.5', '<'))
				return $this->display($this->path, 'views/templates/hook/confirmation.tpl');
			else
				return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

	public function getCargo($cart, $check_id_currency = true)
	{
 		$minimo = Configuration::get('SEUR_REMCAR_CARGO_MIN');
 		$minimo = str_replace(',','.',$minimo);

		if ($check_id_currency && $cart->id_currency != Configuration::get('PS_CURRENCY_DEFAULT'))
			$minimo = Tools::convertPrice($minimo, new Currency((int)$cart->id_currency));

 		$total_carrito = (float)($cart->getOrderTotal(true, Cart::BOTH));
 		$porcentaje = Configuration::get('SEUR_REMCAR_CARGO');
 		$porcentaje = str_replace(',','.',$porcentaje);
		$porcentaje = $porcentaje / 100;
 		$cargo = $total_carrito * $porcentaje;

 		if ($cargo < $minimo)
			$cargo = $minimo;

 		return (float)($cargo);
	}

	public function hookDisplayPDFInvoice($params)
	{
		$sql = "SELECT `id_cart`, `module` FROM `"._DB_PREFIX_."orders` WHERE `id_order` = ".(int)$params['object']->id_order;
		$modulo=Db::getInstance()->executeS($sql);
		if (strcmp($modulo[0]['module'], "seurcashondelivery")==0){
			$this->smarty->assign('reembolso_cargo', number_format($this->getCargo(new Cart((int)$modulo[0]['id_cart'])) , 2, '.', ''));
			if (version_compare(_PS_VERSION_, '1.5', '<'))
				return $this->display($this->path, 'views/templates/hook/pdf.tpl');
			else
				return $this->display(__FILE__, 'views/templates/hook/pdf.tpl');
		}
	}

	public function hookDisplayOrderDetail($params)
	{
		if (!is_object($params['order']))
			$params['order'] = new Order((int)$params['id_order']);

		if (!is_object($params['order'])){ return(false); }
		$sql = "SELECT `id_cart`, `module` FROM `"._DB_PREFIX_."orders` WHERE `id_order` = ".(int)$params['order']->id;
		$modulo = Db::getInstance()->executeS($sql);
		$modulo_name=$modulo[0]['module'];

		$reembolso_cargo = number_format($this->getCargo(new Cart((int)$modulo[0]['id_cart'])) , 2, '.', '');
		$order_currency = new Currency((int)$params['order']->id_currency);
		$reembolso_cargo = Tools::convertPrice($reembolso_cargo, $order_currency);

		$this->smarty->assign(array(
			'reembolso_cargo' => $reembolso_cargo,
			'modulo' => $modulo_name
		));
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return($this->display($this->path, 'views/templates/hook/detallecomprareembolso.tpl'));
		else
			return $this->display(__FILE__, 'views/templates/hook/detallecomprareembolso.tpl');
	}

	public function hookOrderDetailDisplayed($params)
	{
		return($this->hookDisplayOrderDetail($params));
	}

	public function hookDisplayAdminOrder($params)
	{
		if (!is_object($params['cart'])){
			$order = new Order((int)$params['id_order']);
			if ($order->id_cart > 0){ $params['cart'] = new Cart((int)$order->id_cart); }
		}
		if (!is_object($params['cart'])){ return(false); }
		$sql = "SELECT `module` FROM `"._DB_PREFIX_."orders` WHERE `id_cart` = ".(int)$params['cart']->id;
		$modulo = Db::getInstance()->executeS($sql);
		$modulo = $modulo[0]['module'];
		$this->smarty->assign(array(
			'reembolso_cargo' => number_format($this->getCargo($params['cart']) , 2, '.', ''),
			'modulo' => $modulo
		));
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return $this->display($this->path, 'views/templates/hook/cargocomprareembolso.tpl');

		else
			return $this->display(__FILE__, 'views/templates/hook/cargocomprareembolso.tpl');
	}

	public function hookAdminOrder($params)
	{
		return($this->hookDisplayAdminOrder($params));
	}

	public function validateOrderFORWEBS_v5($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = false, $secure_key = false, Shop $shop = null)
	{
        $this->context->cart = new Cart($id_cart);
        $cargo = number_format($this->getCargo($this->context->cart) , 2, '.', '');
        $this->context->customer = new Customer((int)$this->context->cart->id_customer);
		$this->context->language = new Language((int)$this->context->cart->id_lang);
		$this->context->shop = ($shop ? $shop : new Shop((int)$this->context->cart->id_shop));
		$id_currency = $currency_special ? (int)$currency_special : (int)$this->context->cart->id_currency;
		$this->context->currency = new Currency((int)$id_currency, null, (int)$this->context->shop->id);
		if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery')
			$context_country = $this->context->country;

        $order_status = new OrderState((int)$id_order_state, (int)$this->context->language->id);
		if (!Validate::isLoadedObject($order_status))
			throw new PrestaShopException('Can\'t load Order state status');

		if (!$this->active)
			die(Tools::displayError());
		// Does order already exists ?
		if (Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists() == false)
		{
			if ($secure_key !== false && $secure_key != $this->context->cart->secure_key)
				die(Tools::displayError());

			// For each package, generate an order
			$delivery_option_list = $this->context->cart->getDeliveryOptionList();
			$package_list = $this->context->cart->getPackageList();
			$cart_delivery_option = $this->context->cart->getDeliveryOption();

			// If some delivery options are not defined, or not valid, use the first valid option
			foreach ($delivery_option_list as $id_address => $package)
				if (!isset($cart_delivery_option[$id_address]) || !array_key_exists($cart_delivery_option[$id_address], $package))
					foreach (array_keys($package) as $key)
					{
						$cart_delivery_option[$id_address] = $key;
						break;
					}

			$order_list = array();
			$order_detail_list = array();
			$reference = Order::generateReference();
			$this->currentOrderReference = $reference;

			$order_creation_failed = false;
			$cart_total_paid = (float)Tools::ps_round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH), 2);

			if ($this->context->cart->orderExists())
			{
				$error = Tools::displayError('An order has already been placed using this cart.');
				Logger::addLog($error, 4, '0000001', 'Cart', (int)($this->context->cart->id));
				die($error);
			}

			foreach ($cart_delivery_option as $id_address => $key_carriers)
				foreach ($delivery_option_list[$id_address][$key_carriers]['carrier_list'] as $id_carrier => $data)
					foreach ($data['package_list'] as $id_package)
					{
						// Rewrite the id_warehouse
						if (version_compare(_PS_VERSION_, "1.5.2.0", ">"))
							$package_list[$id_address][$id_package]['id_warehouse'] = (int)$this->context->cart->getPackageIdWarehouse($package_list[$id_address][$id_package], (int)$id_carrier);
						$package_list[$id_address][$id_package]['id_carrier'] = (int)$id_carrier;
                                        }
			// Make sure CarRule caches are empty

            CartRule::cleanCache();
			foreach ($package_list as $id_address => $packageByAddress)
				foreach ($packageByAddress as $id_package => $package)
				{
					$order = new Order();
					$order->product_list = $package['product_list'];

					if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery')
					{
						$address = new Address($id_address);
						$this->context->country = new Country((int)$address->id_country, (int)$this->context->cart->id_lang);
					}

					$carrier = null;
					if (!$this->context->cart->isVirtualCart() && isset($package['id_carrier']))
					{
						$carrier = new Carrier((int)$package['id_carrier'], (int)$this->context->cart->id_lang);
						$order->id_carrier = (int)$carrier->id;
						$id_carrier = (int)$carrier->id;
					}
					else
					{
						$order->id_carrier = 0;
						$id_carrier = 0;
					}

					$order->id_customer = (int)$this->context->cart->id_customer;
					$order->id_address_invoice = (int)$this->context->cart->id_address_invoice;
					$order->id_address_delivery = (int)$id_address;
					$order->id_currency = (int)$this->context->currency->id;
					$order->id_lang = (int)$this->context->cart->id_lang;
					$order->id_cart = (int)$this->context->cart->id;
					$order->reference = $reference;
					$order->id_shop = (int)$this->context->shop->id;
					$order->id_shop_group = (int)$this->context->shop->id_shop_group;

					$order->secure_key = ($secure_key ? pSQL($secure_key) : pSQL($this->context->customer->secure_key));
					$order->payment = $payment_method;
					if (isset($this->name))
						$order->module = $this->name;
					$order->recyclable = $this->context->cart->recyclable;
					$order->gift = (int)$this->context->cart->gift;
					$order->gift_message = $this->context->cart->gift_message;
					$order->conversion_rate = $this->context->currency->conversion_rate;
					$amount_paid = !$dont_touch_amount ? Tools::ps_round((float)$amount_paid, 2) : $amount_paid;
					$order->total_paid_real = 0;

					$order->total_products = (float)$this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $order->product_list, $id_carrier);
					$order->total_products_wt = (float)$this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $order->product_list, $id_carrier);

					$order->total_discounts_tax_excl = (float)abs($this->context->cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $id_carrier));
					$order->total_discounts_tax_incl = (float)abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $order->product_list, $id_carrier));
					$order->total_discounts = $order->total_discounts_tax_incl;

					$order->total_shipping_tax_excl = (float)$this->context->cart->getPackageShippingCost((int)$id_carrier, false, null, $order->product_list);
					$order->total_shipping_tax_incl = (float)$this->context->cart->getPackageShippingCost((int)$id_carrier, true, null, $order->product_list)+$cargo;
					$order->total_shipping =  (float)$order->total_shipping_tax_incl;

					if (!is_null($carrier) && Validate::isLoadedObject($carrier))
						$order->carrier_tax_rate = $carrier->getTaxesRate(new Address($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));

					$order->total_wrapping_tax_excl = (float)abs($this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING, $order->product_list, $id_carrier));
					$order->total_wrapping_tax_incl = (float)abs($this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING, $order->product_list, $id_carrier));
					$order->total_wrapping =  (float)$order->total_wrapping_tax_incl;

					$order->total_paid_tax_excl = (float)Tools::ps_round((float)$this->context->cart->getOrderTotal(false, Cart::BOTH, $order->product_list, $id_carrier), 2);
					$order->total_paid_tax_incl = (float)Tools::ps_round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH, $order->product_list, $id_carrier), 2)+$cargo;
					$order->total_paid =  (float)$order->total_paid_tax_incl;

					$order->invoice_date = '0000-00-00 00:00:00';
					$order->delivery_date = '0000-00-00 00:00:00';

					// Creating order
					$result = $order->add();

					if (!$result)
						throw new PrestaShopException('Can\'t save Order');

					// Amount paid by customer is not the right one -> Status = payment error
					// We don't use the following condition to avoid the float precision issues : http://www.php.net/manual/en/language.types.float.php
					// if ($order->total_paid != $order->total_paid_real)
					// We use number_format in order to compare two string
                                        //if ($order_status->logable && number_format($order->total_paid, 2) != number_format($order->total_paid_real, 2))VERSION 1.4
                                        if ($order_status->logable && number_format($cart_total_paid, 2) != (number_format($amount_paid-$cargo, 2)))
						$id_order_state = Configuration::get('PS_OS_ERROR');

					$order_list[] = $order;

					// Insert new Order detail list using cart for the current order
					$order_detail = new OrderDetail(null, null, $this->context);
					if (version_compare(_PS_VERSION_, "1.5.3", ">=") && strcmp ($package_list[$id_address][$id_package]['id_warehouse'], "")==0)
                                            $order_detail->createList($order, $this->context->cart, $id_order_state, $order->product_list, 0, true, 0);
                                        else
					$order_detail->createList($order, $this->context->cart, $id_order_state, $order->product_list, 0, true, $package_list[$id_address][$id_package]['id_warehouse']);
					$order_detail_list[] = $order_detail;

					// Adding an entry in order_carrier table
					if (!is_null($carrier))
					{
						$order_carrier = new OrderCarrier();
						$order_carrier->id_order = (int)$order->id;
						$order_carrier->id_carrier = (int)$id_carrier;
						$order_carrier->weight = (float)$order->getTotalWeight();
						$order_carrier->shipping_cost_tax_excl = (float)$order->total_shipping_tax_excl;
						$order_carrier->shipping_cost_tax_incl = (float)$order->total_shipping_tax_incl;
						$order_carrier->add();
					}
				}

			// The country can only change if the address used for the calculation is the delivery address, and if multi-shipping is activated
			if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery')
				$this->context->country = $context_country;

			// Register Payment only if the order status validate the order
			if ($order_status->logable)
			{
				// $order is the last order loop in the foreach
				// The method addOrderPayment of the class Order make a create a paymentOrder
				//     linked to the order reference and not to the order id
				if (!$order->addOrderPayment($amount_paid))
					throw new PrestaShopException('Can\'t save Order Payment');
			}

			// Next !
			$cart_rule_used = array();
			$products = $this->context->cart->getProducts();
			$cart_rules = $this->context->cart->getCartRules();

			// Make sure CarRule caches are empty
			if (version_compare(_PS_VERSION_, "1.5.0.15", ">="))
                 CartRule::cleanCache();

			foreach ($order_detail_list as $key => $order_detail)
			{
				$order = $order_list[$key];
				if (!$order_creation_failed & isset($order->id))
				{
					if (!$secure_key)
						$message .= '<br />'.Tools::displayError('Warning: the secure key is empty, check your payment account before validation');
					// Optional message to attach to this order
					if (isset($message) & !empty($message))
					{
						$msg = new Message();
						$message = strip_tags($message, '<br>');
						if (Validate::isCleanHtml($message))
						{
							$msg->message = $message;
							$msg->id_order = (int)($order->id);
							$msg->private = 1;
							$msg->add();
						}
					}

					// Insert new Order detail list using cart for the current order
					//$orderDetail = new OrderDetail(null, null, $this->context);
					//$orderDetail->createList($order, $this->context->cart, $id_order_state);

					// Construct order detail table for the email
					$products_list = '';
					$virtual_product = true;
					foreach ($products as $key => $product)
					{
						$price = Product::getPriceStatic((int)$product['id_product'], false, ($product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null), 6, null, false, true, $product['cart_quantity'], false, (int)$order->id_customer, (int)$order->id_cart, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
						$price_wt = Product::getPriceStatic((int)$product['id_product'], true, ($product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null), 2, null, false, true, $product['cart_quantity'], false, (int)$order->id_customer, (int)$order->id_cart, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

						$customization_quantity = 0;
						$customized_datas = Product::getAllCustomizedDatas((int)$order->id_cart);
						if (isset($customized_datas[$product['id_product']][$product['id_product_attribute']]))
						{
							$customization_text = '';
							foreach ($customized_datas[$product['id_product']][$product['id_product_attribute']] as $customization)
							{
								if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD]))
									foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text)
										$customization_text .= $text['name'].': '.$text['value'].'<br />';

								if (isset($customization['datas'][Product::CUSTOMIZE_FILE]))
									$customization_text .= sprintf(Tools::displayError('%d image(s)'), count($customization['datas'][Product::CUSTOMIZE_FILE])).'<br />';

								$customization_text .= '---<br />';
							}

							$customization_text = rtrim($customization_text, '---<br />');

							$customization_quantity = (int)$product['customizationQuantityTotal'];
							$products_list .=
							'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
								<td style="padding: 0.6em 0.4em;">'.$product['reference'].'</td>
								<td style="padding: 0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '').' - '.Tools::displayError('Customized').(!empty($customization_text) ? ' - '.$customization_text : '').'</strong></td>
								<td style="padding: 0.6em 0.4em; text-align: right;">'.Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ?  Tools::ps_round($price, 2) : $price_wt, $this->context->currency, false).'</td>
								<td style="padding: 0.6em 0.4em; text-align: center;">'.$customization_quantity.'</td>
								<td style="padding: 0.6em 0.4em; text-align: right;">'.Tools::displayPrice($customization_quantity * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $price_wt), $this->context->currency, false).'</td>
							</tr>';
						}

						if (!$customization_quantity || (int)$product['cart_quantity'] > $customization_quantity)
							$products_list .=
							'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
								<td style="padding: 0.6em 0.4em;">'.$product['reference'].'</td>
								<td style="padding: 0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '').'</strong></td>
								<td style="padding: 0.6em 0.4em; text-align: right;">'.Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $price_wt, $this->context->currency, false).'</td>
								<td style="padding: 0.6em 0.4em; text-align: center;">'.((int)$product['cart_quantity'] - $customization_quantity).'</td>
								<td style="padding: 0.6em 0.4em; text-align: right;">'.Tools::displayPrice(((int)$product['cart_quantity'] - $customization_quantity) * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $price_wt), $this->context->currency, false).'</td>
							</tr>';

						// Check if is not a virutal product for the displaying of shipping
						if (!$product['is_virtual'])
							$virtual_product &= false;

					} // end foreach ($products)

					$cart_rules_list = '';
					foreach ($cart_rules as $cart_rule)
					{
						$package = array('id_carrier' => (int)$order->id_carrier, 'id_address' => (int)$order->id_address_delivery, 'products' => $order->product_list);
						$values = array(
							'tax_incl' => $cart_rule['obj']->getContextualValue(true, $this->context, CartRule::FILTER_ACTION_ALL, $package),
							'tax_excl' => $cart_rule['obj']->getContextualValue(false, $this->context, CartRule::FILTER_ACTION_ALL, $package)
						);

						// If the reduction is not applicable to this order, then continue with the next one
						if (!$values['tax_excl'])
							continue;

						$order->addCartRule((int)$cart_rule['obj']->id, $cart_rule['obj']->name, $values);

						/* IF
						** - This is not multi-shipping
						** - The value of the voucher is greater than the total of the order
						** - Partial use is allowed
						** - This is an "amount" reduction, not a reduction in % or a gift
						** THEN
						** The voucher is cloned with a new value corresponding to the remainder
						*/
						if (count($order_list) == 1 && $values['tax_incl'] > $order->total_products_wt && $cart_rule['obj']->partial_use == 1 && $cart_rule['obj']->reduction_amount > 0)
						{
							// Create a new voucher from the original
							$voucher = new CartRule((int)$cart_rule['obj']->id); // We need to instantiate the CartRule without lang parameter to allow saving it
							unset($voucher->id);

							// Set a new voucher code
							$voucher->code = empty($voucher->code) ? Tools::substr(md5($order->id.'-'.$order->id_customer.'-'.$cart_rule['obj']->id), 0, 16) : $voucher->code.'-2';
							if (preg_match('/\-([0-9]{1,2})\-([0-9]{1,2})$/', $voucher->code, $matches) && $matches[1] == $matches[2])
								$voucher->code = preg_replace('/'.$matches[0].'$/', '-'.((int)($matches[1]) + 1), $voucher->code);

							// Set the new voucher value
							if ($voucher->reduction_tax)
								$voucher->reduction_amount = $values['tax_incl'] - $order->total_products_wt;
							else
								$voucher->reduction_amount = $values['tax_excl'] - $order->total_products;

							$voucher->id_customer = (int)$order->id_customer;
							$voucher->quantity = 1;
							if ($voucher->add())
							{
								// If the voucher has conditions, they are now copied to the new voucher
								CartRule::copyConditions($cart_rule['obj']->id, $voucher->id);

								$params = array(
									'{voucher_amount}' => Tools::displayPrice($voucher->reduction_amount, $this->context->currency, false),
									'{voucher_num}' => $voucher->code,
									'{firstname}' => $this->context->customer->firstname,
									'{lastname}' => $this->context->customer->lastname,
									'{id_order}' => $order->reference,
									'{order_name}' => $order->getUniqReference()
								);
								Mail::Send(
									(int)$order->id_lang,
									'voucher',
									sprintf(Mail::l('New voucher regarding your order %s', (int)$order->id_lang), $order->reference),
									$params,
									$this->context->customer->email,
									$this->context->customer->firstname.' '.$this->context->customer->lastname,
									null, null, null, null, _PS_MAIL_DIR_, false, (int)$order->id_shop
								);
							}
						}

						if ($id_order_state != Configuration::get('PS_OS_ERROR') && $id_order_state != Configuration::get('PS_OS_CANCELED') && !in_array($cart_rule['obj']->id, $cart_rule_used))
						{
							$cart_rule_used[] = $cart_rule['obj']->id;

							// Create a new instance of Cart Rule without id_lang, in order to update its quantity
							$cart_rule_to_update = new CartRule((int)$cart_rule['obj']->id);
							$cart_rule_to_update->quantity = max(0, $cart_rule_to_update->quantity - 1);
							$cart_rule_to_update->update();
						}

						$cart_rules_list .= '
						<tr style="background-color:#EBECEE;">
							<td colspan="4" style="padding:0.6em 0.4em;text-align:right">'.Tools::displayError('Voucher name:').' '.$cart_rule['obj']->name.'</td>
							<td style="padding:0.6em 0.4em;text-align:right">'.($values['tax_incl'] != 0.00 ? '-' : '').Tools::displayPrice($values['tax_incl'], $this->context->currency, false).'</td>
						</tr>';
					}

					// Specify order id for message
					$old_message = Message::getMessageByCartId((int)$this->context->cart->id);
					if ($old_message)
					{
						$message = new Message((int)$old_message['id_message']);
						$message->id_order = (int)$order->id;
						$message->update();

						// Add this message in the customer thread
						$customer_thread = new CustomerThread();
						$customer_thread->id_contact = 0;
						$customer_thread->id_customer = (int)$order->id_customer;
						$customer_thread->id_shop = (int)$this->context->shop->id;
						$customer_thread->id_order = (int)$order->id;
						$customer_thread->id_lang = (int)$this->context->language->id;
						$customer_thread->email = $this->context->customer->email;
						$customer_thread->status = 'open';
						$customer_thread->token = Tools::passwdGen(12);
						$customer_thread->add();

						$customer_message = new CustomerMessage();
						$customer_message->id_customer_thread = (int)$customer_thread->id;
						$customer_message->id_employee = 0;
						$customer_message->message = htmlentities($message->message, ENT_COMPAT, 'UTF-8');
						$customer_message->private = 0;

						if (!$customer_message->add())
							$this->errors[] = Tools::displayError('An error occurred while saving message');
					}

					// Hook validate order
					Hook::exec('actionValidateOrder', array(
						'cart' => $this->context->cart,
						'order' => $order,
						'customer' => $this->context->customer,
						'currency' => $this->context->currency,
						'orderStatus' => $order_status
					));

					foreach ($this->context->cart->getProducts() as $product)
						if ($order_status->logable)
							ProductSale::addProductSale((int)$product['id_product'], (int)$product['cart_quantity']);

					if (Configuration::get('PS_STOCK_MANAGEMENT') && $order_detail->getStockState())
					{
						$history = new OrderHistory();
						$history->id_order = (int)$order->id;
						if (version_compare(_PS_VERSION_, "1.5.2", ">=")){
                                                    $history->changeIdOrderState(Configuration::get('PS_OS_OUTOFSTOCK'), $order);
                                                }else{
                                                    $history->changeIdOrderState(Configuration::get('PS_OS_OUTOFSTOCK'), (int)$order->id);
                                                }
						$history->addWithemail();
					}

					// Set order state in order history ONLY even if the "out of stock" status has not been yet reached
					// So you migth have two order states
					$new_history = new OrderHistory();
					$new_history->id_order = (int)$order->id;
					if (version_compare(_PS_VERSION_, "1.5.2", ">=")){
						$new_history->changeIdOrderState((int)$id_order_state, $order, true);
					}
					else{
						$new_history->changeIdOrderState((int)$id_order_state, (int)$order->id, true);

					}
					$new_history->addWithemail(true, $extraVars);

					unset($order_detail);

					// Order is reloaded because the status just changed
					$order = new Order((int)$order->id);

					// Send an e-mail to customer (one order = one email)
					if ($id_order_state != Configuration::get('PS_OS_ERROR') && $id_order_state != Configuration::get('PS_OS_CANCELED') && $this->context->customer->id)
					{
						$invoice = new Address((int)$order->id_address_invoice);
						$delivery = new Address((int)$order->id_address_delivery);
						$delivery_state = $delivery->id_state ? new State((int)$delivery->id_state) : false;
						$invoice_state = $invoice->id_state ? new State($invoice->id_state) : false;
						if (version_compare(_PS_VERSION_, "1.5.0.15", ">=")){
							$name=$order->getUniqReference();
						}
						else{
							$name=sprintf('#%06d', (int)$order->id);
						}
						$data = array(
						'{firstname}' => $this->context->customer->firstname,
						'{lastname}' => $this->context->customer->lastname,
						'{email}' => $this->context->customer->email,
						'{delivery_block_txt}' => $this->_getFormatedAddress($delivery, "\n"),
						'{invoice_block_txt}' => $this->_getFormatedAddress($invoice, "\n"),
						'{delivery_block_html}' => $this->_getFormatedAddress($delivery, '<br />', array(
							'firstname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>',
							'lastname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>'
						)),
						'{invoice_block_html}' => $this->_getFormatedAddress($invoice, '<br />', array(
								'firstname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>',
								'lastname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>'
						)),
						'{delivery_company}' => $delivery->company,
						'{delivery_firstname}' => $delivery->firstname,
						'{delivery_lastname}' => $delivery->lastname,
						'{delivery_address1}' => $delivery->address1,
						'{delivery_address2}' => $delivery->address2,
						'{delivery_city}' => $delivery->city,
						'{delivery_postal_code}' => $delivery->postcode,
						'{delivery_country}' => $delivery->country,
						'{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
						'{delivery_phone}' => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
						'{delivery_other}' => $delivery->other,
						'{invoice_company}' => $invoice->company,
						'{invoice_vat_number}' => $invoice->vat_number,
						'{invoice_firstname}' => $invoice->firstname,
						'{invoice_lastname}' => $invoice->lastname,
						'{invoice_address2}' => $invoice->address2,
						'{invoice_address1}' => $invoice->address1,
						'{invoice_city}' => $invoice->city,
						'{invoice_postal_code}' => $invoice->postcode,
						'{invoice_country}' => $invoice->country,
						'{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
						'{invoice_phone}' => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
						'{invoice_other}' => $invoice->other,
						'{order_name}' => $name,
						'{date}' => Tools::displayDate(date('Y-m-d H:i:s'), (int)$order->id_lang, 1),
						'{carrier}' => $virtual_product ? Tools::displayError('No carrier') : $carrier->name,
						'{payment}' => Tools::substr($order->payment, 0, 32),
						'{products}' => $this->formatProductAndVoucherForEmail($products_list),
						'{discounts}' => $this->formatProductAndVoucherForEmail($cart_rules_list),
						'{total_paid}' => Tools::displayPrice($order->total_paid, $this->context->currency, false),
						'{total_products}' => Tools::displayPrice($order->total_paid - $order->total_shipping - $order->total_wrapping + $order->total_discounts, $this->context->currency, false),
						'{total_discounts}' => Tools::displayPrice($order->total_discounts, $this->context->currency, false),
						'{total_shipping}' => Tools::displayPrice($order->total_shipping, $this->context->currency, false),
						'{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $this->context->currency, false));

						if (is_array($extraVars))
							$data = array_merge($data, $extraVars);

						// Join PDF invoice
						$file_attachement = array();
						if ((int)Configuration::get('PS_INVOICE') && $order_status->invoice && $order->invoice_number)
						{
							$pdf = new PDF($order->getInvoicesCollection(), PDF::TEMPLATE_INVOICE, $this->context->smarty);
							$file_attachement['content'] = $pdf->render(false);
							$file_attachement['name'] = Configuration::get('PS_INVOICE_PREFIX', (int)$order->id_lang).sprintf('%06d', $order->invoice_number).'.pdf';
							$file_attachement['mime'] = 'application/pdf';
						}
						else
							$file_attachement = null;

						if (Validate::isEmail($this->context->customer->email))
							Mail::Send(
								(int)$order->id_lang,
								'order_conf',
								Mail::l('Order confirmation', (int)$order->id_lang),
								$data,
								$this->context->customer->email,
								$this->context->customer->firstname.' '.$this->context->customer->lastname,
								null,
								null,
								$file_attachement,
								null, _PS_MAIL_DIR_, false, (int)$order->id_shop
							);
					}
					// updates stock in shops
					if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
					{
						$product_list = $order->getProducts();
						foreach ($product_list as $product)
						{
							// if the available quantities depends on the physical stock
							if (StockAvailable::dependsOnStock((int)$product['product_id']))
							{
								// synchronizes
								StockAvailable::synchronize((int)$product['product_id'], (int)$order->id_shop);
							}
						}
					}
				}
				else
				{
					$error = Tools::displayError('Order creation failed');
					Logger::addLog($error, 4, '0000002', 'Cart', (int)($order->id_cart));
					die($error);
				}
			} // End foreach $order_detail_list
			// Use the last order as currentOrder
			$this->currentOrder = (int)$order->id;
			return true;
		}
		else
		{
			$error = Tools::displayError('Cart cannot be loaded or an order has already been placed using this cart');
			Logger::addLog($error, 4, '0000001', 'Cart', (int)($this->context->cart->id));
			die($error);
		}
	}

    public function validateOrderFORWEBS_v4($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = false, $secure_key = false)
	{
		// $cart = $this->context->cart; // here were a global reference

		$cart = new Cart((int)($id_cart));

		$coste = (float)(abs($cart->getOrderTotal(true, Cart::BOTH)));
		$cargo = number_format($this->getCargo($cart) , 2, '.', '');
		$total_con_cargo = $coste + $cargo;

		if (Validate::isLoadedObject($cart) && $cart->OrderExists() == false)
		{
			if ($secure_key !== false && $secure_key != $cart->secure_key)
				die(Tools::displayError());

			$order = new Order();
			$order->id_carrier = (int)($cart->id_carrier);
			$order->id_customer = (int)($cart->id_customer);
			$order->id_address_invoice = (int)($cart->id_address_invoice);
			$order->id_address_delivery = (int)($cart->id_address_delivery);
			$vat_address = new Address((int)($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
			$order->id_currency = ($currency_special ? (int)($currency_special) : (int)($cart->id_currency));
			$order->id_lang = (int)($cart->id_lang);
			$order->id_cart = (int)($cart->id);
			$customer = new Customer((int)($order->id_customer));
			$order->secure_key = ($secure_key ? pSQL($secure_key) : pSQL($customer->secure_key));
			$order->payment = $paymentMethod;
			if (isset($this->name))
				$order->module = $this->name;
			$order->recyclable = $cart->recyclable;
			$order->gift = (int)($cart->gift);
			$order->gift_message = $cart->gift_message;
			$currency = new Currency((int)$order->id_currency);
			$order->conversion_rate = $currency->conversion_rate;
			$amountPaid = !$dont_touch_amount ? Tools::ps_round((float)($amountPaid), 2) : $amountPaid;
			$order->total_paid_real = (float)(Tools::ps_round((float)($total_con_cargo), 2));
			//$order->total_paid_real = (float)(Tools::ps_round(Cart::ONLY_PRODUCTS) - $descuento);
			$order->total_products = (float)($cart->getOrderTotal(false, Cart::ONLY_PRODUCTS));
			$order->total_products_wt = (float)($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS));
			$order->total_discounts = (float)(abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)));
			$order->total_shipping = (float)($cargo+$cart->getOrderShippingCost());
			$order->carrier_tax_rate = (float)Tax::getCarrierTaxRate($cart->id_carrier, (int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
			$order->total_wrapping = (float)(abs($cart->getOrderTotal(true, Cart::ONLY_WRAPPING)));
			$order->total_paid = (float)(Tools::ps_round((float)($total_con_cargo), 2));
			//$order->total_paid = (float)(Tools::ps_round((float)($cart->getOrderTotal(true, 3)), 2));
			$order->invoice_date = '0000-00-00 00:00:00';
			$order->delivery_date = '0000-00-00 00:00:00';
			// Amount paid by customer is not the right one -> Status = payment error
			// We don't use the following condition to avoid the float precision issues : http://www.php.net/manual/en/language.types.float.php
			// if ($order->total_paid != $order->total_paid_real)
			// We use number_format in order to compare two string
			if (number_format($order->total_paid, 2) != number_format($order->total_paid_real, 2))
				$id_order_state = Configuration::get('PS_OS_ERROR');
			// Creating order
			if ($cart->OrderExists() == false)
				$result = $order->add();
			else
			{
				$errorMessage = Tools::displayError('An order has already been placed using this cart.');
				Logger::addLog($errorMessage, 4, '0000001', 'Cart', (int)($order->id_cart));
				die($errorMessage);
			}

			// Next !
			if ($result && isset($order->id))
			{
				if (!$secure_key)
					$message .= $this->l('Warning : the secure key is empty, check your payment account before validation');
				// Optional message to attach to this order
				if (isset($message) && !empty($message))
				{
					$msg = new Message();
					$message = strip_tags($message, '<br>');
					if (Validate::isCleanHtml($message))
					{
						$msg->message = $message;
						$msg->id_order = (int)($order->id);
						$msg->private = 1;
						$msg->add();
					}
				}

				// Insert products from cart into order_detail table
				$products = $cart->getProducts();
				$productsList = '';
				$db = Db::getInstance();
				$query = 'INSERT INTO `'._DB_PREFIX_.'order_detail`
					(`id_order`, `product_id`, `product_attribute_id`, `product_name`, `product_quantity`, `product_quantity_in_stock`, `product_price`, `reduction_percent`, `reduction_amount`, `group_reduction`, `product_quantity_discount`, `product_ean13`, `product_upc`, `product_reference`, `product_supplier_reference`, `product_weight`, `tax_name`, `tax_rate`, `ecotax`, `ecotax_tax_rate`, `discount_quantity_applied`, `download_deadline`, `download_hash`)
				VALUES ';

				$customizedDatas = Product::getAllCustomizedDatas((int)($order->id_cart));
				Product::addCustomizationPrice($products, $customizedDatas);
				$outOfStock = false;

				$storeAllTaxes = array();

				foreach ($products AS $key => $product)
				{
					$productQuantity = (int)(Product::getQuantity((int)($product['id_product']), ($product['id_product_attribute'] ? (int)($product['id_product_attribute']) : NULL)));
					$quantityInStock = ($productQuantity - (int)($product['cart_quantity']) < 0) ? $productQuantity : (int)($product['cart_quantity']);
					if ($id_order_state != Configuration::get('PS_OS_CANCELED') && $id_order_state != Configuration::get('PS_OS_ERROR'))
					{
						if (Product::updateQuantity($product, (int)$order->id))
							$product['stock_quantity'] -= $product['cart_quantity'];
						if ($product['stock_quantity'] < 0 && Configuration::get('PS_STOCK_MANAGEMENT'))
							$outOfStock = true;

						Product::updateDefaultAttribute($product['id_product']);
					}
					$price = Product::getPriceStatic((int)($product['id_product']), false, ($product['id_product_attribute'] ? (int)($product['id_product_attribute']) : NULL), 6, NULL, false, true, $product['cart_quantity'], false, (int)($order->id_customer), (int)($order->id_cart), (int)($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
					$price_wt = Product::getPriceStatic((int)($product['id_product']), true, ($product['id_product_attribute'] ? (int)($product['id_product_attribute']) : NULL), 2, NULL, false, true, $product['cart_quantity'], false, (int)($order->id_customer), (int)($order->id_cart), (int)($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));

					/* Store tax info */
					$id_country = (int)Country::getDefaultCountryId();
					$id_state = 0;
					$id_county = 0;
					$id_address = $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
					$address_infos = Address::getCountryAndState($id_address);
					if ($address_infos['id_country'])
					{
						$id_country = (int)($address_infos['id_country']);
						$id_state = (int)$address_infos['id_state'];
						$id_county = (int)County::getIdCountyByZipCode($address_infos['id_state'], $address_infos['postcode']);
					}
					$allTaxes = TaxRulesGroup::getTaxes((int)Product::getIdTaxRulesGroupByIdProduct((int)$product['id_product']), $id_country, $id_state, $id_county);
					$nTax = 0;
					foreach ($allTaxes AS $res)
					{
						if (!isset($storeAllTaxes[$res->id]))
							$storeAllTaxes[$res->id] = array();
						$storeAllTaxes[$res->id]['name'] = $res->name[(int)$order->id_lang];
						$storeAllTaxes[$res->id]['rate'] = $res->rate;

						if (!$nTax++)
							$storeAllTaxes[$res->id]['amount'] = ($price * (1 + ($res->rate * 0.01))) - $price;
						else
						{
							$priceTmp = $price_wt / (1 + ($res->rate * 0.01));
							$storeAllTaxes[$res->id]['amount'] = $price_wt - $priceTmp;
						}
					}
					/* End */

					// Add some informations for virtual products
					$deadline = '0000-00-00 00:00:00';
					$download_hash = NULL;
					if ($id_product_download = ProductDownload::getIdFromIdProduct((int)($product['id_product'])))
					{
						$productDownload = new ProductDownload((int)($id_product_download));
						$deadline = $productDownload->getDeadLine();
						$download_hash = $productDownload->getHash();
					}

					// Exclude VAT
					if (Tax::excludeTaxeOption())
					{
						$product['tax'] = 0;
						$product['rate'] = 0;
						$tax_rate = 0;
					}
					else
						$tax_rate = Tax::getProductTaxRate((int)($product['id_product']), $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

                    $ecotaxTaxRate = 0;
                    if (!empty($product['ecotax']))
                        $ecotaxTaxRate = Tax::getProductEcotaxRate($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

                    $product_price = (float)Product::getPriceStatic((int)($product['id_product']), false, ($product['id_product_attribute'] ? (int)($product['id_product_attribute']) : NULL), (Product::getTaxCalculationMethod((int)($order->id_customer)) == PS_TAX_EXC ? 2 : 6), NULL, false, false, $product['cart_quantity'], false, (int)($order->id_customer), (int)($order->id_cart), (int)($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')}), $specificPrice, false, false);

					$group_reduction = (float)GroupReduction::getValueForProduct((int)$product['id_product'], (int)$customer->id_default_group) * 100;
					if (!$group_reduction)
						$group_reduction = (float)Group::getReduction((int)$order->id_customer);

					$quantityDiscount = SpecificPrice::getQuantityDiscount((int)$product['id_product'], Shop::getCurrentShop(), (int)$cart->id_currency, (int)$vat_address->id_country, (int)$customer->id_default_group, (int)$product['cart_quantity']);
					$unitPrice = Product::getPriceStatic((int)$product['id_product'], true, ($product['id_product_attribute'] ? (int)($product['id_product_attribute']) : NULL), 2, NULL, false, true, 1, false, (int)$order->id_customer, NULL, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
					$quantityDiscountValue = $quantityDiscount ? ((Product::getTaxCalculationMethod((int)$order->id_customer) == PS_TAX_EXC ? Tools::ps_round($unitPrice, 2) : $unitPrice) - $quantityDiscount['price'] * (1 + $tax_rate / 100)) : 0.00;
					$query .= '('.(int)($order->id).',
						'.(int)($product['id_product']).',
						'.(isset($product['id_product_attribute']) ? (int)($product['id_product_attribute']) : 'NULL').',
						\''.pSQL($product['name'].((isset($product['attributes']) && $product['attributes'] != NULL) ? ' - '.$product['attributes'] : '')).'\',
						'.(int)($product['cart_quantity']).',
						'.$quantityInStock.',
						'.$product_price.',
						'.(float)(($specificPrice && $specificPrice['reduction_type'] == 'percentage') ? $specificPrice['reduction'] * 100 : 0.00).',
						'.(float)(($specificPrice && $specificPrice['reduction_type'] == 'amount') ? (!$specificPrice['id_currency'] ? Tools::convertPrice($specificPrice['reduction'], $order->id_currency) : $specificPrice['reduction']) : 0.00).',
						'.(float)$group_reduction.',
						'.(float)$quantityDiscountValue.',
						'.(empty($product['ean13']) ? 'NULL' : '\''.pSQL($product['ean13']).'\'').',
						'.(empty($product['upc']) ? 'NULL' : '\''.pSQL($product['upc']).'\'').',
						'.(empty($product['reference']) ? 'NULL' : '\''.pSQL($product['reference']).'\'').',
						'.(empty($product['supplier_reference']) ? 'NULL' : '\''.pSQL($product['supplier_reference']).'\'').',
						'.(float)($product['id_product_attribute'] ? $product['weight_attribute'] : $product['weight']).',
						\''.(empty($tax_rate) ? '' : pSQL($product['tax'])).'\',
						'.(float)($tax_rate).',
						'.(float)Tools::convertPrice((float)($product['ecotax']), (int)($order->id_currency)).',
						'.(float)$ecotaxTaxRate.',
						'.(($specificPrice && $specificPrice['from_quantity'] > 1) ? 1 : 0).',
						\''.pSQL($deadline).'\',
						\''.pSQL($download_hash).'\'),';

					$customizationQuantity = 0;
					if (isset($customizedDatas[$product['id_product']][$product['id_product_attribute']]))
					{
						$customizationText = '';
						foreach ($customizedDatas[$product['id_product']][$product['id_product_attribute']] AS $customization)
						{
							if (isset($customization['datas'][_CUSTOMIZE_TEXTFIELD_]))
								foreach ($customization['datas'][_CUSTOMIZE_TEXTFIELD_] AS $text)
									$customizationText .= $text['name'].':'.' '.$text['value'].'<br />';

							if (isset($customization['datas'][_CUSTOMIZE_FILE_]))
								$customizationText .= sizeof($customization['datas'][_CUSTOMIZE_FILE_]) .' '. Tools::displayError('image(s)').'<br />';

							$customizationText .= '---<br />';
						}

						$customizationText = rtrim($customizationText, '---<br />');

						$customizationQuantity = (int)($product['customizationQuantityTotal']);
						$productsList .=
						'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
							<td style="padding: 0.6em 0.4em;">'.$product['reference'].'</td>
							<td style="padding: 0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '').' - '.$this->l('Customized').(!empty($customizationText) ? ' - '.$customizationText : '').'</strong></td>
							<td style="padding: 0.6em 0.4em; text-align: right;">'.Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? $price : $price_wt, $currency, false).'</td>
							<td style="padding: 0.6em 0.4em; text-align: center;">'.$customizationQuantity.'</td>
							<td style="padding: 0.6em 0.4em; text-align: right;">'.Tools::displayPrice($customizationQuantity * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? $price : $price_wt), $currency, false).'</td>
						</tr>';
					}

					if (!$customizationQuantity || (int)$product['cart_quantity'] > $customizationQuantity)
						$productsList .=
						'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
							<td style="padding: 0.6em 0.4em;">'.$product['reference'].'</td>
							<td style="padding: 0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '').'</strong></td>
							<td style="padding: 0.6em 0.4em; text-align: right;">'.Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? $price : $price_wt, $currency, false).'</td>
							<td style="padding: 0.6em 0.4em; text-align: center;">'.((int)($product['cart_quantity']) - $customizationQuantity).'</td>
							<td style="padding: 0.6em 0.4em; text-align: right;">'.Tools::displayPrice(((int)($product['cart_quantity']) - $customizationQuantity) * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? $price : $price_wt), $currency, false).'</td>
						</tr>';
				} // end foreach ($products)
				$query = rtrim($query, ',');
				$result = $db->Execute($query);

				/* Add carrier tax */
				$shippingCostTaxExcl = $cart->getOrderShippingCost((int)$order->id_carrier, false);
				$allTaxes = TaxRulesGroup::getTaxes((int)Carrier::getIdTaxRulesGroupByIdCarrier((int)$order->id_carrier), $id_country, $id_state, $id_county);
				$nTax = 0;

				foreach ($allTaxes AS $res)
				{
					if (!isset($res->id))
						continue;

					if (!isset($storeAllTaxes[$res->id]))
						$storeAllTaxes[$res->id] = array();
					if (!isset($storeAllTaxes[$res->id]['amount']))
						$storeAllTaxes[$res->id]['amount'] = 0;
					$storeAllTaxes[$res->id]['name'] = $res->name[(int)$order->id_lang];
					$storeAllTaxes[$res->id]['rate'] = $res->rate;

					if (!$nTax++)
						$storeAllTaxes[$res->id]['amount'] += ($shippingCostTaxExcl * (1 + ($res->rate * 0.01))) - $shippingCostTaxExcl;
					else
					{
						$priceTmp = $order->total_shipping / (1 + ($res->rate * 0.01));
						$storeAllTaxes[$res->id]['amount'] += $order->total_shipping - $priceTmp;
					}
				}

                                $version_ps = explode(".", _PS_VERSION_);

                                if ($version_ps[2] > 5)//No existe en versiones anteriores la tabla "order_tax"
                                    /* Store taxes */
                                    foreach ($storeAllTaxes AS $t)
                                            Db::getInstance()->Execute('
                                            INSERT INTO '._DB_PREFIX_.'order_tax (id_order, tax_name, tax_rate, amount)
                                            VALUES ('.(int)$order->id.', \''.pSQL($t['name']).'\', \''.(float)($t['rate']).'\', '.(float)$t['amount'].')');

				// Insert discounts from cart into order_discount table
				$discounts = $cart->getDiscounts();
				$discountsList = '';
				$total_discount_value = 0;
				$shrunk = false;
				foreach ($discounts AS $discount)
				{
					$objDiscount = new Discount((int)$discount['id_discount']);
					$value = $objDiscount->getValue(sizeof($discounts), $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS), $order->total_shipping, $cart->id);
					if ($objDiscount->id_discount_type == 2 && in_array($objDiscount->behavior_not_exhausted, array(1,2)))
						$shrunk = true;

					if ($shrunk && ($total_discount_value + $value) > ($order->total_products_wt + $order->total_shipping + $order->total_wrapping))
					{
						$amount_to_add = ($order->total_products_wt + $order->total_shipping + $order->total_wrapping) - $total_discount_value;
						if ($objDiscount->id_discount_type == 2 && $objDiscount->behavior_not_exhausted == 2)
						{
							$voucher = new Discount();
							foreach ($objDiscount AS $key => $discountValue)
								$voucher->$key = $discountValue;
							$voucher->name = 'VSRK'.(int)$order->id_customer.'O'.(int)$order->id;
							$voucher->value = (float)$value - $amount_to_add;
							$voucher->add();
							$params = array();
							$params['{voucher_amount}'] = Tools::displayPrice($voucher->value, $currency, false);
							$params['{voucher_num}'] = $voucher->name;
							$params['{firstname}'] = $customer->firstname;
							$params['{lastname}'] = $customer->lastname;
							$params['{id_order}'] = $order->id;
							@Mail::Send((int)$order->id_lang, 'voucher', Mail::l('New voucher regarding your order #').(int)$order->id, $params, $customer->email, $customer->firstname.' '.$customer->lastname);
						}
					}
					else
						$amount_to_add = $value;
					$order->addDiscount($objDiscount->id, $objDiscount->name, $amount_to_add);
					$total_discount_value += $amount_to_add;
					if ($id_order_state != Configuration::get('PS_OS_ERROR') && $id_order_state != Configuration::get('PS_OS_CANCELED'))
						$objDiscount->quantity = $objDiscount->quantity - 1;
					$objDiscount->update();

					$discountsList .=
					'<tr style="background-color:#EBECEE;">
							<td colspan="4" style="padding: 0.6em 0.4em; text-align: right;">'.$this->l('Voucher code:').' '.$objDiscount->name.'</td>
							<td style="padding: 0.6em 0.4em; text-align: right;">'.($value != 0.00 ? '-' : '').Tools::displayPrice($value, $currency, false).'</td>
					</tr>';
				}

				// Specify order id for message
				$oldMessage = Message::getMessageByCartId((int)($cart->id));
				if ($oldMessage)
				{
					$message = new Message((int)$oldMessage['id_message']);
					$message->id_order = (int)$order->id;
					$message->update();
				}

				// Hook new order
				$orderStatus = new OrderState((int)$id_order_state, (int)$order->id_lang);
				if (Validate::isLoadedObject($orderStatus))
				{
					Hook::newOrder($cart, $order, $customer, $currency, $orderStatus);
					foreach ($cart->getProducts() AS $product)
						if ($orderStatus->logable)
							ProductSale::addProductSale((int)$product['id_product'], (int)$product['cart_quantity']);
				}

				if (isset($outOfStock) && $outOfStock && Configuration::get('PS_STOCK_MANAGEMENT'))
				{
					$history = new OrderHistory();
					$history->id_order = (int)$order->id;
					$history->changeIdOrderState(Configuration::get('PS_OS_OUTOFSTOCK'), (int)$order->id);
					$history->addWithemail();
				}

				// Set order state in order history ONLY even if the "out of stock" status has not been yet reached
				// So you migth have two order states
				$new_history = new OrderHistory();
				$new_history->id_order = (int)$order->id;
				$new_history->changeIdOrderState((int)$id_order_state, (int)$order->id);
				$new_history->addWithemail(true, $extraVars);

				// Order is reloaded because the status just changed
				$order = new Order($order->id);

				// Send an e-mail to customer
				if ($id_order_state != Configuration::get('PS_OS_ERROR') && $id_order_state != Configuration::get('PS_OS_CANCELED') && $customer->id)
				{
					$invoice = new Address((int)($order->id_address_invoice));
					$delivery = new Address((int)($order->id_address_delivery));
					$carrier = new Carrier((int)($order->id_carrier), $order->id_lang);
					$delivery_state = $delivery->id_state ? new State((int)($delivery->id_state)) : false;
					$invoice_state = $invoice->id_state ? new State((int)($invoice->id_state)) : false;

                                            $data = array(
                                            '{firstname}' => $customer->firstname,
                                            '{lastname}' => $customer->lastname,
                                            '{email}' => $customer->email,
                                            '{delivery_block_txt}' => $this->_getFormatedAddress($delivery, "\n"),
                                            '{invoice_block_txt}' => $this->_getFormatedAddress($invoice, "\n"),
                                            '{delivery_block_html}' => $this->_getFormatedAddress($delivery, "<br />",
                                                    array(
                                                            'firstname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>',
                                                            'lastname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>')),
                                            '{invoice_block_html}' => $this->_getFormatedAddress($invoice, "<br />",
                                                    array(
                                                            'firstname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>',
                                                            'lastname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>')),
                                            '{delivery_company}' => $delivery->company,
                                            '{delivery_firstname}' => $delivery->firstname,
                                            '{delivery_lastname}' => $delivery->lastname,
                                            '{delivery_address1}' => $delivery->address1,
                                            '{delivery_address2}' => $delivery->address2,
                                            '{delivery_city}' => $delivery->city,
                                            '{delivery_postal_code}' => $delivery->postcode,
                                            '{delivery_country}' => $delivery->country,
                                            '{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
                                            '{delivery_phone}' => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
                                            '{delivery_other}' => $delivery->other,
                                            '{invoice_company}' => $invoice->company,
                                            '{invoice_vat_number}' => $invoice->vat_number,
                                            '{invoice_firstname}' => $invoice->firstname,
                                            '{invoice_lastname}' => $invoice->lastname,
                                            '{invoice_address2}' => $invoice->address2,
                                            '{invoice_address1}' => $invoice->address1,
                                            '{invoice_city}' => $invoice->city,
                                            '{invoice_postal_code}' => $invoice->postcode,
                                            '{invoice_country}' => $invoice->country,
                                            '{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
                                            '{invoice_phone}' => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
                                            '{invoice_other}' => $invoice->other,
                                            '{order_name}' => sprintf("#%06d", (int)($order->id)),
                                            '{date}' => Tools::displayDate(date('Y-m-d H:i:s'), (int)($order->id_lang), 1),
                                            '{carrier}' => $carrier->name,
                                            '{payment}' => Tools::substr($order->payment, 0, 32),
                                            '{products}' => $productsList,
                                            '{discounts}' => $discountsList,
                                            '{total_paid}' => Tools::displayPrice($order->total_paid, $currency, false),
                                            '{total_products}' => Tools::displayPrice($order->total_paid - $order->total_shipping - $order->total_wrapping + $order->total_discounts, $currency, false),
                                            '{total_discounts}' => Tools::displayPrice($order->total_discounts, $currency, false),
                                            '{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency, false),
                                            '{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency, false));

					if (is_array($extraVars))
						$data = array_merge($data, $extraVars);

					// Join PDF invoice
					$file_attachement = array();
					if ((int)(Configuration::get('PS_INVOICE')) && Validate::isLoadedObject($orderStatus) && $orderStatus->invoice && $order->invoice_number)
					{
						$file_attachement['content'] = PDF::invoice($order, 'S');
						$file_attachement['name'] = Configuration::get('PS_INVOICE_PREFIX', (int)($order->id_lang)).sprintf('%06d', $order->invoice_number).'.pdf';
						$file_attachement['mime'] = 'application/pdf';
					}
					else
						$file_attachement = NULL;

					if (Validate::isEmail($customer->email))
						Mail::Send((int)$order->id_lang, 'order_conf', Mail::l('Order confirmation', (int)$order->id_lang), $data, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, $file_attachement);

				}
				$this->currentOrder = (int)$order->id;
				return true;
			}
			else
			{
				$errorMessage = Tools::displayError('Order creation failed');
				Logger::addLog($errorMessage, 4, '0000002', 'Cart', (int)($order->id_cart));
				die($errorMessage);
			}
		}
		else
		{
			$errorMessage = Tools::displayError('Cart can\'t be loaded or an order has already been placed using this cart');
			Logger::addLog($errorMessage, 4, '0000001', 'Cart', (int)($cart->id));
			die($errorMessage);
		}
	}

	public function formatProductAndVoucherForEmail($content)
	{
		return $content;
	}

	/**
	 * @param Object Address $the_address that needs to be txt formated
	 * @return String the txt formated address block
	 */
	protected function _getTxtFormatedAddress($the_address)
	{
		$out = '';
		$adr_fields = AddressFormat::getOrderedAddressFields((int)$the_address->id_country, false, true);
		$r_values = array();
		foreach($adr_fields as $fields_line)
		{
			$tmp_values = array();
			foreach (explode(' ', $fields_line) as $field_item)
			{
				$field_item = trim($field_item);
				$tmp_values[] = $the_address->{$field_item};
			}
			$r_values[] = implode(' ', $tmp_values);
		}

		$out = implode("\n", $r_values);
		return $out;
	}

	/**
	 * @param Object Address $the_address that needs to be txt formated
	 * @return String the txt formated address block
	 */

	//private function _getFormatedAddress(Address $the_address, $line_sep, $fields_style = array())//version 1.4
    protected function _getFormatedAddress(Address $the_address, $line_sep, $fields_style = array())//version 1.5
	{
		if (method_exists("AddressFormat", "generateAddress"))
            return AddressFormat::generateAddress($the_address, array('avoid' => array()), $line_sep, ' ', $fields_style);
        else
            return self::generateAddress($the_address, array('avoid' => array()), $line_sep, ' ', $fields_style);
	}

    private static function generateAddress(Address $address, $patternRules, $newLine = "\r\n", $separator = ' ', $style = array())
	{
		$addressFields = AddressFormat::getOrderedAddressFields($address->id_country);
		$addressFormatedValues = self::getFormattedAddressFieldsValues($address, $addressFields);

		$addressText = '';
		foreach ($addressFields as $line)
			if (($patternsList = explode(' ', $line)))
				{
					$tmpText = '';
					foreach($patternsList as $pattern)
						if (!in_array($pattern, $patternRules['avoid']))
							$tmpText .= (isset($addressFormatedValues[$pattern])) ?
								(((isset($style[$pattern])) ?
									(sprintf($style[$pattern], $addressFormatedValues[$pattern])) :
									$addressFormatedValues[$pattern]).$separator) : '';
					$tmpText = trim($tmpText);
					$addressText .= (!empty($tmpText)) ? $tmpText.$newLine: '';
				}
		return $addressText;
	}

    private static function getFormattedAddressFieldsValues($address, $addressFormat)
	{
		$cookie = Context::getContext()->cookie;
		$tab = array();
		$temporyObject = array();

		// Check if $address exist and it's an instanciate object of Address
		if ($address && ($address instanceof Address))
			foreach($addressFormat as $line)
			{
				if (($keyList = explode(' ', $line)) && is_array($keyList))
					foreach($keyList as $pattern)
						if ($associateName = explode(':', $pattern))
						{
							$totalName = count($associateName);
							if ($totalName == 1 && isset($address->{$associateName[0]}))
								$tab[$associateName[0]] = $address->{$associateName[0]};
							else
							{
								$tab[$pattern] = '';

								// Check if the property exist in both classes
								if (($totalName == 2) && class_exists($associateName[0]) &&
									Tools::property_exists($associateName[0], $associateName[1]) &&
									Tools::property_exists($address, 'id_'.Tools::strtolower($associateName[0])))
								{
									$idFieldName = 'id_'.Tools::strtolower($associateName[0]);

									if (!isset($temporyObject[$associateName[0]]))
										$temporyObject[$associateName[0]] = new $associateName[0]($address->{$idFieldName});
									if ($temporyObject[$associateName[0]])
										$tab[$pattern] = (is_array($temporyObject[$associateName[0]]->{$associateName[1]})) ?
											((isset($temporyObject[$associateName[0]]->{$associateName[1]}[(isset($cookie) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'))])) ?
											$temporyObject[$associateName[0]]->{$associateName[1]}[(isset($cookie) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'))] : '') :
											$temporyObject[$associateName[0]]->{$associateName[1]};
								}
							}
					}
			}
		// Free the instanciate objects
		foreach($temporyObject as &$object)
			unset($object);
		return $tab;
	}

	/**
	 * @param int $id_currency : this parameter is optionnal but on 1.5 version of Prestashop, it will be REQUIRED
	 * @return Currency
	 */
	public function getCurrency($current_id_currency = NULL)
	{
		if (!$this->currencies)
			return false;
		if ($this->currencies_mode == 'checkbox')
		{
			$currencies = Currency::getPaymentCurrencies((int)$this->id);
			return $currencies;
		}
		elseif ($this->currencies_mode == 'radio')
		{
			$currencies = Currency::getPaymentCurrenciesSpecial((int)$this->id);
			$currency = $currencies['id_currency'];
			if ($currency == -1)
			{
				// not use $this->context->cookie if $current_id_currency is set
				if ((int)$current_id_currency)
					$id_currency = (int)$current_id_currency;
				else
					$id_currency = (int)($this->context->cookie->id_currency);
			}
			elseif ($currency == -2)
				$id_currency = (int)(Configuration::get('PS_CURRENCY_DEFAULT'));
			else
				$id_currency = (int)$currency;
		}
		if (!isset($id_currency) || empty($id_currency))
			return false;
		return (new Currency($id_currency));
	}

	/**
	 * Allows specified payment modules to be used by a specific currency
	 *
	 * @since 1.4.5
	 * @param int $id_currency
	 * @param array $id_module_list
	 * @return boolean
	 */
	public static function addCurrencyPermissions($id_currency, array $id_module_list = array())
	{
		$values = '';
		if (count($id_module_list) == 0)
		{
			// fetch all installed module ids
			$modules = PaymentModuleCore::getInstalledPaymentModules();
			foreach ($modules as $module)
				$id_module_list[] = $module['id_module'];
		}

		foreach ($id_module_list as $id_module)
			$values .= '('.(int)$id_module.','.(int)$id_currency.'),';

		if (!empty($values))
		{
			return Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'module_currency` (`id_module`, `id_currency`)
			VALUES '.rtrim($values, ',')
			);
		}

		return true;
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

    public function getModuleLink($module, $controller = 'default')
	{
        return (_PS_BASE_URL_.__PS_BASE_URI__."modules/".$module."/".$controller);
	}
}