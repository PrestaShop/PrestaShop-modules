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
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/*Load the correct class version for PS 1.4 or PS 1.5*/
if (version_compare(_PS_VERSION_, '1.5', '<'))
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
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$cookie = new Cookie('ps');
			$cart = new Cart($cookie->id_cart);
		}
		else
			$cart = Context::getContext()->cart;
		$customer = new Customer((int)$cart->id_customer);
		$module = new Kwixo();
		//For multishop
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$kwixo = new KwixoPayment();
			$customer_gender = $customer->id_gender;
			$male_gender = 1;
			$carrier_id = $cart->id_carrier;
		}
		else
		{
			$kwixo = new KwixoPayment($cart->id_shop);
			$gender = new Gender($customer->id_gender);
			$customer_gender = $gender->type;
			$male_gender = 0;

			//retrieve carrier_id in delivery string option, fix for PS 1.5 with onepagecheckout
			foreach ($cart->getDeliveryOption() as $delivery_string)
				$carrier_id = Tools::substr($delivery_string, 0, -1);
		}
		$mobile_detect = new MobileDetectKwixo();
		$mobile = $mobile_detect->isMobile();

		$control = new FianetKwixoControl();
		$products = $cart->getProducts();

		$invoice_address = new Address((int)$cart->id_address_invoice);
		$delivery_address = new Address((int)$cart->id_address_delivery);

		$carrier = new Carrier((int)$carrier_id);
		$currency = new Currency((int)$cart->id_currency);
		$invoice_country = new Country((int)$invoice_address->id_country);
		$delivery_country = new Country((int)$delivery_address->id_country);

		$invoice_company = ($invoice_address->company == '' ? null : $invoice_address->company);
		$delivery_company = ($delivery_address->company == '' ? null : $delivery_address->company);

		//Address and customer invoice
		$control->createInvoiceCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'), $invoice_address->lastname,
			$invoice_address->firstname, $customer->email, $invoice_company,
			$invoice_address->phone_mobile, $invoice_address->phone);
		$control->createInvoiceAddress($invoice_address->address1, $invoice_address->postcode, $invoice_address->city,
			$invoice_country->iso_code, $invoice_address->address2);

		//gets the carrier kwixo type
		if (version_compare(_PS_VERSION_, '1.5', '>=') && Shop::isFeatureActive())
		{
			$carrier_type = Configuration::get('KWX_CARRIER_TYPE_'.(string)$carrier->id, null, null, $cart->id_shop);
			$carrier_speed = Configuration::get('KWX_CARRIER_SPEED_'.(string)$carrier->id, null, null, $cart->id_shop);
		}
		else
		{
			$carrier_type = Configuration::get('KWX_CARRIER_TYPE_'.(string)$carrier->id);
			$carrier_speed = Configuration::get('KWX_CARRIER_SPEED_'.(string)$carrier->id);
		}

		//if carrier type is empty, we take defaut carrier type
		if ($carrier_type == '0' || $carrier_type == '' || $carrier_type == false)
		{
			$carrier_type = Configuration::get('KWIXO_DEFAULT_CARRIER_TYPE');
			$carrier_speed = Configuration::get('KWIXO_DEFAULT_CARRIER_SPEED');
			$carrier_name = 'Transporteur';
		}
		else
			$carrier_name = $carrier->name;
		switch ($carrier_type)
		{
			//if the order is to be delivered at home: element <utilisateur type="livraison"...> has to be added
			case '4':

				$control->createDeliveryCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'),
					$delivery_address->lastname, $delivery_address->firstname,
					null, $delivery_company, $delivery_address->phone_mobile, $delivery_address->phone);
				$control->createDeliveryAddress($delivery_address->address1, $delivery_address->postcode,
					$delivery_address->city, $delivery_country->iso_code, $delivery_address->address2);

				//xml <infocommande>
				$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
					$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
				$kwixo_carrier = $order_details->createCarrier($carrier_name, $carrier_type, $carrier_speed);

				break;

			case '5':
				$control->createDeliveryCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'),
					$delivery_address->lastname, $delivery_address->firstname,
					null, $delivery_company, $delivery_address->phone_mobile, $delivery_address->phone);
				$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
					$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
				$kwixo_carrier = $order_details->createCarrier($carrier_name, $carrier_type, $carrier_speed);

				break;

			case '6':

				$socolissimoinfo = $module->getSoColissimoInfo($cart->id);

				$socolissimo_installed_module = Module::getInstanceByName('socolissimo');

				if ($socolissimoinfo != false)
				{
					foreach ($socolissimoinfo as $info)
					{
						//get socolissimo informations
						$delivery_mode = $info['delivery_mode'];
						$firstname = $info['prfirstname'];
						$name = $info['prname'];
						$mobile_phone = $info['cephonenumber'];
						$company_name = $info['cecompanyname'];
						$address1 = $info['pradress1'];
						$address2 = $info['pradress2'];
						$address3 = $info['pradress3'];
						$address4 = $info['pradress4'];
						$zipcode = $info['przipcode'];
						$city = $info['prtown'];

						//data is retrieved differently and depending on the version of the module
						if ($socolissimo_installed_module->version < '2.8')
						{
							$address2 = $address1;
							$address1 = $name;
							$country = 'FR';
							KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Module So Colissimo '.$socolissimo_installed_module->version.' détecté');
						}
						else
							$country = $info['cecountry'];
					}
					$control->createDeliveryCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'),
							$name, $firstname, null, $company_name, $mobile_phone, null);

					//if delivery mode is DOM or RDV, <adresse type="livraison" ...> and <utilisateur type="livraison" ...> added
					if ($delivery_mode == 'DOM' || $delivery_mode == 'RDV')
					{
						$control->createDeliveryAddress($address3, $zipcode, $city, $country, $address4);
						$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
					$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
						$kwixo_carrier = $order_details->createCarrier($carrier_name, '4', $carrier_speed);
					}
					else
					{
						//<pointrelais> added if delivery mode is not BPR, A2P or CIT
						$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
					$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
						$kwixo_carrier = $order_details->createCarrier($carrier_name, '2', $carrier_speed);
						$drop_off_point_address = $control->createAddress('', $address2, $zipcode,
							$city, $country, null);
						$kwixo_carrier->createDropOffPoint($address1, null, $drop_off_point_address);
					}
				}
				else
					KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Flux incorrect : Module SoColissimo non installé ou non activé');
				break;
			case '7':
				$socolissimoinfo = $module->getSoColissimoLiberteInfo($cart->id);

				if ($socolissimoinfo != false)
				{
					foreach ($socolissimoinfo as $info)
					{
						$delivery_mode = $info['type'];
						$firstname = $info['firstname'];
						$name = $info['lastname'];
						if ($info['telephone'] != null && $info['telephone'] != '' && $info['telephone'] != '0000000000')
							$mobile_phone = $info['telephone'];
						$address1 = $info['adresse1'];
						$address2 = $info['adresse2'];
						$enseigne = $info['libelle'];
						$zipcode = $info['code_postal'];
						$city = $info['commune'];
						$country = 'FR';
					}
					$control->createDeliveryCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'),
							$name, $firstname, null, null, $mobile_phone, null);

					//if delivery mode is DOM or RDV, <adresse type="livraison" ...> and <utilisateur type="livraison" ...> added
					if ($delivery_mode == 'DOM' || $delivery_mode == 'RDV')
					{
						$control->createDeliveryAddress($address1, $zipcode, $city, $country, $address2);
						$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
					$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
						$kwixo_carrier = $order_details->createCarrier($carrier_name, '4', $carrier_speed);
					}
					else
					{
						//<pointrelais> added if delivery mode is not BPR, A2P or CIT
						$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
					$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
						$kwixo_carrier = $order_details->createCarrier($carrier_name, '2', $carrier_speed);
						$drop_off_point_address = $control->createAddress('', $address2, $zipcode,
							$city, $country, null);
						$kwixo_carrier->createDropOffPoint($enseigne, null, $drop_off_point_address);
					}
				}
				else
					KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Flux incorrect : Module SoColissimo Liberté non installé ou non activé');
				break;
			case '8':
				$control->createDeliveryCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'), $delivery_address->lastname,
					$delivery_address->firstname, '', $delivery_company, $delivery_address->phone_mobile, $delivery_address->phone);
				$mondialrelayinfo = $module->getMondialRelayInfo($cart->id);

				if ($mondialrelayinfo != false)
				{
					foreach ($mondialrelayinfo as $info)
					{
						//get mondialrelay information

						$address1 = trim($info['MR_Selected_LgAdr1']);
						$address2 = trim($info['MR_Selected_LgAdr2']);
						$address3 = trim($info['MR_Selected_LgAdr3']);
						$address4 = trim($info['MR_Selected_LgAdr4']);
						$zipcode = trim($info['MR_Selected_CP']);
						$city = trim($info['MR_Selected_Ville']);
						$country = trim($info['MR_Selected_Pays']);
						$delivery_mode = trim($info['dlv_mode']);
					}
					//<pointrelais>
					if ($delivery_mode == '24R' || $delivery_mode == 'DRI')
					{
						$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
							$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
						$kwixo_carrier = $order_details->createCarrier($carrier_name, '2', $carrier_speed);
						$drop_off_point_address = $control->createAddress('', $address3, $zipcode,
							$city, $country, null);
						$kwixo_carrier->createDropOffPoint($address1, null, $drop_off_point_address);
					}
					else
					{
						$control->createDeliveryAddress($delivery_address->address1, $delivery_address->postcode,
							$delivery_address->city, $delivery_country->iso_code, $delivery_address->address2);
						$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
							$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
						$kwixo_carrier = $order_details->createCarrier($carrier_name, '4', $carrier_speed);
					}
				}
				else
					KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Flux incorrect : Module Mondial relay non installé ou non activé');
				break;
			case '9':
				$control->createDeliveryCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'),
					$delivery_address->lastname, $delivery_address->firstname,
					null, $delivery_company, $delivery_address->phone_mobile, $delivery_address->phone);
				$icirelaisinfo = $module->getIciRelaisInfo($cart->id);
				if ($icirelaisinfo != false)
				{
					foreach ($icirelaisinfo as $info)
					{
						//get mondialrelay information
						$address1 = $info['address1'];
						$address2 = $info['address2'];
						$enseigne = $info['company'];
						$zipcode = $info['postcode'];
						$city = $info['city'];
						$country = $info['iso_code'];
					}
					//<pointrelais>
					$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
						$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
					$kwixo_carrier = $order_details->createCarrier($carrier_name, '2', $carrier_speed);
					$drop_off_point_address = $control->createAddress('', $address1, $zipcode,
						$city, $country, null);
					$kwixo_carrier->createDropOffPoint($enseigne, null, $drop_off_point_address);
				}
				else
					KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Flux incorrect : Module Mondial relay non installé ou non activé');
				break;
			default:

				$control->createDeliveryCustomer((($customer_gender == $male_gender) ? 'Monsieur' : 'Madame'),
					$delivery_address->lastname, $delivery_address->firstname,
					null, $delivery_company, $delivery_address->phone_mobile, $delivery_address->phone);
				$order_details = $control->createOrderDetails($cart->id, $kwixo->getSiteid(), (string)$cart->getOrderTotal(true),
					$currency->iso_code, Tools::getRemoteAddr(), date('Y-m-d H:i:s'));
				$kwixo_carrier = $order_details->createCarrier($carrier_name, $carrier_type, $carrier_speed);

				if ($carrier_type == 1)
				{
					if ($module->checkShopAddress() == true)
					{
						//xml <pointrelais>
						$drop_off_point_address = $control->createAddress('', Configuration::get('PS_SHOP_ADDR1'), Configuration::get('PS_SHOP_CODE'),
							Configuration::get('PS_SHOP_CITY'), Configuration::get('PS_SHOP_COUNTRY'), Configuration::get('PS_SHOP_ADDR2'));
						$kwixo_carrier->createDropOffPoint(Configuration::get('PS_SHOP_NAME'), Configuration::get('PS_SHOP_NAME'),
							$drop_off_point_address);
					}
					else
					{
						//xml <pointrelais>
						$drop_off_point_address = $control->createAddress('', $delivery_address->address1, $delivery_address->postcode,
							$delivery_address->city, $invoice_country->iso_code, $delivery_address->address2);
						$kwixo_carrier->createDropOffPoint($carrier_name, $carrier_name, $drop_off_point_address);

					}
				}
				else
				{
					//xml <pointrelais>
					$drop_off_point_address = $control->createAddress('', $delivery_address->address1, $delivery_address->postcode,
							$delivery_address->city, $invoice_country->iso_code, $delivery_address->address2);
					$kwixo_carrier->createDropOffPoint($carrier_name, $carrier_name, $drop_off_point_address);
				}
				break;
		}
		//xml <list>
		$product_list = $order_details->createProductList();

		$product_deliveries = array();

		foreach ($products as $product)
		{
			$kwixo_categorie_id = (Configuration::get('KWX_CATEGORY_'.(int)$product['id_category_default']) == 0 ?
				Configuration::get('KWIXO_DEFAULT_PRODUCT_TYPE') :
				Configuration::get('KWX_CATEGORY_'.(int)$product['id_category_default']));
			$product_reference = ((isset($product['reference'])
				&& !empty($product['reference'])) ? $product['reference'] : ((isset($product['ean13'])
				&& !empty($product['ean13'])) ? $product['ean13'] : $product['name']));
			$product_list->createProduct(
				$product['name'],
				str_replace("'", '', $product_reference),
				$kwixo_categorie_id, $product['price'],
				$product['cart_quantity']);
			$product_deliveries[] = Configuration::get('KWX_CATEGORY_DLV_'.(int)$product['id_category_default']);
		}
		$kwixo_delivery = $module->getKwixoDelivery($product_deliveries, $carrier->id);
		//xml <wallet>
		$date_order = date('Y-m-d H:i:s');
		$wallet = $control->createWallet($date_order, $kwixo->generateDatelivr($date_order, $kwixo_delivery));
		$wallet->addCrypt($kwixo->generateCrypt($control), '2.0');
		$xml_params = new KwixoXMLParams();
		//kwixo payment options
		//standard kwixo
		if (Tools::getValue('payment') == '1')
		{
			$control->createPaymentOptions('comptant', 0);
			$xml_params->addParam('payment_type', 'kwixo_standard');
		}

		//comptant kwixo
		if (Tools::getValue('payment') == '2')
		{
			$control->createPaymentOptions('comptant', 1);
			$xml_params->addParam('payment_type', 'kwixo_comptant');
		}

		//credit kwixo
		if (Tools::getValue('payment') == '3')
		{
			$control->createPaymentOptions('credit');
			$xml_params->addParam('payment_type', 'kwixo_credit');
		}

		//facturable kwixo
		if (Tools::getValue('payment') == '4')
		{
			$control->createPaymentOptions('comptant', 1, 0);
			$xml_params->addParam('payment_type', 'kwixo_comptant');
		}
		$xml_params->addParam('custom', $cart->id);
		$xml_params->addParam('amount', $cart->getOrderTotal(true));
		$xml_params->addParam('secure_key', $customer->secure_key);
		$xml_params->addParam('id_module', $module->name);
		$xml_params->addParam('shop_version', _PS_VERSION_);
		//urlcall and urlsys link on PS 1.4 and PS 1.5
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$token = Tools::getAdminToken($kwixo->getSiteid().$kwixo->getAuthkey());
			$link_urlcall = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'],
				ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/kwixo/payment_return.php?token='.$token;
			$link_urlsys = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'],
				ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/kwixo/push.php?token='.$token;

			//returns kwixo form with auto submit
			return $kwixo->getTransactionForm($control, $xml_params, $link_urlsys, $link_urlcall, $mobile, KwixoForm::SUBMIT_AUTO, null);
		}
		else
		{
			$link_urlcall = Context::getContext()->link->getModuleLink('kwixo', 'urlcall');
			$link_urlsys = Context::getContext()->link->getModuleLink('kwixo', 'urlsys');

			//returns kwixo form with standard submit
			return $kwixo->getTransactionForm($control, $xml_params, $link_urlsys, $link_urlcall,
				$mobile, KwixoForm::SUBMIT_IMAGE, __PS_BASE_URI__.'modules/kwixo/img/logo_kwixo.png');
		}
	}
}
