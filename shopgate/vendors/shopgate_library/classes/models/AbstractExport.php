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
abstract class Shopgate_Model_AbstractExport extends Shopgate_Model_Abstract {
	public static $allowedEncodings = array(SHOPGATE_LIBRARY_ENCODING, 'ASCII', 'CP1252', 'ISO-8859-15', 'UTF-16LE', 'ISO-8859-1');
	
	/** @var stdClass $item */
	protected $item;

	/**
	 * @var string
	 */
	protected $xsdFileLocation = false;

	/**
	 * @var string
	 */
	protected $itemNodeIdentifier = '<items></items>';

	/**
	 * @var string
	 */
	protected $identifier = 'items';

	/**
	 * @var array
	 */
	protected $fireMethods = array();

	/** set the data by key or array
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return Shopgate_Model_AbstractExport
	 */
	public function setData($key, $value = null) {
		if (is_array($key)) {
			foreach ($key as $key => $value) {
				if (!is_array($value) && !is_object($value)) {
					$value = $this->stripInvalidUnicodeSequences($this->stringToUtf8($value, self::$allowedEncodings));
				}
				$this->$key = $value;
			}
		} else {
			if (!is_array($value) && !is_object($value)) {
				if (!is_null($value)) {
					$value = $this->stripInvalidUnicodeSequences($this->stringToUtf8($value, self::$allowedEncodings));
				}
			}
			$this->$key = $value;
		}

		return $this;
	}

	/**
	 * Strips unicode sequences that are not valid for XML.
	 *
	 * @param string $string
	 * @return string
	 */
	protected function stripInvalidUnicodeSequences($string) {
		return preg_replace('/\\x00-\\x1f/', '', $string);
	}

	/**
	 * returns the xsd file location
	 *
	 * @return string
	 */
	public function getXsdFileLocation() {
		return sprintf('%s/%s', ShopgateConfig::getCurrentXsdLocation(), $this->xsdFileLocation);
	}

	/**
	 * returns the item node identifier
	 *
	 * @return string
	 */
	public function getItemNodeIdentifier() {
		return $this->itemNodeIdentifier;
	}

	/**
	 * returns the identifier
	 *
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * generate data dom object
	 *
	 * @return $this
	 */
	public function generateData() {
		foreach ($this->fireMethods as $method) {
			$this->{$method}();
		}

		return $this;
	}

	/**
	 * @param $item
	 *
	 * @return $this
	 */
	public function setItem($item) {
		$this->item = $item;

		return $this;
	}
	
	/**
	 * @param Shopgate_Model_XmlResultObject $itemsNode
	 * @return Shopgate_Model_XmlResultObject
	 */
	abstract public function asXml(Shopgate_Model_XmlResultObject $itemsNode);
	
	/**
	 * @return array
	 */
	public function asArray() {
		return array($this->getIdentifier() => 'Conversion of this node to array not implemented, yet.');
	}
}