<?php 

/*Supplier::$definition['fields']['email'] = array('type' => ObjectModel::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128);*/

class Supplier extends SupplierCore
{
	/** @var string Email */
	public $email;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
			'table' => 'supplier',
			'primary' => 'id_supplier',
			'multilang' => true,
			'fields' => array(
					'name' =>                                 array('type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'required' => true, 'size' => 64),
					'active' =>                         array('type' => self::TYPE_BOOL),
					'date_add' =>                         array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
					'date_upd' =>                         array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
					'email' =>                         array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128),
					'send' => 							array('type'=>self::TYPE_BOOL),
					
					// Lang fields
					'description' =>                 array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
					'meta_title' =>                 array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
					'meta_description' =>         array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
					'meta_keywords' =>                 array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
			),
	);

	public function __construct($id = null, $id_lang = null)
	{	
		parent::__construct($id, $id_lang);
	}
	
}
