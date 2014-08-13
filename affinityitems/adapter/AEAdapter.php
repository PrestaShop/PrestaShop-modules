<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future.If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

require_once(dirname(__FILE__).'/../backward_compatibility/backward.php');

class AEAdapter {

	public static function countCategory($clause)
	{
		if (Context::getContext()->shop->isFeatureActive())
		{
			return Db::getInstance()->executeS('SELECT DISTINCT count(*) as ccategory
				FROM `'._DB_PREFIX_.'category` c, `'._DB_PREFIX_.'category_shop` cs
				'.$clause.'
				AND c.id_category = cs.id_category
				AND cs.id_shop = '.Shop::getContextShopID(true).';');
		}
		else
		{
			return Db::getInstance()->executeS('SELECT DISTINCT count(*) as ccategory
				FROM `'._DB_PREFIX_.'category` c
				'.$clause.';');
		}
	}

	public static function newCategoryClause()
	{
		if (Context::getContext()->shop->isFeatureActive())
			return 'WHERE (c.id_category, cs.id_shop) NOT IN (SELECT id_category, id_shop FROM '._DB_PREFIX_.'ae_category_repository)';
		else
			return 'WHERE c.id_category NOT IN (SELECT id_category FROM '._DB_PREFIX_.'ae_category_repository)';
	}

	public static function updateCategoryClause()
	{
		if (Context::getContext()->shop->isFeatureActive())
		{
			return 'WHERE c.date_upd > (SELECT cr.date_upd FROM '._DB_PREFIX_.'ae_category_repository cr WHERE c.id_category = cr.id_category
				AND id_shop = '.(int)Shop::getContextShopID(true).')';
		}
		else
			return 'WHERE c.date_upd > (SELECT cr.date_upd FROM '._DB_PREFIX_.'ae_category_repository cr WHERE c.id_category = cr.id_category)';
	}

	public static function deleteCategoryClause()
	{
		if (Context::getContext()->shop->isFeatureActive())
		{
			return Db::getInstance()->ExecuteS('SELECT cr.id_category
				FROM '._DB_PREFIX_.'ae_category_repository cr
				WHERE (cr.id_category, cr.id_shop) NOT IN (SELECT DISTINCT cs.id_category, cs.id_shop FROM `'._DB_PREFIX_.'category_shop` cs
					WHERE cs.id_shop = '.Shop::getContextShopID(true).')
				AND cr.id_shop = '.(int)Shop::getContextShopID(true).';');
		}
		else
		{
			return Db::getInstance()->ExecuteS('SELECT cr.id_category
				FROM '._DB_PREFIX_.'ae_category_repository cr
				WHERE cr.id_category NOT IN (SELECT DISTINCT c.id_category FROM `'._DB_PREFIX_.'category` c);');
		}
	}

	public static function getCategoryList($clause, $bulk)
	{
		if (Context::getContext()->shop->isFeatureActive())
		{
			return Db::getInstance()->executeS('SELECT DISTINCT c.id_category, c.date_upd, c.id_parent
				FROM `'._DB_PREFIX_.'category` c, `'._DB_PREFIX_.'category_shop` cs
				'.$clause.'
				AND c.id_category = cs.id_category
				AND cs.id_shop = '.(int)Shop::getContextShopID(true).'
				ORDER BY c.id_category
				LIMIT 0,'.intval($bulk).';');
		}
		else
		{
			return Db::getInstance()->executeS('SELECT DISTINCT c.id_category, c.date_upd, c.id_parent
				FROM `'._DB_PREFIX_.'category` c
				'.$clause.'
				ORDER BY c.id_category
				LIMIT 0,'.intval($bulk).';');
		}
	}

	public static function getCategoryFeatures($category_id)
	{
		return Db::getInstance()->ExecuteS(
			'SELECT DISTINCT c.id_parent, l.iso_code, cl.name, cl.description
			FROM '._DB_PREFIX_.'category_group cg, '._DB_PREFIX_.'category c, '._DB_PREFIX_.'category_lang cl, '._DB_PREFIX_.'lang l
			WHERE c.id_category = cg.id_category
			AND cl.id_lang = l.id_lang
			AND c.id_category = cl.id_category
			AND c.id_category = '.(int)$category_id);
	}

	public static function insertCategory($category)
	{
		$multishop = (Context::getContext()->shop->isFeatureActive()) ? Shop::getContextShopID(true) : 1;
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_category_repository` VALUES('.(int)$category->categoryId.', '.(int)$multishop.'
			,\''.pSQL($category->updateDate).'\');');
	}

	public static function updateCategory($category)
	{
		$multishop = (Context::getContext()->shop->isFeatureActive()) ? Shop::getContextShopID(true) : 1;
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ae_category_repository` SET date_upd = \''.pSQL($category->updateDate).'\'
			WHERE id_category = '.(int)$category->categoryId.' AND id_shop = '.(int)$multishop.';');
	}

	public static function deleteCategory($category)
	{
		$multishop = (Context::getContext()->shop->isFeatureActive()) ? Shop::getContextShopID(true) : 1;
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ae_category_repository` WHERE id_category = '.(int)$category->categoryId.'
			AND id_shop = '.(int)$multishop.';');
	}

	public static function countProduct($clause)
	{
		if (Context::getContext()->shop->isFeatureActive())
		{
			return Db::getInstance()->executeS('SELECT DISTINCT count(*) as cproduct
				FROM `'._DB_PREFIX_.'product_shop` ps
				'.$clause.'
				AND ps.id_shop = '.(int)Shop::getContextShopID(true).';');
		}
		else
		{
			return Db::getInstance()->executeS('SELECT DISTINCT count(*) as cproduct
				FROM `'._DB_PREFIX_.'product` p
				'.$clause.';');
		}
	}

	public static function newProductClause()
	{
		if (Context::getContext()->shop->isFeatureActive())
			return 'WHERE (ps.id_product, ps.id_shop) NOT IN(SELECT id_product, id_shop FROM '._DB_PREFIX_.'ae_product_repository)';
		else
			return 'WHERE p.id_product NOT IN(SELECT id_product FROM '._DB_PREFIX_.'ae_product_repository)';
	}

	public static function updateProductClause()
	{
		if (Context::getContext()->shop->isFeatureActive())
		{
			return 'WHERE ps.date_upd > (SELECT date_upd FROM '._DB_PREFIX_.'ae_product_repository pr WHERE pr.id_product = ps.id_product
				AND id_shop = '.(int)Shop::getContextShopID(true).')';
		}
		else
			return 'WHERE p.date_upd > (SELECT date_upd FROM '._DB_PREFIX_.'ae_product_repository pr WHERE pr.id_product = p.id_product)';
	}

	public static function deleteProductClause()
	{
		if (Context::getContext()->shop->isFeatureActive())
		{
			return Db::getInstance()->ExecuteS('SELECT DISTINCT pr.id_product
				FROM `'._DB_PREFIX_.'ae_product_repository` pr
				WHERE (pr.id_product, pr.id_shop) NOT IN (SELECT ps.id_product, ps.id_shop FROM `'._DB_PREFIX_.'product_shop` ps)
				AND pr.id_shop = '.(int)Shop::getContextShopID(true).';');
		}
		else
		{
			return Db::getInstance()->ExecuteS('SELECT DISTINCT pr.id_product
				FROM `'._DB_PREFIX_.'ae_product_repository` pr
				WHERE pr.id_product NOT IN (SELECT p.id_product FROM `'._DB_PREFIX_.'product` p);');
		}
	}

	public static function getProductList($clause, $bulk)
	{
		if (Context::getContext()->shop->isFeatureActive())
		{
			return Db::getInstance()->executeS('SELECT DISTINCT ps.id_product, ps.date_upd, ps.active
				FROM `'._DB_PREFIX_.'product_shop` ps
				'.$clause.'
				AND ps.id_shop = '.(int)Shop::getContextShopID(true).'
				ORDER BY ps.id_product
				LIMIT 0,'.intval($bulk).';');
		}
		else
		{
			return Db::getInstance()->executeS('SELECT DISTINCT p.id_product, p.date_upd, p.active
				FROM `'._DB_PREFIX_.'product` p
				'.$clause.'
				ORDER BY p.id_product
				LIMIT 0,'.intval($bulk).';');
		}
	}

	public static function getProductsLocalizations($product_id)
	{
		return Db::getInstance()->executeS('SELECT l.iso_code, pl.description, pl.description_short, pl.name, m.name as mname, s.name as sname
			FROM  `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON pl.id_product = p.id_product
			LEFT JOIN `'._DB_PREFIX_.'lang` l ON l.id_lang = pl.id_lang
			LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON p.id_manufacturer = m.id_manufacturer
			LEFT JOIN `'._DB_PREFIX_.'supplier` s ON p.id_supplier = s.id_supplier
			WHERE p.id_product = '.intval($product_id).';');
	}

	public static function getProductCategories($product_id)
	{
		return Db::getInstance()->ExecuteS('
			SELECT id_category
			FROM '._DB_PREFIX_.'category_product
			WHERE id_product = '.(int)$product_id);
	}

	public static function getProductTags($product_id, $iso_code)
	{
		return Db::getInstance()->ExecuteS('
			SELECT l.`iso_code`, t.`name`
			FROM '._DB_PREFIX_.'tag t
			LEFT JOIN '._DB_PREFIX_.'product_tag pt ON (pt.id_tag = t.id_tag)
			LEFT JOIN '._DB_PREFIX_.'lang l ON (l.id_lang = t.id_lang)
			WHERE l.iso_code = \''.pSQL($iso_code).'\'
			AND pt.`id_product`='.(int)$product_id);
	}

	public static function getProductPrices($product_id)
	{
		return Db::getInstance()->ExecuteS('
			SELECT TRUNCATE(p.price , 2) as price, c.iso_code
			FROM '._DB_PREFIX_.'currency c, '._DB_PREFIX_.'product p
			WHERE p.`id_product`='.(int)$product_id.'
			AND c.id_currency = (SELECT value FROM '._DB_PREFIX_.'configuration WHERE name = \'PS_CURRENCY_DEFAULT\')
			UNION
			SELECT TRUNCATE(p.price , 2) as price, c.iso_code
			FROM '._DB_PREFIX_.'specific_price p, '._DB_PREFIX_.'currency c
			WHERE p.id_currency = c.id_currency
			AND p.`id_product`='.(int)$product_id);
	}

	public static function getProductAttributes($product_id, $iso_code)
	{
		return Db::getInstance()->ExecuteS('
			SELECT DISTINCT al.id_attribute, al.name,  agl.public_name as groupname
			FROM '._DB_PREFIX_.'product_attribute pa, '._DB_PREFIX_.'product_attribute_combination pac, '._DB_PREFIX_.'attribute_lang al
			, '._DB_PREFIX_.'lang l, '._DB_PREFIX_.'attribute a, '._DB_PREFIX_.'attribute_group_lang agl, '._DB_PREFIX_.'attribute_group ag
			WHERE pa.id_product_attribute = pac.id_product_attribute
			AND pac.id_attribute = al.id_attribute
			AND pac.id_attribute = a.id_attribute
			AND al.id_lang = l.id_lang
			AND agl.id_attribute_group = a.id_attribute_group
			AND agl.id_lang = l.id_lang
			AND pa.id_product = '.(int)$product_id.'
			AND l.iso_code = \''.pSQL($iso_code).'\';');

	}

	public static function getProductFeatures($product_id, $iso_code)
	{
		return Db::getInstance()->ExecuteS('
			SELECT DISTINCT fp.id_product, fl.id_feature, fl.name, fvl.id_feature_value, fvl.value
			FROM '._DB_PREFIX_.'feature_lang fl, '._DB_PREFIX_.'lang l, '._DB_PREFIX_.'feature_product fp, '._DB_PREFIX_.'feature_value fv,
			'._DB_PREFIX_.'feature_value_lang fvl
			WHERE fl.id_lang = l.id_lang
			AND fvl.id_lang = l.id_lang
			AND fp.id_feature = fl.id_feature
			AND fp.id_feature_value = fv.id_feature_value
			AND fl.id_feature = fv.id_feature
			AND fv.id_feature_value = fvl.id_feature_value
			AND fp.id_product = '.intval($product_id).'
			AND l.iso_code=\''.pSQL($iso_code).'\';');
	}

	public static function getProductAttributesByAttributeId($product_id_attribute)
	{
		return Db::getInstance()->ExecuteS('
			SELECT id_attribute
			FROM '._DB_PREFIX_.'product_attribute_combination
			WHERE id_product_attribute = '.intval($product_id_attribute).';');
	}

	public static function insertProduct($product)
	{
		$multishop = (Context::getContext()->shop->isFeatureActive()) ? Shop::getContextShopID(true) : 1;
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_product_repository` VALUES('.(int)$product->productId.'
			, '.(int)$multishop.' ,\''.pSQL($product->updateDate).'\');');
	}

	public static function updateProduct($product)
	{
		$multishop = (Context::getContext()->shop->isFeatureActive()) ? Shop::getContextShopID(true) : 1;
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ae_product_repository` SET date_upd = \''.pSQL($product->updateDate).'\'
			WHERE id_product = '.(int)$product->productId.' AND id_shop = '.(int)$multishop.';');
	}

	public static function deleteProduct($product)
	{
		$multishop = (Context::getContext()->shop->isFeatureActive()) ? Shop::getContextShopID(true) : 1;
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ae_product_repository` WHERE id_product = '.(int)$product->productId.'
			AND id_shop = '.(int)$multishop.';');
	}

	public static function countCart($clause)
	{
		return Db::getInstance()->executeS('SELECT DISTINCT count(*) as celement '.$clause);
	}

	public static function getCartList($sql)
	{
		return Db::getInstance()->ExecuteS($sql);
	}

	public static function getCartsProductAttributes($product_id_attribute)
	{
		return self::getProductAttributesByAttributeId($product_id_attribute);
	}

	public static function newMemberCartClause($bulk)
	{
		$sql = array();
		$multishop = Context::getContext()->shop->isFeatureActive() ? 'AND c.id_shop = '.Shop::getContextShopID(true) : '';
		$sql[0] = 'SELECT c.id_cart, cp.id_product, cp.id_product_attribute, cp.quantity, cp.date_add, c.id_customer ';
		$sql[1] = 'FROM '._DB_PREFIX_.'cart_product cp, '._DB_PREFIX_.'cart c
		WHERE (cp.id_cart, cp.id_product, cp.id_product_attribute) NOT IN (SELECT id_cart, id_product,
		id_product_attribute FROM '._DB_PREFIX_.'ae_cart_repository)
		AND cp.id_cart = c.id_cart
		AND cp.id_product IN (SELECT p.id_product FROM '._DB_PREFIX_.'product p)
		AND c.id_customer IN (SELECT id_customer FROM '._DB_PREFIX_.'customer)
		AND c.id_customer <> 0
		'.$multishop.'
		LIMIT 0,'.intval($bulk).';';
		return $sql;
	}

	public static function updateMemberCartClause($bulk)
	{
		$sql = array();
		$multishop = Context::getContext()->shop->isFeatureActive() ? 'AND c.id_shop = '.Shop::getContextShopID(true) : '';
		$sql[0] = 'SELECT c.id_cart, cp.id_product, cp.id_product_attribute, cp.quantity, cp.date_add, c.id_customer ';
		$sql[1] = 'FROM '._DB_PREFIX_.'cart_product cp, '._DB_PREFIX_.'cart c
		WHERE cp.date_add > (SELECT date_add FROM '._DB_PREFIX_.'ae_cart_repository aecp
		WHERE aecp.id_cart = cp.id_cart AND aecp.id_product = cp.id_product
		AND aecp.id_product_attribute = cp.id_product_attribute) AND cp.id_cart = c.id_cart
		AND cp.id_product IN (SELECT p.id_product FROM '._DB_PREFIX_.'product p)
		AND c.id_customer IN (SELECT id_customer FROM '._DB_PREFIX_.'customer)
		AND c.id_customer <> 0
		'.$multishop.'
		LIMIT 0,'.intval($bulk).';';
		return $sql;
	}

	public static function deleteMemberCartClause($bulk)
	{
		$sql = array();
		$multishop = Context::getContext()->shop->isFeatureActive() ? 'AND c.id_shop = '.Shop::getContextShopID(true) : '';
		$sql[0] = 'SELECT aecp.id_cart, aecp.id_product, aecp.id_product_attribute, aecp.quantity, aecp.date_add, c.id_customer ';
		$sql[1] = 'FROM '._DB_PREFIX_.'ae_cart_repository aecp, '._DB_PREFIX_.'cart c
		WHERE (aecp.id_cart, aecp.id_product, aecp.id_product_attribute)
		NOT IN (SELECT cp.id_cart, cp.id_product, cp.id_product_attribute FROM '._DB_PREFIX_.'cart_product cp
		WHERE aecp.id_cart = cp.id_cart AND aecp.id_product = cp.id_product AND aecp.id_product_attribute = cp.id_product_attribute)
		AND aecp.id_cart = c.id_cart
		AND c.id_customer IN (SELECT id_customer FROM '._DB_PREFIX_.'customer)
		AND c.id_customer <> 0
		'.$multishop.'
		LIMIT 0,'.intval($bulk).';';
		return $sql;
	}

	public static function newGuestCartClause($cart_id)
	{
		$multishop = Context::getContext()->shop->isFeatureActive() ? 'AND c.id_shop = '.Shop::getContextShopID(true) : '';
		$sql = 'SELECT c.id_cart, cp.id_product, cp.id_product_attribute, cp.quantity, cp.date_add, c.id_guest
		FROM '._DB_PREFIX_.'cart_product cp, '._DB_PREFIX_.'cart c
		WHERE cp.id_cart = c.id_cart
		AND (cp.id_cart, cp.id_product, cp.id_product_attribute)
		NOT IN (SELECT id_cart, id_product, id_product_attribute FROM '._DB_PREFIX_.'ae_cart_repository)
		'.$multishop.'
		AND c.id_cart = '.(int)$cart_id;
		return $sql;
	}

	public static function deleteGuestCartClause($cart_id)
	{
		$multishop = Context::getContext()->shop->isFeatureActive() ? 'AND c.id_shop = '.Shop::getContextShopID(true) : '';
		$sql = 'SELECT aecp.id_cart, aecp.id_product, aecp.id_product_attribute, aecp.quantity, aecp.date_add, c.id_guest
		FROM '._DB_PREFIX_.'ae_cart_repository aecp, '._DB_PREFIX_.'cart c
		WHERE (aecp.id_cart, aecp.id_product, aecp.id_product_attribute)
		NOT IN (SELECT cp.id_cart, cp.id_product, cp.id_product_attribute FROM '._DB_PREFIX_.'cart_product cp
		WHERE aecp.id_cart = cp.id_cart AND aecp.id_product = cp.id_product AND aecp.id_product_attribute = cp.id_product_attribute)
		AND aecp.id_cart = c.id_cart
		'.$multishop.'
		AND c.id_cart = '.(int)$cart_id;
		return $sql;
	}

	public static function countOrder()
	{
		$multishop = Context::getContext()->shop->isFeatureActive() ? 'AND o.id_shop = '.Shop::getContextShopID(true) : '';
		return Db::getInstance()->executeS('SELECT count(*) as corder
			FROM '._DB_PREFIX_.'orders o
			WHERE o.id_order NOT IN (SELECT id_order FROM '._DB_PREFIX_.'ae_order_repository)
			AND o.id_customer IN (SELECT id_customer FROM '._DB_PREFIX_.'customer)
			AND o.id_order IN (SELECT id_order FROM '._DB_PREFIX_.'order_detail)
			AND id_customer <> 0
			'.$multishop);
	}

	public static function getOrderList($bulk)
	{
		$multishop = Context::getContext()->shop->isFeatureActive() ? 'AND o.id_shop = '.Shop::getContextShopID(true) : '';
		$total_paid = (_PS_VERSION_) >= '1.5' ? 'o.total_paid_tax_excl' : 'o.total_products as total_paid_tax_excl';
		return Db::getInstance()->ExecuteS('
			SELECT o.id_order, o.date_add, o.date_upd, o.id_cart, o.id_customer, '.$total_paid.'
			FROM '._DB_PREFIX_.'orders o
			WHERE o.id_order NOT IN (SELECT id_order FROM '._DB_PREFIX_.'ae_order_repository)
			AND o.id_customer IN (SELECT id_customer FROM '._DB_PREFIX_.'customer)
			AND o.id_order IN (SELECT id_order FROM '._DB_PREFIX_.'order_detail)
			AND id_customer <> 0
			'.$multishop.'
			LIMIT 0,'.intval($bulk).';');
	}

	public static function getOrderLines($order_id)
	{
		return Db::getInstance()->ExecuteS('SELECT product_id, product_attribute_id, product_quantity
			FROM '._DB_PREFIX_.'order_detail
			WHERE id_order = '.intval($order_id).'
			AND product_id IN (SELECT p.id_product FROM '._DB_PREFIX_.'product p);');
	}

	public static function getOrdersProductAttributes($product_id_attribute)
	{
		return self::getProductAttributesByAttributeId($product_id_attribute);
	}

	public static function countAction()
	{
		return Db::getInstance()->getValue('SELECT count(*) as countElement FROM `'._DB_PREFIX_.'ae_guest_action_repository`');
	}

	public static function getActionList($bulk)
	{
		return Db::getInstance()->executeS('
			SELECT id_guest as id, action
			FROM '._DB_PREFIX_.'ae_guest_action_repository
			ORDER BY id ASC
			LIMIT 0,'.intval($bulk).';');
	}

	public static function getGuestActionList($guest_id)
	{
		return Db::getInstance()->executeS('SELECT action
			FROM '._DB_PREFIX_.'ae_guest_action_repository
			WHERE id_guest = \''.pSQL((string)$guest_id).'\'');
	}

	public static function insertAction($action)
	{
		$class = new ReflectionObject($action);
		if ($class->hasProperty('memberId'))
		{
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_member_action_repository` VALUES('.(int)$action->memberId.'
				, \''.pSQL(serialize($action)).'\');');
		}
		else if ($class->hasProperty('guestId'))
		{
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_guest_action_repository` VALUES(\''.pSQL($action->guestId).'\'
				, \''.pSQL(serialize($action)).'\');');
		}
	}

	public static function deleteAction($action)
	{
		$class = new ReflectionObject($action);
		if ($class->hasProperty('memberId'))
		{
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ae_member_action_repository` WHERE id_member = '.(int)$action->memberId.'
				AND action = \''.pSQL(serialize($action)).'\';');
		}
		else if ($class->hasProperty('guestId'))
		{
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ae_guest_action_repository` WHERE id_guest = \''.pSQL($action->guestId).'\'
				AND action = \''.pSQL(serialize($action)).'\';');
		}
	}

	public static function insertOrder($order)
	{
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_order_repository` VALUES('.(int)$order->id.', \''.pSQL($order->addDate).'\'
			, \''.pSQL($order->updateDate).'\');');
	}

	public static function insertCart($cart)
	{
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_cart_repository` VALUES('.(int)$cart->id.', '.(int)$cart->orderLine->productId.'
			, '.(int)$cart->productAttributesId.',
			'.(int)$cart->orderLine->quantity.', \''.pSQL($cart->addDate).'\');');
	}

	public static function updateCart($cart)
	{
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ae_cart_repository` SET date_add = \''.pSQL($cart->addDate).'\',
			quantity = '.(int)$cart->orderLine->quantity.'
			WHERE id_cart = '.(int)$cart->id.' AND id_product = '.(int)$cart->orderLine->productId.'
			AND id_product_attribute = '.(int)$cart->productAttributesId.';');
	}

	public static function deleteCart($cart)
	{
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ae_cart_repository` WHERE id_cart = '.(int)$cart->id.'
			AND id_product = '.(int)$cart->orderLine->productId.'
			AND id_product_attribute = '.(int)$cart->productAttributesId.';');
	}

	public static function getCartGroup($cart_id)
	{
		return Db::getInstance()->getValue('SELECT cgroup FROM '._DB_PREFIX_.'ae_cart_ab_testing WHERE id_cart = '.(int)$cart_id);
	}

	public static function setCartGroup($cart_id, $group, $person_id, $ip)
	{
		Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'ae_cart_ab_testing(id_cart, id_guest, cgroup, date_add, ip)
			VALUES('.(int)$cart_id.', \''.pSQL($person_id).'\' , \''.pSQL($group).'\', NOW(), \''.pSQL($ip).'\')
			ON DUPLICATE KEY UPDATE cgroup = \''.pSQL($group).'\' , ip = \''.pSQL($ip).'\', id_guest = \''.pSQL($person_id).'\'');
	}

	public static function getRecommendationSelect()
	{
		if (version_compare(_PS_VERSION_, '1.4.0.1', '>='))
		{
			$select = '
			SELECT p.*, pa.id_product_attribute, pl.description, pl.description_short, pl.available_now, pl.available_later, pl.link_rewrite,
			pl.meta_description, pl.meta_keywords, pl.meta_title, pl.name, i.id_image, il.legend, m.name as manufacturer_name,
			tl.name as tax_name, t.rate, cl.name as category_default, cl.`link_rewrite` as category_rewrite
			';
		}
		else
		{
			$select = '
			SELECT p.*, pa.`id_product_attribute`, pl.`description`, pl.`description_short`, pl.`available_now`,
			pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`,
			pl.`meta_keywords`, pl.`meta_title`, pl.`name`, i.`id_image`, il.`legend`, m.`name` AS manufacturer_name,
			tl.`name` AS tax_name, t.`rate`, cl.`name` AS category_default,
			(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1) - IF((DATEDIFF(`reduction_from`, CURDATE()) <= 0
				AND DATEDIFF(`reduction_to`, CURDATE()) >=0) OR `reduction_from` = `reduction_to`,
			IF(`reduction_price` > 0, `reduction_price`,
			(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1) * `reduction_percent` / 100)),0)) AS orderprice
			';
		}

		return $select;
	}

	public static function getRecommendationTax()
	{
		$tax = '
		LEFT JOIN '._DB_PREFIX_.'tax_rule tr ON (p.id_tax_rules_group = tr.id_tax_rules_group AND tr.id_country = 1 AND tr.id_state = 0)
		LEFT JOIN '._DB_PREFIX_.'tax t ON (t.id_tax = tr.id_tax)
		LEFT JOIN '._DB_PREFIX_.'tax_lang tl ON (t.id_tax = tl.id_tax AND tl.id_lang = 1)
		';
		return $tax;
	}

	public static function renderRecommendation($select, $tax, $product_pool, $lang_id)
	{
		$products = Db::getInstance()->ExecuteS('
			'.$select.'
			FROM '._DB_PREFIX_.'product p
			LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product)
			LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (p.id_product = pa.id_product AND default_on = 1)
			LEFT JOIN '._DB_PREFIX_.'manufacturer m ON m.id_manufacturer = p.id_manufacturer
			LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = p.id_product AND i.cover = 1)
			LEFT JOIN '._DB_PREFIX_.'image_lang il ON (il.id_image = i.id_image)
			LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = p.id_category_default)
			'.$tax.'
			WHERE p.id_product IN ('.implode("," , array_map('intval', $product_pool)).')
			AND pl.id_lang = '.(int)$lang_id.'
			AND cl.id_lang = '.(int)$lang_id.'
			AND p.active = 1
			GROUP BY p.id_product
			ORDER BY FIELD(p.id_product,'.implode("," , array_map('intval', $product_pool)).')');

		$products = Product::getProductsProperties((int)$lang_id, $products);

		return $products;
	}

	public static function insertNotification($notification)
	{
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_notification`
			VALUES(\''.pSQL($notification->id).'\', \''.pSQL($notification->date).'\', 0);');
	}

	public static function insertTranslation($translation)
	{
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_notification_lang` VALUES(\''.pSQL($translation->language).'\'
			, \''.pSQL($translation->title).'\' , \''.pSQL($translation->text).'\' , \''.pSQL($translation->notificationId).'\');');
	}

	public static function updateNotification($notification)
	{
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ae_notification` SET nread = 1 WHERE id_notification = \''.pSQL($notification->id).'\';');
	}

	public static function getNotifications($lang)
	{
		return Db::getInstance()->ExecuteS('SELECT n.id_notification, nl.title, nl.text FROM '._DB_PREFIX_.'ae_notification n,
			`'._DB_PREFIX_.'ae_notification_lang` nl
			WHERE nread = 0 and n.id_notification = nl.id_notification
			AND nl.language = (SELECT iso_code FROM `'._DB_PREFIX_.'lang` WHERE id_lang = '.intval($lang).')');
	}

	public static function log($severity, $message)
	{
		$multishop = (Context::getContext()->shop->isFeatureActive()) ? Shop::getContextShopID(true) : 1;
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ae_log` VALUES(\'\', NOW(), \''.pSQL($severity).'\'
			, \''.pSQL($message).'\', '.(int)$multishop.');');
		error_log($severity.' '.$message);
	}

	public static function getLog()
	{
		$multishop = (Context::getContext()->shop->isFeatureActive()) ? Shop::getContextShopID(true) : 1;
		return Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'ae_log WHERE id_shop = '.intval($multishop).'
			ORDER BY date_add DESC LIMIT 0, 500');
	}

	public static function getHost()
	{
		return Configuration::get('AE_CONF_HOST');
	}

	public static function getPort()
	{
		return Configuration::get('AE_CONF_PORT');
	}

	public static function getSiteId()
	{
		return Configuration::get('AE_SITE_ID');
	}

	public static function getSecurityKey()
	{
		return Configuration::get('AE_SECURITY_KEY');
	}

	public static function getStartDate()
	{
		return Configuration::get('AE_LAST_SYNC_START');
	}

	public static function getEndDate()
	{
		return Configuration::get('AE_LAST_SYNC_END');
	}

	public static function getLock()
	{
		return Configuration::get('AE_LAST_SYNC_LOCK');
	}

	public static function getStep()
	{
		return Configuration::get('AE_LAST_SYNC_STEP');
	}

	public static function authentication($email, $password, $site_id, $security_key)
	{
		Configuration::updateValue('AE_LOGIN', $email);
		Configuration::updateValue('AE_PASSWORD', $password);
		Configuration::updateValue('AE_SITE_ID', $site_id);
		Configuration::updateValue('AE_SECURITY_KEY', $security_key);
	}

	public static function setStartDate($timestamp)
	{
		Configuration::updateValue('AE_LAST_SYNC_START', $timestamp);
	}

	public static function setEndDate($timestamp)
	{
		Configuration::updateValue('AE_LAST_SYNC_END', $timestamp);
	}

	public static function setLock($state)
	{
		Configuration::updateValue('AE_LAST_SYNC_LOCK', $state);
	}

	public static function setStep($step)
	{
		Configuration::updateValue('AE_LAST_SYNC_STEP', $step);
	}

	public static function getShopName()
	{
		return Configuration::get('PS_SHOP_NAME');
	}

	public static function getActivity()
	{
		return Configuration::get('PS_SHOP_ACTIVITY');
	}

	public static function getSyncDiff()
	{
		return Configuration::get('AE_SYNC_DIFF');
	}

	public static function getLocalHosts()
	{
		return Configuration::get('AE_HOST_LIST');
	}

	public static function setLocalHosts($hosts)
	{
		Configuration::updateValue('AE_HOST_LIST', $hosts);
	}

	public static function getAbTestingPercentage()
	{
		return Configuration::get('AE_A_TESTING');
	}

	public static function setAbTestingPercentage($percentage)
	{
		Configuration::updateValue('AE_A_TESTING', $percentage);
	}

	public static function getBlackListIp()
	{
		return Configuration::get('AE_AB_TESTING_BLACKLIST');
	}

	public static function setBlackListIp($black_list)
	{
		Configuration::updateValue('AE_AB_TESTING_BLACKLIST', $black_list);
	}

	public static function getBackOfficeToken()
	{
		return Configuration::get('AE_BACKOFFICE_TOKEN');
	}

	public static function isConfig()
	{
		if (!AELibrary::isEmpty(Configuration::get('AE_SITE_ID'))
		&& !AELibrary::isEmpty(Configuration::get('AE_SECURITY_KEY')))
			return true;
		return false;
	}

	public static function isLastSync()
	{
		if (!AELibrary::isEmpty(Configuration::get('AE_LAST_SYNC_END')))
			return true;
		return false;
	}

}
