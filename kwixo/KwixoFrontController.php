<?php

/*
 * 2007-2013 PrestaShop
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
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

//Load the correct class version for PS 1.4 or PS 1.5
if (_PS_VERSION_ < '1.5')
	include_once 'controllers/front/MyFrontController14.php';
else
	include_once 'controllers/front/MyFrontController15.php';

include_once 'lib/includes/includes.inc.php';

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

include_once 'kwixo.php';

/**
 * Build xml order, generate form and send it to kwixo
 * 
 */
class KwixoFrontController extends KwixoPaymentModuleFrontController
{

	public static function generateForm()
	{
		global $cart, $cookie;


		$customer = new Customer((int) $cart->id_customer);

		//For multishop
		if (_PS_VERSION_ < '1.5')
		{
			$kwixo = new KwixoPayment();
			$customer_gender = $customer->id_gender;
			$male_gender = 1;
		}
		else
		{
			$kwixo = new KwixoPayment($cart->id_shop);
			$gender = new Gender($customer->id_gender);
			$customer_gender = $gender->type;
			$male_gender = 0;
		}


		$mobile_detect = new MobileDetect();
		$mobile = $mobile_detect->isMobile();

		$control = new FianetKwixoControl();
		$products = $cart->getProducts();

		$invoice_address = new Address((int) $cart->id_address_invoice);
		$delivery_address = new Address((int) $cart->id_address_delivery);

		$carrier = new Carrier((int) $cart->id_carrier);
		$currency = new Currency((int) $cart->id_currency);
		$invoice_country = new Country((int) $invoice_address->id_country);
		$delivery_country = new Country((int) $delivery_address->id_country);

		$invoice_company = ($invoice_address->company == '' ? null : $invoice_address->company);
		$delivery_company = ($delivery_address->company == '' ? null : $delivery_address->company);

		//Address and customer invoice
		$control->createInvoiceCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'), $invoice_address->lastname, $invoice_address->firstname, $customer->email, $invoice_company, $invoice_address->phone_mobile, $invoice_address->phone);
		$control->createInvoiceAddress($invoice_address->address1, $invoice_address->postcode, $invoice_address->city, $invoice_country->name[(int) $cookie->id_lang], $invoice_address->address2);

		//filter on carrier which have not address and customer delivery
		$types = array("1", "2", "3", "5");

		//gets the carrier kwixo type
		if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive())
		{
			$carrier_type = Configuration::get('KWIXO_CARRIER_TYPE_'.(string) ($carrier->id), null, null, $cart->id_shop);
			$carrier_speed = Configuration::get('KWIXO_CARRIER_SPEED_'.(string) ($carrier->id), null, null, $cart->id_shop);
		}
		else
		{
			$carrier_type = Configuration::get('KWIXO_CARRIER_TYPE_'.(string) ($carrier->id));
			$carrier_speed = Configuration::get('KWIXO_CARRIER_SPEED_'.(string) ($carrier->id));
		}

		//if carrier type is empty, we take defaut carrier type
		if ($carrier_type == '0' || $carrier_type == '' || $carrier_type == false)
		{
			$carrier_type = Configuration::get('KWIXO_DEFAULT_CARRIER_TYPE');
			$carrier_speed = Configuration::get('KWIXO_DEFAULT_CARRIER_SPEED');
		}

