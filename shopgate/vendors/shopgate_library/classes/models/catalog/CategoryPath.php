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
 * @class Shopgate_Model_Catalog_CategoryPath
 * @see http://developer.shopgate.com/file_formats/xml/products
 *
 *  @method                             setUid(int $value)
 *  @method int                         getUid()
 *
 *  @method                             setSortOrder(int $value)
 *  @method int                         getSortOrder()
 *
 *  @method                             setItems(array $value)
 *  @method array                       getItems()
 *
 *  @method                             setParentUid(int $value)
 *  @method int                         getParentUid()
 *
 *  @method                             setImage(Shopgate_Model_Media_Image $value)
 *  @method Shopgate_Model_Media_Image  getImage()
 *
 *  @method                             setIsActive(bool $value)
 *  @method bool                        getIsActive()
 *
 *  @method                             setDeeplink(string $value)
 *  @method string                      getDeeplink()
 *
 */
class Shopgate_Model_Catalog_CategoryPath extends Shopgate_Model_AbstractExport {

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'SortOrder',
		'Items',
		'ParentUid',
		'Image',
		'IsActive',
		'Deeplink');

	/**
	 * init default object
	 */
	public function __construct() {
		$this->setItems(array());
	}

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $categoryPathNode
		 * @var Shopgate_Model_XmlResultObject $itemsNode
		 * @var Shopgate_Model_Abstract        $item
		 */
		$categoryPathNode = $itemNode->addChild('category');
		$categoryPathNode->addAttribute('uid', $this->getUid());
		$categoryPathNode->addAttribute('sort_order', $this->getSortOrder());
		$itemsNode = $categoryPathNode->addChild('paths');
		foreach ($this->getItems() as $item) {
			$itemsNode->addChildWithCDATA('path', $item->getData('path'))->addAttribute('level', $item->getData('level'));
		}

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$categoryPathResult = new Shopgate_Model_Abstract();

		$categoryPathResult->setData('uid', $this->getUid());
		$categoryPathResult->setData('sort_order', $this->getSortOrder());

		$itemsData = array();

		/**
		 * @var Shopgate_Model_Abstract $item
		 */
		foreach ($this->getItems() as $item) {
			$itemResult = new Shopgate_Model_Abstract();
			$itemResult->setData('level', $item->getData('level'));
			$itemResult->setData('path', $item->getData('path'));
			array_push($itemsData, $itemResult->getData());
		}
		$categoryPathResult->setData('paths', $itemsData);

		return $categoryPathResult->getData();
	}

	/**
	 * add category path
	 *
	 * @param int    $level
	 * @param string $path
	 */
	public function addItem($level, $path) {
		$items = $this->getItems();
		$item = new Shopgate_Model_Abstract();
		$item->setData('level', $level);
		$item->setData('path', $path);
		array_push($items, $item);
		$this->setItems($items);
	}
}