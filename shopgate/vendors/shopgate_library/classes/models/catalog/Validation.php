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
 * Date: 15.03.14
 * Time: 16:31
 *
 * File: Validation.php
 *
 * @method          setValidationType(string $value)
 * @method string   getValidationType()
 *
 * @method          setValue(string $value)
 * @method string   getValue()
 *
 */
class Shopgate_Model_Catalog_Validation extends Shopgate_Model_AbstractExport {
	/**
	 * types
	 */
	const DEFAULT_VALIDATION_TYPE_FILE = 'file';
	const DEFAULT_VALIDATION_TYPE_VARIABLE = 'variable';
	const DEFAULT_VALIDATION_TYPE_REGEX = 'regex';

	/**
	 * file
	 */
	const DEFAULT_VALIDATION_FILE_UNKNOWN = 'unknown';
	const DEFAULT_VALIDATION_FILE_TEXT = 'text';
	const DEFAULT_VALIDATION_FILE_PDF = 'pdf';
	const DEFAULT_VALIDATION_FILE_JPEG = 'jpeg';

	/**
	 * variable
	 */
	const DEFAULT_VALIDATION_VARIABLE_NOT_EMPTY = 'not_empty';
	const DEFAULT_VALIDATION_VARIABLE_INT = 'int';
	const DEFAULT_VALIDATION_VARIABLE_FLOAT = 'float';
	const DEFAULT_VALIDATION_VARIABLE_STRING = 'string';
	const DEFAULT_VALIDATION_VARIABLE_DATE = 'date';
	const DEFAULT_VALIDATION_VARIABLE_TIME = 'time';

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'ValidationType',
		'Value');

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $validationNode
		 */
		$validationNode = $itemNode->addChildWithCDATA('validation', $this->getValue());
		$validationNode->addAttribute('type', $this->getValidationType());

		return $itemNode;
	}

}