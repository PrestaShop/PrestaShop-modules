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

class GiveItProduct extends GiveItObjectModel
{
	public $id;

	public $id_giveit_product;

	public $id_product;

	public $id_product_attribute;

	public $display_button;

	public $id_shop;

	public $date_add;

	public $date_upd;

	protected $context;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'giveit_product',
		'primary' => 'id_giveit_product',
		'multilang' => false,
		'fields' => array(
			'id_product' =>             array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId', 'required' => true),
			'id_product_attribute' =>   array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId', 'required' => true),
			'display_button' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'id_shop' => 		        array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId', 'required' => true),
			'date_add' => 		        array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => 		        array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
		),
	);

	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
		$this->id_shop = $this->context->shop->id;
	}

		public static function buttonIsDisplayed($id_product, $id_product_attribute)
		{
				return Db::getInstance()->getValue('SELECT `display_button`
													FROM `'._DB_PREFIX_.self::$definition['table'].'`
													WHERE `id_shop`='.(int)Context::getContext()->shop->id.' AND `id_product`='
													.(int)$id_product.' AND `id_product_attribute`='.(int)$id_product_attribute);
		}

	public static function clearProductAssociations($id_product)
	{
				return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.self::$definition['table'].'`
											WHERE `id_shop`='.(int)Context::getContext()->shop->id.' AND `id_product`='.(int)$id_product);
	}

	public static function saveProductCombinationSetting($id_product, $id_product_attribute, $display_button, $id_shop)
	{
		$id_giveit_product = DB::getInstance()->getValue('
			SELECT `id_giveit_product`
			FROM `'._DB_PREFIX_.self::$definition['table'].'`
			WHERE `id_product` = "'.(int)$id_product.'"
				AND `id_product_attribute` = "'.(int)$id_product_attribute.'"
				AND `id_shop` = "'.(int)$id_shop.'"
		');

		if ($display_button == '')
			return DB::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.self::$definition['table'].'`
				WHERE `id_giveit_product` = "'.(int)$id_giveit_product.'"
			');

		if ($id_giveit_product)
			return DB::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.self::$definition['table'].'`
				SET
					`display_button` = "'.(int)$display_button.'"
				WHERE `id_giveit_product` = "'.(int)$id_giveit_product.'"
			');

		return DB::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.self::$definition['table'].'`
				(`id_product`, `id_product_attribute`, `display_button`, `id_shop`, `date_add`, `date_upd`)
			VALUES
				("'.(int)$id_product.'", "'.(int)$id_product_attribute.'", "'.(int)$display_button.'", "'
				.(int)$id_shop.'", "'.date('Y-m-d H:i:s').'", "'.date('Y-m-d H:i:s').'")
		');
	}
}
