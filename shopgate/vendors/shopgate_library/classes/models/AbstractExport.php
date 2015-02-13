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
 * Time: 09:44
 *
 * File: AbstractExport.php
 *
 *
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