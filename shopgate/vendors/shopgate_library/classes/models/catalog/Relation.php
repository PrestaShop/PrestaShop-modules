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
 * @class Shopgate_Model_Catalog_Relation
 * @see http://developer.shopgate.com/file_formats/xml/products
 *
 * @method          setType(string $value)
 * @method string   getType()
 *
 * @method          setValues(array $value)
 * @method array    getValues()
 *
 * @method          setLabel(string $value)
 * @method string   getLabel()
 *
 */
class Shopgate_Model_Catalog_Relation extends Shopgate_Model_AbstractExport {

	const DEFAULT_RELATION_TYPE_CROSSSELL = 'crosssell';
	const DEFAULT_RELATION_TYPE_RELATION = 'relation';
	const DEFAULT_RELATION_TYPE_CUSTOM = 'custom';
	const DEFAULT_RELATION_TYPE_UPSELL = 'upsell';

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Type',
		'Values',
		'Label');

	/**
	 * init default data
	 */
	public function __construct() {
		$this->setValues(array());
	}

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $relationNode
		 */
		$relationNode = $itemNode->addChild('relation');
		$relationNode->addAttribute('type', $this->getType());
		if ($this->getType() == self::DEFAULT_RELATION_TYPE_CUSTOM) {
			$relationNode->addChildWithCDATA('label', $this->getLabel());
		}
		foreach ($this->getValues() as $value) {
			$relationNode->addChild('uid', $value);
		}

		return $itemNode;
	}

	/**
	 * add new value
	 *
	 * @param int $value
	 */
	public function addValue($value) {
		$values = $this->getValues();
		array_push($values, $value);
		$this->setValues($values);
	}
}