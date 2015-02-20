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
 * @class Shopgate_Model_Review
 * @see http://developer.shopgate.com/file_formats/xml/reviews
 *
 * @method          setUid(string $value)
 * @method string   getUid()
 *
 * @method          setItemUid(string $value)
 * @method string   getItemUid()
 *
 * @method          setScore(int $value)
 * @method int      getScore()
 *
 * @method          setReviewerName(string $value)
 * @method string   getReviewerName()
 *
 * @method          setDate(string $value)
 * @method string   getDate()
 *
 * @method          setTitle(string $value)
 * @method string   getTitle()
 *
 * @method          setText(string $value)
 * @method string   getText()
 */
class Shopgate_Model_Catalog_Review extends Shopgate_Model_AbstractExport {
	
	/**
	 * @var string
	 */
	protected $itemNodeIdentifier = '<reviews></reviews>';

	/**
	 * @var string
	 */
	protected $identifier = 'reviews';

	/**
	 * define xsd file location
	 *
	 * @var string
	 */
	protected $xsdFileLocation = 'catalog/reviews.xsd';

	/**
	 * @var array
	 */
	protected $fireMethods = array(
		'setUid',
		'setItemUid',
		'setScore',
		'setReviewerName',
		'setDate',
		'setTitle',
		'setText'
	);

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'ItemUid',
		'Score',
		'ReviewerName',
		'Date',
		'Title',
		'Text'
	);

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $reviewNode
		 */
		$reviewNode = $itemNode->addChild('review');
		$reviewNode->addAttribute('uid', $this->getUid());
		$reviewNode->addChild('item_uid', $this->getItemUid());
		$reviewNode->addChild('score', $this->getScore());
		$reviewNode->addChildWithCDATA('reviewer_name', $this->getReviewerName());
		$reviewNode->addChild('date', $this->getDate());
		$reviewNode->addChildWithCDATA('title', $this->getTitle());
		$reviewNode->addChildWithCDATA('text', $this->getText());

		return $itemNode;
	}

	/**
	 * @return array|null
	 */
	public function asArray() {
		$reviewNode = new Shopgate_Model_Abstract();
		$reviewNode->setData('uid', $this->getUid());
		$reviewNode->setData('item_uid', $this->getItemUid());
		$reviewNode->setData('score', $this->getScore());
		$reviewNode->setData('reviewer_name', $this->getReviewerName());
		$reviewNode->setData('date', $this->getDate());
		$reviewNode->setData('title', $this->getTitle());
		$reviewNode->setData('text', $this->getText());

		return $reviewNode->getData();
	}
}

/**
 * Class Shopgate_Model_Review
 *
 * @deprecated use Shopgate_Model_Catalog_Review
 */
class Shopgate_Model_Review extends Shopgate_Model_Catalog_Review {}