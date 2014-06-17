<?php

/**
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
 * @author     Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
 * @copyright  Shopgate GmbH
 * @license    http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
 *
 * User: awesselburg
 * Date: 14.03.14
 * Time: 21:25
 *
 * File: Shipping.php
 *
 * @method          setCostsPerOrder(float $value)
 * @method float    getCostsPerOrder()
 *
 * @method          setAdditionalCostsPerUnit(float $value)
 * @method float    getAdditionalCostsPerUnit()
 *
 * @method          setIsFree(bool $value)
 * @method bool     getIsFree()
 *
 */
class Shopgate_Model_Catalog_Shipping extends Shopgate_Model_AbstractExport {

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'CostsPerOrder',
		'AdditionalCostsPerUnit',
		'IsFree');

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $shippingNode
		 */
		$shippingNode = $itemNode->addChild('shipping');
		$shippingNode->addChild('costs_per_order', $this->getCostsPerOrder());
		$shippingNode->addChild('additional_costs_per_unit', $this->getAdditionalCostsPerUnit());
		$shippingNode->addChild('is_free', $this->getIsFree());

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$shippingResult = new Shopgate_Model_Abstract();

		$shippingResult->setData('costs_per_order', $this->getCostsPerOrder());
		$shippingResult->setData('additional_costs_per_unit', $this->getAdditionalCostsPerUnit());
		$shippingResult->setData('is_free', $this->getIsFree());

		return $shippingResult->getData();
	}
}