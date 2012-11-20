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
*  @version  Release: $Revision: 11467 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_MODULE_DIR_.'kialasmall/classes/SmKialaOrder.php');

class SmExportFormat
{
	public $export_folder;
	public $export_name;
	public $path;
	public $file_handle;
	public $exportContent = '';
	public $extension = '.txt';

	protected $kiala_instance;
	protected $field_separator = '|';

	protected $fields = array('partnerId' => array('value' => null, 'mandatory' => 1, 'type' => 'string', 'length' => 8),
						  'partnerBarcode' => array('value' => null, 'mandatory' => 0, 'type' => 'string', 'length' => 64),
						  'parcelNumber' => array('value' => null, 'mandatory' => 1, 'type' => 'string', 'length' => 8),
						  'orderNumber' => array('value' => null, 'mandatory' => 1, 'type' => 'string', 'length' => 32),
						  'orderDate' => array('value' => null, 'mandatory' => 0, 'type' => 'date', 'length' => 8),
						  'shipmentNumber' => array('value' => null, 'mandatory' => 0, 'type' => 'string', 'length' => 32),
						  'CODAmount' => array('value' => null, 'mandatory' => 1, 'type' => 'price', 'length' => 6),
						  'commercialValue' => array('value' => null, 'mandatory' => 1, 'type' => 'price', 'length' => 9),
						  'parcelWeight' => array('value' => null, 'mandatory' => 1, 'type' => 'float', 'length' => 5),
						  'parcelVolume' => array('value' => null, 'mandatory' => 0, 'type' => 'float', 'length' => 6),
						  'parcelDescription' => array('value' => null, 'mandatory' => 0, 'type' => 'string', 'length' => 70),
						  'customerId' => array('value' => null, 'mandatory' => 0, 'type' => 'string', 'length' => 32),
						  'customerName' => array('value' => null, 'mandatory' => 1, 'type' => 'string', 'length' => 35),
						  'customerFirstName' => array('value' => null, 'mandatory' => 0, 'type' => 'string', 'length' => 20),
						  'customerStreet' => array('value' => null, 'mandatory' => 0, 'type' => 'string', 'length' => 35),
	);

	/**
	 * Constructor
	 *
	 * @param Kiala $kiala_instance needed for access to Module::l()
	 */
	public function __construct($kiala_instance){
		if (!$kiala_instance)
			die("No Kiala instance provided");
		$this->kiala_instance = $kiala_instance;
		$this->export_folder = Configuration::get('KIALA_EXPORT_FOLDER');
	}

	/**
	 * Format a date to Kiala export file format
	 *
	 * @param string $date
	 * @return string
	 */
	public function formatDate($date)
	{
		$date = substr($date, 0, 10);
		if ($date == '0000-00-00')
			return '';

		return $date;
	}

