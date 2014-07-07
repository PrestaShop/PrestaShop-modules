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
	 * Base class for storing parcel <-> product links on orders
	 *
	 * @author  sweber <sw@boxdrop.com>
	 * @package BoxdropShipment
	 */
	class BoxdropOrderShipmentParcelHasOrderDetail
	{
		public $boxdrop_order_shipment_parcel_id;
		public $order_detail_id;
		/**
		 * Creates a relation between a shipment parcel and an OrderDetail product if not yet existing
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $boxdrop_order_shipment_parcel_id
		 * @param  integer $order_detail_id
		 * @return BoxdropOrderShipmentParcelHasOrderDetail
		 */
		public static function create($boxdrop_order_shipment_parcel_id, $order_detail_id)
		{
			$relation = self::retrieveByFKs($boxdrop_order_shipment_parcel_id, $order_detail_id);
			if (!is_object($relation))
			{
				$relation = new BoxdropOrderShipmentParcelHasOrderDetail();
				$relation->boxdrop_order_shipment_parcel_id = $boxdrop_order_shipment_parcel_id;
				$relation->order_detail_id = $order_detail_id;
				$relation->save();
			}
			return $relation;
		}

		/**
		 * Retrieves a relation between a shipment parcel and an OrderDetail product
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $boxdrop_order_shipment_parcel_id
		 * @param  integer $order_detail_id
		 * @return BoxdropOrderShipmentParcelHasOrderDetail
		 */
		public static function retrieveByFKs($boxdrop_order_shipment_parcel_id, $order_detail_id)
		{
			$result =
			Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'boxdrop_order_shipment_parcel_has_order_detail` WHERE 
			`boxdrop_order_shipment_parcel_id` = '.(int)$boxdrop_order_shipment_parcel_id.' AND `order_detail_id` = '.(int)$order_detail_id);
			if ($result)
			{
				$relation = new BoxdropOrderShipmentParcelHasOrderDetail();
				$relation->boxdrop_order_shipment_parcel_id = $result['boxdrop_order_shipment_parcel_id'];
				$relation->order_detail_id = $result['order_detail_id'];
				return $relation;
			}
			return null;
		}

		/**
		 * Saves a relation between a shipment parcel and an OrderDetail product
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @return void
		 */
		private function save()
		{
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'boxdrop_order_shipment_parcel_has_order_detail` (`boxdrop_order_shipment_parcel_id`,
			`order_detail_id`) VALUES ('.(int)$this->boxdrop_order_shipment_parcel_id.', '.(int)$this->order_detail_id.');');
		}

		/**
		 * Deletes all relations for the given BoxdropOrderShipmentParcelId
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $boxdrop_order_shipment_parcel_id
		 * @return void
		 */
		public static function deleteByBoxropOrderShipmentParcelId($boxdrop_order_shipment_parcel_id)
		{
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'boxdrop_order_shipment_parcel_has_order_detail` WHERE 
			`boxdrop_order_shipment_parcel_id` = '.(int)$boxdrop_order_shipment_parcel_id.';');
		}

		/**
		 * Returns boolean whether the given OrderDetail object has already been shipped
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $order_detail_id
		 * @return boolean
		 */
		public static function orderDetailIsShipped($order_detail_id)
		{
			$result =
			Db::getInstance()->executeS('SELECT order_detail_id FROM `'._DB_PREFIX_.'boxdrop_order_shipment_parcel_has_order_detail` 
			WHERE `order_detail_id` = '.(int)$order_detail_id);
			return (is_array($result) && count($result) > 0);
		}

		/**
		 * Deletes the SQL table, always for this modules version.
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return boolean
		 */
		public static function deleteTable()
		{
			Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'boxdrop_order_shipment_parcel_has_order_detail`;');
			return true;
		}
	}
