<?php
/**
 * 2013 Give.it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@give.it so we can send you a copy immediately.
 *
 * @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2013 Give.it
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of Give.it
 */

class GiveItObjectModel extends ObjectModel
{
	public static $definition = array();
	private $fields = array();

	protected $tables = array();
	protected $table;
	protected $fieldsValidate = array();

	protected $fieldsRequired = array();
	protected $fieldsSize = array();

	protected $fieldsRequiredLang = array();
	protected $fieldsSizeLang = array();
	protected $fieldsValidateLang = array();

	protected $identifier;

	const TYPE_INT = 1;
	const TYPE_BOOL = 2;
	const TYPE_STRING = 3;
	const TYPE_FLOAT = 4;
	const TYPE_DATE = 5;
	const TYPE_HTML = 6;
	const TYPE_NOTHING = 7;

	public function __construct($id = null, $id_lang = null)
	{
		$definition = $this->getDefinitionProperty();

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$this->tables = array($definition['table']);

			if (isset($definition['multilang']) && $definition['multilang'])
				$this->tables[] = $definition['table'].'_lang';

			$this->table = $definition['table'];
			$this->identifier = $definition['primary'];

			foreach ($definition['fields'] as $field_name => $field)
			{
				if (!in_array($field_name, array('id_shop', 'date_upd', 'date_add')))
				{
					$validate_rule = (isset($field['validate'])) ? $field['validate'] : 'isAnything';
					if (isset($field['lang']) && $field['lang'])
						$this->fieldsValidateLang[$field_name] = $validate_rule;
					else
						$this->fieldsValidate[$field_name] = $validate_rule;

					if (isset($field['required']))
						if (isset($field['lang']) && $field['lang'])
							array_push($this->fieldsRequiredLang, $field_name);
						else
							array_push($this->fieldsRequired, $field_name);

					if (isset($field['size']))
						if (isset($field['lang']) && $field['lang'])
							$this->fieldsSizeLang[$field_name] = $field['size'];
						else
							$this->fieldsSize[$field_name] = $field['size'];
				}
			}
		}

		parent::__construct($id, $id_lang);

		if (version_compare(_PS_VERSION_, '1.4', '>'))
		{
			if (!isset($definition['multilang']) || !$definition['multilang'])
				$this->def['multilang'] = false;
		}
	}

	public function getFields()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			parent::validateFields();

			$definition = $this->getDefinitionProperty(); // child class name
			$fields = array();

			foreach ($definition['fields'] as $field_name => $field)
				if ($field_name == $this->identifier && isset($this->$field_name))
					$fields[$field_name] = $this->$field_name;
				else
					if (!is_array($this->$field_name))
							$fields[$field_name] = $this->cast($field['type'], $this->$field_name);
			return $fields;
		}
		else
			return parent::getFields();
	}

	public function getTranslationsFieldsChild()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			if (count($this->tables) == 1) return;

			parent::validateFieldsLang();

			$fields_array = array();
			$fields = array();

			$definition = $this->getDefinitionProperty();
			if (isset($definition))
				foreach ($definition['fields'] as $field_name => $field)
					if (is_array($this->$field_name))
						$fields_array[] = $field_name;

			foreach (Language::getLanguages(false) as $language)
			{
				$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
				$fields[$language['id_lang']][$this->identifier] = (int)$this->{$this->identifier};

				foreach ($fields_array as $field)
				{
					if (!Validate::isTableOrIdentifier($field))
						die(Tools::displayError());

					if (isset($this->{$field}[$language['id_lang']]) && !empty($this->{$field}[$language['id_lang']]))
						$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']]);
					elseif (in_array($field, $this->fieldsRequiredLang))
					{
						if ($this->{$field} != '')
							$fields[$language['id_lang']][$field] = pSQL($this->{$field}[(int)_PS_LANG_DEFAULT_]);
					}
					else
						$fields[$language['id_lang']][$field] = '';
				}
			}
			return $fields;
		}
		else
		{
			$this->validateFieldsLang();
			$is_lang_multishop = $this->isLangMultishop();

			$fields = array();
			if ($this->id_lang === null)
			{
				foreach (Language::getLanguages(false) as $language)
				{
					$fields[$language['id_lang']] = $this->formatFields(self::FORMAT_LANG, $language['id_lang']);
					$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
					if ($this->id_shop && $is_lang_multishop)
						$fields[$language['id_lang']]['id_shop'] = (int)$this->id_shop;
				}
			}
			else
			{
				$fields = array($this->id_lang => $this->formatFields(self::FORMAT_LANG, $this->id_lang));
				$fields[$this->id_lang]['id_lang'] = $this->id_lang;
				if ($this->id_shop && $is_lang_multishop)
					$fields[$this->id_lang]['id_shop'] = (int)$this->id_shop;
			}

			return $fields;
		}
	}

	private function cast($type, $field)
	{
		switch ($type)
		{
			case 1:
				return (int)$field;
			case 2:
				return (bool)$field;
			case 3:
			case 5:
			case 6:
				return pSQL($field, true);
			case 4:
				return (float)$field;
			case 7:
			default:
				return $field;
		}
	}

	private function getDefinitionProperty()
	{
		$class = get_class($this);
		$reflection = new ReflectionClass($class);
		$definition = $reflection->getStaticPropertyValue('definition');

		return $definition;
	}
}
