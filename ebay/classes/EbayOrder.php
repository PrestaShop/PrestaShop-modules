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

class EbayOrder
{
	private $id_order_ref;
	private $amount;
	private $status;
	private $date;
	private $name;
	private $firstname;
	private $familyname;
	private $address1;
	private $address2;
	private $city;
	private $state;
	private $country_iso_code;
	private $country_name;
	private $phone;
	private $postalcode;
	public $shippingService;
	private $shippingServiceCost;
	private $email;
	private $product_list;
	private $payment_method;
	private $id_order_seller;
	private $date_add;

	private $error_messages = array();

	/* PS variables */
	private $id_customer;
	private $id_address;
	private $id_order;
	private $cart;

	public function __construct(SimpleXMLElement $order_xml = null)
	{
		if (!$order_xml)
			return;

		/** Backward compatibility */
		require(dirname(__FILE__).'/../backward_compatibility/backward.php');

		list($this->firstname, $this->familyname) = $this->_formatShippingAddressName($order_xml->ShippingAddress->Name);
		$this->id_order_ref = (string)$order_xml->OrderID;
		$this->amount = (string)$order_xml->AmountPaid;
		$this->status = (string)$order_xml->CheckoutStatus->Status;
		$this->name = (string)$order_xml->ShippingAddress->Name;
		$this->address1 = (string)$order_xml->ShippingAddress->Street1;
		$this->address2 = (string)$order_xml->ShippingAddress->Street2;
		$this->city = (string)$order_xml->ShippingAddress->CityName;
		$this->state = (string)$order_xml->ShippingAddress->StateOrProvince;
		$this->country_iso_code = (string)$order_xml->ShippingAddress->Country;
		$this->country_name = (string)$order_xml->ShippingAddress->CountryName;
		$this->postalcode = (string)$order_xml->ShippingAddress->PostalCode;
		$this->shippingService = (string)$order_xml->ShippingServiceSelected->ShippingService;
		$this->shippingServiceCost = (string)$order_xml->ShippingServiceSelected->ShippingServiceCost;
		$this->payment_method = (string)$order_xml->CheckoutStatus->PaymentMethod;
		$this->id_order_seller = (string)$order_xml->ShippingDetails->SellingManagerSalesRecordNumber;

		if (count($order_xml->TransactionArray->Transaction))
			$this->email = (string)$order_xml->TransactionArray->Transaction[0]->Buyer->Email;

		$phone = (string)$order_xml->ShippingAddress->Phone;

		if (!$phone || !Validate::isPhoneNumber($phone))
				$this->phone = '0100000000';
		else
			$this->phone = $phone;

		$date = substr((string)$order_xml->CreatedTime, 0, 10).' '.substr((string)$order_xml->CreatedTime, 11, 8);
		$this->date = $date;
		$this->date_add = $date;

		if ($order_xml->TransactionArray->Transaction)
			$this->product_list = $this->_getProductsFromTransactions($order_xml->TransactionArray->Transaction);
	}

	public function isCompleted()
	{
		return $this->status == 'Complete'
			&& $this->amount > 0.1
			&& !empty($this->product_list);
	}

	public function exists()
	{
		return (boolean)Db::getInstance()->getValue('SELECT `id_ebay_order`
			FROM `'._DB_PREFIX_.'ebay_order`
			WHERE `id_order_ref` = \''.pSQL($this->id_order_ref).'\'');
	}

	public function hasValidContact()
	{
		return Validate::isEmail($this->email)
			&& $this->firstname
			&& $this->familyname;
	}

