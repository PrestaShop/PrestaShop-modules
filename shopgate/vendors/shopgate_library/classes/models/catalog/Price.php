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
 * Time: 16:47
 *
 * File: Price.php
 *
 *  @method         setType(string $value)
 *  @method string  getType()
 *
 *  @method         setPrice(float $value)
 *  @method float   getPrice()
 *
 *  @method         setCost(float $value)
 *  @method float   getCost()
 *
 *  @method         setSalePrice(float $value)
 *  @method float   getSalePrice()
 *
 *  @method         setMsrp(float $value)
 *  @method float   getMsrp()
 *
 *  @method         setTierPricesGroup(array $value)
 *  @method array   getTierPricesGroup()
 *
 *  @method         setMinimumOrderAmount(int $value)
 *  @method int     getMinimumOrderAmount()
 *
 */
class Shopgate_Model_Catalog_Price extends Shopgate_Model_AbstractExport {
	/**
	 * default price types
	 *
	 * gross
	 */
	const DEFAULT_PRICE_TYPE_GROSS = 'gross';

	/**
	 * net
	 */
	const DEFAULT_PRICE_TYPE_NET = 'net';

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Type',
		'Price',
		'Cost',
		'SalePrice',
		'Msrp',
		'TierPricesGroup',
		'MinimumOrderAmount');

	/**
	 * init default object
	 */
	public function __construct() {
		$this->setTierPricesGroup(array());
	}

	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $tierPricesNode
		 * @var Shopgate_Model_Customer_Group  $customerGroupItem
		 */
		$pricesNode = $itemNode->addChild('prices');
		$pricesNode->addAttribute('type', $this->getType());
		$pricesNode->addChild('price', $this->getPrice());
		$pricesNode->addChild('cost', $this->getCost());
		$pricesNode->addChild('sale_price', $this->getSalePrice());
		$pricesNode->addChild('msrp', $this->getMsrp());
		$pricesNode->addChild('minimum_order_amount', $this->getMinimumOrderAmount());

		$tierPricesNode = $pricesNode->addChild('tier_prices');
		foreach ($this->getTierPricesGroup() as $customerGroupItem) {
			$customerGroupItem->asXml($tierPricesNode);
		}

		return $itemNode;
	}

	/**
	 * add tier price
	 *
	 * @param Shopgate_Model_Catalog_TierPrice $tierPrice
	 */
	public function addTierPriceGroup(Shopgate_Model_Catalog_TierPrice $tierPrice) {
		$tierPrices = $this->getTierPricesGroup();
		array_push($tierPrices, $tierPrice);
		$this->setTierPricesGroup($tierPrices);
	}
}