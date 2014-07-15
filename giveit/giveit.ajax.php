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

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/giveit.php'); /*module core*/
include_once(_GIVEIT_CLASSES_DIR_.'/configuration.view.php');

$module_instance = new GiveIT;
if (!Tools::isSubmit('token') || (Tools::isSubmit('token')) && Tools::getValue('token') != sha1(_COOKIE_KEY_.$module_instance->name)) exit;

if (Tools::isSubmit('updateCombinationSettings'))
{
	if ($combinations = Tools::getValue('combinations'))
	{
		$id_product = (int)Tools::getValue('id_product');
		GiveItProduct::clearProductAssociations($id_product);

		foreach ($combinations as $id_product_attribute => $display_button)
		{
			if ($display_button !== '')
			{
				$product = new GiveItProduct;
				$product->id_product = $id_product;
				$product->id_product_attribute = (int)$id_product_attribute;
				$product->display_button = (int)$display_button;

				if (!$product->save())
					die(Tools::jsonEncode(
						array(
							'error' => sprintf(
								$module_instance->l('Error on save settings for product #%d and product combination #%d', 'giveit.ajax.php'),
								$product->id_product, $product->id_product_attribute
							)
						))
					);
			}
		}

		die(Tools::jsonEncode(array('success' => $module_instance->l('Product combination settings were successfully updated', 'giveit.ajax.php'))));
	}
}

if (Tools::isSubmit('getProductList'))
{
	$id_category = (int)Tools::getValue('id_category');
	$id_shop = (int)Tools::getValue('id_shop');
	$id_lang = (int)Tools::getValue('id_lang');
	$order_url = Tools::getValue('order_url', '');
	$filter = Tools::getValue('filtering');

	if ($filter)
		foreach ($filter as $item => $value)
		{
			$item = explode('.', $item);
			Context::getContext()->smarty->assign('cookie_productsListFilter_'.$item[1], $value);
		}

	$order_url = $order_url ? explode('/', $order_url) : '';
	$order_by = $order_url ? $order_url[0] : '';
	$order_way = $order_url ? $order_url[1] : '';

	$page = (int)Tools::getValue('current_page', '1');
	$per_page = (int)Tools::getValue('pagination');
	$start = ($per_page * $page) - $per_page;
	$limit = ' LIMIT '.$start.', '.$per_page.' ';


	$configuration_view_obj = new GiveItConfigurationView();
	$products_data = $configuration_view_obj->getProductsByCategory($id_category, $id_shop, $id_lang, $order_by, $order_way, $filter, $limit);

	$list_total = count($configuration_view_obj->getProductsByCategory($id_category, $id_shop, $id_lang, $order_by, $order_way, $filter, $limit, true));
	$pagination = version_compare(_PS_VERSION_, '1.6', '>=') ? array(20, 50, 100, 300, 1000) : array(20, 50, 100, 300);

	$total_pages = ceil($list_total / $per_page);

	if (!$total_pages)
		$total_pages = 1;

	$selected_pagination = Tools::getValue(
		'pagination',
		isset(Context::getContext()->cookie->{'project_pagination'}) ? Context::getContext()->cookie->{'project_pagination'} : null
	);

	Context::getContext()->smarty->assign(array(
		'products'              => $products_data,
		'page'                  => $page,
		'selected_pagination'   => $selected_pagination,
		'pagination'            => $pagination,
		'total_pages'           => $total_pages,
		'list_total'            => $list_total,
		'cookie_order_by'       => $order_by,
		'cookie_order_way'      => $order_way
	));

	if (version_compare(_PS_VERSION_, '1.6', '>='))
		die(Context::getContext()->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/selected_category_ps16.tpl'));
	else
		die(Context::getContext()->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/selected_category.tpl'));
}

if (Tools::isSubmit('getCombinationSetting'))
{
	$id_product     = (int)Tools::getValue('id_product');
	$id_combination = (int)Tools::getValue('id_combination');
	die(GiveItProduct::buttonIsDisplayed($id_product, $id_combination));
}

if (Tools::isSubmit('setCombinationSetting'))
{
	$id_product     = (int)Tools::getValue('id_product');
	$id_combination = (int)Tools::getValue('id_combination');
	$setting_value  = Tools::getValue('setting_value');
	$id_shop        = (int)Tools::getValue('id_shop');
	die(GiveItProduct::saveProductCombinationSetting($id_product, $id_combination, $setting_value, $id_shop));
}