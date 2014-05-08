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

class GiveItCategory extends GiveItObjectModel
{
	public $id_give_it_category;

	public $id_category;

	public $id_shop;

	public $date_add;

	public $date_upd;

	protected $context;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'giveit_category',
		'primary' => 'id_give_it_category',
		'multilang' => false,
		'multishop' => true,
		'fields' => array(
			'id_category' =>    array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId', 'required' => true),
			'id_shop' => 		array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId', 'required' => true),
			'date_add' => 		array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => 		array('type' => self::TYPE_DATE, 'validate' => 'isDate')
		)
	);

	public static function clearCategory()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			return Db::getInstance()->execute('
				DELETE FROM `'._DB_PREFIX_.self::$definition['table'].'`
				WHERE `id_shop`='.(int)Context::getContext()->shop->id
			);
		}

		$shop_context = Shop::getContext();

		if ($shop_context == Shop::CONTEXT_SHOP)
		{
			return Db::getInstance()->execute('
				DELETE FROM `'._DB_PREFIX_.self::$definition['table'].'`
				WHERE `id_shop`='.(int)Context::getContext()->shop->id
			);
		}
		else
		{
			$id_shop_group = (Shop::getContext() == Shop::CONTEXT_GROUP) ? Shop::getContextShopGroupID() : null;
						$shop_ids = Shop::getShops(false, $id_shop_group, true);

			foreach ($shop_ids as $id_shop)
			{
				Db::getInstance()->execute('
					DELETE FROM `'._DB_PREFIX_.self::$definition['table'].'`
					WHERE `id_shop`='.(int)$id_shop
				);
			}
		}
	}

	public static function getCategories()
	{
		$categories_ids = Db::getInstance()->executeS('
			SELECT id_category
			FROM `'._DB_PREFIX_.self::$definition['table'].'`
			WHERE `id_shop`='.(int)Context::getContext()->shop->id
		);
		$return = array();
		foreach ($categories_ids as $category_id)
			$return[] = $category_id['id_category'];
		return $return;
	}
}
