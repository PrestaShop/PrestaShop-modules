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
 * File: Attribute.php
 *
 * @method              setUid(int $value)
 * @method int          getUid()
 *
 * @method              setGroupUid(int $value)
 * @method int          getGroupUid()
 *
 * @method              setLabel(string $value)
 * @method string       getLabel()
 *
 */
class Shopgate_Model_Catalog_Attribute extends Shopgate_Model_AbstractExport {

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'GroupUid',
		'Label');

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $attributeNode
		 */
		$attributeNode = $itemNode->addChildWithCDATA('attribute', $this->getLabel());
		$attributeNode->addAttribute('uid', $this->getUid());
		$attributeNode->addAttribute('group_uid', $this->getGroupUid());

		return $itemNode;
	}
}