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
*  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class GatewayOrder extends Gateway
{
	public static $type_sku = 'reference';

	private $time_analyse = false;
	private $start_time = 0;
	private $current_time_0 = 0;
	private $current_time_2 = 0;

	/* @var array List of Gateway instance */
	protected static $instance = array();

	public static function getInstance($client = null)
	{
		if ($client != null)
			$wsdl = 1;
		else
			$wsdl = 0;
			
		if (!isset(self::$instance[$wsdl]))
			self::$instance[$wsdl] = new GatewayOrder($client);

		self::$type_sku = (Gateway::getConfig('TYPE_SKU') !== false)?Gateway::getConfig('TYPE_SKU'):'reference';

		return self::$instance[$wsdl];
	}
	
	/**
	 * Get NetEven order
	 * @param bool $display
	 */
	public function getOrderNetEven($display = true)
	{
		try
		{
			$params = array();
			$getOrdersResponse = $this->client->GetOrders($params);
			$neteven_orders = (array)$getOrdersResponse->GetOrdersResult->MarketPlaceOrder;
		}
		catch (Exception $e)
		{
			Toolbox::manageError($e, 'get order ');
			$neteven_orders = array();
		}

		if ($this->getValue('send_request_to_mail'))
			$this->sendDebugMail($this->getValue('mail_list_alert'), self::getL('Debug - Control request').' getOrderNetEven', $this->client->__getLastRequest(), true);
		
		/* if one command, transform this to array. */
		if (isset($neteven_orders['OrderID']))
		{
			$temp = $neteven_orders;
			$neteven_orders = array();
			$neteven_orders[] = $temp;
		}
		
		/* get command already in presta. */
		$order_prev = $this->getOrderNetEvenInPresta();

		$t_order_real = array();
		
		foreach ($neteven_orders as $key => &$neteven_order)
		{
			$neteven_order = (object)$neteven_order;
			$control = true;
			
			if ((trim(Tools::strtolower($neteven_order->BillingAddress->FirstName)) == 'none' || !isset($neteven_order->DatePayment) && strpos(Tools::strtolower($neteven_order->MarketPlaceName), 'cdiscount') !== false))
				continue;
			
			/* Test status of others products of this command. */
			foreach ($neteven_orders as $neteven_order_temp)
			{
				if ($neteven_order->OrderID == $neteven_order_temp->OrderID && !in_array($neteven_order_temp->Status, $this->getValue('t_list_order_status_traite')))
				{
					$control = false;
					break;
				}
			}
			
			if (!$display)
			{
				/* test command status and if the command already exists.*/
				if ($control && !in_array($neteven_order->Status, $this->getValue('t_list_order_status')) && !isset($order_prev[$neteven_order->OrderLineID]))
					$this->addOrderInBDD($neteven_order, $neteven_orders);
				
				if (strpos(Tools::strtolower($neteven_order->MarketPlaceName), 'priceminister') !== false)
					$this->updateOrder($neteven_order, $neteven_orders);
				
				if (strpos(Tools::strtolower($neteven_order->MarketPlaceName), 'laredoute') !== false || strpos(Tools::strtolower($neteven_order->MarketPlaceName), 'cdiscount') !== false)
					$this->updateOrderRedoute($neteven_order);
				
			}
		}

		if ($display)
			echo Tools::p($neteven_orders);

	}

	private function updateOrderRedoute($neteven_order)
	{
		if ($id_order = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_order` FROM `'._DB_PREFIX_.'orders_gateway` WHERE `id_order_neteven` = '.(int)$neteven_order->OrderID))
		{
			$order = new Order((int)$id_order);
			$last_name = Toolbox::removeAccents($neteven_order->BillingAddress->LastName);
			
			$customer = new Customer((int)$order->id_customer);
			$customer->firstname = (!empty($neteven_order->BillingAddress->FirstName)) ? Tools::substr(Toolbox::stringFilter($neteven_order->BillingAddress->FirstName), 0, 32) : ' ';
			$customer->lastname	= (!empty($last_name)) ? Tools::substr(Toolbox::stringFilter($last_name), 0, 32) : ' ';
			$customer->email = (Validate::isEmail($neteven_order->BillingAddress->Email) && !empty($neteven_order->BillingAddress->Email)) ? '_'.$neteven_order->BillingAddress->Email : '_client'.$neteven_order->OrderID.'@'.$neteven_order->MarketPlaceName.'.com';
			$customer->save();
			$date_now = date('Y-m-d H:i:s');
			
			$shipping_address = $neteven_order->ShippingAddress;
			$id_country = $this->getValue('id_country_default');
			
			$address = new Address((int)$order->id_address_delivery);
			$address->lastname = (!empty($shipping_address->LastName)) ? Tools::substr(Toolbox::stringFilter($shipping_address->LastName), 0, 32) : ' ';
			$address->firstname	= (!empty($shipping_address->FirstName)) ? Tools::substr(Toolbox::stringFilter($shipping_address->FirstName), 0, 32) : ' ';
			$address->address1 = (!empty($shipping_address->Address1)) ? Toolbox::stringWithNumericFilter($shipping_address->Address1) : ' ';
			$address->address2 = Toolbox::stringWithNumericFilter($shipping_address->Address2);
			$address->postcode = Toolbox::numericFilter($shipping_address->PostalCode);
			$address->city = (!empty($shipping_address->CityName)) ? Toolbox::stringFilter($shipping_address->CityName) : ' ';
			$address->phone	= Tools::substr(Toolbox::numericFilter($shipping_address->Phone), 0, 16);
			$address->phone_mobile = Tools::substr(Toolbox::numericFilter($shipping_address->Mobile), 0, 16);
			$address->id_country = $id_country;
			$address->date_upd = $date_now;

			if (!empty($shipping_address->Company))
				$address->company = $shipping_address->Company;

			$address->save();

			$billing_address = $neteven_order->BillingAddress;
			$address = new Address((int)$order->id_address_invoice);
			$address->lastname = (!empty($billing_address->LastName)) ? Tools::substr(Toolbox::stringFilter($billing_address->LastName), 0, 32) : ' ';
			$address->firstname	= (!empty($billing_address->FirstName)) ? Tools::substr(Toolbox::stringFilter($billing_address->FirstName), 0, 32) : ' ';
			$address->address1 = (!empty($billing_address->Address1)) ? Toolbox::stringWithNumericFilter($billing_address->Address1):' ';
			$address->address2 = Toolbox::stringWithNumericFilter($billing_address->Address2);
			$address->postcode = Toolbox::numericFilter($billing_address->PostalCode);
			$address->city = (!empty($billing_address->CityName)) ? Toolbox::stringFilter($billing_address->CityName) : ' ';
			$address->phone	= Tools::substr(Toolbox::numericFilter($billing_address->Phone), 0, 16);
			$address->phone_mobile = Tools::substr(Toolbox::numericFilter($billing_address->Mobile), 0, 16);
			$address->id_country = $id_country;
			$address->date_upd = $date_now;

			if (!empty($billing_address->Company))
				$address->company = $billing_address->Company;

			$address->save();
		}
	}
	
	private function updateOrder($neteven_order, $neteven_orders)
	{
		if ($id_order = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_order` FROM `'._DB_PREFIX_.'orders_gateway` WHERE `id_order_neteven` = '.(int)$neteven_order->OrderID))
		{
			$total_wt = 0;
			$total_product = 0;
			$total_product_wt = 0;

			foreach ($neteven_orders as $neteven_order_temp)
			{				
				if (in_array($neteven_order_temp->Status, $this->getValue('t_list_order_status_retraite_order')))
					continue;

				if ($neteven_order_temp->OrderID == $neteven_order->OrderID)
				{
					$total_product += (((float)($neteven_order_temp->Price->_) - (float)($neteven_order_temp->VAT->_)));
					$total_product_wt += (float)($neteven_order_temp->Price->_);
				}
			}

			$order = new Order((int)$id_order);

			if (!$order->id_carrier)
				$order->id_carrier = (int)Gateway::getConfig('CARRIER_NETEVEN');

			
			$carrier = new Carrier((int)$order->id_carrier);

			$carrier_tax_rate = 100;
			if (method_exists($carrier, 'getTaxesRate'))
				$carrier_tax_rate = $carrier->getTaxesRate(new Address($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));

			if (method_exists('Tax', 'getCarrierTaxRate'))
				$carrier_tax_rate = (float)Tax::getCarrierTaxRate($order->id_carrier, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});


			$total_shipping_tax_excl = $carrier_tax_rate ? $neteven_order->OrderShippingCost->_ / ($carrier_tax_rate/100) : $neteven_order->OrderShippingCost->_;
			
			$total_wt = $total_product_wt + $neteven_order->OrderShippingCost->_;
			$total = $total_product + $total_shipping_tax_excl;
			
			$order->total_products = (float)number_format($total_product, 2, '.', '');
			$order->total_products_wt = (float)number_format($total_product_wt, 2, '.', '');
			$order->total_shipping_tax_excl = (float)number_format($total_shipping_tax_excl, 2, '.', '');
			$order->total_shipping_tax_incl = (float)number_format($neteven_order->OrderShippingCost->_, 2, '.', '');
			$order->total_shipping = (float)number_format($neteven_order->OrderShippingCost->_, 2, '.', '');
			$order->total_paid_tax_excl = (float)number_format($total, 2, '.', '');
			$order->total_paid_tax_incl = (float)number_format($total_wt, 2, '.', '');
			$order->total_paid_real = (float)number_format($total_wt, 2, '.', '');
			$order->total_paid = (float)number_format($total_wt, 2, '.', '');
			$order->carrier_tax_rate = (float)number_format($carrier_tax_rate, 2, '.', '');
			$order->save();
		}
	}
	
	/**
	 * Add NetEven order in PrestaShop
	 * @param $neteven_order
	 * @param $neteven_orders
	 * @return mixed
	 */
	private function addOrderInBDD($neteven_order, $neteven_orders)
	{
		if ($this->time_analyse)
			$this->start_time = time();

		$ref_temp = $neteven_order->SKU;

		if (self::$type_sku == 'reference')
			$where_req = ' (p.`reference` = "'.pSQL($ref_temp).'" OR pa.`reference` = "'.pSQL($ref_temp).'") ';
		else
		{
			$type_temp = Tools::substr($ref_temp, 0, 1);
			$id_p_temp = str_replace($type_temp, '', $ref_temp);
			$where_req = '';
	
			if ($type_temp == 'D')
				$where_req = ' pa.`id_product_attribute` = '.(int)$id_p_temp;
	
			if ($type_temp == 'P')
				$where_req = ' p.`id_product` = '.(int)$id_p_temp;
			
		}
		
		if (empty($where_req))
			return;

		if (!Db::getInstance()->getRow('
			SELECT pl.`name` as name_product, p.`id_product`, pa.`id_product_attribute`, p.`reference` as product_reference, pa.`reference` as product_attribute_reference, GROUP_CONCAT(CONCAT(agl.`name`," : ",al.`name`) SEPARATOR ", ") as attribute_name
			FROM `'._DB_PREFIX_.'product` p
			INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON(p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$this->getValue('id_lang').')
			LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = p.`id_product`)
			LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.`id_product_attribute`=pa.`id_product_attribute`)
			LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute`=pac.`id_attribute`)
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.id_attribute=a.`id_attribute` AND al.`id_lang`='.(int)$this->getValue('id_lang').')
			LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (agl.`id_attribute_group`=a.`id_attribute_group` AND agl.`id_lang`='.(int)$this->getValue('id_lang').')
			WHERE p.`active` = 1 AND '.$where_req.'
			GROUP BY p.`id_product`
		'))
		{
			if ($this->getValue('mail_active'))
				$this->sendDebugMail($this->getValue('mail_list_alert'), self::getL('Product not found when importing a NetEven order'), self::getL('Product not found SKU').' ('.$neteven_order->SKU.'). '.self::getL('NetEven Order Detail').' : '.print_r($neteven_order, true));
	
			return;
		}

		if ($this->time_analyse)
		{
			$this->current_time_0 = time();
			Toolbox::displayDebugMessage(self::getL('Start').' : '.((int)$this->current_time_0 - (int)$this->start_time).'s');
		}

        $order_already_exist = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'orders_gateway` WHERE `id_order_neteven` = '.(int)$neteven_order->OrderID.' AND `id_order_detail_neteven` = 0');

		/* Treatment of order */
		$id_order_temp = $this->createOrder($neteven_order, $neteven_orders);

		if ($this->time_analyse)
			$this->current_time_2 = time();

		/* Treatment of order details */
		if ($id_order_temp != 0)
		{
			$this->createOrderDetails($neteven_order, $id_order_temp);

            if(!$order_already_exist)
                $this->addStatusOnOrder($id_order_temp, $neteven_order);
        }

		if ($this->time_analyse)
		{ 
			$this->current_time_2 = time();
			Toolbox::displayDebugMessage(self::getL('Total').' : '.((int)$this->current_time_2 - (int)$this->start_time).'s');
		}
		
		Toolbox::writeLog();
	}

	/**
	 * Creating of the PrestaShop order
	 * @param $neteven_order
	 * @param $neteven_orders
	 * @return int
	 */
	private function createOrder($neteven_order, $neteven_orders)
	{
		if (constant('_PS_VERSION_') >= 1.5)
			include_once(dirname(__FILE__).'/OrderInvoiceOverride.php');
		
		/* Treatment of customer */
		$id_customer = $this->addCustomerInBDD($neteven_order);

		if ($this->time_analyse)
		{
			$this->current_time_2 = time();
			Toolbox::displayDebugMessage(self::getL('Customer').' : '.((int)$this->current_time_2 - (int)$this->current_time_0).'s');
		}

		/* Treatment of addresses of the customer */
		$id_address_billing = $this->addAddresseInBDD($neteven_order->OrderID, $neteven_order->BillingAddress, 'facturation', $id_customer);
		$id_address_shipping = $this->addAddresseInBDD($neteven_order->OrderID, $neteven_order->ShippingAddress, 'livraison', $id_customer);

		if ($this->time_analyse)
		{
			$this->current_time_0 = time();
			Toolbox::displayDebugMessage(self::getL('Address').' : '.((int)$this->current_time_0 - (int)$this->current_time_2).'s');
		}

		/* Get secure key of customer */
		$secure_key_default = md5(uniqid(rand(), true));
		if ($secure_key = Db::getInstance()->getValue('SELECT `secure_key` FROM `'._DB_PREFIX_.'customer` WHERE `id_customer` = '.(int)$id_customer))
			$secure_key_default = $secure_key;
		else
			Toolbox::addLogLine(self::getL('Problem with a secure key recovery for the customer / NetEven Order Id').' '.$neteven_order->OrderID);

		/* Treatment of order informations */
		$total_wt = 0;
		$total_product = 0;
		$total_product_wt = 0;
		$total_taxe = 0;

		foreach ($neteven_orders as $neteven_order_temp)
		{
			if ($neteven_order_temp->OrderID == $neteven_order->OrderID)
			{
				if (in_array($neteven_order_temp->Status, $this->getValue('t_list_order_status')))
					continue;
				
				$total_product += (((float)($neteven_order_temp->Price->_) - (float)($neteven_order_temp->VAT->_)));
				$total_product_wt += ((float)($neteven_order_temp->Price->_));
				$total_taxe += $neteven_order_temp->VAT->_;
			}
		}

		$total_wt = $total_product_wt + $neteven_order->OrderShippingCost->_;
		$date_now = date('Y-m-d H:i:s');

		if ($this->time_analyse)
		{
			$this->current_time_2 = time();
			Toolbox::displayDebugMessage(self::getL('Order total').' : '.((int)$this->current_time_2 - (int)$this->current_time_0).'s');
		}

		/* Creating and add order in PrestaShop */
		if (!$res = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'orders_gateway` WHERE `id_order_neteven` = '.(int)$neteven_order->OrderID.' AND `id_order_detail_neteven` = 0'))
		{
			/* Creating cart */
			$cart = new Cart();
			$cart->id_address_delivery = (int)$id_address_shipping;
			$cart->id_address_invoice = (int)$id_address_billing;
			$cart->id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
			$cart->id_customer = (int)$id_customer;
			$cart->id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
			$cart->id_carrier = Gateway::getConfig('CARRIER_NETEVEN');
			$cart->recyclable = 1;
			$cart->gift	= 0;
			$cart->gift_message = '';
			$cart->date_add	= $date_now;
			$cart->secure_key = $secure_key_default;
			$cart->date_upd	= $date_now;


			if (!$cart->add())
				Toolbox::addLogLine(self::getL('Failed for cart creation / NetEven Order Id').' '.(int)$neteven_order->OrderID);

			if ($this->time_analyse)
			{
				$this->current_time_0 = time();
				Toolbox::displayDebugMessage(self::getL('Cart').' : '.((int)$this->current_time_0 - (int)$this->current_time_2).'s');
			}


			/* Creating order */
			$id_order_temp = 0;
			$order = new Order();
			$order->id_carrier = Gateway::getConfig('CARRIER_NETEVEN');
			$order->id_lang = Configuration::get('PS_LANG_DEFAULT');
			$order->id_customer = $id_customer;
			$order->id_cart = $cart->id;
			$order->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
			$order->id_address_delivery = $id_address_shipping;
			$order->id_address_invoice = $id_address_billing;
			$order->secure_key = $secure_key_default;
			$order->payment = $neteven_order->PaymentMethod;
			$order->conversion_rate = 1;
			$order->module = 'nqgatewayneteven';
			$order->recyclable = 0;
			$order->gift = 0;
			$order->gift_message = ' ';
			$order->shipping_number = '';

			/* generate reference order */
			$nbr_order_neteven = Configuration::get('NUMBER_ORDER_NETEVEN');

			if (false === $nbr_order_neteven)
				$nbr_order_neteven = 1;
			else
			{
				$nbr_order_neteven = (int)(str_replace('N', '', $nbr_order_neteven));
				$nbr_order_neteven++;
			}

			$next_ref_gen_order_neteven = 'N'.sprintf('%07s', $nbr_order_neteven);
			Configuration::updateValue('NUMBER_ORDER_NETEVEN', $next_ref_gen_order_neteven);
			$order->reference = $next_ref_gen_order_neteven;
			/* ----- */
			
			$carrier = new Carrier((int)$order->id_carrier);


			if (method_exists($carrier, 'getTaxesRate'))
				$carrier_tax_rate = $carrier->getTaxesRate(new Address($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
			elseif (method_exists('Tax', 'getCarrierTaxRate'))
				$carrier_tax_rate = (float)Tax::getCarrierTaxRate($order->id_carrier, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
			else
				$carrier_tax_rate = 100;

			$total_shipping_tax_excl = $carrier_tax_rate ? $neteven_order->OrderShippingCost->_ / ($carrier_tax_rate/100) : $neteven_order->OrderShippingCost->_;
			
			$total_wt = $total_product_wt + $neteven_order->OrderShippingCost->_;
			$total = $total_product + $total_shipping_tax_excl;

			$order->total_discounts_tax_excl = 0;
			$order->total_discounts_tax_incl = 0;
			$order->total_discounts = 0;
			$order->total_wrapping_tax_excl = 0;
			$order->total_wrapping_tax_incl = 0;
			$order->total_wrapping = 0;
			$order->total_products = (float)number_format($total_product, 2, '.', '');
			$order->total_products_wt = (float)number_format($total_product_wt, 2, '.', '');
			$order->total_shipping_tax_excl = (float)number_format($total_shipping_tax_excl, 2, '.', '');
			$order->total_shipping_tax_incl = (float)number_format($neteven_order->OrderShippingCost->_, 2, '.', '');
			$order->total_shipping = (float)number_format($neteven_order->OrderShippingCost->_, 2, '.', '');
			$order->total_paid_tax_excl = (float)number_format($total_wt - $total_taxe, 2, '.', '');
			$order->total_paid_tax_incl = (float)number_format($total_wt, 2, '.', '');
			$order->total_paid_real = (float)number_format($total_wt, 2, '.', '');
			$order->total_paid = (float)number_format($total_wt, 2, '.', '');
			$order->carrier_tax_rate = 0;
			$order->total_wrapping = 0;
			$order->invoice_number = 0;
			$order->delivery_number = 0;
			$order->invoice_date = $date_now;
			$order->delivery_date = $date_now;
			$order->valid = 1;
			$order->date_add = $date_now;
			$order->date_upd = $date_now;


			if (Configuration::get('PS_SHOP_ENABLE'))
				$order->id_shop = (int)Configuration::get('PS_SHOP_DEFAULT');

			if (!$order->add())
				Toolbox::addLogLine(self::getL('Failed for order creation / NetEven Order Id').' '.(int)$neteven_order->OrderID);
			else
			{
				$id_order_temp = $order->id;

				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'order_carrier` (`id_order`, `id_carrier`, `id_order_invoice`, `weight`, `shipping_cost_tax_excl`, `shipping_cost_tax_incl`, `tracking_number`, `date_add`) VALUES ('.(int)$id_order_temp.', '.(int)Gateway::getConfig('CARRIER_NETEVEN').', 0, 0, 0, 0, 0,"'.pSQL(date('Y-m-d H:i:s')).'")');

				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'message` (`id_order`, `message`, `date_add`) VALUES ('.(int)$id_order_temp.', "Place de marché '.$neteven_order->MarketPlaceName.'", "'.pSQL(date('Y-m-d H:i:s')).'")');
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'message` (`id_order`, `message`, `date_add`) VALUES ('.(int)$id_order_temp.', "ID order NetEven '.$neteven_order->MarketPlaceOrderId.'", "'.pSQL(date('Y-m-d H:i:s')).'")');

				if ($this->time_analyse)
				{
					$this->current_time_2 = time();
					Toolbox::displayDebugMessage(self::getL('Order').' : '.((int)$this->current_time_2 - (int)$this->current_time_0).'s');
				}

				Toolbox::addLogLine(self::getL('Add order Id').' '.(int)$id_order_temp.' '.self::getL('NetEven Order Id').' '.(int)$neteven_order->OrderID);


				if ($this->time_analyse)
				{
					$this->current_time_0 = time();
					Toolbox::displayDebugMessage(self::getL('History').' : '.((int)$this->current_time_0 - (int)$this->current_time_2).'s');
				}

				/* Insert order in orders_gateway table */
				if (!Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'orders_gateway` (`id_order_neteven`, `id_order`, `id_order_detail_neteven`, `date_add`, `date_upd`) VALUES ('.(int)$neteven_order->OrderID.', '.(int)$id_order_temp.', 0, "'.pSQL($date_now).'", "'.pSQL($date_now).'")'))
					Toolbox::addLogLine(self::getL('Failed for save export NetEven order Id').' '.(int)$neteven_order->OrderID);
				else
					Toolbox::addLogLine(self::getL('Save export NetEven order Id').' '.(int)$neteven_order->OrderID);

			}
		}
		else
		{
			$id_order_temp = $res['id_order'];
			Toolbox::addLogLine(self::getL('Get already exported order Id').' '.$res['id_order'].' '.self::getL('NetEven Order Id').' '.(int)$neteven_order->OrderID);
		}
		
		return $id_order_temp;
	}

	private function addStatusOnOrder($id_order, $neteven_order)
	{
		/* Update order state in order */
		$order_state = array_merge($this->getValue('order_state_before'), array($this->getValue('id_order_state_neteven')), $this->getValue('order_state_after'));

		if (is_array($order_state) && count($order_state) > 0)
		{
			foreach ($order_state as $id_order_state)
			{
				if (class_exists('OrderInvoiceOverride' && method_exists('OrderInvoiceOverride', 'clearCacheTotalPaid')))
					OrderInvoiceOverride::clearCacheTotalPaid();

				$new_history = new OrderHistory();
				$new_history->id_order = (int)$id_order;
				$new_history->changeIdOrderState((int)$id_order_state, $id_order);
				$new_history->addWithemail(true, array());
				Toolbox::addLogLine(self::getL('Save order state Id').' '.(int)$id_order_state.' '.self::getL('NetEven Order Id').' '.(int)$neteven_order->OrderID);
			}
		}
	}

	/**
	 * Creating order details of order
	 * @param $neteven_order
	 * @param $id_order
	 * @return mixed
	 */
	private function createOrderDetails($neteven_order, $id_order)
	{
		$context = Context::getContext();

		$date_now = date('Y-m-d H:i:s');

		if (in_array($neteven_order->Status, $this->getValue('t_list_order_status')))
			return;

		/* If order detail doesn't exist */
		if (!$res = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'orders_gateway` WHERE `id_order_neteven` = '.(int)$neteven_order->OrderID.' AND `id_order_detail_neteven` = '.(int)$neteven_order->OrderLineID))
		{
			/* If product exist */
			$ref_temp = $neteven_order->SKU;
			$type_temp = Tools::substr($ref_temp, 0, 1);
			$id_p_temp = str_replace($type_temp, '', $ref_temp);
			$where_req = '';

			if ($type_temp == 'D')
				$where_req = 'pa.`id_product_attribute` = '.(int)$id_p_temp;

			if ($type_temp == 'P')
				$where_req = 'p.`id_product` = '.(int)$id_p_temp;

			if (self::$type_sku == 'reference')
				$where_req = ' (p.`reference` = "'.pSQL($ref_temp).'" OR pa.`reference` = "'.pSQL($ref_temp).'") ';
			
			if (empty($where_req))
				return;

			$res_product = Db::getInstance()->getRow('
					SELECT pl.`name` as name_product, p.`id_product`, pa.`id_product_attribute`, p.`reference` as product_reference, pa.`reference` as product_attribute_reference, p.`weight` as weight, GROUP_CONCAT(CONCAT(agl.`name`," : ",al.`name`) SEPARATOR ", ") as attribute_name
					FROM `'._DB_PREFIX_.'product` p
					INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON(p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$this->getValue('id_lang').')
					LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = p.`id_product`)
					LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.`id_product_attribute`=pa.`id_product_attribute`)
					LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute`=pac.`id_attribute`)
					LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.`id_attribute`=a.`id_attribute` AND al.`id_lang`='.(int)$this->getValue('id_lang').')
					LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (agl.`id_attribute_group`=a.`id_attribute_group` AND agl.`id_lang`='.(int)$this->getValue('id_lang').')
					WHERE p.`active` = 1 AND '.$where_req.'
					GROUP BY pa.`id_product_attribute`, p.`id_product`
				');

			if ($res_product)
			{
				/* Get order detail informations */
				$product_reference = $res_product['product_reference'];
				$id_product_attribute = 0;
				$name = $res_product['name_product'];
				$control_attribute_product = false;
				if (!empty($res_product['id_product_attribute']))
				{
					$product_reference = $res_product['product_attribute_reference'];
					$id_product_attribute = $res_product['id_product_attribute'];

					if (!empty($res_product['attribute_name']))
						$name .= ' - '.$res_product['attribute_name'];

					$control_attribute_product = true;
				}

				/* Add product in cart */
				$order = new Order($id_order);

				if (!Db::getInstance()->getRow('SELECT `id_cart` FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.(int)$order->id_cart.' AND `id_product` = '.(int)$res_product['id_product'].' AND `id_product_attribute` = '.(int)$id_product_attribute))
					Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'cart_product` (`id_cart`, `id_product`, `id_product_attribute`, `quantity`, `date_add`) VALUES ('.(int)$order->id_cart.', '.(int)$res_product['id_product'].', '.(int)$id_product_attribute.', '.(int)$neteven_order->Quantity.', "'.pSQL($date_now).'")');

				if ($this->time_analyse)
				{
					$this->current_time_0 = time();
					Toolbox::displayDebugMessage(self::getL('Order information').' : '.((int)$this->current_time_0 - (int)$this->current_time_2).'s');
				}

				/* Add order detail */
				$tax = new Tax(Configuration::get('PS_TAX'), $context->cookie->id_lang);
				
				$price_product = ($neteven_order->Price->_ - (float)($neteven_order->VAT->_)) / $neteven_order->Quantity;
				$order_detail = new OrderDetail();
				$order_detail->id_order	= $id_order;
				$order_detail->product_id = $res_product['id_product'];
				$order_detail->product_attribute_id = $id_product_attribute;
				$order_detail->product_name = $name;
				$order_detail->product_quantity	= $neteven_order->Quantity;
				$order_detail->product_quantity_in_stock = $neteven_order->Quantity;
				$order_detail->product_quantity_refunded = 0;
				$order_detail->product_quantity_return = 0;
				$order_detail->product_quantity_reinjected = 0;
				$order_detail->product_price = number_format((float)$price_product, 4, '.', '');
				$order_detail->total_price_tax_excl	= number_format((float)$price_product, 4, '.', '');
				$order_detail->unit_price_tax_incl = number_format((float)$price_product, 4, '.', '');
				$order_detail->unit_price_tax_excl = $tax->rate ? number_format((float)$price_product / ((float)$tax->rate/100), 4, '.', '') : $price_product;
				$order_detail->reduction_percent = 0;
				$order_detail->reduction_amount = 0;
				$order_detail->group_reduction = 0;
				$order_detail->product_quantity_discount = 0;
				$order_detail->product_ean13 = null;
				$order_detail->product_upc = null;
				$order_detail->product_reference = $product_reference;
				$order_detail->product_supplier_reference = null;
				$order_detail->product_weight = !empty($res_product['weight']) ? (float)$res_product['weight'] : 0;
				$order_detail->tax_name	= $tax->name;
				$order_detail->tax_rate	= (float)$tax->rate;
				$order_detail->ecotax = 0;
				$order_detail->ecotax_tax_rate = 0;
				$order_detail->discount_quantity_applied = 0;
				$order_detail->download_hash = '';
				$order_detail->download_nb = 0;
				$order_detail->download_deadline = '0000-00-00 00:00:00';
				$order_detail->id_warehouse	= 0;

				if (Configuration::get('PS_SHOP_ENABLE'))
					$order_detail->id_shop = (int)Configuration::get('PS_SHOP_DEFAULT');

				if (!$order_detail->add())
					Toolbox::addLogLine(self::getL('Failed for creation of order detail / NetEven Order Id').' '.(int)$neteven_order->OrderID.' '.self::getL('NetEven order detail id').' '.$neteven_order->OrderLineID);
				else
				{
					if ($this->time_analyse)
					{
						$this->current_time_2 = time();
						Toolbox::displayDebugMessage(self::getL('Order detail').' : '.((int)$this->current_time_2 - (int)$this->current_time_0).'s');
					}

					$id_order_detail_temp = $order_detail->id;

					Toolbox::addLogLine(self::getL('Creation of order detail for NetEven order Id').' '.(int)$neteven_order->OrderID.' '.self::getL('NetEven order detail id').' '.(int)$neteven_order->OrderLineID);

					/* Update quantity of product */
					if (class_exists('StockAvailable'))
					{
						/* Update quantity of product */
						if ($control_attribute_product)
							StockAvailable::setQuantity($res_product['id_product'], $id_product_attribute, StockAvailable::getQuantityAvailableByProduct($res_product['id_product'], $id_product_attribute) - $neteven_order->Quantity);
						else
							StockAvailable::setQuantity($res_product['id_product'], 0, StockAvailable::getQuantityAvailableByProduct($res_product['id_product']) - $neteven_order->Quantity);
					}
					else
					{
						$t_info_product = array();

						$t_info_product['id_product'] = $res_product["id_product"];
						$t_info_product['cart_quantity'] = $neteven_order->Quantity;
						$t_info_product['id_product_attribute'] = null;
						if ($control_attribute_product)
							$t_info_product['id_product_attribute'] = $id_product_attribute;

						Product::updateQuantity($t_info_product);

					}

					if ($this->time_analyse)
					{
						$this->current_time_0 = time();
						Toolbox::displayDebugMessage(self::getL('Cart product').' : '.((int)$this->current_time_0 - (int)$this->current_time_2).'s');
					}

					/* Insert order in orders_gateway table */
					if (!Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'orders_gateway` (`id_order_neteven`, `id_order`, `id_order_detail_neteven`, `date_add`, `date_upd`) VALUES ('.(int)$neteven_order->OrderID.', '.(int)$id_order.', '.(int)$neteven_order->OrderLineID.', "'.pSQL($date_now).'", "'.pSQL($date_now).'")'))
						Toolbox::addLogLine(self::getL('Failed for save export NetEven order Id').' '.(int)$neteven_order->OrderID.' '.self::getL('NetEven order detail id').' '.(int)$neteven_order->OrderLineID);
					else
						Toolbox::addLogLine(self::getL('Save export NetEven order Id').' '.(int)$neteven_order->OrderID.' '.self::getL('NetEven order detail id').' '.(int)$neteven_order->OrderLineID);
				}
			}
		}
		else
			Toolbox::addLogLine(self::getL('Failed for creation of order detail of NetEven order Id').$neteven_order->OrderID.' '.self::getL('NetEven order detail id').' '.$neteven_order->OrderLineID.' '.self::getL('Product not found SKU').' '.$neteven_order->SKU);

		$order = new Order($id_order);
		$products = $order->getProductsDetail();
		
		if (count($products) == 0 && $this->getValue('mail_active'))
			$this->sendDebugMail($this->getValue('mail_list_alert'), self::getL('Order imported is empty'),  self::getL('Order Id').' '.(int)$order->id);
	}

	/**
	 * Add customer
	 * @param $order_infos
	 * @return mixed
	 */
	private function addCustomerInBDD($neteven_order)
	{
		/* If customer exist */
		$client = Db::getInstance()->getRow('
				SELECT c.`id_customer`
				FROM `'._DB_PREFIX_.'customer` c
				INNER JOIN `'._DB_PREFIX_.'orders_gateway_customer` ogc ON (ogc.`id_customer` = c.`id_customer`)
				WHERE ogc.`id_customer_neteven` = '.(int)$neteven_order->CustomerId.'
				OR ogc.`mail_customer_neteven` = "_'.pSQL($neteven_order->BillingAddress->Email).'"
				OR ogc.`mail_customer_neteven` = "_client'.(int)$neteven_order->OrderID.'@'.$neteven_order->MarketPlaceName.'.com"'
			);

		if (!$client)
		{
			if (empty($neteven_order->CustomerId) && empty($neteven_order->BillingAddress->Email))
				return $this->getValue('id_customer_neteven');

			Toolbox::addLogLine(self::getL('Creation of customer for NetEven order Id').' '.$neteven_order->OrderID);

			$last_name = Toolbox::removeAccents($neteven_order->BillingAddress->LastName);
			
			$new_customer = new Customer();
			$new_customer->firstname = (!empty($neteven_order->BillingAddress->FirstName))?Tools::substr(Toolbox::stringFilter($neteven_order->BillingAddress->FirstName), 0, 32):' ';
			$new_customer->lastname	= (!empty($last_name))?Tools::substr(Toolbox::stringFilter($last_name), 0, 32):' ';
			$new_customer->passwd = Tools::encrypt($this->getValue('default_passwd'));
			$new_customer->email = (Validate::isEmail($neteven_order->BillingAddress->Email) && !empty($neteven_order->BillingAddress->Email))?'_'.$neteven_order->BillingAddress->Email:'_client'.$neteven_order->OrderID.'@'.$neteven_order->MarketPlaceName.'.com';
			$new_customer->optin = 0;
			if (isset($this->repere_customer) && $this->repere_customer)
				$new_customer->is_neteven = 1;
			
			if (!$new_customer->add())
				Toolbox::addLogLine(self::getL('Failed for creation of customer of NetEven order Id').' '.$neteven_order->OrderID);
			
			/* Insert customer in orders_gateway_customer table */
			if (!empty($neteven_order->CustomerId))
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'orders_gateway_customer` (`id_customer`, `id_customer_neteven`) VALUES ('.(int)$new_customer->id.', '.(int)$neteven_order->CustomerId.')');
			else
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'orders_gateway_customer` (`id_customer`, `mail_customer_neteven`) VALUES ('.(int)$new_customer->id.', "'.(Validate::isEmail($neteven_order->BillingAddress->Email) ? pSQL('_'.$neteven_order->BillingAddress->Email) : pSQL('_client'.(int)$neteven_order->OrderID.'@'.$neteven_order->MarketPlaceName.'.com')).'")');

			return (int)$new_customer->id;
		}

		Toolbox::addLogLine(self::getL('Get existing customer for NetEven Order Id').' '.$neteven_order->OrderID);
		return (int)$client['id_customer'];
	}

	/**
	 * Add addresses
	 * @param $order_id
	 * @param $order_infos
	 * @param $type
	 * @param $id_customer
	 * @return mixed
	 */
	private function addAddresseInBDD($order_id, $neteven_address, $type, $id_customer)
	{
		$id_country = $this->getValue('id_country_default');
		if (!$id_country = Country::getIdByName(2, $neteven_address->Country))
			Toolbox::addLogLine(self::getL('Problem with id_country on address').' '.$type.' '.self::getL('NetEven Order Id').' '.$order_id);
		
		$country = Db::getInstance()->getRow('
				SELECT c.`id_country`
				FROM `'._DB_PREFIX_.'country` c
				INNER JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country`)
				WHERE LOWER(c.`iso_code`) = "'.pSQL(Tools::strtolower($neteven_address->Country)).'"
				OR LOWER(cl.`name`) = "'.pSQL(Tools::strtolower($neteven_address->Country)).'"
				GROUP BY c.`id_country`
			');
		
		if (!empty($country['id_country']))
			$id_country = $country['id_country'];

		if ($id_address = Toolbox::existAddress($neteven_address, $id_country, $id_customer))
			Toolbox::addLogLine(self::getL('Get existing address for NetEven Order Id').' '.$order_id);
		else
		{
			Toolbox::addLogLine(self::getL('Creation of address of NetEven order Id').' '.$order_id);

			$date_now = date('Y-m-d H:i:s');
			$new_address = new Address();
			$new_address->alias	= 'Address';
			$new_address->lastname = (!empty($neteven_address->LastName)) ? Tools::substr(Toolbox::stringFilter($neteven_address->LastName), 0, 32) : ' ';
			$new_address->firstname = (!empty($neteven_address->FirstName)) ? Tools::substr(Toolbox::stringFilter($neteven_address->FirstName), 0, 32) : ' ';
			$new_address->address1 = (!empty($neteven_address->Address1)) ? Toolbox::stringWithNumericFilter($neteven_address->Address1) : ' ';
			$new_address->address2 = Toolbox::stringWithNumericFilter($neteven_address->Address2);
			$new_address->postcode = Toolbox::numericFilter($neteven_address->PostalCode);
			$new_address->city = (!empty($neteven_address->CityName)) ? Toolbox::stringFilter($neteven_address->CityName) : ' ';
			$new_address->phone	= Tools::substr(Toolbox::numericFilter($neteven_address->Phone), 0, 16);
			$new_address->phone_mobile = Tools::substr(Toolbox::numericFilter($neteven_address->Mobile), 0, 16);
			$new_address->id_country = $id_country;
			$new_address->id_customer = $id_customer;
			$new_address->date_add = $date_now;
			$new_address->date_upd = $date_now;

			if (!empty($neteven_address->Company))
				$new_address->company = $neteven_address->Company;

			if (!$new_address->add())
				Toolbox::addLogLine(self::getL('Failed for creation of address of NetEven order Id').' '.$order_id);
			else
				$id_address = $new_address->id;

		}

		return $id_address;
	}

	/**
	 * Get orders NetEven already saved
	 * @return array
	 */
	private function getOrderNetEvenInPresta()
	{
		$orders = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'orders_gateway` WHERE `id_order_detail_neteven` <> 0 AND `id_order` <> 0');
		$orders_temp = array();

		foreach ($orders as $order)
			$orders_temp[$order['id_order_detail_neteven']] = $order;
		
		return $orders_temp;
	}

	/**
	 * Set order NetEven
	 * @param $param
	 */
	public function setOrderNetEven($params)
	{
		if (!self::$send_order_state_to_neteven)
			return;
		
		if ($res = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'orders_gateway` WHERE `id_order` = '.(int)$params['id_order']))
		{
			$status = '';
			$track = '';
			$amounttorefund = 0;
			$date_now = date('Y-m-d H:i:s');

			if ($params['newOrderStatus']->id == (int)Configuration::get('PS_OS_PREPARATION'))
				$status = 'Confirmed';

			if ($params['newOrderStatus']->id == (int)Configuration::get('PS_OS_CANCELED'))
				$status = 'Canceled';

			if ($params['newOrderStatus']->id == (int)Configuration::get('PS_OS_DELIVERED'))
			{
				$res_order = Db::getInstance()->getRow('SELECT `shipping_number` FROM `'._DB_PREFIX_.'orders` WHERE `id_order` = '.(int)$params['id_order']);
				$status = 'Shipped';
				$track = $res_order['shipping_number'];
			}

			if ($params['newOrderStatus']->id == (int)Configuration::get('PS_OS_REFUND'))
			{
				$status = 'Refunded';
				$amounttorefund = Db::getInstance()->getValue('SELECT `total_paid_real` FROM `'._DB_PREFIX_.'orders` WHERE `id_order` = '.(int)$params['id_order']);
			}

			if ($status != '')
			{
				$order1 = array(
					'OrderID' => $res['id_order_neteven'],
					'Status' => $status
				);

				if (!empty($amounttorefund))
					$order1['AmountToRefund'] = $amounttorefund;

				if (!empty($track))
					$order1['TrackingNumber'] = $track;
				
				$params_web = array('orders' => array ($order1));

				try
				{
					$response = $this->client->PostOrders($params_web);
					$order_status = $response->PostOrdersResult->MarketPlaceOrderStatusResponse;
				}
				catch (Exception $e)
				{
					Toolbox::manageError($e, 'Update order statu in neteven');
					$order_status = null;
				}

				if ($this->getValue('send_request_to_mail'))
					$this->sendDebugMail($this->getValue('mail_list_alert'), self::getL('Debug - Control request').' setOrderNetEven', $this->client->__getLastRequest(), true);

				if (!isset($order_status->StatusResponse) || (isset($order_status->StatusResponse) && $order_status->StatusResponse != 'Updated') || is_null($order_status))
				{
					$complement = !is_array($order_status->StatusResponse) ? $order_status->StatusResponse : '';
					$complement .= is_array($order_status->StatusResponse) ? '<pre>'.print_r($order_status->StatusResponse, true).'</pre>' : '';
					
					if ($this->getValue('send_request_to_mail'))
						$this->sendDebugMail($this->getValue('mail_list_alert'), self::getL('Fail for update order state'), self::getL('Order Id').' ('.(int)$params['id_order'].'). '.self::getL('NetEven response').' : '.$complement);
				}
				
				if (!empty($order_status) && !is_null($order_status))
					Toolbox::addLogLine(self::getL('Update order state').' '.$status.' '.self::getL('NetEven Order Id').' '.$res['id_order_neteven'].' '.self::getL('Order Id').' '.$res['id_order'].' - '.$order_status->StatusResponse.'');

				if (!Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'orders_gateway_order_state` (`id_order`, `id_order_state`, `date_add`, `date_upd`) VALUES ('.(int)$res['id_order'].', '.(int)$params['newOrderStatus']->id.', "'.pSQL($date_now).'", "'.pSQL($date_now).'")'))
					Toolbox::addLogLine(self::getL('Failed for save export NetEven order state Id').' '.(int)$res['id_order']);
				else
					Toolbox::addLogLine(self::getL('Save export of NetEven order state Id').' '.(int)$res['id_order']);

			}
		}
		
		Toolbox::writeLog();
	}
}