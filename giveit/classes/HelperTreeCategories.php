<?php

class GiveItHelperTreeCategoriesCore extends HelperTreeCategoriesCore
{
	public function render($data = NULL)
	{
		if (!isset($data))
			$data = $this->getData();

		if (isset($this->_disabled_categories)
			&& !empty($this->_disabled_categories))
			$this->_disableCategories($data, $this->getDisabledCategories());

		if (isset($this->_selected_categories)
			&& !empty($this->_selected_categories))
			$this->_getSelectedChildNumbers($data, $this->getSelectedCategories());

		//Default bootstrap style of search is push-right, so we add this button first
		if ($this->useSearch())
		{
			$this->addAction(new TreeToolbarSearchCategories(
				'Find a category:',
				$this->getId().'-categories-search')
			);
			$this->setAttribute('use_search', $this->useSearch());
		}

		$collapse_all = new TreeToolbarLink(
			'Collapse All',
			'#',
			'$(\'#'.$this->getId().'\').tree(\'collapseAll\');$(\'#collapse-all-'.$this->getId().'\').hide();$(\'#expand-all-'.$this->getId().'\').show(); return false;',
			'icon-collapse-alt');
		$collapse_all->setAttribute('id', 'collapse-all-'.$this->getId());
		$expand_all = new TreeToolbarLink(
			'Expand All',
			'#',
			'$(\'#'.$this->getId().'\').tree(\'expandAll\');$(\'#collapse-all-'.$this->getId().'\').show();$(\'#expand-all-'.$this->getId().'\').hide(); return false;',
			'icon-expand-alt');
		$expand_all->setAttribute('id', 'expand-all-'.$this->getId());
		$this->addAction($collapse_all);
		$this->addAction($expand_all);

		if ($this->useCheckBox())
		{
			$check_all = new TreeToolbarLink(
				'Check All',
				'#',
				'checkAllAssociatedCategories($(\'#'.$this->getId().'\')); return false;',
				'icon-check-sign');
			$check_all->setAttribute('id', 'check-all-'.$this->getId());
			$uncheck_all = new TreeToolbarLink(
				'Uncheck All',
				'#',
				'uncheckAllAssociatedCategories($(\'#'.$this->getId().'\')); return false;',
				'icon-check-empty');
			$uncheck_all->setAttribute('id', 'uncheck-all-'.$this->getId());
			$this->addAction($check_all);
			$this->addAction($uncheck_all);
			$this->setNodeFolderTemplate('tree_node_folder_checkbox.tpl');
			$this->setNodeItemTemplate('tree_node_item_checkbox.tpl');
			$this->setAttribute('use_checkbox', $this->useCheckBox());
		}

		$this->setAttribute('selected_categories', $this->getSelectedCategories());
		$this->getContext()->smarty->assign('root_category', Configuration::get('PS_ROOT_CATEGORY'));

		/* Tree class render() function */
		
		//Adding tree.js
		$admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
		$admin_webpath = preg_replace('/^'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', '', $admin_webpath);
		$bo_theme = ((Validate::isLoadedObject($this->getContext()->employee)
			&& $this->getContext()->employee->bo_theme) ? $this->getContext()->employee->bo_theme : 'default');

		if (!file_exists(_PS_BO_ALL_THEMES_DIR_.$bo_theme.DIRECTORY_SEPARATOR.'template'))
			$bo_theme = 'default';

		$js_path = __PS_BASE_URI__.$admin_webpath.'/themes/'.$bo_theme.'/js/tree.js';
		if ($this->getContext()->controller->ajax)
			$html = '<script type="text/javascript" src="'.$js_path.'"></script>';
		else
			$this->getContext()->controller->addJs($js_path);

		//Create Tree Template
		$template = $this->getContext()->smarty;

		if (trim($this->getTitle()) != '' || $this->useToolbar())
		{
			//Create Tree Header Template
			$headerTemplate = $this->getContext()->smarty->createTemplate(
				$this->getTemplateFile($this->getHeaderTemplate()),
				$this->getContext()->smarty
			);
			$headerTemplate->assign($this->getAttributes())
				->assign(array(
				'title'   => $this->getTitle(),
				'toolbar' => $this->useToolbar() ? $this->renderToolbar() : null
			));
			$template->assign('header', $headerTemplate->fetch());
		}
		
		//Assign Tree nodes
		$template->assign($this->getAttributes())->assign(array(
			'id'    => $this->getId(),
			'nodes' => $this->renderNodes($data)
		));
	}
}