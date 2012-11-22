<?php
/* 2007-2011 PrestaShop
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
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_MODULE_DIR_.'kialasmall/classes/SmKialaPoint.php');

class SmKialaRequest
{
	public $search_url = 'http://locateandselect.kiala.com/search';
	public $list_url = 'http://locateandselect.kiala.com/kplist';
	public $tracking_url = 'http://trackandtrace.kiala.com/search';
	public $details_url = 'http://locateandselect.kiala.com/details';

	public function makeRequestRest($request)
	{
		// Init
		$connection = curl_init($request);

		// Set it to return the transfer as a string from curl_exec
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		// Send the Request
		$response = curl_exec($connection);

		// Close the connection
		curl_close($connection);
		// Return the response
		return $response;
	}

	public function makeRequestSoap($params)
	{
		// Test server url: http://packandship-ws-test.kiala.com/psws-web-1.0.0/order?wsdl
		$client = new SoapClient(Configuration::get('KIALASMALL_WS_URL'), array('trace' => true, 'exceptions' => false));

		$result = $client->createOrder($params);

		if (isset($result->trackingNumber))
			return $result->trackingNumber;
		else
		{
			// Uncomment for debug mode
			/*
			p('KIALASMALL error - [SOAP]: '.$result->getMessage()
			  .' [faultCode]: '.(isset($result->detail->orderFault->faultCode) ? $result->detail->orderFault->faultCode : '')
			  .' [message]: '.(isset($result->detail->orderFault->message) ? $result->detail->orderFault->message : '')
			);
			echo htmlentities($client->__getLastRequest());
			die();
			*/
			Logger::addLog('KIALASMALL error - [SOAP]: '.$result->getMessage().' [faultCode]: '.$result->detail->orderFault->faultCode. ' [message]: '.$result->detail->orderFault->message, 4, 1, null, null, true);
		}

		return false;
	}

	/**
     * Build the WS request to create an order
     *
     * @param array fields
     * @param KialaOrder $kiala_order
	 */
	public function getCreateOrderRequest($fields, $kiala_order)
	{
		global $cart;

        $kiala_country = SmKialaCountry::getByIdCountry($kiala_order->id_country_pickup);
		// Spain demo credentials: 6bdaca7d8f4249445550316618e69001 - kialac2c
		$sender_id = $kiala_country->sender_id;
		$sender_password = $kiala_country->password;
		$reference = $fields['orderNumber']['value'];
		$hash = hash('sha512', $reference.$sender_id.$sender_password);
		$originator = 'prestashop';

		$params = array('reference' => $reference,
						'identification' => array('sender' => $sender_id, 'hash' => $hash, 'originator' => $originator),
						'delivery' => array(
										'from' => array('country' => Country::getIsoById($kiala_order->id_country_pickup),
														'node' => $kiala_order->point_short_id),
										'to' => array('country' => Country::getIsoById($kiala_order->id_country_delivery),
													  'node' => $kiala_order->point_short_id),
									  ),
						'parcel' => array('description' => $fields['parcelDescription']['value'],
										  'weight' => $fields['parcelWeight']['value'],
										  'orderNumber' => $fields['orderNumber']['value'],
										  'orderDate' => $fields['orderDate']['value']
									),
						'receiver' => array('firstName' => $fields['customerFirstName']['value'],
											'surname' => $fields['customerName']['value'],
											'address' => array(
												'line1' => $fields['customerStreet']['value'],
												'line2' => $fields['customerExtraAddressLine']['value'],
												'postalCode' => $fields['customerZip']['value'],
												'city' => $fields['customerCity']['value'],
												'country' => $fields['customerCountry']['value']),
											'email' => $fields['customerEmail1']['value'],
											'language' => $fields['customerLanguage']['value'])
					);

		return $params;
	}

	/**
	 * Create a parameters string from an array of key => values
	 *
	 * @param array $params
	 * @return string
	 */
	public function prepareParams($params)
	{
		$request = '?';
		foreach ($params as $key => $value)
			$request .= $key.'='.urlencode($value).'&';

		$request = trim($request, '&');
		return $request;
	}

	/**
	 * Build the locate&select iframe url
	 *
	 * @param string $address
	 * @param int $id_lang
	 * @param string $bckUrl
	 * @return boolean|string
	 */
	public function getSearchRequest($address, $id_lang, $bckUrl)
	{
		$kiala_country = SmKialaCountry::getByIdCountry($address->id_country);
		if (!Validate::isLoadedObject($kiala_country))
			return false;
		$params = array(
						'dspid' => $kiala_country->dspid,
						'country' => Country::getIsoById($address->id_country),
						'language' => Language::getIsoById($id_lang),
						'preparation_delay' => $kiala_country->preparation_delay,
						'street' => $address->address1,
						'zip' => $address->postcode,
						'city' => $address->city,
						'bckUrl' => $bckUrl,
						'target' => '_parent',
						'map-controls' => 'off',
						'thumbnails' => 'off',
						'css' => 'http://prestashop-css.kiala.com/search_kiala_theme.css'
		);

		return $this->search_url.$this->prepareParams($params);
	}

	/**
	 * Build the locate&select iframe url
	 *
	 * @param string $address
	 * @param int $id_lang
	 * @param string $bckUrl
	 * @return boolean|string
	 */
	public function getDetailsRequest($point_short_id, $id_country, $id_lang)
	{
		$params = array(
						'shortID' => $point_short_id,
						'country' => Country::getIsoById($id_country),
						'language' => Language::getIsoById($id_lang),
						'map' => 'on'
		);

		return $this->details_url.$this->prepareParams($params);
	}

	/**
	 * Build the locate&select point list url
	 *
	 * @param int $max_result
	 * @param string $point_short_id
	 */
	public function getPointRequest($max_result, $point_short_id = null)
	{
		global $cart;
		$address = new Address($cart->id_address_delivery);
		$kiala_country = SmKialaCountry::getByIdCountry($address->id_country);
		if (!Validate::isLoadedObject($kiala_country) || !$kiala_country->isActive())
			return false;

		$params = array(
						'dspid' => $kiala_country->dspid,
						'country' => Country::getIsoById($address->id_country),
						'language' => Language::getIsoById($cart->id_lang),
						'preparation_delay' => $kiala_country->preparation_delay,
						'street' => $address->address1,
						'zip' => $address->postcode,
						'city' => $address->city,
						'sort-method' => 'ACTIVE_ONLY'.($point_short_id ? ' '.$point_short_id : ''),
						'max-result' => (int)$max_result
		);
		return $this->list_url.$this->prepareParams($params);
	}

	/**
	 * Get Kiala points from the webservice
	 *
	 * @param unknown_type $point_short_id
	 * @return boolean|Ambigous <multitype:, SmKialaPoint>
	 */
	public function getPointList($point_short_id = null)
	{
		if ($point_short_id)
			$request = $this->getPointRequest(2, $point_short_id);
		else
			$request = $this->getPointRequest(1);
		if (!$request)
			return false;
		$xml = simplexml_load_string($this->makeRequestRest($request));
		$points = SmKialaPoint::getPointListFromXml($xml);
		return $points;
	}

	/**
	 * Build the track&trace url
	 *
	 * @param string $address
	 * @param SmKialaCountry $kiala_country
	 * @param int $id_lang
	 * @param int $key search_key
	 * @param string $search_by
	 */
	public function getTrackingRequest($address, $kiala_country, $id_lang, $key, $search_by)
	{
		$params = array(
						'countryid' => Country::getIsoById($address->id_country),
						'language' => Language::getIsoById($id_lang),
						'dspid' => $kiala_country->dspid
						);
		if ($search_by == 'order')
			$params['dsporderid'] = $key;
		elseif ($search_by == 'customer')
			$params['dspcustomerid'] = $key;
		else
			return false;

		return $this->tracking_url.$this->prepareParams($params);
	}
}