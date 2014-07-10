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
 * Date: 14.03.14
 * Time: 23:23
 *
 * File: Input.php
 *
 * @method                                      setUid(int $value)
 * @method int                                  getUid()
 *
 * @method                                      setType(string $value)
 * @method string                               getType()
 *
 * @method                                      setOptions(array $value)
 * @method array                                getOptions()
 *
 * @method                                      setValidation(Shopgate_Model_Catalog_Validation $value)
 * @method Shopgate_Model_Catalog_Validation    getValidation()
 *
 * @method                                      setRequired(bool $value)
 * @method bool                                 getRequired()
 *
 * @method                                      setLabel(string $value)
 * @method string                               getLabel()
 *
 * @method                                      setInfoText(string $value)
 * @method string                               getInfoText(string)
 *
 */
class Shopgate_Model_Catalog_Input extends Shopgate_Model_AbstractExport {

	const DEFAULT_INPUT_TYPE_SELECT = 'select';
	const DEFAULT_INPUT_TYPE_MULTIPLE = 'multiple';
	const DEFAULT_INPUT_TYPE_RADIO = 'radio';
	const DEFAULT_INPUT_TYPE_CHECKBOX = 'checkbox';
	const DEFAULT_INPUT_TYPE_TEXT = 'text';
	const DEFAULT_INPUT_TYPE_AREA = 'area';
	const DEFAULT_INPUT_TYPE_FILE = 'file';
	const DEFAULT_INPUT_TYPE_DATE = 'date';
	const DEFAULT_INPUT_TYPE_TIME = 'time';
	const DEFAULT_INPUT_TYPE_DATETIME = 'datetime';

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'Type',
		'Options',
		'Validation',
		'Required',
		'Label',
		'InfoText');

	/**
	 * init default objects
	 */
	public function __construct() {
		$this->setValidation(new Shopgate_Model_Catalog_Validation());
		$this->setOptions(array());
	}

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject    $inputNode
		 * @var Shopgate_Model_XmlResultObject    $optionsNode
		 * @var Shopgate_Model_Catalog_Validation $validationItem
		 * @var Shopgate_Model_Catalog_Option     $optionItem
		 */
		$inputNode = $itemNode->addChild('input');
		$inputNode->addAttribute('uid', $this->getUid());
		$inputNode->addAttribute('type', $this->getType());
		$inputNode->addAttribute('required', $this->getRequired());
		$inputNode->addChildWithCDATA('label', $this->getLabel());
		$inputNode->addChildWithCDATA('info_text', $this->getInfoText());
		$optionsNode = $inputNode->addChild('options');

		/**
		 * options
		 */
		foreach ($this->getOptions() as $optionItem) {
			$optionItem->asXml($optionsNode);
		}

		/**
		 * validation
		 */
		$this->getValidation()->asXml($inputNode);

		return $itemNode;
	}

	/**
	 * add option
	 *
	 * @param Shopgate_Model_Catalog_Option $option
	 */
	public function addOption($option) {
		$options = $this->getOptions();
		array_push($options, $option);
		$this->setOptions($options);
	}
}