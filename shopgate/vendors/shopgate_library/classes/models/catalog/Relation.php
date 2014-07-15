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
 * Time: 22:57
 *
 * File: Relation.php
 *
 * @method          setType(string $value)
 * @method string   getType()
 *
 * @method          setValues(array $value)
 * @method array    getValues()
 *
 * @method          setLabel(string $value)
 * @method string   getLabel()
 *
 */
class Shopgate_Model_Catalog_Relation extends Shopgate_Model_AbstractExport {
	const DEFAULT_RELATION_TYPE_CROSSSELL = 'crosssell';
	const DEFAULT_RELATION_TYPE_RELATION = 'relation';
	const DEFAULT_RELATION_TYPE_CUSTOM = 'custom';
	const DEFAULT_RELATION_TYPE_UPSELL = 'upsell';

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Type',
		'Values',
		'Label');

	/**
	 * init default data
	 */
	public function __construct() {
		$this->setValues(array());
	}

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $relationNode
		 */
		$relationNode = $itemNode->addChild('relation');
		$relationNode->addAttribute('type', $this->getType());
		if ($this->getType() == self::DEFAULT_RELATION_TYPE_CUSTOM) {
			$relationNode->addChildWithCDATA('label', $this->getLabel());
		}
		foreach ($this->getValues() as $value) {
			$relationNode->addChild('uid', $value);
		}

		return $itemNode;
	}

	/**
	 * add new value
	 *
	 * @param int $value
	 */
	public function addValue($value) {
		$values = $this->getValues();
		array_push($values, $value);
		$this->setValues($values);
	}
}