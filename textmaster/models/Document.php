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
class TextMasterDocument extends InvObjectModel
{
	/** @var int prestashop project ID */
	public $id;

	public $id_project;

	/** @var string TextMaster document ID */
	public $id_document_api;

	public $name;

	public $id_product;

	/** @var string TextMaster document creation date */
	public $date_add;

	/** @var string TextMaster document last modification date */
	public $date_upd;

	private $api_data = array();

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'textmaster_document',
		'primary' => 'id',
		'multilang' => false,
		'multishop' => false,
		'fields' => array(
			'id'				=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_project'		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_document_api'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 24),
			'name'				=>	array('type' => self::TYPE_STRING, 'validate' => 'isAnything'),
			'id_product'		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'date_add'			=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd'			=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate')
		),
	);

	public function __construct($id_document = null)
	{
		parent::__construct($id_document);

		if ($id_document)
		{
			$textMasterAPI = new TextMasterAPI;
			$id_project_api = TextMasterProject::getProjectApiId($this->id_project);
			$this->api_data = $textMasterAPI->getDocument($id_project_api, $this->id_document_api);
			$this->id_project_api = $id_project_api;
		}
	}

	public function __set($name, $value)
	{
		$this->api_data[$name] = $value;
	}

	public function __get($name)
	{
		return (isset($this->api_data[$name])) ? $this->api_data[$name] : null;
	}

	public function getApiData($clean_content = true)
	{
		if ($clean_content)
			self::cleanDocumentContent($this->api_data);
		return $this->api_data;
	}

	public function setApiData($data)
	{
		$this->api_data = $data;
	}

	public static function cleanDocumentContent(&$document)
	{
		if (isset($document['original_content']))
			foreach ($document['original_content'] as &$text)
				$text['original_phrase'] = Tools::stripslashes(str_replace("\\r\\n", '<br />', $text['original_phrase']));
	}

	public function getStatus()
	{
		return $this->api_data['status'];
	}

	public function save($autodate = false, $id_project_api = true)
	{
		return (int)$this->id > 0 ? $this->update() : $this->add();
	}

	public function add($send_to_api = true, $null_values = false)
	{
		if ($send_to_api)
		{
			$textMasterAPI = new TextMasterAPI;
			$result = $textMasterAPI->addDocument($this->id_project_api, $this->api_data);

			if (is_array($result))
			{
				$id_document_api = $result['id'];

				foreach ($this->api_data['original_content'] as $element => $text)
				{
					$textMasterDocument = new TextMasterDocument();
					$textMasterDocument->name = $element;
					$textMasterDocument->id_product = (int)$this->api_data['id_product'];
					$textMasterDocument->id_project = $this->id_project;
					$textMasterDocument->id_document_api = $id_document_api;

					if (!$textMasterDocument->add(false))
						return false;
				}

			}
			else
				return $result; // error
		}
		else
			return parent::add(true, false);

		return true;
	}

	public function update($null_values = false)
	{
		$textMasterAPI = new TextMasterAPI;
		$result = $textMasterAPI->updateDocument($this->api_data['id_project_api'], $this->api_data);

		if (!is_array($result))
			return $result; // error

		return parent::update($null_values);
	}
	
	public function delete()
	{
		$textMasterAPI = new TextMasterAPI;
		$result = $textMasterAPI->deleteDocument($this->api_data['id_project_api'], $this->id_document_api);
		if ($result) return false;

		if (!$this->id) return true;
		return parent::delete();
	}
	
	public function approve()
	{
		$textMasterAPI = new TextMasterAPI;
		$result = $textMasterAPI->approveDocument($this->api_data['id_project_api'], $this->id_document_api);

		if (!is_array($result))
			return $result; // error

		return true;
	}
	
	public function comment($message)
	{
		$textMasterAPI = new TextMasterAPI;
		$result = $textMasterAPI->commentDocument($this->api_data['id_project_api'], $this->id_document_api, $message);

		if (!is_array($result))
			return $result; // error
		return true;
	}
	
	public function getComments()
	{
		$textMasterAPI = new TextMasterAPI;
		return $textMasterAPI->getDocumentComments($this->api_data['id_project_api'], $this->id_document_api);
	}
	
	public static function getDocuments($id_project, $id_project_api)
	{
		$documents = array();
		$documents_db = Db::getInstance()->executeS('SELECT `id`, `id_document_api`, `id_product`
													 FROM `'._DB_PREFIX_.'textmaster_document`
													 WHERE `id_project`="'.(int)$id_project.'"
													 GROUP BY `id_product`');

		$textMasterAPI = new TextMasterAPI;
		$documents_api = $textMasterAPI->getDocuments($id_project_api);

		if ($documents_db)
		{
			foreach ($documents_db as $row)
			{
				foreach ($documents_api['documents'] as &$document)
				{
					if ($document['id'] == $row['id_document_api'])
					{
						self::cleanDocumentContent($document);
						$document['id_product'] = $row['id_product'];
						$document['id'] = $row['id'];
						$documents[] = $document;
					}
				}
			}
		}

		return $documents;
	}
	
	public static function wipeProjectDocuments($id_project)
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'textmaster_document` WHERE `id_project`="'.(int)$id_project.'"');
	}
}