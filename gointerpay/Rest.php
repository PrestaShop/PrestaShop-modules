<?php
/*
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @version  Release: $Revision: 1.7.4 $
 *
 *  International Registered Trademark & Property of PrestaShop SA
 */

class Rest
{
	private $_storeId;
	private $_secret;
	private $_test;

	Const ACCEPTED = 'VENDOR_PREPARING_ORDER';
	Const CANCEL = 'VENDOR_CANCELLATION_REQUEST';

	public function __construct($store, $secret)
	{
		$this->_storeId = $store;
		$this->_secret = $secret;
		$this->_test = 'true';
	}

	public static function getUUID($params)
	{
		$ch = curl_init(_GOINTERPAY_API_UUID_URL_);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); /* Added by PrestaShop */
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); /* Added by PrestaShop */
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}

	public function orderDetail($orderId)
	{
		$ch = curl_init(_GOINTERPAY_API_URL_);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'store='.$this->_storeId.'&secret='.$this->_secret.'&operation=orderDetail&test='.$this->_test.'&orderId='.Tools::safeOutput($orderId));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); /* Added by PrestaShop */
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); /* Added by PrestaShop */
		$content = curl_exec($ch);
		curl_close($ch);										
		$xml = new SimpleXMLElement($content);

		$delivery_address = array();
		$delivery_address['name'] = (string)$xml->name;
		$delivery_address['company'] = (string)$xml->company;
		$delivery_address['phone'] = (string)$xml->phone;
		$delivery_address['altPhone'] = (string)$xml->altPhone;
		$delivery_address['countryCode'] = (string)$xml->countryCode;
		$delivery_address['address1'] = (string)$xml->address1;
		$delivery_address['address2'] = (string)$xml->address2;
		$delivery_address['city'] = (string)$xml->city;
		$delivery_address['state'] = (string)$xml->state;
		$delivery_address['zip'] = (string)$xml->zip;
		
		$invoice_address = array();
		$invoice_address['name'] = (string)$xml->billingName;
		$invoice_address['company'] = (string)$xml->company;
		$invoice_address['phone'] = (string)$xml->billingPhone;
		$invoice_address['altPhone'] = (string)$xml->altPhone;
		$invoice_address['countryCode'] = (string)$xml->billingCountryCode;
		$invoice_address['address1'] = (string)$xml->billingAddress1;
		$invoice_address['address2'] = (string)$xml->billingAddress2;
		$invoice_address['city'] = (string)$xml->billingCity;
		$invoice_address['state'] = (string)$xml->billingState;
		$invoice_address['zip'] = (string)$xml->billingZip;

		return (array('cartId' => (int)$xml->misc1, 'status' => Tools::safeOutput($xml->statuses->status->name), 'shippingTotalForeign' => $xml->shippingTotalForeign,
		'quotedDutyTaxesForeign' => $xml->quotedDutyTaxesForeign, 'itemsTotalForeign' => $xml->itemsTotalForeign, 'grandTotalForeign' => $xml->grandTotalForeign, 'foreignCurrencyCode' => $xml->foreignCurrencyCode,
		'quotedDutyTaxes' => $xml->quotedDutyTaxes, 'itemsTotal' => $xml->itemsTotal, 'grandTotal' => $xml->grandTotal, 'shippingTotal' => $xml->shippingTotal,
		'delivery_address' => $delivery_address, 'invoice_address' => $invoice_address));
	}

	public function updateOrderStatus($orderId, $orderStatus, $id = false)
	{
		$ch = curl_init(_GOINTERPAY_API_URL_);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'store='.$this->_storeId.'&secret='.$this->_secret.'&operation=updateOrderStatus&test='.$this->_test.'&sinceOrderId=&sinceDate=20100101&throughDate=&orderId='.Tools::safeOutput($orderId).'&orderStatusName='.(!$id ? Tools::safeOutput($orderStatus) : '').'&orderStatusId='.($id ? (int)$orderStatus: '' ).'&merchantOrderId=');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); /* Added by PrestaShop */
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); /* Added by PrestaShop */
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}

	public function getOrderStatusLink($orderId)
	{
		$ch = curl_init(_GOINTERPAY_API_URL_);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'store='.$this->_storeId.'&secret='.$this->_secret.'&operation=getOrderStatusLink&test='.$this->_test.'&sinceOrderId=&sinceDate=20100101&throughDate=&orderId='.Tools::safeOutput($orderId).'&orderStatusName=&orderStatusId=&merchantOrderId=');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); /* Added by PrestaShop */
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); /* Added by PrestaShop */
		$content = curl_exec($ch);
		curl_close($ch);

		return $content;
	}
	
	public function checkCredentials($params)
	{
		$ch = curl_init(_GOINTERPAY_API_URL_);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params.'&operation=orderNumbers&sinceDate=20130101');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); /* Added by PrestaShop */
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); /* Added by PrestaShop */
		$content = curl_exec($ch);
		curl_close($ch);

		return !strstr($content, 'error');
	}
}