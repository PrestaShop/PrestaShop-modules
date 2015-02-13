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
 * Time: 21:01
 *
 * File: Category.php
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