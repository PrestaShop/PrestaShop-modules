<?php
/*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*/
class TextMasterConfiguration extends InvObjectModel
{
	public $id;

	/** @var string Key */
	public $name;

	public $id_shop;

	/** @var string Value */
	public $value;

	/** @var string configuration value last modification date */
	public $date_upd;

	private $data = array();

	private $context;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'textmaster_configuration',
		'primary' => 'name',
		'multilang' => false,
		'fields' => array(
			'name' => 			array('type' => self::TYPE_STRING, 'validate' => 'isConfigName', 'required' => true, 'size' => 32),
			'id_shop' => 		array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
			'value' => 			array('type' => self::TYPE_STRING),
			'date_upd' => 		array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
		),
	);

	public function __construct()
	{
		parent::__construct();

		$module_instance = Module::getInstanceByName('textmaster'); // module object is needed for calling text translations
		$this->context = Context::getContext();
		$this->id_shop = $this->context->shop->id;

		/* assigns default configuration values */
		$this->data = array('api_key' => '',
							'api_secret' => '',
							'copywriting_on' => 1,
							'proofreading_on' => 1,
							'translation_on' => 1,
							'copywriting_language_from' => $this->context->language->iso_code,
							'copywriting_category' => 'C001',
							'copywriting_project_briefing' => $module_instance->l('Hello, Thank you for making the changes asked on the text. Please maintain the style and vocabulary level and make sure you correct any grammatical error or typo. Also, please maintain the text format and HTML tags. Thank you', 'Configuration'),
							'copywriting_same_author_must_do_entire_project' => 1,
							'copywriting_language_level' => 'regular',
							'copywriting_quality_on' => 0,
							'copywriting_expertise_on' => 0,
							'copywriting_target_reader_groups' => 'not_specified',

							'proofreading_language_from' => $this->context->language->iso_code,
							'proofreading_category' => 'C019',
							'proofreading_project_briefing' => $module_instance->l('Hello, Thank you for proofreading this text. Please maintain the style and vocabulary level and make sure you correct any grammatical error or typo. Also, please maintain the text format and HTML tags. Thank you', 'Configuration'),
							'proofreading_same_author_must_do_entire_project' => 0,
							'proofreading_language_level' => 'regular',
							'proofreading_quality_on' => 0,
							'proofreading_expertise_on' => 0,
							'proofreading_target_reader_groups' => 'not_specified',

							'translation_language_from' => $this->context->language->iso_code,
							'translation_language_to' => $this->context->language->iso_code,
							'translation_category' => 'C019',
							'translation_project_briefing' => $module_instance->l('Hello, Please translate as faithfully as possible the text provided while respecting the paragraph structure of the document. Please note that the expected number of words is given as an indication only. Also, please maintain the text format and HTML tags. Thank you.', 'Configuration'),
							'translation_same_author_must_do_entire_project' => 0,
							'translation_language_level' => 'premium',
							'translation_quality_on' => 0,
							'translation_expertise_on' => 0,
							'translation_vocabulary_type' => 'not_specified',
							'translation_target_reader_groups' => 'not_specified',
							'translation_grammatical_person' => 'not_specified');

		/* overrides default configuration values with user defined configuration values */
		foreach ($this->getSettings() as $setting)
			$this->data[$setting['name']] = $setting['value'];
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function __get($name)
	{
		return (isset($this->data[$name])) ? $this->data[$name] : null;
	}

	public function updateConfiguration()
	{
		/* clears old configuration values */
		$result = Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'textmaster_configuration` WHERE `id_shop`='.$this->context->shop->id);

		foreach ($this->data as $name => $value)
		{
			$result &= Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'textmaster_configuration` (`name`, `value`, `id_shop`, `date_upd`)
												   VALUES ("'.pSQL($name).'", "'.pSQL($value).'", "'.(int)$this->context->shop->id.'", "'.date('Y-m-d H:i:s').'")');
		}

		return $result;
	}

	private function getSettings()
	{
		return Db::getInstance()->executeS('SELECT `name`, `value`
										    FROM `'._DB_PREFIX_.'textmaster_configuration`
										    WHERE `id_shop`='.(int)$this->context->shop->id);
	}

	public static function get($name)
	{
		$id_shop = (Tools::getValue('id_shop', Context::getContext()->shop->id));

		return Db::getInstance()->getValue('SELECT `value`
										    FROM `'._DB_PREFIX_.'textmaster_configuration`
											WHERE `name`="'.pSQL($name).'" AND `id_shop`='.(int)$id_shop);
	}

	public function getData()
	{
		return $this->data;
	}
}