		//if carrier type = 4, xml order have delivery address and customer
		if ($carrier_type == 4)
		{
			$control->createDeliveryCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'), $delivery_address->lastname, $delivery_address->firstname, $customer->email, $delivery_company, $delivery_address->phone_mobile, $delivery_address->phone);
			$control->createDeliveryAddress($delivery_address->address1, $delivery_address->postcode, $delivery_address->city, $delivery_country->name[(int) $cookie->id_lang], $delivery_address->address2);

			//xml <infocommande>
			$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string) $cart->getOrderTotal(true), $currency->iso_code, $_SERVER['REMOTE_ADDR'], date('Y-m-d H:i:s'));
			$kwixo_carrier = $order_details->createCarrier($carrier->name, $carrier_type, $carrier_speed);
		}
		elseif (in_array($carrier_type, $types))
		{
			//xml <infocommande>
			$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string) $cart->getOrderTotal(true), $currency->iso_code, $_SERVER['REMOTE_ADDR'], date('Y-m-d H:i:s'));
			$kwixo_carrier = $order_details->createCarrier($carrier->name, $carrier_type, $carrier_speed);

			if ($carrier_type == 1)
			{
				$payment = new Kwixo();
				if ($payment->checkShopAddress() == true)
				{
					//xml <pointrelais>
					$drop_off_point = $kwixo_carrier->createDropOffPoint(Configuration::get('PS_SHOP_NAME'), Configuration::get('PS_SHOP_NAME'));
					$drop_off_point->createAddress(Configuration::get('PS_SHOP_ADDR1'), Configuration::get('PS_SHOP_CODE'), Configuration::get('PS_SHOP_CITY'), Configuration::get('PS_SHOP_COUNTRY'), Configuration::get('PS_SHOP_ADDR2'));
				}
				else
				{
					//xml <pointrelais>
					$drop_off_point = $kwixo_carrier->createDropOffPoint($carrier->name, $carrier->name);
					$drop_off_point->createAddress($delivery_address->address1, $delivery_address->postcode, $delivery_address->city, $invoice_country->name[(int) $cookie->id_lang], $delivery_address->address2);
				}
			}
			else
			{
				//xml <pointrelais>
				$drop_off_point = $kwixo_carrier->createDropOffPoint($carrier->name, $carrier->name);
				$drop_off_point->createAddress($delivery_address->address1, $delivery_address->postcode, $delivery_address->city, $invoice_country->name[(int) $cookie->id_lang], $delivery_address->address2);
			}
		}

		//xml <list>
		$product_list = $order_details->createProductList();

		foreach ($products as $product)
		{
			$kwixo_categorie_id = (Configuration::get('KWIXO_PRODUCT_TYPE_'.(int) $product['id_category_default']) == 0 ? Configuration::get('KWIXO_DEFAULT_PRODUCT_TYPE') : Configuration::get('KWIXO_PRODUCT_TYPE_'.(int) $product['id_category_default']));
			$product_reference = ((isset($product['reference']) AND !empty($product['reference'])) ? $product['reference'] : ((isset($product['ean13']) AND !empty($product['ean13'])) ? $product['ean13'] : $product['name']));
			$product_list->createProduct($product['name'], $product_reference, $kwixo_categorie_id, $product['price'], $product['cart_quantity']);
		}

		//xml <wallet>
		$date_order = date('Y-m-d H:i:s');
		$wallet = $control->createWallet($date_order, $kwixo->generateDatelivr($date_order, 3));
		$wallet->addCrypt($kwixo->generateCrypt($control), $kwixo->getCryptversion());

		//kwixo payment options   
		//standard kwixo 
		if (Tools::getValue('payment') == '1')
			$control->createPaymentOptions('comptant', 0);

		//comptant kwixo 
		if (Tools::getValue('payment') == '2')
			$control->createPaymentOptions('comptant', 1);

		//credit kwixo 
		if (Tools::getValue('payment') == '3')
			$control->createPaymentOptions('credit');

		//facturable kwixo 
		if (Tools::getValue('payment') == '4')
			$control->createPaymentOptions('comptant', 1, 0);

		$xml_params = new KwixoXMLParams();
		$module = new Kwixo();

		$xml_params->addParam('custom', $cart->id);
		$xml_params->addParam('amount', $cart->getOrderTotal(true));
		$xml_params->addParam('secure_key', $customer->secure_key);
		$xml_params->addParam('id_module', $module->name);

		//urlcall and urlsys link on PS 1.4 and PS 1.5
		if (_PS_VERSION_ < '1.5')
		{
			$token = Tools::getAdminToken($kwixo->getSiteid().$kwixo->getAuthkey());
			$link_urlcall = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/kwixo/payment_return.php?token='.$token;
			$link_urlsys = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/kwixo/push.php?token='.$token;

			//returns kwixo form with auto submit
			return $kwixo->getTransactionForm($control, $xml_params, $link_urlsys, $link_urlcall, $mobile, KwixoForm::SUBMIT_AUTO, null);
		}
		else
		{
			$link_urlcall = Context::getContext()->link->getModuleLink('kwixo', 'urlcall');
			$link_urlsys = Context::getContext()->link->getModuleLink('kwixo', 'urlsys');

			//returns kwixo form with standard submit
			return $kwixo->getTransactionForm($control, $xml_params, $link_urlsys, $link_urlcall, $mobile, KwixoForm::SUBMIT_IMAGE, __PS_BASE_URI__.'modules/kwixo/img/logo_kwixo.png');
		}
	}

}
