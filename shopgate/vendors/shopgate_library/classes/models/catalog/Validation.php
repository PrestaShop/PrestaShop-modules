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
 * @class Shopgate_Model_Catalog_Validation
 * @see http://developer.shopgate.com/file_formats/xml/products
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