<?php

/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayReturnsPolicyConfiguration extends ObjectModel
{
	const DEFAULT_RETURNS_WITHIN = 'Days_14';
	const DEFAULT_RETURNS_WHO_PAYS = 'Buyer';
	const DEFAULT_RETURNS_DESCRIPTION = '';
	const DEFAULT_RETURNS_ACCEPTED_OPTION = 'ReturnsAccepted';
	
	public $id_ebay_returns_policy_configuration;
	public $ebay_returns_within;
	public $ebay_returns_who_pays;
	public $ebay_returns_description;
	public $ebay_returns_accepted_option;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'ebay_returns_policy_configuration',
		'primary' => 'id_ebay_returns_policy_configuration',
		'fields' => array(
			'ebay_returns_within' =>		array('type' => self::TYPE_STRING, 'size' => 255, 'default' => self::DEFAULT_RETURNS_WITHIN),
			'ebay_returns_who_pays' =>		array('type' => self::TYPE_STRING, 'size' => 255, 'default' => self::DEFAULT_RETURNS_WHO_PAYS),
			'ebay_returns_description' => array('type' => self::TYPE_STRING, 'default' => self::DEFAULT_RETURNS_DESCRIPTION),
			'ebay_returns_accepted_option' => array('type' => self::TYPE_STRING, 'size' => 255, 'default' => self::DEFAULT_RETURNS_ACCEPTED_OPTION),
		),
	);		
	
	public function __construct($id_ebay_returns_policy_configuration = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_ebay_returns_policy_configuration, $id_lang, $id_shop);
	}	
		
	/*
	public function setDefaults()
	{
		// we set the default values
		$this->ebay_returns_within = self::DEFAULT_RETURNS_WITHIN;
		$this->ebay_returns_who_pays = self::DEFAULT_RETURNS_WHO_PAYS;
		$this->ebay_returns_description = self::DEFAULT_RETURNS_DESCRIPTION;
		$this->ebay_returns_accepted_option = self::DEFAULT_RETURNS_ACCEPTED_OPTION;		
	}
	*/
	
	public static function getDefaultObjectId()
	{
		$sql = 'SELECT `id_ebay_returns_policy_configuration`
			FROM `'._DB_PREFIX_.self::$definition['table'].'` erpc
			WHERE erpc.`ebay_returns_within`= \''.pSQL(self::DEFAULT_RETURNS_WITHIN).'\'
			AND erpc.`ebay_returns_who_pays` = \''.pSQL(self::DEFAULT_RETURNS_WHO_PAYS).'\'
			AND erpc.`ebay_returns_description` = \''.pSQL(self::DEFAULT_RETURNS_DESCRIPTION).'\'
			AND erpc.`ebay_returns_accepted_option` = \''.pSQL(self::DEFAULT_RETURNS_ACCEPTED_OPTION).'\'';
		if ($row = Db::getInstance()->getRow($sql))
			return $row['id_ebay_returns_policy_configuration'];
	}
}