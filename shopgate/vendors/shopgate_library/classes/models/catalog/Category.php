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
 * @class Shopgate_Model_Catalog_Category
 * @see http://developer.shopgate.com/file_formats/xml/categories
 *
 *  @method                             setUid(int $value)
 *  @method int                         getUid()
 *
 *  @method                             setSortOrder(int $value)
 *  @method int                         getSortOrder()
 *
 *  @method                             setName(string $value)
 *  @method string                      getName()
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
 *  @method                             setIsAnchor(bool $value)
 *  @method bool                        getIsAnchor()
 *
 */
class Shopgate_Model_Catalog_Category extends Shopgate_Model_AbstractExport {
	/**
	 * @var string
	 */
	protected $itemNodeIdentifier = '<categories></categories>';

	/**
	 * @var string
	 */
	protected $identifier = 'categories';

	/**
	 * define xsd file location
	 *
	 * @var string
	 */
	protected $xsdFileLocation = 'catalog/categories.xsd';

	/**
	 * @var array
	 */
	protected $fireMethods = array(
		'setUid',
		'setSortOrder',
		'setName',
		'setParentUid',
		'setSortOrder',
		'setDeeplink',
		'setIsAnchor',
		'setImage',
		'setIsActive');

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'SortOrder',
		'Name',
		'ParentUid',
		'Image',
		'IsActive',
		'Deeplink',
		'IsAnchor');

	/**
	 * init default object
	 */
	public function __construct() {
		$this->setData(
			array(
				'image' => new Shopgate_Model_Media_Image()
			)
		);
	}

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $categoryNode
		 */
		$categoryNode = $itemNode->addChild('category');
		$categoryNode->addAttribute('uid', $this->getUid());
		$categoryNode->addAttribute('sort_order', $this->getSortOrder());
		$categoryNode->addAttribute('parent_uid', $this->getParentUid() ? $this->getParentUid() : null);
		$categoryNode->addAttribute('is_active', $this->getIsActive());
		$categoryNode->addAttribute('is_anchor', $this->getIsAnchor());
		$categoryNode->addChildWithCDATA('name', $this->getName());
		$categoryNode->addChildWithCDATA('deeplink', $this->getDeeplink());

		/**
		 * image
		 */
		$this->getImage()->asXml($categoryNode);

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$categoryResult = new Shopgate_Model_Abstract();

		$categoryResult->setData('uid', $this->getUid());
		$categoryResult->setData('sort_order', $this->getSortOrder());
		$categoryResult->setData('parent_uid', $this->getParentUid());
		$categoryResult->setData('is_active', $this->getIsActive());
		$categoryResult->setData('is_anchor', $this->getIsAnchor());
		$categoryResult->setData('name', $this->getName());
		$categoryResult->setData('deeplink', $this->getDeeplink());

		$categoryResult->setData('image', $this->getImage()->asArray());

		return $categoryResult->getData();
	}
}