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

class TextMasterView
{
    protected $module_instance;
    
    protected $context;
    
    protected $table;
    
    protected $limit = '';
	
	protected $startpoint = 0;
	
    protected $textmasterAPI;
	
	public 	  $settings_obj;
	
	protected $tpl_dir;
	
	protected $tpl = '';
	
	protected $list_skip_actions;
    
    function __construct($module_instance)
    {
        if (!$module_instance) exit;
        $this->module_instance = $module_instance;
        $this->context = Context::getContext();
		$this->settings_obj = new TextMasterConfiguration();
        $this->textmasterAPI = new TextMasterAPI($module_instance, $this->settings_obj->api_key, $this->settings_obj->api_secret);
		$this->tpl_dir = TEXTMASTER_TPL_DIR .'admin/';
    }
	
	protected function display()
	{
		if ($this->tpl)
			return $this->context->smarty->fetch($this->tpl_dir.$this->tpl);
	}
    
    protected function manageFilter()
    {
		if (!$this->context->cookie->__isset($this->table.'_pagination'))
		{
			$this->context->cookie->{$this->table.'_pagination'} = 50;
		}
		
		/* reset filter */
		if (Tools::isSubmit('submitReset'.$this->table))
		{
			$this->clearFilterDataFromCookie();
		}
		
        /* submit filter */
		if (Tools::isSubmit('submitFilter'))
		{
			$this->addFilterDataToCookie();
		}
        
        /* pagination */
        if (Tools::isSubmit('submitFilter'.$this->table))
        {
            $page = (Tools::getValue('submitFilter' . $this->table)) ? (int)Tools::getValue('submitFilter' . $this->table) : 1;
            $this->startpoint = ($page * (int)Tools::getValue('pagination')) - (int)Tools::getValue('pagination');
            $this->limit = 'LIMIT ' . $this->startpoint . ', '.(int)Tools::getValue('pagination');
        }
    }
	
	private function addFilterDataToCookie()
	{
		foreach ($_POST as $key => $value)
			if (strpos($key, $this->table.'Filter_') !== false) // looking for filter values in $_POST
				$this->context->cookie->$key = !is_array($value) ? pSQL($value) : serialize($value);
	}
	
	private function clearFilterDataFromCookie()
	{
		$fields_list = array('id_project', 'name', 'language_from', 'language_to', 'date_add', 'date_upd', 'status');
		foreach ($fields_list as $key => $value)
			if ($this->context->cookie->__isset($this->table.'Filter_'.$value))
				$this->context->cookie->__unset($this->table.'Filter_'.$value);
	}
	
	/* returns HAVING statement for filter */
	protected function getFilterSQL()
	{
		$sql = '';
		
		foreach ($this->module_instance->fields_list as $key => $value)
		{
			if ($this->context->cookie->__isset($this->table.'Filter_'.$key))
			{
				$value = $this->context->cookie->{$this->table.'Filter_'.$key};
				if (Validate::isSerializedArray($value))
				{
					if (version_compare(_PS_VERSION_, '1.5', '<'))
						$date = unserialize($value);
					else
						$date = Tools::unSerialize($value);
					
					if (!empty($date[0]))
						$sql .= "`$key` > '".pSQL($date[0])."' AND ";
						
					if (!empty($date[1]))
						$sql .= "`$key` < '".pSQL($date[1])."' AND ";
				}
				elseif(!empty($value) || $value == '0')
				{
					$sql .= "`$key` LIKE '%".pSQL($value)."%' AND ";
				}
			}
		}
		
		if ($sql) $sql = 'HAVING ' . Tools::substr($sql,0,-4); // remove 'AND ' from the end of SQL
		
		return $sql;
	}
	
	protected function filterArray($items)
	{
		if (!$page = (int)Tools::getValue('submitFilter'.$this->table))
			$page = 1;
		
		$pagination = Tools::getValue('pagination', $this->context->cookie->{$this->table.'_pagination'}*$page);
		
		
		$rules = $this->collectFilterRules();
		$data = $this->applyRulesToArray($items, $rules);
		
		$orderWay = (Tools::getValue($this->table.'Orderway', 'desc') == 'asc') ? SORT_ASC : SORT_DESC;
		$orderBy = Tools::getValue($this->table.'Orderby', 'id_project');
		
		$this->array_sort_by_column($data, $orderBy, $orderWay);
		$data = array_slice($data, $this->startpoint, $pagination); // pagination for array
		return $data;
	}
	
