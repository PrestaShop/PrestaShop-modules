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
 * Time: 20:37
 *
 * File: Images.php
 *
 *  @method         setUid(int $value)
 *  @method int     getUid()
 *
 *  @method         setSortOrder(int $value)
 *  @method int     getSortOrder()
 *
 *  @method         setUrl(string $value)
 *  @method string  getUrl()
 *
 *  @method         setTitle(string $value)
 *  @method string  getTitle()
 *
 *  @method         setAlt(string $value)
 *  @method string  getAlt()
 *
 *  @method         setIsCover(bool $value)
 *  @method string  getIsCover()
 *
 */
class Shopgate_Model_Media_Image extends Shopgate_Model_AbstractExport {

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'SortOrder',
		'Url',
		'Title',
		'Alt',
		'IsCover');

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $imageNode
		 */
		$imageNode = $itemNode->addChild('image');
		$imageNode->addAttribute('uid', $this->getUid());
		$imageNode->addAttribute('sort_order', $this->getSortOrder());
		$imageNode->addAttribute('is_cover', $this->getIsCover());
		$imageNode->addChildWithCDATA('url', $this->getUrl());
		$imageNode->addChildWithCDATA('title', $this->getTitle());
		$imageNode->addChildWithCDATA('alt', $this->getAlt());

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$imageResult = new Shopgate_Model_Media_Image();

		$imageResult->setUid($this->getUid());
		$imageResult->setSortOrder($this->getSortOrder());
		$imageResult->setUrl($this->getUrl());
		$imageResult->setTitle($this->getTitle());
		$imageResult->setAlt($this->getAlt());
		$imageResult->setIsCover($this->getIsCover());

		return $imageResult->getData();

	}
}