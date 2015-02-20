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
 * @class Shopgate_Model_Catalog_Visibility
 * @see http://developer.shopgate.com/file_formats/xml/products
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