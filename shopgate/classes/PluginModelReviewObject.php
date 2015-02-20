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
 * @author    Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
 * @copyright Shopgate GmbH
 * @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
 *
 * User: awesselburg
 * Date: 28.01.14
 * Time: 10:21
 *
 * File: PluginModelReviewObject.php
 */

class PluginModelReviewObject
	extends Shopgate_Model_Catalog_Review
{
	/**
	 * set uid
	 */
	public function setUid()
	{
		parent::setUid($this->item['id_product_comment']);
	}

	/**
	 * set item uid
	 */
	public function setItemUid()
	{
		parent::setItemUid($this->item['id_product']);
	}

	/**
	 * set score
	 */
	public function setScore()
	{
		parent::setScore($this->item['grade']);
	}

	/**
	 * set reviewer name
	 */
	public function setReviewerName()
	{
		parent::setReviewerName($this->item['customer_name']);
	}

	/**
	 * set date
	 */
	public function setDate()
	{
		parent::setDate($this->item['date_add']);
	}

	/**
	 * set title
	 */
	public function setTitle()
	{
		parent::setTitle($this->item['title']);
	}

	/**
	 * set text
	 */
	public function setText()
	{
		parent::setText($this->item['content']);
	}
}