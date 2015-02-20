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
 * @class Shopgate_Model_Media_Attachment
 * @see http://developer.shopgate.com/file_formats/xml/products
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