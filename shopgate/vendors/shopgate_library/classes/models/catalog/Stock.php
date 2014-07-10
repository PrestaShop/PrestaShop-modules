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
 * Time: 22:26
 *
 * File: Stock.php
 *
 * @method          setIsSaleable(bool $value)
 * @method bool     getIsSaleable()
 *
 * @method          setBackorders(bool $value)
 * @method bool     getBackorders()
 *
 * @method          setUseStock(bool $value)
 * @method bool     getUseStock()
 *
 * @method          setStockQuantity(int $value)
 * @method int      getStockQuantity()
 *
 * @method          setMinimumOrderQuantity(int $value)
 * @method int      getMinimumOrderQuantity()
 *
 * @method          setMaximumOrderQuantity(int $value)
 * @method int      getMaximumOrderQuantity()
 *
 * @method          setAvailabilityText(string $value)
 * @method string   getAvailabilityText()
 *
 */
class Shopgate_Model_Catalog_Stock extends Shopgate_Model_AbstractExport {

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'IsSaleable',
		'Backorders',
		'UseStock',
		'StockQuantity',
		'MinimumOrderQuantity',
		'MaximumOrderQuantity',
		'AvailabilityText');

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $stockNode
		 */
		$stockNode = $itemNode->addChild('stock');
		$stockNode->addChild('is_saleable', $this->getIsSaleable());
		$stockNode->addChild('backorders', $this->getBackorders());
		$stockNode->addChild('use_stock', $this->getUseStock());
		$stockNode->addChild('stock_quantity', $this->getStockQuantity());
		$stockNode->addChild('minimum_order_quantity', $this->getMinimumOrderQuantity());
		$stockNode->addChild('maximum_order_quantity', $this->getMaximumOrderQuantity());
		$stockNode->addChildWithCDATA('availability_text', $this->getAvailabilityText());

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$stockResult = new Shopgate_Model_Abstract();

		$stockResult->setData('is_saleable', $this->getIsSaleable());
		$stockResult->setData('backorders', $this->getBackorders());
		$stockResult->setData('use_stock', $this->getUseStock());
		$stockResult->setData('stock_quantity', $this->getStockQuantity());
		$stockResult->setData('minimum_order_quantity', $this->getMinimumOrderQuantity());
		$stockResult->setData('maximum_order_quantity', $this->getMaximumOrderQuantity());
		$stockResult->setData('availability_text', $this->getAvailabilityText());

		return $stockResult->getData();
	}
}