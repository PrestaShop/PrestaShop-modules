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
 * Time: 21:52
 *
 * File: Visibility.php
 *
 * @method          setMarketplace(bool $value)
 * @method bool     getMarketplace()
 *
 * @method          setLevel(string $value)
 * @method string   getLevel()
 *
 */
class Shopgate_Model_Catalog_Visibility extends Shopgate_Model_AbstractExport {

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Marketplace',
		'Level');

	const DEFAULT_VISIBILITY_CATALOG_AND_SEARCH = 'catalog_and_search';
	const DEFAULT_VISIBILITY_CATALOG = 'catalog';
	const DEFAULT_VISIBILITY_SEARCH = 'search';
	const DEFAULT_VISIBILITY_NOT_VISIBLE = "not_visible";

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $visibilityNode
		 */
		$visibilityNode = $itemNode->addChild('visibility');
		$visibilityNode->addAttribute('level', $this->getLevel());
		$visibilityNode->addAttribute('marketplace', $this->getMarketplace());

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$visibilityResult = new Shopgate_Model_Abstract();

		$visibilityResult->setData('level', $this->getLevel());
		$visibilityResult->setData('marketplace', $this->getMarketplace());

		return $visibilityResult->getData();
	}
}