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
 * @class Shopgate_Model_Catalog_Input
 * @see http://developer.shopgate.com/file_formats/xml/products
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
 * @method                                      setAdditionalPrice(string $value)
 * @method string                               getAdditionalPrice()
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
		'AdditionalPrice',
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
		$inputNode->addAttribute('additional_price', $this->getAdditionalPrice());
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