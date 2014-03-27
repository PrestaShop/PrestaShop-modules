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
if (!defined('_PS_VERSION_'))
	exit;

class TextMasterProjectView extends TextMasterView
{
	public function getProjectProperties()
	{
		$textmaster_data_with_cookies_manager_obj = new TextMasterDataWithCookiesManager();
		$products = $textmaster_data_with_cookies_manager_obj->getSelectedProductsIds();
		$id_product = '';
		foreach ($products as $product)
			$id_product.='&id_product[]='.$product;
			
		$textmaster_project = $textmaster_data_with_cookies_manager_obj->getAllProject();
		
		if (isset($textmaster_project['project_name']))
		{
			$projectObj = new stdClass;
			foreach($textmaster_project as $element => $data)
				$projectObj->$element = $data;
		}
		else
		{
			$projectObj = $this->settings_obj;
		}
		
		$this->context->smarty->assign(array(
			'id_product' => $id_product,
			'projectObj' => $projectObj,
			'textmaster_project_authors' => $textmaster_data_with_cookies_manager_obj->getSelectedAuthors(),
			'textmaster_project' => $textmaster_project
		));
		
		$this->module_instance->collectProjectPropertiesData($products, true);
	}
	
	private function getDocumentsData($documents = array())
	{
		$textmaster_data_with_cookies_manager_obj = new TextMasterDataWithCookiesManager();
		if (!empty($documents))
			return $documents;
		
		$result = array();
		$products = $textmaster_data_with_cookies_manager_obj->getSelectedProductsIds();
		$_POST = $textmaster_data_with_cookies_manager_obj->getAllProject();
		$args = $this->module_instance->formatProjectArguments();

		foreach ($products AS $product => $id_product)
		{
			$title = $this->module_instance->getProductTitle((int) $id_product);
			$id = '';
			$word_count = 0;
			$project_name = $textmaster_data_with_cookies_manager_obj->getProjectData('project_name') != '' ? $textmaster_data_with_cookies_manager_obj->getProjectData('project_name') : false;
			$price = $this->module_instance->quoteProject($args, $project_name, (int) $id_product);
			
			$word_counts = $this->module_instance->countProductWords($id_product);
            if (isset($args['project_data']) && is_array($args['project_data']))
                foreach($args['project_data'] as $element)
                    if (isset($word_counts[$element]) && isset($word_counts[$element][$args['language_from']]))
                        $word_count += $word_counts[$element][$args['language_from']];
			
			
			$result[] = array(
				'title' => $title,
				'id' => $id,
				'word_count' => $word_count,
				'price' => $price
			);
		}
		
		return $result;
	}
	
	public function getProjectSummary()
	{
		$textmaster_data_with_cookies_manager_obj = new TextMasterDataWithCookiesManager();
		
		$_POST = $textmaster_data_with_cookies_manager_obj->getAllProject();
		$args = $this->module_instance->formatProjectArguments();
		
		$total_price = $this->module_instance->quoteProject($args, $textmaster_data_with_cookies_manager_obj->getProjectData('project_name'), $textmaster_data_with_cookies_manager_obj->getSelectedProductsIds());

		
		$total_words = 0;
		$words = $this->module_instance->countProductWords($textmaster_data_with_cookies_manager_obj->getSelectedProductsIds());
		
		foreach ($words as $element => $langs)
		{
			if (in_array($element, $textmaster_data_with_cookies_manager_obj->getProjectProjectData()))
			{
				$lang = $textmaster_data_with_cookies_manager_obj->getProjectData('ctype').'_language_from';
				$lang = $textmaster_data_with_cookies_manager_obj->getProjectData($lang);
				if (isset($langs[$lang]))
					$total_words+=$langs[$lang];
			}
		}
		
		$this->context->smarty->assign(
			array(
				'summary' 					=> $textmaster_data_with_cookies_manager_obj,
				'languages' 				=> $this->textmasterAPI->getLanguages(),
				'categories'				=> $this->textmasterAPI->getCategories(),
				'audiences'					=> $this->textmasterAPI->getSelectOf('audiences'),
				'grammatical_persons' 		=> $this->textmasterAPI->getSelectOf('grammatical_persons'),
				'language_levels'			=> $this->textmasterAPI->getSelectOf('service_levels'),
				'vocabulary_levels'			=> $this->textmasterAPI->getSelectOf('vocabulary_levels'),
				'total_words' 				=> $total_words,
				'total_price' 				=> $total_price,
				'documents' 				=> $this->getDocumentsData()
			)
		);
	}
	
	public function getProductsSelectForm()
	{
		$root_category = Category::getRootCategory();
		$root_category = array('id_category' => $root_category->id_category, 'name' => $root_category->name);
		$selected_cat = Tools::getValue('id_category', Category::getRootCategory()->id);
		
		$category_values = array(
			'trads' => array(
				'Root' => $root_category,
				'selected' => $this->module_instance->l('selected', 'project.view'),
				'Collapse All' => $this->module_instance->l('Collapse All', 'project.view'),
				'Expand All' => $this->module_instance->l('Expand All', 'project.view')
			),
			'selected_cat' => array($selected_cat),
			'input_name' => 'id_category',
			'use_radio' => true,
			'use_search' => false,
			'disabled_categories' => array(4),
			'top_category' => version_compare(_PS_VERSION_, '1.5', '<') ? $this->module_instance->getTopCategory() : Category::getTopCategory(),
			'use_context' => true
		);
		
		$this->context->smarty->assign(array(
			'category_values' => $category_values
		));
	}
	
	public function getProductsSelectedProductsForm()
	{
		$projects = array();
		$list_total = 0;
		$pagination = array(20, 50, 100, 300);
		
		$page = (int)Tools::getValue('submitFilterproject');
		if (!$page)
			$page = 1;
		
		$total_pages = ceil($list_total / Tools::getValue('pagination', 50));

		if (!$total_pages) 
			$total_pages = 1;
		$selected_pagination = Tools::getValue(
			'pagination',
			isset($this->context->cookie->{'project_pagination'}) ? $this->context->cookie->{'project_pagination'} : null
		);
		
		$textmaster_data_with_cookies_manager_obj = new TextMasterDataWithCookiesManager();
		
		$this->context->smarty->assign(array(
			'products' => $projects,
			'list_total' => $list_total,
			'page' => $page,
			'menu' => 'new_project',
			'selected_pagination' => $selected_pagination,
			'pagination' => $pagination,
			'total_pages' => $total_pages,
			'selected_products_ids' => $textmaster_data_with_cookies_manager_obj->getSelectedProductsIds()
		));
	}
}