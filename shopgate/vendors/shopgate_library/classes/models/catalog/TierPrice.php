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
 * Time: 17:20
 *
 * File: TierPrice.php
 *
 * @method          setFromQuantity(int $value)
 * @method int      getFromQuantity()
 *
 * @method          setReductionType(string $value)
 * @method string   getReductionType()
 *
 * @method          setReduction(float $value)
 * @method float    getReduction()
 *
 * @method			setCustomerGroupUid(int $value)
 * @method int		getCustomerGroupUid()
 *
 * @method			setToQuantity(int $value)
 * @method int		getToQuantity()
 *
 * @method			setAggregateChildren(bool $value)
 * @method bool		getAggregateChildren()
 */

class Shopgate_Model_Catalog_TierPrice extends Shopgate_Model_AbstractExport {

	const DEFAULT_TIER_PRICE_TYPE_PERCENT = 'percent';
	const DEFAULT_TIER_PRICE_TYPE_FIXED = 'fixed';
	const DEFAULT_TIER_PRICE_TYPE_DIFFERENCE = 'difference';

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'FromQuantity',
		'ReductionType',
		'Reduction',
		'CustomerGroupUid',
		'ToQuantity',
		'AggregateChildren');

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $tierPriceNode
		 */
		$tierPriceNode = $itemNode->addChild('tier_price', $this->getReduction());
		$tierPriceNode->addAttribute('aggregate_children', $this->getAggregateChildren());
		$tierPriceNode->addAttribute('threshold', $this->getFromQuantity());
		$tierPriceNode->addAttribute('max_quantity', $this->getToQuantity());
		$tierPriceNode->addAttribute('type', $this->getReductionType());
		$tierPriceNode->addAttribute('customer_group_uid', $this->getCustomerGroupUid());

		return $itemNode;
	}
}