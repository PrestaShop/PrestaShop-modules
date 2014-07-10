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
 * Time: 22:08
 *
 * File: Property.php
 *
 *  @method         setUid(int $value)
 *  @method int     getUid()
 *
 *  @method         setLabel(string $value)
 *  @method string  getLabel()
 *
 *  @method         setValue(string $value)
 *  @method string  getValue()
 *
 */
class Shopgate_Model_Catalog_Property extends Shopgate_Model_AbstractExport {

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'Label',
		'Value');

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $propertyNode
		 */
		$propertyNode = $itemNode->addChild('property');
		$propertyNode->addAttribute('uid', $this->getUid());
		$propertyNode->addChildWithCDATA('label', $this->getLabel());
		$propertyNode->addChildWithCDATA('value', $this->getValue());

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$propertyResult = new Shopgate_Model_Abstract();

		$propertyResult->setData('uid', $this->getUid());
		$propertyResult->setData('label', $this->getLabel());
		$propertyResult->setData('value', $this->getValue());

		return $propertyResult->getData();
	}
}