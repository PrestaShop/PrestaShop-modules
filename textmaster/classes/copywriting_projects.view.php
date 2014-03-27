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

class TextMasterCopywritingProjectsView extends TextMasterView
{	
    function __construct($module_instance)
    {
		parent::__construct($module_instance);
		$this->table = 'project';
    }
    
	public function initList(&$helper = false)
	{
		$helper = new HelperList();
		$helper->title = array($this->module_instance->displayName, $this->module_instance->l('Copywriting projects', 'copywriting_projects.view'));
		return parent::initList($helper);
	}
	
	public function getData($filter = true)
	{
        $projects = $this->textmasterAPI->getProjects('copywriting');
		
		if ($filter)
		{
			$this->manageFilter();
			$projects = $this->filterArray($projects);
		}
		
		return $projects;
	}
}