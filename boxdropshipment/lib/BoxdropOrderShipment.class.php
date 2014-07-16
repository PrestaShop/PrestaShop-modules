<?php
	/**
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
	 * @author    boxdrop Group AG
	 * @copyright boxdrop Group AG
	 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
	 * International Registered Trademark & Property of boxdrop Group AG
	 */

	/**
	 * Base class for storing shipment information on orders
	 *
	 * @author  sweber <sw@boxdrop.com>
	 * @package BoxdropShipment
	 */
	class BoxdropOrderShipment extends ObjectModel
	{
		public $airwaybill;
		public $boxdrop_order_id;
		public $created_at;
		public $created_by;
		public $current_status;
		public $current_status_l;
		public $id_order;
		public $id_order_carrier;
		public $label;
		public $order_carrier;
		public $parcel_count;
		public $pickup_date;
		public $shipment_mode;
		public $shipping_weight;
		/**
		 * @see ObjectModel::$definition
		 */
		public static $definition = array(
			'table' => 'boxdrop_order_shipment',
			'primary' => 'boxdrop_order_shipment_id',
			'fields' => array(
				'boxdrop_order_id' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isNullOrUnsignedId'
				),
				'id_order' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId',
					'required' => true
				),
				'id_order_carrier' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId'
				),
				'shipment_mode' => array(
					'type' => self::TYPE_STRING,
					'validate' => 'isString'
				),
				'airwaybill' => array(
					'type' => self::TYPE_STRING,
					'validate' => 'isString'
				),
				'pickup_date' => array(
					'type' => self::TYPE_DATE,
					'validate' => 'isDate'
				),
				'label' => array(
					'type' => self::TYPE_STRING,
					'validate' => 'isString'
				),
				'shipping_weight' => array(
					'type' => self::TYPE_FLOAT,
					'validate' => 'isUnsignedFloat'
				),
				'parcel_count' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId'
				),
				'current_status' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isNullOrUnsignedId'
				),
				'created_at' => array(
					'type' => self::TYPE_DATE,
					'validate' => 'isDate',
					'required' => true
				),
				'created_by' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId',
					'required' => true
				)
			)
		);

		/**
		 * Constructor, overloaded to have a readable shipment status included
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @see    ObjectModel::__construct()
		 * @param  integer $id      Existing object id in order to load object (optional)
		 * @param  integer $id_lang Required if object is multilingual (optional)
		 * @param  integer $id_shop ID shop for objects with multishop on langs
		 * @return BoxdropOrderShipment
		 */
		public function __construct($id = null, $id_lang = null, $id_shop = null)
		{
			parent::__construct($id, $id_lang, $id_shop);
			$this->current_status_l = $this->getReadableShippingStatus();
			$this->getOrderCarrier();
		}

		/**
		 * Returns a localized, human readable shipment status information
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return string
		 */
		public function getReadableShippingStatus()
		{
			return BoxdropHelper::l('shipmentStatus.'.$this->current_status);
		}

		/**
		 * Creates a PDF label file from the given base_64encoded contents
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @param  string $label
		 * @return string
		 */
		public function createShipmentLabel($label)
		{
			$base_path = BoxdropHelper::getDataDir();
			$date_path = date('Ym').'/'.date('d').'/';
			$filename = uniqid($this->id_order.'_'.$this->airwaybill.'_').'.pdf';
			$this->label = $date_path.$filename;
			BoxdropHelper::checkAndCreateWriteable($base_path.$date_path);
			file_put_contents($base_path.$date_path.$filename, base64_decode($label));
		}

		/**
		 * Retrieves the correlating OrderCarrier and places it in this object
		 * Makes usage of source located in Order::getIdOrderCarrier(), which is available as of 1.5.5.0, copied here for wider compatibility.
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return OrderCarrier
		 */
		public function getOrderCarrier()
		{
			if ($this->order_carrier === null)
			{
				$id_order_carrier = (int)Db::getInstance()->getValue('SELECT `id_order_carrier` FROM `'._DB_PREFIX_.'order_carrier` WHERE 
				`id_order` = '.(int)$this->id_order);
				if ($id_order_carrier > 0)
				{
					$this->id_order_carrier = $id_order_carrier;
					$this->order_carrier = new OrderCarrier($id_order_carrier);
				}
			}
			return $this->order_carrier;
		}

		/**
		 * Deletes this shipment, plus all relations stored
		 * Does NOT take care of orders generated in the boxdrop systems!
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return void
		 */
		public function delete()
		{
			$parcels = BoxdropOrderShipmentParcel::getByBoxdropOrderShipmentId($this->id);
			foreach ($parcels as $parcel)
				$parcel->delete();
			parent::delete();
		}

		/**
		 * Creates a shipment object for an order
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $employee_id
		 * @param  Order   $order
		 * @return BoxdropOrderShipment
		 */
		public static function create($employee_id, $order)
		{
			$shipment = new BoxdropOrderShipment();
			$shipment->id_order = $order->id;
			$shipment->created_at = date('Y-m-d H:i:s');
			$shipment->created_by = $employee_id;
			$shipment->save();
			$shipment->getOrderCarrier();
			$shipment->setupShipmentMode();
			return $shipment;
		}

		/**
		 * Sets up a shipment mode (one of the constants for the API) to save the shipment service type
		 *
		 * @author <sw@boxdrop.com>
		 * @return void
		 */
		public function setupShipmentMode()
		{
			$carrier_id = $this->order_carrier->id_carrier;
			if (in_array($carrier_id, BoxdropCarrier::getUsedCarrierIds(BoxdropCarrier::TYPE_EXPRESS)))
				$mode = BoxdropShipment::SHIP_MODE_EXPRESS;
			else
				$mode = BoxdropShipment::SHIP_MODE_ECONOMY;
			$this->shipment_mode = $mode;
			$this->save();
		}

		/**
		 * Retrieves all shipments made for a given order
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $id_order
		 * @return string  json_encoded BoxdropOrderShipment[]
		 */
		public static function getByOrderId($id_order)
		{
			$results = Db::getInstance()->executeS('SELECT '.self::$definition['primary'].' FROM `'._DB_PREFIX_.self::$definition['table'].'` WHERE 
			`id_order` = '.(int)$id_order.' ORDER BY '.self::$definition['primary'].' ASC;');
			$shipments = array();
			if (is_array($results))
			{
				foreach ($results as $result)
					array_push($shipments, new BoxdropOrderShipment($result[self::$definition['primary']]));
			}
			return Tools::jsonEncode($shipments);
		}
	}