	private function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{
		$sort_col = array();
		foreach ($arr as $key=> $row) {
			$sort_col[$key] = $row[$col];
		}
	
		array_multisort($sort_col, $dir, $arr);
	}
	
	private function collectFilterRules()
	{
		$rules = array();
		$fields = array('id_project', 'name', 'language_from', 'language_to', 'status');
		
		foreach ($fields as $key => $values)
		{
			if ($this->context->cookie->__isset($this->table.'Filter_'.$values))
			{
				$value = $this->context->cookie->{$this->table.'Filter_'.$values};
				if (!empty($value) || $value == '0')
				{
					$rule = array('key' => $values, 'value' => pSQL($value));
					$rules['find'][] = $rule;
				}
			}
		}
		
		$fields = array('date_add', 'date_upd');
		foreach ($fields as $key => $values)
		{
			if ($this->context->cookie->__isset($this->table.'Filter_'.$values))
			{
				if (version_compare(_PS_VERSION_, '1.5', '<'))
					$date = unserialize($this->context->cookie->{$this->table.'Filter_'.$values});
				else
					$date = Tools::unSerialize($this->context->cookie->{$this->table.'Filter_'.$values});
				
				if (!empty($date[0]))
					$rules['compare'][] = array('key' => $values, 'value' => pSQL($date[0]), 'operator' => '>');
				
				if (!empty($date[1]))
						$rules['compare'][] = array('key' => $values, 'value' => pSQL($date[1]), 'operator' => '<');
			}
		}
		return $rules;
		
		$rules = array();
		foreach ($this->module_instance->fields_list as $key => $value)
		{
			$rule = '';
			if ($this->context->cookie->__isset($this->table.'Filter_'.$key))
			{
				$value = $this->context->cookie->{$this->table.'Filter_'.$key};
				if (Validate::isSerializedArray($value))
				{
					if (version_compare(_PS_VERSION_, '1.5', '<'))
						$date = unserialize($value);
					else
						$date = Tools::unSerialize($value);
					
					if (!empty($date[0]))
						$rules['compare'][] = array('key' => $key, 'value' => pSQL($date[0]), 'operator' => '>');
						
					if (!empty($date[1]))
						$rules['compare'][] = array('key' => $key, 'value' => pSQL($date[1]), 'operator' => '<');
				}
				elseif((!empty($value) || $value == '0'))
				{
					$rule = array('key' => $key, 'value' => pSQL($value));
					$rules['find'][] = $rule;
				}
			}
		}
		return $rules;
	}
	
	/* Applies filter rules to list array,
		returns only those items, that matches filter
	*/
	private function applyRulesToArray($items, $rules)
	{
		foreach ($items as $key => &$item)
		{
			$found_match = true;
			
			if (isset($rules['find']))
				foreach ($rules['find'] as $rule)
					if (isset($item[$rule['key']]) && $rule['value']) /* checking if field exists in list array and search value is not empty */
						if (stripos($item[$rule['key']], $rule['value']) === false) // looking for value in list array
							$found_match &= false;
							
			if (isset($rules['compare']))
				foreach ($rules['compare'] as $rule)
					if (isset($item[$rule['key']]) && $rule['value']) /* checking if field exists in list array and search value is not empty */
					{
						if (!$this->compareDates($item[$rule['key']], $rule['value'], $rule['operator']))
							$found_match &= false;
					}
			
			if (!$found_match) // if not found, row will be dismissed
				unset($items[$key]); 
		}
		
		return $items;
	}
	
	private function compareDates($date1, $date2, $operator = '>')
	{
		if ($operator == '>')
			return (strtotime($date1) > strtotime($date2));
		return (strtotime($date1) < strtotime($date2));
	}
	
	private function priceInput($name, $value='')
	{
		return '<input type="text" value="'.Tools::getvalue($name, $value).'" name="'.$name.'" size="20" maxlength="10" /> <span>'.$this->context->currency->sign.'</span>';
	}
	
	public function decodeHtmlCharacters($value, $row)
	{
		return htmlspecialchars_decode(htmlentities($value));
	}
}

?>