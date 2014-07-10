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
 * Date: 06.03.14
 * Time: 13:17
 *
 * File: XmlResultObject.php
 */
class Shopgate_Model_XmlResultObject extends SimpleXMLElement {

	/**
	 * define default main node
	 */
	const DEFAULT_MAIN_NODE = '<items></items>';

	/**
	 * Adds a child with $value inside CDATA
	 *
	 * @param      $name
	 * @param null $value
	 *
	 * @return SimpleXMLElement
	 */
	public function addChildWithCDATA($name, $value = null) {
		$new_child = $this->addChild($name);

		if ($new_child !== null) {
			$node = dom_import_simplexml($new_child);
			$no = $node->ownerDocument;
			if ($value != '') {
				$node->appendChild($no->createCDATASection($value));
			}
		}

		return $new_child;
	}

	/**
	 * @param SimpleXMLElement $new
	 * @param SimpleXMLElement $old
	 *
	 * @return SimpleXMLElement
	 */
	public function replaceChild(SimpleXMLElement $new, SimpleXMLElement $old) {
		$tmp = dom_import_simplexml($this);
		$new = $tmp->ownerDocument->importNode(dom_import_simplexml($new), true);

		$node = $tmp->replaceChild($new, dom_import_simplexml($old));

		return simplexml_import_dom($node, get_class($this));
	}

	/**
	 * Adds an attribute to the SimpleXML element is value not empty
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $namespace
	 */
	public function addAttribute($name, $value = null, $namespace = null) {
		if (isset($value)) {
			parent::addAttribute($name, $value, $namespace);
		}
	}
} 