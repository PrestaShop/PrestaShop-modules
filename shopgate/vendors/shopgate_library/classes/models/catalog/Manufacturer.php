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
 * Time: 21:41
 *
 * File: Manufacturer.php
 *
 *  @method         setUid(int $value)
 *  @method int     getUid()
 *
 *  @method         setItemNumber(string $value)
 *  @method string  getItemNumber()
 *
 *  @method         setTitle(string $value)
 *  @method string  getTitle()
 *
 */
class Shopgate_Model_Catalog_Manufacturer extends Shopgate_Model_AbstractExport {

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'ItemNumber',
		'Title');

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $manufacturerNode
		 */
		$manufacturerNode = $itemNode->addChild('manufacturer');
		$manufacturerNode->addAttribute('uid', $this->getUid());
		$manufacturerNode->addChildWithCDATA('title', $this->getTitle());
		$manufacturerNode->addChild('item_number', $this->getItemNumber());

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$manufacturerResult = new Shopgate_Model_Abstract();

		$manufacturerResult->setData('uid', $this->getUid());
		$manufacturerResult->setData('title', $this->getTitle());
		$manufacturerResult->setData('item_number', $this->getItemNumber());

		return $manufacturerResult->getData();
	}
}