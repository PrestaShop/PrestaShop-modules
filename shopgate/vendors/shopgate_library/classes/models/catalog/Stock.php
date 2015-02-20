<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

/**
 * @class Shopgate_Model_Catalog_Stock
 * @see http://developer.shopgate.com/file_formats/xml/products
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