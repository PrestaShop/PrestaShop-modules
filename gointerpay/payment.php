<?php

/*
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @version  Release: $Revision: 1.7.4 $
 *
 *  International Registered Trademark & Property of PrestaShop SA
 */

$useSSL = true;
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');

class GoInterpayController extends FrontController
{
	public $ssl = true;

	public function process()
	{
		parent::process();

		$address = new Address((int)self::$cart->id_address_delivery);
		$country = new Country((int)$address->id_country);

		$current_offer = Db::getInstance()->getValue('
		SELECT c.rateoffer_id
		FROM '._DB_PREFIX_.'currency c
		WHERE c.iso_code = \''.pSQL($this->context->cookie->interpay_currency_code).'\' AND deleted = 0');

		$id_rate = 0;
		if ($current_offer)
			$id_rate = $current_offer;
				
		$customer = new Customer((int)self::$cart->id_customer);
		$address_shipping = new Address((int)self::$cart->id_address_delivery);
		$state = new State((int)$address->id_state);
		$link = new Link();
		
		$products = '';
		$i = 1;
		$cart_currency = new Currency((int)self::$cart->id_currency);
		foreach (self::$cart->getProducts() as $val)
		{
			$products .= 'itemDescription'.$i.'='.urlencode($val['name']).'&'; // .': '.$val['description_short']
			$products .= 'itemQuantity'.$i.'='.(int)$val['quantity'].'&';
			$products .= 'itemUnitPrice'.$i.'='.(float)number_format($val['price'] / $cart_currency->conversion_rate, 2, '.', '').'&';
			$products .= 'itemImageURL'.$i.'='.urlencode('https://'.$link->getImageLink($val['id_product'], $val['id_image'])).'&';
			$products .= 'itemSkuN'.$i.'='.''.'&';
			$products .= 'itemProductId'.$i.'='.(int)$val['id_product'].'&';
			$products .= 'itemWeight'.$i.'='.(float)$val['weight'].'&';
			$products .= 'itemLength'.$i.'='.(float)$val['depth'].'&';
			$products .= 'itemWidth'.$i.'='.(float)$val['width'].'&';
			$products .= 'itemHeight'.$i.'='.(float)$val['height'].'&';
			$products .= 'itemStatus'.$i.'='.''.'&';
			$products .= 'itemCountryOfOrigin'.$i.'='.''.'&';
			$products .= 'itemURL'.$i.'='.''.'&';
			$i++;
		}

		$discounts = _PS_VERSION_ >= 1.5 ? self::$cart->getCartRules() : self::$cart->getDiscounts();
		foreach ($discounts as $val)
		{
			$products .= 'itemDescription'.$i.'='.urlencode($val['name'].': '.$val['description']).'&';
			$products .= 'itemQuantity'.$i.'='.(int)$val['quantity'].'&';
			$products .= 'itemUnitPrice'.$i.'='.(float)$val['value_real'].'&';
			$products .= 'itemImageURL'.$i.'='.''.'&';
			$products .= 'itemSkuN'.$i.'='.''.'&';
			$products .= 'itemProductId'.$i.'='.'discount_'.(int)$val['id_cart_rule'].'&';
			$products .= 'itemWeight'.$i.'='.''.'&';
			$products .= 'itemLength'.$i.'='.''.'&';
			$products .= 'itemWidth'.$i.'='.''.'&';
			$products .= 'itemHeight'.$i.'='.''.'&';
			$products .= 'itemStatus'.$i.'='.''.'&';
			$products .= 'itemCountryOfOrigin'.$i.'='.''.'&';
			$products .= 'itemURL'.$i.'='.''.'&';
			$i++;
		}

		self::$cart->id_carrier = (int)Configuration::get('GOINTERPAY_SHIPPING_CARRIER');
		self::$cart->update();

		$carrierPrice = (float)self::$cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

		if ($carrierPrice === false)
		{
			self::$smarty->assign('error', true);
			return false;
		}

		$account = 'store'.'='.(int)Configuration::get('GOINTERPAY_STORE').'&';
		$account .= 'secret'.'='.urlencode(Configuration::get('GOINTERPAY_SECRET')).'&';
		$data = 'misc1'.'='.(int)self::$cart->id.'&';
		$data .= 'misc2'.'='.(float)$carrierPrice.'&';
		$data .= 'domesticShippingCharge'.'='.(float)$carrierPrice.'&';
		$data .= 'interpayRateOfferId'.'='.urlencode($id_rate).'&';
		$data .= 'customerName'.'='.urlencode($customer->firstname.' '.$customer->lastname).'&';
		$data .= 'customerCompany'.'='.urlencode($address->company).'&';
		$data .= 'customerEmail'.'='.urlencode($customer->email).'&';
		$data .= 'customerPhone'.'='.urlencode($address->phone).'&';
		$data .= 'CustomerAltPhone'.'='.urlencode($address->phone_mobile).'&';
		$data .= 'customerAddress1'.'='.urlencode($address->address1).'&';
		$data .= 'customerAddress2'.'='.urlencode($address->address2).'&';
		$data .= 'customerCity'.'='.urlencode($address->city).'&';
		$data .= 'customerState'.'='.($address->id_state ? urlencode($state->name) : '').'&';
		$data .= 'customerZip'.'='.urlencode($address->postcode);
		$address_country = new Country((int)$address->id_country);
		if (Validate::isLoadedObject($address_country))
			$address_country_iso = $address_country->iso_code;
		$post = $account.$products.$data;

		include_once(_PS_MODULE_DIR_.'gointerpay/Rest.php');
		$uuid = Rest::getUUID($post);

		if ($uuid)
			self::$smarty->assign(array('pathInterpaySsl' => _GOINTERPAY_MAIN_URL_.(isset($address_country_iso) ? '?country='.$address_country->iso_code : '').'#Destination',
				'store' => (int)Configuration::get('GOINTERPAY_STORE'),
				'tempCartUUID' => Tools::safeOutput($uuid),
				'country' => urlencode($country->iso_code),
				'modulePath' => './',
			));
		else
			self::$smarty->assign('error', 'An error has occured, please try again later.');
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_MODULE_DIR_.'gointerpay/tpl/redirect.tpl');
	}
}

$interpayController = new GoInterpayController();
$interpayController->run();