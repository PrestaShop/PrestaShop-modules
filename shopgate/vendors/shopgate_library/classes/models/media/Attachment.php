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
 * File: Attachment.php
 *
 *  @method         setNumber(int $value)
 *  @method int     getNumber()
 *
 *  @method         setUrl(string $value)
 *  @method string  getUrl()
 *
 *  @method         setTitle(string $value)
 *  @method string  getTitle()
 *
 *  @method         setDescription(string $value)
 *  @method string  getDescription()
 *
 *  @method         setMimeType(string $value)
 *  @method string  getMimeType()
 *
 *  @method         setFileName(string $value)
 *  @method string  getFileName()
 *
 */
class Shopgate_Model_Media_Attachment extends Shopgate_Model_AbstractExport {
	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $attachmentNode
		 */
		$attachmentNode = $itemNode->addChild('attachment');
		$attachmentNode->addAttribute('number', $this->getNumber());
		$attachmentNode->addChildWithCDATA('url', $this->getUrl());
		$attachmentNode->addChild('mime_type', $this->getMimeType());
		$attachmentNode->addChild('file_name', $this->getFileName());
		$attachmentNode->addChildWithCDATA('title', $this->getTitle());
		$attachmentNode->addChildWithCDATA('description', $this->getDescription());

		return $itemNode;
	}
}