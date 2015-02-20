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