	public function getOrAddCustomer()
	{
		$id_customer = (int)Db::getInstance()->getValue('SELECT `id_customer`
			FROM `'._DB_PREFIX_.'customer`
			WHERE `active` = 1
			AND `email` = \''.pSQL($this->email).'\'
			AND `deleted` = 0'.(substr(_PS_VERSION_, 0, 3) == '1.3' ? '' : ' AND `is_guest` = 0'));

		// Add customer if he doesn't exist
		//if ($id_customer < 1) RAPH
		if (!$id_customer)
		{
			$customer = new Customer();
			$customer->id_gender = 0;
			$customer->id_default_group = 1;
			$customer->secure_key = md5(uniqid(rand(), true));
			$customer->email = $this->email;
			$customer->passwd = md5(pSQL(_COOKIE_KEY_.rand()));
			$customer->last_passwd_gen = pSQL(date('Y-m-d H:i:s'));
			$customer->newsletter = 0;
			$customer->lastname = EbayOrder::_formatFamilyName($this->familyname);
			$customer->firstname = pSQL($this->firstname);
			$customer->active = 1;
			$customer->add();
			$id_customer = $customer->id;
		}

		$this->id_customer = $id_customer;

		return $id_customer;
	}

	public function updateOrAddAddress()
	{
		// Search if address exists
		$id_address = (int)Db::getInstance()->getValue('SELECT `id_address`
			FROM `'._DB_PREFIX_.'address`
			WHERE `id_customer` = '.(int)$this->id_customer.'
			AND `alias` = \'eBay\'');

		if ($id_address)
				$address = new Address((int)$id_address);
		else
		{
			$address = new Address();
			$address->id_customer = (int)$this->id_customer;
		}

		$address->id_country = (int)Country::getByIso($this->country_iso_code);
		$address->alias = 'eBay';
		$address->lastname = EbayOrder::_formatFamilyName($this->familyname);
		$address->firstname = pSQL($this->firstname);
		$address->address1 = pSQL($this->address1);
		$address->address2 = pSQL($this->address2);
		$address->postcode = pSQL(str_replace('.', '', $this->postalcode));
		$address->city = pSQL($this->city);
		$address->phone = pSQL($this->phone);
		$address->active = 1;

		if ($id_address > 0 && Validate::isLoadedObject($address))
			$address->update();
		else
		{
			$address->add();
			$id_address = $address->id;
		}

		$this->id_address = $id_address;

		return $id_address;
	}

	/**
	 * Formats the family name to match eBay constraints:
	 * - length < 32 chars
	 * - no brackets ()
	 *
	 */
	private function _formatFamilyName($family_name)
	{
		return str_replace(array('(', ')'), '', substr(pSQL($family_name), 0, 32));
	}

	public function hasAllProductsWithAttributes()
	{
		foreach ($this->product_list as $product)
		{
			if ((int)$product['id_product'] < 1
				|| !Db::getInstance()->getValue('SELECT `id_product`
					FROM `'._DB_PREFIX_.'product`
					WHERE `id_product` = '.(int)$product['id_product']))
				return false;

			if (isset($product['id_product_attribute'])
				&& $product['id_product_attribute'] > 0
				&& !Db::getInstance()->getValue('SELECT `id_product_attribute`
					FROM `'._DB_PREFIX_.'product_attribute`
					WHERE `id_product` = '.(int)$product['id_product'].'
					AND `id_product_attribute` = '.(int)$product['id_product_attribute']))
				return false;
		}
		return true;
	}

	public function addCart($ebay_country)
	{
		$id_carrier = (int)EbayShipping::getPsCarrierByEbayCarrier($this->shippingService);
		$cart = new Cart();

		$this->context->customer = new Customer($this->id_customer);
		$cart->id_customer = $this->id_customer;
		$cart->id_address_invoice = $this->id_address;
		$cart->id_address_delivery = $this->id_address;
		$cart->id_carrier = $id_carrier;
		$cart->delivery_option = @serialize(array($this->id_address => $id_carrier.','));
		$cart->id_lang = $ebay_country->getIdLang();
		$cart->id_currency = Currency::getIdByIsoCode($ebay_country->getCurrency());
		$cart->recyclable = 0;
		$cart->gift = 0;
		$cart->add();

		$this->cart = $cart;

		return $cart;
	}

	public function deleteCart()
	{
		return $this->cart->delete();
	}

	/* returns true is still products in the cart, false otherwise */
	public function updateCartQuantities()
	{
		$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		$cart_nb_products = 0;

		foreach ($this->product_list as $product)
		{
			$prod = new Product($product['id_product'], false, $id_lang);
			$minimal_quantity = empty($product['id_product_attribute']) ? $prod->minimal_quantity : (int)Attribute::getAttributeMinimalQty($product['id_product_attribute']);

			if ($product['quantity'] >= $minimal_quantity)
			{
				$id_product_attribute = empty($product['id_product_attribute']) ? null : $product['id_product_attribute'];

				if (version_compare(_PS_VERSION_, '1.5', '>'))
				{
					$update = $this->cart->updateQty(
						(int)$product['quantity'],
						(int)$product['id_product'],
						$id_product_attribute,
						false,
						'up',
						0,
						new Shop(Configuration::get('PS_SHOP_DEFAULT')));

					if ($update === true)
						$cart_nb_products++;
				}
				elseif ($this->cart->updateQty((int)$product['quantity'], (int)$product['id_product'], $id_product_attribute))
						$cart_nb_products++;
			}
			else // minimal quantity for purchase not met
				$this->_sendMinimalQtyAlertEmail($prod->name, $minimal_quantity, $product['quantity']);
		}

		$this->cart->update();

		if (version_compare(_PS_VERSION_, '1.5', '>'))
		{
			$this->cart->getProducts(true);
			$this->cart->getPackageList(true);
			$this->cart->getDeliveryOptionList(null, true);
		}

		return (boolean)$cart_nb_products;
	}

	public function validate()
	{
		$customer = new Customer($this->id_customer);
		$paiement = new EbayPayment();

		$paiement->validateOrder(
			(int)$this->cart->id,
			Configuration::get('PS_OS_PAYMENT'),
			(float)$this->cart->getOrderTotal(true, 3),
			'eBay '.$this->payment_method.' '.$this->id_order_seller,
			null,
			array(),
			(int)$this->cart->id_currency,
			false,
			$customer->secure_key,
			version_compare(_PS_VERSION_, '1.5', '>') ? new Shop((int)Configuration::get('PS_SHOP_DEFAULT')) : null
		);

		$this->id_order = $paiement->currentOrder;

		// Fix on date
		Db::getInstance()->autoExecute(_DB_PREFIX_.'orders', array('date_add' => pSQL($this->date_add)), 'UPDATE', '`id_order` = '.(int)$this->id_order);

		return $paiement->currentOrder;
	}

	public function updatePrice()
	{

		$total_price_tax_excl = 0;
		$total_shipping_tax_incl = 0;
		$total_shipping_tax_excl = 0;
		$id_carrier = (int)EbayShipping::getPsCarrierByEbayCarrier($this->shippingService);
		
		if(version_compare(_PS_VERSION_, '1.4.0.5', '<'))
			$carrier_tax_rate = (float)$this->_getTaxByCarrier((int)$id_carrier);
		else
			$carrier_tax_rate = (float)Tax::getCarrierTaxRate((int)$id_carrier);

		foreach ($this->product_list as $product)
		{
			if(version_compare(_PS_VERSION_, '1.4.0.5', '<'))
				$tax_rate = (float)$this->_getTaxByProduct((int)$product['id_product']);
			else 
				$tax_rate = (float)Tax::getProductTaxRate((int)$product['id_product']);

			$coef_rate = (1 + ($tax_rate / 100));

			$detail_data = array(
				'product_price'        => (float)($product['price'] / $coef_rate),
				'reduction_percent'    => 0,
				'reduction_amount'     => 0
			);

			if(version_compare(_PS_VERSION_, '1.5', '>'))
			{
				$detail_data = array_merge($detail_data, array(
					'unit_price_tax_incl'  => (float)$product['price'],
					'unit_price_tax_excl'  => (float)($product['price'] / $coef_rate ),
					'total_price_tax_incl' => (float)($product['price'] * $product['quantity']),
					'total_price_tax_excl' => (float)(($product['price'] / $coef_rate) * $product['quantity']),
				));
			}

			Db::getInstance()->autoExecute(
				_DB_PREFIX_.'order_detail', 
				$detail_data, 
				'UPDATE', 
				'`id_order` = '.(int)$this->id_order.' AND `product_id` = '.(int)$product['id_product'].' AND `product_attribute_id` = '.(int)$product['id_product_attribute']
			);

			if(version_compare(_PS_VERSION_, '1.5', '>')) 
			{
				$detail_tax_data = array(
					'unit_amount' => (float)($product['price']-($product['price'] / $coef_rate )),
					'total_amount' => ((float)($product['price']-($product['price'] / $coef_rate )) * $product['quantity'])
				);

				DB::getInstance()->autoExecute(_DB_PREFIX_ .'order_detail_tax', $detail_tax_data, 'UPDATE', '`id_order_detail` = (SELECT `id_order_detail` FROM `'. _DB_PREFIX_ .'order_detail` WHERE `id_order` = '.(int)$this->id_order.' AND `product_id` = '.(int)$product['id_product'].' AND `product_attribute_id` = '.(int)$product['id_product_attribute'] .') ');
			} 
			
			$total_price_tax_excl += (float)(($product['price'] / $coef_rate) * $product['quantity']);
			// ebay get one shipping cost by product
			$total_shipping_tax_incl += $this->shippingServiceCost;
			$total_shipping_tax_excl += $this->shippingServiceCost / (1 + ($carrier_tax_rate / 100));

		}

		$data = array(
			'total_paid'              => (float)$this->amount,
			'total_paid_real'         => (float)$this->amount,
			'total_products'          => (float)$total_price_tax_excl,
			'total_products_wt'       => (float)($this->amount - $this->shippingServiceCost),
			'total_shipping'          => (float)$total_shipping_tax_incl,
			
		);


		


		if(version_compare(_PS_VERSION_, '1.5', '>')) 
		{
			$order = new Order((int)$this->id_order);
			$data_old = $data;
			$data = array_merge(
				$data, 
				array(
					'total_paid_tax_incl' => (float)$this->amount,
					'total_paid_tax_excl' => (float)($total_price_tax_excl + $order->total_shipping_tax_excl),
					'total_shipping_tax_incl' => (float)$total_shipping_tax_incl,
					'total_shipping_tax_excl' => (float)$total_shipping_tax_excl
				)
			);

			if((float)$this->shippingServiceCost == 0) 
			{
				$data = array_merge(
					$data,
					array(
						'total_shipping_tax_excl' => 0,
						'total_shipping_tax_incl' => 0
					)
				);
			}
			// Update Incoice
			$invoice_data = $data;
			unset($invoice_data['total_paid'], $invoice_data['total_paid_real'], $invoice_data['total_shipping']);
			Db::getInstance()->autoExecute(_DB_PREFIX_.'order_invoice', $invoice_data, 'UPDATE', '`id_order` = '.(int)$this->id_order);


			// Update payment
			$payment_data = array(
				'amount' => (float)$this->amount
			);
			Db::getInstance()->autoExecute(_DB_PREFIX_.'order_payment', $payment_data, 'UPDATE', '`order_reference` = "'.pSQL($order->reference).'" ');
			
		}

		return Db::getInstance()->autoExecute(_DB_PREFIX_.'orders', $data, 'UPDATE', '`id_order` = '.(int)$this->id_order);
	}

	public function _getTaxByProduct($id_product) 
	{
		$sql = "SELECT t.`rate` 
				FROM `"._DB_PREFIX_ ."product` AS p
				INNER JOIN `"._DB_PREFIX_ ."tax_rule` AS tr
					ON tr.`id_tax_rules_group` = p.`id_tax_rules_group` AND tr.`id_country` = '".(int)Country::getByIso($this->country_iso_code)."'
				INNER JOIN `"._DB_PREFIX_."tax` AS t
					ON t.`id_tax` = tr.`id_tax` 
				WHERE p.`id_product` = '".(int)$id_product."' ";

		return DB::getInstance()->getValue($sql);
	}

	public function _getTaxByCarrier($id_carrier) 
	{
		$sql = "SELECT t.`rate` 
				FROM `"._DB_PREFIX_ ."carrier` AS c
				INNER JOIN `"._DB_PREFIX_ ."tax_rule` AS tr
					ON tr.`id_tax_rules_group` = c.`id_tax_rules_group` AND tr.`id_country` = '".(int)Country::getByIso($this->country_iso_code)."'
				INNER JOIN `"._DB_PREFIX_."tax` AS t
					ON t.`id_tax` = tr.`id_tax` 
				WHERE c.`id_carrier` = '".(int)$id_carrier."' ";

		return DB::getInstance()->getValue($sql);
	}

	public function add()
	{
		EbayOrder::insert(array(
			'id_order_ref' => pSQL($this->id_order_ref),
			'id_order' => (int)$this->id_order
		));
	}

	public function addErrorMessage($message)
	{
		$this->error_messages[] = $message;
	}

	public function getErrorMessages()
	{
		return $this->error_messages;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getProducts()
	{
		return $this->product_list;
	}

	public function getIdOrderRef()
	{
		return $this->id_order_ref;
	}

	public function getIdOrderSeller()
	{
		return $this->id_order_seller;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getDate()
	{
		return $this->date;
	}

	private function _parseSku($sku, $id_product, $id_product_attribute)
	{
		$data = explode('-', (string)$sku);

		if (isset($data[1]))
			$id_product = $data[1];
		if (isset($data[2]))
			$id_product_attribute = $data[2];

		return array($id_product, $id_product_attribute);
	}

	private function _formatShippingAddressName($name)
	{
		$name = str_replace(array('_', ',', '  '), array('', '', ' '), (string)$name);
		$name = preg_replace('/\-?\d+/', '', $name);
		$name = explode(' ', $name, 2);
		$firstname = trim(substr(trim($name[0]), 0, 32));
		$familyname = trim(isset($name[1]) ? substr(trim($name[1]), 0, 32) : substr(trim($name[0]), 0, 32));

		if (!$familyname)
				$familyname = $firstname;
		if (!$firstname)
				$firstname = $familyname;

		return array($firstname, $familyname);
	}

	private function _getReference($transaction)
	{
		$skuItem = (string)$transaction->Item->SKU;
		$skuVariation = (string)$transaction->Variation->SKU;
		$customLabel = (string)$transaction->SellingManagerProductDetails->CustomLabel;

		if ($customLabel)
			$reference = $customLabel;
		else
		{
			if ($skuVariation)
				$reference = $skuVariation;
			else
				$reference = $skuItem;
		}

		return trim($reference);
	}

	private function _getProductsFromTransactions($transactions)
	{
		$products = array();

		foreach ($transactions as $transaction)
		{
			$id_product = 0;
			$id_product_attribute = 0;
			$quantity = (string)$transaction->QuantityPurchased;

			if (isset($transaction->Item->SKU))
				list($id_product, $id_product_attribute) = $this->_parseSku($transaction->Item->SKU, $id_product, $id_product_attribute);

			if (isset($transaction->Variation->SKU))
				list($id_product, $id_product_attribute) = $this->_parseSku($transaction->Variation->SKU, $id_product, $id_product_attribute);

			$id_product = (int)Db::getInstance()->getValue('SELECT `id_product`
				FROM `'._DB_PREFIX_.'product`
				WHERE `id_product` = '.(int)$id_product);

			$id_product_attribute = (int)Db::getInstance()->getValue('SELECT `id_product_attribute`
				FROM `'._DB_PREFIX_.'product_attribute`
				WHERE `id_product` = '.(int)$id_product.'
				AND `id_product_attribute` = '.(int)$id_product_attribute);

			if ($id_product)
				$products[] = array(
					'id_product' => $id_product,
					'id_product_attribute' => $id_product_attribute,
					'quantity' => $quantity,
					'price' => (string)$transaction->TransactionPrice);
			else
			{
				$reference = $this->_getReference($transaction);

				if (!empty($reference))
				{
					$id_product = Db::getInstance()->getValue('SELECT `id_product`
						FROM `'._DB_PREFIX_.'product`
						WHERE `reference` = \''.pSQL($reference).'\'');

					if ((int)$id_product)
						$products[] = array(
							'id_product' => $id_product,
							'id_product_attribute' => 0,
							'quantity' => $quantity,
							'price' => (string)$transaction->TransactionPrice);
					else
					{
						$row = Db::getInstance()->getValue('SELECT `id_product`, `id_product_attribute`
							FROM `'._DB_PREFIX_.'product_attribute`
							WHERE `reference` = \''.pSQL($reference).'\'');

						if ((int)$row['id_product'])
							$products[] = array(
								'id_product' => (int)$row['id_product'],
								'id_product_attribute' => (int)$row['id_product_attribute'],
								'quantity' => $quantity,
								'price' => (string)$transaction->TransactionPrice);
					}
				}
			}
		}

		return $products;
	}

	/**
	 * Sends an email when the minimal quantity of a product to order is not met
	 *
	 * @param string $product_name
	 * @param int $minimal_quantity Minimal quantity to place an order
	 * @param int $quantity Quantity ordered
	 * @return array
	 **/
	private function _sendMinimalQtyAlertEmail($product_name, $minimal_quantity, $quantity)
	{
		$template_vars = array(
			'{name_product}' => $product_name,
			'{min_qty}' => (string)$minimal_quantity,
			'{cart_qty}' => (string)$quantity
		);

		Mail::Send(
			(int)Configuration::get('PS_LANG_DEFAULT'),
			'alertEbay',
			Mail::l('Product quantity', (int)Configuration::get('PS_LANG_DEFAULT')),
			$template_vars,
			strval(Configuration::get('PS_SHOP_EMAIL')),
			null,
			strval(Configuration::get('PS_SHOP_EMAIL')),
			strval(Configuration::get('PS_SHOP_NAME')),
			null,
			null,
			dirname(__FILE__).'/../views/templates/mails/'
		);
	}

	public static function insert($data)
	{
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_order', $data, 'INSERT');
	}

	public static function __set_state($attributes)
	{
		$ebay_order = new EbayOrder();
		
		foreach ($attributes as $name => $attribute)
			$ebay_order->{$name} = $attribute;

		return $ebay_order;
	}

}