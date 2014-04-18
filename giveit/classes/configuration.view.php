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

if (!defined('_PS_VERSION_'))
	exit;

require_once (_GIVEIT_MODELS_DIR_.'Category.php');

class GiveItConfigurationView {
	private $module_instance;

	private $context;

	public function __construct()
	{
		$this->module_instance = Module::getInstanceByName('giveit');
		$this->context = Context::getContext();
	}

	public function getConfigurationForm()
	{
		$this->context->smarty->assign(array(
			'saveAction' => $this->module_instance->module_url.'&menu=configuration',
			'public_key' => Configuration::get(GiveIt::PUBLIC_KEY),
			'data_key' => Configuration::get(GiveIt::DATA_KEY),
			'private_key' => Configuration::get(GiveIt::PRIVATE_KEY),
			'button_active' => (int)Configuration::get(GiveIt::BUTTON_ACTIVE),
			'button_position' => Configuration::get(GiveIt::BUTTON_POSITION),
			'mode' => Configuration::get(GiveIt::MODE),
		));

		if (version_compare(_PS_VERSION_, '1.6', '>='))
			return $this->context->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/configuration_ps16.tpl');
		else
			return $this->context->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/configuration.tpl');
	}

	private function getCategories($radio = false)
	{
		$root_category = Category::getRootCategory();
		$root_category = array('id_category' => $root_category->id_category, 'name' => $root_category->name);
		$selected_cat = $radio ? array(Category::getRootCategory()->id) : GiveItCategory::getCategories();

		$category_values = array('trads' => array(
													'Root' => $root_category,
													'selected' => $this->module_instance->l('selected'),
													'Collapse All' => $this->module_instance->l('Collapse All'),
													'Expand All' => $this->module_instance->l('Expand All'),
													'Check All' => $this->module_instance->l('Check All'),
													'Uncheck All' => $this->module_instance->l('Uncheck All')),
								'selected_cat' => $selected_cat,
								'input_name' => 'categoryBox[]',
								'use_radio' => $radio,
								'use_search' => false,
								'disabled_categories' => array(4),
								'top_category' => version_compare(_PS_VERSION_, '1.5', '<') ? $this->getTopCategory() : Category::getTopCategory(),
								'use_context' => true);

		$this->context->smarty->assign(array('category_values' => $category_values));
	}

	public function getTopCategory($id_lang = null)
	{
		if (is_null($id_lang))
			$id_lang = $this->context->language->id;
		$id_category = Db::getInstance()->getValue('
			SELECT `id_category`
			FROM `'._DB_PREFIX_.'category`
			WHERE `id_parent` = 0');
		return new Category($id_category, $id_lang);
	}

	private function getCategoryTree($radio_buttons = false)
	{
		include_once(_GIVEIT_CLASSES_DIR_.'/HelperTreeCategories.php');

		$selected_cat = (!$radio_buttons) ? GiveItCategory::getCategories() : array(Category::getRootCategory()->id);

		$tree = new GiveItHelperTreeCategoriesCore('associated-categories-tree', 'Associated categories');
		$tree->setRootCategory(Category::getRootCategory()->id)->setUseCheckBox(!$radio_buttons)->setUseSearch(true)->setSelectedCategories($selected_cat);

		$tree->render();
	}

	public function getConfigurationCategoryForm()
	{
		$this->context->smarty->assign(array(
			'saveAction' => $this->module_instance->module_url.'&menu=configuration_category',
			'categories' => $this->getCategories()
		));

		if (version_compare(_PS_VERSION_, '1.6', '>='))
		{
			$this->getCategoryTree();
			return $this->context->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/configuration_category_ps16.tpl');
		}
		else
			return $this->context->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/configuration_category.tpl');
	}

	public function getConfigurationProductForm()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			echo '<script src="'._GIVEIT_JS_URI_.'backoffice.js" type="text/javascript"></script>';
		else
			$this->context->controller->addJS(_GIVEIT_JS_URI_.'backoffice.js');

		$this->context->smarty->assign(array(
			'saveAction' => $this->module_instance->module_url.'&menu=configuration_product',
			'categories' => $this->getCategories(true),
			'give_it_token' => sha1(_COOKIE_KEY_.$this->module_instance->name),
			'id_shop' => (int)$this->context->shop->id, 'id_lang' => (int)$this->context->language->id
		));

		if (version_compare(_PS_VERSION_, '1.6', '>='))
		{
			$this->getCategoryTree(true);
			return $this->context->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/configuration_product_ps16.tpl');
		}
		else
			return $this->context->smarty->fetch(_GIVEIT_TPL_DIR_.'admin/configuration_product.tpl');
	}

	public function getProductsByCategory($id_category, $id_shop, $id_lang, $order_by = '', $order_way = '', $filter = '', $limit = '', $count = false)
	{
		if (!(int)$id_category)
			$id_category = (int)Category::getRootCategory()->id;

		if ($count)
			$limit = '';

		$ordering = ' ORDER BY `id_product` ASC ';
		
		$filtering = '';
		if ($order_by && $order_way)
		{
			$order_way = strtoupper($order_way);
			if (!in_array($order_way, array('ASC', 'DESC')))
				$order_way = 'ASC';
				
			$ordering = ' ORDER BY `'.bqSQL($order_by).'` '.pSQL($order_way);
		}

		$filtering = '';
		if ($filter && !$count)
			foreach ($filter as $item => $value)
				$filtering .= ' AND `'.bqSQL($item).'` LIKE "%'.pSQL($value).'%"';

		$shop_field = version_compare(_PS_VERSION_, '1.5', '<') ? ' ' : ' AND pl.`id_shop` = "'.(int)$id_shop.'"';

		$products = DB::getInstance()->executeS('
			SELECT
				p.`id_product` 		AS `id_product`,
				pl.`name` 			AS `name`,
				pl.`link_rewrite` 	AS `link_rewrite`,
				i.`id_image` 		AS `id_image`
			FROM `'._DB_PREFIX_.'category_product` cp
			LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = cp.`id_product`)
			LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = cp.`id_product` AND i.`cover` = "1")
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = cp.`id_product` AND pl.`id_lang` = "'.(int)$id_lang.'" '.$shop_field.')
			WHERE cp.`id_category` = "'.(int)$id_category.'" '.$filtering.$ordering.pSQL($limit));

		if (!$products)
			$products = array();

		foreach ($products as &$product)
		{
			$product_obj = new Product((int)$product['id_product'], null, $this->context->language->id);
			$product['combinations'] = $this->module_instance->getProductCombinations($product_obj);
			$product['image'] = $this->getImage($product['id_product'], $product['id_image'], $product['link_rewrite']);
		}

		return $products;
	}

	private function getImage($id_product, $id_image, $link_rewrite)
	{
		$id_image = Product::defineProductImage(array('id_image' => $id_image, 'id_product' => $id_product), $this->context->language->id);
		$img_profile = (version_compare(_PS_VERSION_, '1.5', '<')) ? 'small' : ImageType::getFormatedName('small');
		$image_link = $this->context->link->getImageLink($link_rewrite, $id_image, $img_profile);
		if (!$image_link)
			return $this->module_instance->l('Image');
		else
			return $image_link;
	}
}