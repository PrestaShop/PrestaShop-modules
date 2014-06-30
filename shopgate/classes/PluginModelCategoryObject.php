<?php

/*
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
 * @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
 * @copyright Shopgate GmbH
 * @license http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
 *
 * User: awesselburg
 * Date: 06.03.14
 * Time: 10:14
 *
 * File: PluginModelCategoryObject.php
 *
 *  @method setContext(array $value)
 *  @method getContext()
 *
 */
class PluginModelCategoryObject
	extends Shopgate_Model_Catalog_Category {

	/**
	 * set uid
	 */
	public function setUid () {
		parent::setUid($this->item['category_number']);
	}

	/**
	 * set name
	 */
	public function setName () {
		parent::setName($this->item['category_name']);
	}

	/**
	 * set parent uid
	 */
	public function setParentUid () {
		parent::setParentUid($this->item['parent_id']);
	}

	/**
	 * set sort order
	 */
	public function setSortOrder () {
		parent::setSortOrder($this->item['order_index']);
	}

	/**
	 * set deep link
	 */
	public function setDeeplink () {
		parent::setDeeplink($this->item['url_deeplink']);
	}

	/**
	 * set is anchor
	 */
	public function setIsAnchor () {
		parent::setIsAnchor(false);
	}

	/**
	 * set is active
	 */
	public function setIsActive () {
		parent::setIsActive($this->item['is_active']);
	}

	/**
	 * set image
	 */
	public function setImage () {
		$imageItem = new Shopgate_Model_Media_Image();
		$imageItem->setUid($this->item['category_number']);
		$imageItem->setUrl($this->item['url_image']);
		parent::setImage($imageItem);
	}
}