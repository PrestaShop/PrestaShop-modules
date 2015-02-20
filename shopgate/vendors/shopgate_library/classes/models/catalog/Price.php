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
 * @class Shopgate_Model_Catalog_Price
 * @see http://developer.shopgate.com/file_formats/xml/products
 *
 *  @method                                      setType(string $value)
 *  @method string                               getType()
 *
 *  @method                                      setPrice(float $value)
 *  @method float                                getPrice()
 *
 *  @method                                      setCost(float $value)
 *  @method float                                getCost()
 *
 *  @method                                      setSalePrice(float $value)
 *  @method float                                getSalePrice()
 *
 *  @method                                      setMsrp(float $value)
 *  @method float                                getMsrp()
 *
 *  @method                                      setTierPricesGroup(array $value)
 *  @method Shopgate_Model_Catalog_TierPrice[]   getTierPricesGroup()
 *
 *  @method                                      setMinimumOrderAmount(int $value)
 *  @method int                                  getMinimumOrderAmount()
 *
 *  @method                                      setBasePrice(string $value)
 *  @method string                               getBasePrice()
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
		'MinimumOrderAmount',
		'BasePrice'
	);

	/**
	 * init default object
	 */
	public function __construct() {
		$this->setTierPricesGroup(array());
	}

	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $tierPricesNode
		 * @var Shopgate_Model_Catalog_TierPrice  $customerGroupItem
		 */
		$pricesNode = $itemNode->addChild('prices');
		$pricesNode->addAttribute('type', $this->getType());
		$pricesNode->addChild('price', $this->getPrice());
		$pricesNode->addChild('cost', $this->getCost());
		$pricesNode->addChild('sale_price', $this->getSalePrice());
		$pricesNode->addChild('msrp', $this->getMsrp());
		$pricesNode->addChild('minimum_order_amount', $this->getMinimumOrderAmount());
		$pricesNode->addChildWithCDATA('base_price', $this->getBasePrice());

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