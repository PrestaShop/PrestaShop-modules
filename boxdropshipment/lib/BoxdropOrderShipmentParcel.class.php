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
	 * Base class for storing parcel information on orders
	 *
	 * @author  sweber <sw@boxdrop.com>
	 * @package BoxdropShipment
	 */
	class BoxdropOrderShipmentParcel extends ObjectModel
	{
		public $boxdrop_order_shipment_id;
		public $depth;
		public $length;
		public $width;
		public $volumetric_weight;
		public $weight;
		public $shipment_weight;
		public $content;
		public $created_at;
		public $boxdrop_shipment = null;
		/**
		 * @see ObjectModel::$definition
		 */
		public static $definition = array(
			'table' => 'boxdrop_order_shipment_parcel',
			'primary' => 'boxdrop_order_shipment_parcel_id',
			'fields' => array(
				'boxdrop_order_shipment_id' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId',
					'required' => true
				),
				'depth' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isNullOrUnsignedId'
				),
				'length' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isNullOrUnsignedId'
				),
				'width' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isNullOrUnsignedId'
				),
				'volumetric_weight' => array(
					'type' => self::TYPE_FLOAT,
					'validate' => 'isUnsignedFloat'
				),
				'weight' => array(
					'type' => self::TYPE_FLOAT,
					'validate' => 'isUnsignedFloat',
					'required' => true
				),
				'shipment_weight' => array(
					'type' => self::TYPE_FLOAT,
					'validate' => 'isUnsignedFloat',
					'required' => true
				),
				'content' => array(
					'type' => self::TYPE_STRING,
					'validate' => 'isString',
					'required' => true
				),
				'created_at' => array(
					'type' => self::TYPE_DATE,
					'validate' => 'isDate',
					'required' => true
				)
			)
		);

		/**
		 * Constructor, overloaded to have the correlating BoxdropOrderShipment object ready
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
			$this->boxdrop_order_shipment = new BoxdropOrderShipment($this->boxdrop_order_shipment_id);
		}

		/**
		 * Creates a shipment parcel object for an order (does not save yet!)
		 *
		 * @author sweber               <sw@boxdrop.com>
		 * @param  BoxdropOrderShipment $boxdrop_shipment
		 * @return BoxdropOrderShipmentParcel
		 */
		public static function create($boxdrop_shipment)
		{
			$parcel = new BoxdropOrderShipmentParcel();
			$parcel->boxdrop_order_shipment = $boxdrop_shipment;
			$parcel->boxdrop_order_shipment_id = $boxdrop_shipment->id;
			$parcel->created_at = date('Y-m-d H:i:s');
			return $parcel;
		}

		/**
		 * Adds an OrderDetail "product" to this parcel:
		 *  - create a relation
		 *  - update parcels dimensions and weight
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @param  integer $order_detail_id
		 * @return void
		 */
		public function addOrderDetail($order_detail_id)
		{
			$order_detail = new OrderDetail($order_detail_id);
			$product = new Product($order_detail->product_id);
			$this->content .= $order_detail->product_quantity.'x '.$order_detail->product_name.' ';
			$this->depth = (int)max($product->depth, $this->depth);
			$this->length = (int)max($product->height, $this->length);
			$this->weight += $product->weight;
			$this->width = (int)max($product->width, $this->width);
			$this->volumetric_weight = ($this->depth * $this->length * $this->weight) / 5000;
			$this->shipment_weight = max($this->volumetric_weight, $this->weight);
			$this->save();
			$this->boxdrop_order_shipment->shipping_weight += $this->shipment_weight;
			$this->boxdrop_order_shipment->parcel_count++;
			$this->createOrderDetailRelation($order_detail_id);
		}

		/**
		 * Creates a relation in BoxdropOrderShipmentParcelHasOrderDetail for this parcel to an OrderObject
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $order_detail_id
		 * @return BoxdropOrderShipmentParcelHasOrderDetail
		 */
		private function createOrderDetailRelation($order_detail_id)
		{
			return BoxdropOrderShipmentParcelHasOrderDetail::create($this->id, $order_detail_id);
		}

		/**
		 * Returns the current objects members as array
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return array
		 */
		public function toApiArray()
		{
			return array(
				'content' => $this->content,
				'depth' => $this->depth,
				'length' => $this->length,
				'weight' => $this->weight,
				'width' => $this->width
			);
		}

		/**
		 * Deletes this parcel and all relations to it
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return void
		 */
		public function delete()
		{
			BoxdropOrderShipmentParcelHasOrderDetail::deleteByBoxropOrderShipmentParcelId($this->id);
			parent::delete();
		}

		/**
		 * Retrieves all Parcels by BoxdropOrderShipmentId
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $boxdrop_order_shipment_id
		 * @return BoxdropOrderShipmentParcel[]
		 */
		public static function getByBoxdropOrderShipmentId($boxdrop_order_shipment_id)
		{
			$results =
			Db::getInstance()->executeS('SELECT '.self::$definition['primary'].' FROM `'._DB_PREFIX_.self::$definition['table'].'` 
			WHERE `boxdrop_order_shipment_id` = '.(int)$boxdrop_order_shipment_id.' ORDER BY '.self::$definition['primary'].' ASC;');
			$parcels = array();
			if (is_array($results))
			{
				foreach ($results as $result)
					array_push($parcels, new BoxdropOrderShipmentParcel($result[self::$definition['primary']]));

			}
			return $parcels;
		}
	}