	/**
	 * Set the values of the fields that will be exported
	 *
	 * @param array $kiala_order content of the SmKialaOrder object
	 * @return array|bool fields to be exported or false
	 */
	public function initRecordData($kiala_order, $dspid)
	{
		if (!Validate::isLoadedObject($kiala_order))
			return false;
		$order = new Order($kiala_order->id_order);
		$cart = new Cart($order->id_cart);
		$customer = new Customer($kiala_order->id_customer);
		$address = new Address($order->id_address_invoice);

		if (!Validate::isLoadedObject($order) ||
			!Validate::isLoadedObject($customer) ||
			!Validate::isLoadedObject($address))
			return false;

		$products = $cart->getProducts();

		$width = 1;
		$height = 1;
		$depth = 1;
		foreach ($products as $product)
		{
			$width = ($width < $product['width'] ? $product['width'] : $width);
			$height = ($height < $product['height'] ? $product['height'] : $height);
			$depth = ($depth < $product['depth'] ? $product['depth'] : $depth);
		}
		// volume in liters
		$volume = ($width * $height * $depth) / 1000;
		if ($volume < 1)
			$volume = 1;

		$prefix = Configuration::get('KIALASMALL_NUMBER_PREFIX');

		$fields = array();
		$fields['partnerId']['value'] = $dspid;

		// Parcel information
		$fields['parcelBarcode']['value'] = '';
		$fields['parcelNumber']['value'] = $prefix.$kiala_order->id;
		$fields['orderNumber']['value'] = $prefix.$kiala_order->id_order;
		$fields['orderDate']['value'] = $this->formatDate($order->date_add);
		$fields['invoiceNumber']['value'] = ($order->invoice_number ? $order->invoice_number : '');
		$fields['invoiceDate']['value'] =  $this->formatDate($order->invoice_date);
		$fields['shipmentNumber']['value'] = '';
		// @todo Need to check currency = EUR
		if ($order->module == 'cashondelivery')
			$cod_amount = $order->total_paid;
		else
			$cod_amount = '0';
		$fields['CODAmount']['value'] = sprintf('%.2f', $cod_amount);
		$fields['CODCurrency']['value'] = 'EUR';
		$fields['commercialValue']['value'] = sprintf('%.2f', $kiala_order->commercialValue);
		$fields['commercialCurrency']['value'] = 'EUR';
		$fields['parcelWeight']['value'] = sprintf('%.3f', $order->getTotalWeight());
		$fields['parcelVolume']['value'] = sprintf('%.3f', $volume);
		$fields['parcelDescription']['value'] = $kiala_order->parcelDescription;

		// Point information
		$fields['kialaPoint']['value'] = $kiala_order->point_short_id;
		$fields['backupKialaPoint']['value'] = '';

		// Recipient information
		$fields['customerId']['value'] = $customer->id;
		$fields['customerName']['value'] = $customer->lastname;
		$fields['customerFirstName']['value'] = $customer->firstname;

		switch ($customer->id_gender)
		{
			case '1':
				$title = $this->kiala_instance->l('Mr.');
				break;
			case '2':
				$title = $this->kiala_instance->l('Ms.');
				break;
			default:
				$title = '';
		}
		$fields['customerTitle']['value'] = $title;
		$fields['customerExtraAddressLine']['value'] = $address->address2;
		$fields['customerStreet']['value'] = $address->address1;
		$fields['customerStreetNumber']['value'] = '';
		$fields['customerLocality']['value'] = State::getNameById($address->id_state);
		$fields['customerZip']['value'] = $address->postcode;
		$fields['customerCity']['value'] = $address->city;
		$fields['customerCountry']['value'] = Country::getIsoById($address->id_country);
		$fields['customerLanguage']['value'] = strtolower(Language::getIsoById($order->id_lang));
		$fields['positiveNotificationRequested']['value'] = 'Y';
		$fields['customerPhone1']['value'] = $address->phone;
		$fields['customerPhone2']['value'] = $address->phone_mobile;
		$fields['customerPhone3']['value'] = '';
		$fields['customerEmail1']['value'] = $customer->email;
		$fields['customerEmail2']['value'] = '';
		$fields['customerEmail3']['value'] = '';
		return $fields;
	}

	/**
	 * Validate fields before exportation
	 *
	 * @return array errors
	 */
	public function validateFields()
	{
		$errors = array();
		foreach ($this->fields as $key => $params)
		{
			if ($params['mandatory'] == 1 && $params['value'])
				$errors[] = $kiala_instance->l('Field ').$key.$kiala_instance->l(' is mandatory but was not found.');
			switch ($params['type'])
			{
				case 'string':
					if ($params['value'] && (strlen($params['value']) > $params['length']))
						$errors[] = $kiala_instance->l('Field ').$key.$kiala_instance->l(' is too long.');
					break;
				case 'price':
					if ($params['value'] && (strlen($params['value']) > $params['length']))
						$errors[] = $kiala_instance->l('Field ').$key.$kiala_instance->l(' is too long.');
					if ($params['value'] && !Validate::isPrice($params['value']))
						$errors[] = $kiala_instance->l('Field ').$key.$kiala_instance->l(' has a wrong format.');
					break;
				case 'float':
					if ($params['value'] && !Validate::isFloat($params['value']))
						$errors[] = $kiala_instance->l('Field ').$key.$kiala_instance->l(' has a wrong format.');
					break;
				default:
			}
		}
		return $errors;
	}
}