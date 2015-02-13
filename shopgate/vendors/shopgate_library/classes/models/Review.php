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
 *
 * User: pliebig
 * Date: 04.09.14
 * Time: 12:07
 * E-Mail: p.liebig@me.com, peter.liebig@magcorp.de
 *
 * File: Review.php
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
class Shopgate_Model_Review extends Shopgate_Model_AbstractExport
{
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