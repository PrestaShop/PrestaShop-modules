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

class GiveItShipping extends GiveItObjectModel
{
	public $id;

	public $id_giveit_shipping;

	public $title;

	public $price;
	public $free_above;
	public $tax_percent;
	public $id_currency;

	public $id_shop;

	public $date_add;

	public $date_upd;

	public $iso_code;
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
			'table' => 'giveit_shipping',
			'primary' => 'id_giveit_shipping',
			'multishop' => true,
			'multilang' => true,
			'fields' => array(
				'iso_code' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName',  'required' => true),
				'title' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'lang' => true, 'required' => true),
				'price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
				'tax_percent' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => false),
				'free_above' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => false),
				'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
				'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
				'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
				'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
				),
			);

	public function __construct($id_shipping = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
		if (!$id_shipping)
			$this->id_shop = Context::getContext()->shop->id;

		parent::__construct($id_shipping, $id_lang, $id_shop, $context);
	}

	public static function getShippingRules()
	{
		$query = 'SELECT s.`id_giveit_shipping`, s.`iso_code`,`title`,   s.`tax_percent` ,';
		$query .= 's.`free_above`,`price`, s.`id_currency`, c.`sign` AS `currency_sign`';
		$query .= ' FROM `'._DB_PREFIX_.self::$definition['table'].'` s';
		$query .= ' LEFT JOIN `'._DB_PREFIX_.self::$definition['table'].'_lang` sl ON sl.`id_giveit_shipping`=s.`id_giveit_shipping` AND sl.`id_lang`=';
		$query .= (int)Context::getContext()->language->id;
		$query .= ' LEFT JOIN `'._DB_PREFIX_.'currency` c ON (c.`id_currency`=s.`id_currency`)';
		$query .= ' WHERE `id_shop`='.(int)Context::getContext()->shop->id;

		return Db::getInstance()->executeS($query);
	}

	public static function isDefaultCurrencyUsed()
	{
		$list = Db::getInstance()->executeS('SELECT `id_currency`
				FROM `'._DB_PREFIX_.self::$definition['table'].'`
				WHERE `id_shop`='.(int)Context::getContext()->shop->id);

		if (!$list) return true;

		foreach ($list as $entry)
			if ($entry['id_currency'] == (int)Context::getContext()->currency->id)
				return true;

		return false;
	}
}
