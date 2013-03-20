<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Advice extends ObjectModel
{
	public $id;
	
	public $id_ps_advice;
		
	public $id_tab;
	
	public $validated;
	
	public $selector;
	
	public $location;
	
	public $html;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'advice',
		'primary' => 'id_advice',
		'multilang' => true,
		'fields' => array(
			'id_ps_advice' =>	array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'id_tab' =>			array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'selector' =>		array('type' => self::TYPE_STRING),
			'location' =>		array('type' => self::TYPE_STRING),
			'validated' =>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),

			// Lang fields
			'html' => 			array('type' => self::TYPE_HTML, 'lang' => true, 'required' => true, 'validate' => 'isString'),
		),
	);
	
	public static function getIdByIdPs($id_ps_advice)
	{
		$query = new DbQuery();
		$query->select('id_advice');
		$query->from('advice', 'b');
		$query->where('`id_ps_advice` = '.(int)$id_ps_advice);
		
		return (int)Db::getInstance()->getValue($query);
	}

	public static function getValidatedByIdTab($id_tab)
	{
		$advices = new Collection('advice', Context::getContext()->language->id);
		$advices->where('validated', '=' , 1);
		$advices->where('id_tab', '=' , (int)$id_tab);
		return $advices;
	}
	
	public static function getIdsAdviceToValidate()
	{
		$ids = array();
		$query = new DbQuery();
		$query->select('a.`id_advice`');
		$query->from('advice', 'a');
		$query->join('
			LEFT JOIN `'._DB_PREFIX_.'condition_advice` ca ON ca.`id_advice` = a.`id_advice` AND ca.`display` = 1 
			LEFT JOIN `'._DB_PREFIX_.'condition` c ON c.`id_condition` = ca.`id_condition` AND c.`validated` = 1');
		$query->where('a.validated = 0');
		$query->groupBy('a.`id_advice`');
		$query->having('count(*) = SUM(c.validated)');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
		
		foreach($result as $advice)
			$ids[] = $advice['id_advice'];
		return $ids;
	}
	
	public static function getIdsAdviceToUnvalidate()
	{
		$ids = array();
		$query = new DbQuery();
		$query->select('a.`id_advice`');
		$query->from('advice', 'a');
		$query->join('
			LEFT JOIN `'._DB_PREFIX_.'condition_advice` ca ON ca.`id_advice` = a.`id_advice` AND ca.`display` = 0 
			LEFT JOIN `'._DB_PREFIX_.'condition` c ON c.`id_condition` = ca.`id_condition` AND c.`validated` = 1');
		$query->where('a.validated = 1');
		$query->groupBy('a.`id_advice`');
		$query->having('count(*) = SUM(c.validated)');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
		
		foreach($result as $advice)
			$ids[] = $advice['id_advice'];
		return $ids;
	}
}
