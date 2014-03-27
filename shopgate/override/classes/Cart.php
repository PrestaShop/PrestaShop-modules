<?php
/*
* Shopgate GmbH
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file AFL_license.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to interfaces@shopgate.com so we can send you a copy immediately.
*
* @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
* @copyright  Shopgate GmbH
* @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
*/

if(version_compare(_PS_VERSION_, '1.4.0.2', '>=')  && version_compare(_PS_VERSION_, '1.4.1.0', '<=') && !class_exists('CartCore')){
	// load CartCore for extending it for overwritting methods in 1.4.0.2 to 1.4.1.0
	// in newer versions this is load automatically
	include_once(dirname(__FILE__).'/../../../../classes/Cart.php');
}

if(version_compare(_PS_VERSION_, '1.4.0.2', '>=')){
	// CartCore only exists in version 1.4.0.2 and above
	class Cart extends CartCore {
		
		public function getDeliveryOptionList(Country $default_country = null, $flush = false){
			$r = parent::getDeliveryOptionList($default_country, $flush);
	
			if($this->id_carrier == Configuration::get('SHOPGATE_CARRIER_ID')){
				require_once(_PS_MODULE_DIR_.'shopgate/classes/PSShopgateOrder.php');
	
				$shopgateOrder = PSShopgateOrder::instanceByCartId($this->id);
	
				$r[$this->id_address_delivery][$this->id_carrier.',']['carrier_list'][$this->id_carrier] = array
				(
					'price_with_tax' => $shopgateOrder->shipping_cost,
					'price_without_tax' => 0,
					'package_list' => array(0),
					'product_list' => array(),
					'instance' => new Carrier($this->id_carrier),
				);
				
				$r[$this->id_address_delivery][$this->id_carrier.',']['is_best_price'] = 1;
				$r[$this->id_address_delivery][$this->id_carrier.',']['is_best_grade'] = 1;
				$r[$this->id_address_delivery][$this->id_carrier.',']['unique_carrier'] = 1;
				$r[$this->id_address_delivery][$this->id_carrier.',']['total_price_with_tax'] = $shopgateOrder->shipping_cost;
				$r[$this->id_address_delivery][$this->id_carrier.',']['total_price_without_tax'] = 0;
				$r[$this->id_address_delivery][$this->id_carrier.',']['position'] = 0;
			}
			return $r;
		}
	
		public function isCarrierInRange($id_carrier, $id_zone){
			if(version_compare(_PS_VERSION_, "1.4.2.5", "==") || version_compare(_PS_VERSION_, "1.4.3.0", "==")){
				// fix a bug in Prestashop
				$carrier = new Carrier((int)$id_carrier, Configuration::get('PS_LANG_DEFAULT'));
				$shippingMethod = $carrier->getShippingMethod();
				
				###### that is the bug BOF ######
				if (!$carrier->range_behavior) {
					return true;
				}
				###### that is the bug EOF ######
				
				if ($shippingMethod == Carrier::SHIPPING_METHOD_FREE) {
					return true;
				}
			
				if ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT
				&& (Carrier::checkDeliveryPriceByWeight((int)$id_carrier, $this->getTotalWeight(), $id_zone))) {
					return true;
				}
				if ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE
				&& (Carrier::checkDeliveryPriceByPrice((int)$id_carrier, $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, (int)$this->id_currency))) {
					return true;
				}
			} elseif(version_compare(_PS_VERSION_, "1.4.1.0", "==")) {
				// fix a bug in prestashop
				$carrier = new Carrier((int)$id_carrier, Configuration::get('PS_LANG_DEFAULT'));
				$is_in_zone = false;
				$order_total = $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
				
				###### that is the bug BOF ######
				if (!$carrier->range_behavior) {
					return true;
				}
				###### that is the bug EOF ######
					
				if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT
				AND (Carrier::checkDeliveryPriceByWeight((int)$id_carrier, $this->getTotalWeight(), $id_zone)))
				OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE
				AND (Carrier::checkDeliveryPriceByPrice((int)$id_carrier, $order_total, $id_zone, (int)($this->id_currency)))))
				{
					$is_in_zone = true;
				}
				
				unset($carrier);
				return $is_in_zone;
			} else {
				return parent::isCarrierInRange($id_carrier, $id_zone);
			}
			return false;
		}
	}
}
?>