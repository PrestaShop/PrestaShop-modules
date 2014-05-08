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

class TextMasterProofreadingProjectsView extends TextMasterView
{	
    function __construct($module_instance)
    {
		parent::__construct($module_instance);
		$this->table = 'project';
    }
    
	public function initList(&$helper = false)
	{
		//$helper = new HelperList();
		//$helper->title = array($this->module_instance->displayName, $this->module_instance->l('Proofreading projects', 'proofreading_projects.view'));
		//return parent::initList($helper);
		$projects = $this->getData();
		$list_total = count($this->getData(false));
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
		
		$this->context->smarty->assign(array(
			'projects' => $projects,
			'list_total' => $list_total,
			'page' => $page,
			'full_url' => TextMaster::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->module_instance->name.'&menu='.Tools::getValue('menu').'&token='.Tools::getAdminTokenLite('AdminModules'),
			'menu' => 'proofreading',
			'selected_pagination' => $selected_pagination,
			'pagination' => $pagination,
			'total_pages' => $total_pages
		));
		
		$fields = array('id_project', 'name', 'language_from', 'language_to', 'status');
		foreach ($fields AS $key => $values)
			if ($this->context->cookie->__isset('projectFilter_'.$values))
				$this->context->smarty->assign('cookie_projectFilter_'.$values, $this->context->cookie->{'projectFilter_'.$values});
		
		$fields = array('date_add', 'date_upd');
		foreach ($fields AS $key => $values)
			if ($this->context->cookie->__isset('projectFilter_'.$values))
			{
				if (version_compare(_PS_VERSION_, '1.5', '<'))
					$date = unserialize($this->context->cookie->{'projectFilter_'.$values});
				else
					$date = Tools::unSerialize($this->context->cookie->{'projectFilter_'.$values});
				
				if (!empty($date[0]))
					$this->context->smarty->assign('cookie_projectFilter_'.$values.'_0', pSQL($date[0]));
				
				if (!empty($date[1]))
					$this->context->smarty->assign('cookie_projectFilter_'.$values.'_1', pSQL($date[1]));
			}
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			includeDatepicker(array('projectFilter_date_add_0', 'projectFilter_date_add_1', 'projectFilter_date_upd_0', 'projectFilter_date_upd_1'));
		else
			$this->context->controller->addJqueryUI('ui.datepicker');
		return $this->context->smarty->fetch(_PS_MODULE_DIR_.'textmaster/views/templates/admin/main_list.tpl');
	}
	
	public function getData($filter = true)
	{
        $projects = $this->textmasterAPI->getProjects('proofreading');
		
		if ($filter)
		{
			$this->manageFilter();
			$projects = $this->filterArray($projects);
		}

		return $projects;
	}
}