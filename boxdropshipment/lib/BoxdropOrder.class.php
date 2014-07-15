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
	 * Base class for storing shipment link information on orders
	 *
	 * @author  sweber <sw@boxdrop.com>
	 * @package BoxdropShipment
	 */
	class BoxdropOrder extends ObjectModel
	{
		public $boxdrop_shop_id;
		public $id_customer;
		public $id_order;
		public $created_at;
		/**
		 * @see ObjectModel::$definition
		 */
		public static $definition = array(
			'table' => 'boxdrop_order',
			'primary' => 'boxdrop_order_id',
			'fields' => array(
				'id_cart' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId',
					'required' => true
				),
				'boxdrop_shop_id' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId'
				),
				'id_customer' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId',
					'required' => true
				),
				'id_order' => array(
					'type' => self::TYPE_INT,
					'validate' => 'isUnsignedId',
					'required' => true
				),
				'created_at' => array(
					'type' => self::TYPE_DATE,
					'validate' => 'isDate'
				)
			)
		);

		/**
		 * Retrieves a BoxdropOrder object by id_cart.
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $id_cart
		 * @return BoxdropOrder
		 */
		public static function retrieveByCartId($id_cart)
		{
			$result =
			Db::getInstance()->executeS('SELECT '.self::$definition['primary'].' FROM `'._DB_PREFIX_.self::$definition['table'].'` WHERE `id_cart` = '.
			(int)$id_cart.' LIMIT 0,1;');
			return (isset($result[0][self::$definition['primary']])) ? new BoxdropOrder($result[0][self::$definition['primary']]) : null;
		}

		/**
		 * Creates a BopxdropOrderShipment and all parcels, plus calls the boxdrop API
		 *
		 * @author sweber     <sw@boxdrop.com>
		 * @param  Order      $order
		 * @param  array      $parceldata
		 * @param  integer    $employee_id
		 * @param  BoxdropSDK $sdk
		 * @return array
		 */
		public function createBoxdropShipment($order, $parceldata, $employee_id, $sdk)
		{
			$api_parcels = array();
			$boxdrop_parcels = array();
			$boxdrop_shipment = BoxdropOrderShipment::create($employee_id, $order);
			$smarty_data = array();
			foreach ($parceldata as $order_detail_id => $parcel_number)
			{
				if ($parcel_number != '')
				{
					if (!isset($boxdrop_parcels[$parcel_number]))
						$boxdrop_parcels[$parcel_number] = BoxdropOrderShipmentParcel::create($boxdrop_shipment);

					$boxdrop_parcels[$parcel_number]->addOrderDetail($order_detail_id);
				}
			}

			foreach ($boxdrop_parcels as $boxdrop_parcel)
				array_push($api_parcels, $boxdrop_parcel->toApiArray());

			$api_data = array(
				'customer_reference' => $order->reference,
				'insurance_amount' => 0,
				'parcels' => $api_parcels,
				'receiver' => BoxdropHelper::getReceiverAddressByOrder($order),
				'sender' => BoxdropHelper::getDefaultSenderAddress(),
				'shipment_date' => date('Y-m-d'),
				'shipment_mode' => $boxdrop_shipment->shipment_mode
			);

			try
			{
				$response = $sdk->request('shipmentOrder', 'createShipmentOrder', $api_data);
				if (isset($response->airwaybill))
				{
					$this->id_cart = $order->id_cart;
					$this->created_at = date('Y-m-d H:i:s');
					$this->save();
					$boxdrop_shipment->order_carrier->tracking_number = $response->airwaybill;
					$boxdrop_shipment->order_carrier->save();
					$boxdrop_shipment->boxdrop_order_id = $this->id;
					$boxdrop_shipment->airwaybill = $response->airwaybill;
					$boxdrop_shipment->pickup_date = date('Y-m-d', $response->pickup_ts);
					$boxdrop_shipment->boxdrop_order_number = $response->order_no;
					$boxdrop_shipment->createShipmentLabel($response->label);
					$boxdrop_shipment->save();
					$order->setCurrentState(Configuration::get(BoxdropShipment::CONF_SHIPPING_STATUS), $employee_id);
					$smarty_data = array(
						'auto_download' => Configuration::get(BoxdropShipment::CONF_AUTO_DOWNLOAD),
						'products' => BoxdropOrder::getProductsToBeShipped($order->id),
						'shipments' => BoxdropOrderShipment::getByOrderId($order->id),
						'status' => 'success'
					);
				}
				else
				{
					$boxdrop_shipment->delete();
					$smarty_data = array(
						'message' => preg_replace('~[\r\n]+~', '', $response->error),
						'status' => 'error'
					);
				}
			}
			catch(BoxdropSDKException $e)
			{
				$boxdrop_shipment->delete();
				$smarty_data = array(
					'message' => $e->toJSString(),
					'status' => 'error'
				);
			}
			return $smarty_data;
		}

		/**
		 * Retrieves all products in a JS usable format for an order that still need shipping
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $order_id
		 * @return string  json_encoded product array
		 */
		public static function getProductsToBeShipped($order_id)
		{
			$order_details = OrderDetail::getList($order_id);
			$products = array();
			foreach ($order_details as $order_detail)
			{
				$is_shipped = BoxdropOrderShipmentParcelHasOrderDetail::orderDetailIsShipped($order_detail['id_order_detail']);
				if (!$is_shipped)
				{
					$image = Image::getCover($order_detail['product_id']);
					$imagepath = '';
					$product = new Product($order_detail['product_id']);
					if ($product->is_virtual)
						continue;

					if (isset($image['id_image']))
					{
						$image = new Image($image['id_image']);
						if (is_object($image))
						{
							$tmp = _PS_IMG_DIR_.'p/'.$image->getExistingImgPath().'.jpg';
							$imagepath = ImageManager::thumbnail($tmp, 'product_mini_'.$product->id.'_0.jpg', 45, 'jpg');
						}
					}
					array_push($products, array(
						'artno' => $product->reference,
						'amount' => $order_detail['product_quantity'],
						'id' => $order_detail['id_order_detail'],
						'image' => $imagepath,
						'name' => $order_detail['product_name'],
						'volumical' => (($product->width * $product->depth * $product->height) / (1000000 / 200)),
						'weight' => $product->weight
					));
				}
			}
			return Tools::jsonEncode($products);
		}
	}
