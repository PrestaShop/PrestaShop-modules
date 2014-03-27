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
class TextMasterProject extends InvObjectModel
{
    /** @var int prestashop project ID */
	public $id;

	/** @var string TextMaster project ID */
	public $id_project_api;

	public $id_shop;

	/** @var string TextMaster project creation date */
	public $date_add;

	/** @var string TextMaster project last modification date */
	public $date_upd;
	
	/** @var string TextMaster project name */
	public $name;
	
	/** @var string TextMaster project original language iso code */
	public $language_from;
	
	/** @var string TextMaster project result language iso code */
	public $language_to;
	
	/** @var string TextMaster project status */
	public $status;
	
	/** @var integer which shows if TextMaster project is launched */
	public $launch;
	
	public $type;
	
	private $api_data = array();

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'textmaster_project',
		'primary' => 'id',
		'multilang' => false,
        'multishop' => true,
		'fields' => array(
            'id'  =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_project_api' => array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => true, 'size' => 24),
			'id_shop' => 		array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
			'date_add' => 		array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => 		array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'type' => 			array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => true),
			'name' => 			array('type' => self::TYPE_STRING, 'validate' => 'isAnything'),
			'language_from' => 	array('type' => self::TYPE_STRING, 'validate' => 'isAnything'),
			'language_to' => 	array('type' => self::TYPE_STRING, 'validate' => 'isAnything'),
			'status' => 		array('type' => self::TYPE_STRING, 'validate' => 'isAnything'),
			'launch' => 		array('type' => self::TYPE_INT, 'validate' => 'isAnything'),
		),
	);
	
	function __construct($id_project = null, $api_data = true)
	{
		parent::__construct($id_project);

        if (!$id_project) $this->id_shop = Context::getContext()->shop->id; //for multishops compatibility
		
		if ($id_project && $api_data)
		{
			$textMasterAPI = new TextMasterAPI;
			$this->api_data = $textMasterAPI->getProject($this->id_project_api);
		}
	}
	
	function __set($name, $value)
	{
		$this->api_data[$name] = $value;
	}
	
	function __get($name)
	{
		return (isset($this->api_data[$name])) ? $this->api_data[$name] : null;
	}
	
	public function getProjectData()
	{
		return $this->api_data;
	}
	
	public function add($autodate = true, $null_values = false)
	{
		$textMasterAPI = new TextMasterAPI;
		$result = $textMasterAPI->addProject($this->api_data);
		if (is_array($result))
		{
			//$this->documents_api = $result['documents'];
			$this->id_project_api = $result['id'];
		}
		else
			return $result; // error
		
		if(!parent::add($autodate, $null_values))
			return false;
		
		$this->id = Db::getInstance()->Insert_ID();
		
		return true;
	}
	
	public function update($null_values = false)
	{
		$textMasterAPI = new TextMasterAPI;
		$result = $textMasterAPI->updateProject($this->api_data);
		
		if (!is_array($result))
			return $result; // error
		
		return parent::update($null_values);
	}
	
	public function delete()
	{
		$textMasterAPI = new TextMasterAPI;
		$result = $textMasterAPI->deleteProject($this->id_project_api);
		
		if (!is_array($result))
		{
			return $result; // error
		}
		
		return TextMasterDocument::wipeProjectDocuments($this->id) && parent::delete();
	}
	
	public function launch()
	{
		$textMasterAPI = new TextMasterAPI;
		$result = $textMasterAPI->launchProject($this->id_project_api);
		if (!is_array($result))
			return $result; // error
		$this->status = $result['status'];
		return parent::update();
	}
	
	public function quote()
	{
		$textMasterAPI = new TextMasterAPI;
		
		$result = $textMasterAPI->addProject($this->api_data, true);

		if(is_array($result) && isset($result['total_costs']))
		{
			$user_info = $textMasterAPI->getUserInfo();
			
			foreach ($result['total_costs'] as $costs)
				if ($costs['currency'] == $user_info['wallet']['currency_code']) // looks for the correct currency
			return $costs['amount'] . ' ' . $costs['currency'];
		}
		
		return $result;
	}

	public static function getProjectByApiId($id_project_api, $load_api_data = false)
	{
		$id = Db::getInstance()->getValue('SELECT `id`
										   FROM `'._DB_PREFIX_.'textmaster_project`
										   WHERE `id_project_api`="'.pSQL($id_project_api).'"');
		
        return new TextMasterProject($id, $load_api_data);
	}
	
	public static function getProjectApiId($id_project)
	{
		return Db::getInstance()->getValue('SELECT `id_project_api`
										    FROM `'._DB_PREFIX_.'textmaster_project`
										    WHERE `id`='.(int)$id_project);
	}
}