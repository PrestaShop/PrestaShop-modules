<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once dirname(__FILE__).'/classes/Badge.php';
include_once dirname(__FILE__).'/classes/Advice.php';
include_once dirname(__FILE__).'/classes/Condition.php';
include_once dirname(__FILE__).'/classes/GamificationTools.php';

class Gamification extends Module
{
	public function __construct()
	{
		$this->name = 'gamification';
		$this->tab = 'administration';
		$this->version = '1.7.0';
		$this->author = 'PrestaShop';

		parent::__construct();

		$this->displayName = $this->l('Merchant Expertise');
		$this->description = $this->l('Become an e-commerce expert within the blink of an eye!');
		$this->cache_data = dirname(__FILE__).'/data/';
		$this->url_data = 'http://gamification.prestashop.com/json/';		
	}

	public function install()
	{
		if (Db::getInstance()->getValue('SELECT id_module FROM '._DB_PREFIX_.'module WHERE name =\''.pSQL($this->name).'\''))
			return true;

		Tools::deleteDirectory($this->cache_data, false);
		if (!$this->installDb() || !$this->installTab() ||
			!Configuration::updateGlobalValue('GF_INSTALL_CALC', 0) ||
			!Configuration::updateGlobalValue('GF_CURRENT_LEVEL', 1) || !Configuration::updateGlobalValue('GF_CURRENT_LEVEL_PERCENT', 0) ||
			!Configuration::updateGlobalValue('GF_NOTIFICATION', 0) || !parent::install() || !$this->registerHook('displayBackOfficeHeader'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() || !$this->uninstallTab() || 
			!$this->uninstallDb() || !Configuration::updateGlobalValue('GF_CURRENT_LEVEL', 1) || 
			!Configuration::updateGlobalValue('GF_NOTIFICATION', 0) || 
			!Configuration::updateGlobalValue('GF_INSTALL_CALC', 0) || 
			!Configuration::updateGlobalValue('GF_CURRENT_LEVEL_PERCENT', 0))
			return false;
		return true;
	}
	
	public function installDb()
	{
		$return = true;
		include(dirname(__FILE__).'/sql_install.php');
		foreach ($sql as $s)
			$return &= Db::getInstance()->execute($s);
		return $return;
	}
	
	public function uninstallDb()
	{
		include(dirname(__FILE__).'/sql_install.php');
		foreach ($sql as $name => $v)
			Db::getInstance()->execute('DROP TABLE '.$name);
		return true;
	}
	
	public function installTab()
	{
		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = "AdminGamification";
		$tab->name = array();
		foreach (Language::getLanguages(true) as $lang)
			$tab->name[$lang['id_lang']] = "Merchant Expertise";
		$tab->id_parent = (int)Tab::getIdFromClassName('AdminAdmin');
		$tab->module = $this->name;
		return $tab->add();
	}
	
	public function uninstallTab()
	{
		$id_tab = (int)Tab::getIdFromClassName('AdminGamification');
		if ($id_tab)
		{
			$tab = new Tab($id_tab);
			return $tab->delete();
		}
		else
			return false;
	}
	
	public function getContent()
	{
		Tools::redirectAdmin($this->context->link->getAdminLink('AdminGamification'));
	}
	
	public function __call($name, $arguments)
	{
		if (!Validate::isHookName($name))
			return false;
		$name = str_replace('hook', '', $name);
		$condition_ids = Condition::getIdsByHookCalculation($name);
		foreach ($condition_ids as $id)
		{
			$cond = new Condition((int)$id);
			$cond->processCalculation();
		}
	}
	
	public function hookDisplayBackOfficeHeader()
	{
		if (method_exists($this->context->controller, 'addJquery'))
		{
			$this->context->controller->addJquery();
			$this->context->controller->addCss($this->_path.'views/css/gamification.css');

			if (version_compare(_PS_VERSION_, '1.6.0', '>=') === TRUE)
				$this->context->controller->addJs($this->_path.'views/js/gamification_bt.js');
			else
				$this->context->controller->addJs($this->_path.'views/js/gamification.js');

			$this->context->controller->addJqueryPlugin('fancybox');
		
			return '<script>
				var admin_gamification_ajax_url = \''.$this->context->link->getAdminLink('AdminGamification').'\';
				var current_id_tab = '.(int)$this->context->controller->id.';
			</script>';
		}
	}
	
	public function renderHeaderNotification()
	{
		$current_level = (int)Configuration::get('GF_CURRENT_LEVEL');
		$current_level_percent = (int)Configuration::get('GF_CURRENT_LEVEL_PERCENT');
		
		$badges_to_display = array();
		$not_viewed_badge = explode('|', Configuration::get('GF_NOT_VIEWED_BADGE', ''));
		foreach ($not_viewed_badge as $id)
		{
			$badges_to_display[] = new Badge((int)$id, (int)$this->context->language->id);
			$badges_to_display[] = new Badge(end($badges_to_display)->getNextBadgeId(), (int)$this->context->language->id);
		}
						
		$this->context->smarty->assign(array(
			'link' => $this->context->link,
			'current_level_percent' => $current_level_percent,
			'current_level' => $current_level,
			'badges_to_display' => $badges_to_display,
			'current_id_tab' => (int)$this->context->controller->id,
			'notification' => (int)Configuration::get('GF_NOTIFICATION'),
			
			));

		if (version_compare(_PS_VERSION_, '1.6.0', '>='))
			return $this->display(__FILE__, 'notification_bt.tpl');
		else
			return $this->display(__FILE__, 'notification.tpl');
	}
	
	public function refreshDatas($iso_lang = null)
	{
		if (is_null($iso_lang))
			$iso_lang = $this->context->language->iso_code;
		
		$default_iso_lang = Language::getIsoById((int)Configuration::get('PS_LANG_DEFAULT'));
		$id_lang = Language::getIdByIso($iso_lang);
		
		$iso_country = $this->context->country->iso_code;
		$iso_currency = $this->context->currency->iso_code;
		
		if ($iso_lang != $default_iso_lang)
			$this->refreshDatas($default_iso_lang);

		$cache_file = $this->cache_data.'data_'.strtoupper($iso_lang).'_'.strtoupper($iso_currency).'_'.strtoupper($iso_country).'.json';
		if (!$this->isFresh($cache_file, 604800))
			if ($this->getData($iso_lang))
			{
				$data = Tools::jsonDecode(Tools::file_get_contents($cache_file));
				if (!isset($data->signature))
					return false;
				
				$this->processCleanAdvices(array_merge($data->advices, $data->advices_16));
				
				if (function_exists('openssl_verify'))
				{
					if (!openssl_verify(Tools::jsonencode(array($data->conditions, $data->advices_lang)), base64_decode($data->signature), file_get_contents(dirname(__FILE__).'/prestashop.pub')))
						return false;
				}
				if (isset($data->conditions))
					$this->processImportConditions($data->conditions, $id_lang);

				if (isset($data->badges) && isset($data->badges_lang))
					$this->processImportBadges($data->badges, $data->badges_lang, $id_lang);
					
				if (isset($data->advices) && isset($data->advices_lang))
					$this->processImportAdvices($data->advices, $data->advices_lang, $id_lang);
				
				if (function_exists('openssl_verify'))
				{
					if (!openssl_verify(Tools::jsonencode(array($data->advices_lang_16)), base64_decode($data->signature_16), file_get_contents(dirname(__FILE__).'/prestashop.pub')))
						return false;
				}
				
				if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true && isset($data->advices_16) && isset($data->advices_lang_16))
					$this->processImportAdvices($data->advices_16, $data->advices_lang_16, $id_lang);
			}
	}
	
	public function getData($iso_lang = null)
	{
		if (is_null($iso_lang))
			$iso_lang = $this->context->language->iso_code;
		$iso_country = $this->context->country->iso_code;
		$iso_currency = $this->context->currency->iso_code;
		
		$file_name = 'data_'.strtoupper($iso_lang).'_'.strtoupper($iso_currency).'_'.strtoupper($iso_country).'.json';
		$data = Tools::file_get_contents($this->url_data.$file_name);
		
		return (bool)file_put_contents($this->cache_data.'data_'.strtoupper($iso_lang).'_'.strtoupper($iso_currency).'_'.strtoupper($iso_country).'.json', $data);
	}
	
	public function processCleanAdvices($advices)
	{
		$current_advices = array();
		$result = Db::getInstance()->ExecuteS('SELECT id_advice, id_ps_advice FROM '._DB_PREFIX_.'advice');
		foreach ($result as $row)
			$current_advices[(int)$row['id_ps_advice']] = (int)$row['id_advice'];
			
		// Delete advices that are not in the file anymore
		foreach ($current_advices as $id_advice)
		{
			// Check that the advice is used in this language
			$html = Db::getInstance()->getValue('SELECT html FROM '._DB_PREFIX_.'advice_lang WHERE id_advice = '.(int)$id_advice.' AND id_lang = '.(int)$this->context->language->id);
			if (!$html)
				continue;
			$adv = new Advice($id_advice);
			$adv->delete();
		}
	}
	
	public function processImportConditions($conditions, $id_lang)
	{
		foreach ($conditions as $condition)
		{
			try 
			{
				if (Condition::getIdByIdPs($condition->id_ps_condition))
					continue;//only add new condition, if already exist we continue
				
				$cond = new Condition();
				$cond->hydrate((array)$condition, (int)$id_lang);
				$time = 86400;
				if ($cond->calculation_type == 'time')
					$time = 86400 * (int)$cond->calculation_detail;
				
				$cond->date_upd = date('Y-m-d H:i:s', time() - $time);
				$cond->date_add = date('Y-m-d H:i:s');
				$condition->calculation_detail = trim($condition->calculation_detail);
				$cond->add(false);
				if ($condition->calculation_type == 'hook' && !$this->isRegisteredInHook($condition->calculation_detail) && Validate::isHookName($condition->calculation_detail))
					$this->registerHook($condition->calculation_detail);
				unset($cond);
			} catch (Exception $e) {
					continue;
			}
		}
	}
	
	public function processImportBadges($badges, $badges_lang, $id_lang)
	{
		$formated_badges_lang = array();
		foreach ($badges_lang as $lang)
			$formated_badges_lang[$lang->id_ps_badge] = array(
				'name' => array($id_lang => $lang->name),
				'description' => array($id_lang => $lang->description),
				'group_name' => array($id_lang => $lang->group_name));

		$current_badges = array();
		$result = Db::getInstance()->ExecuteS('SELECT id_badge FROM '._DB_PREFIX_.'badge');
		foreach ($result as $row)
			$current_badges[] = (int)$row['id_badge'];

		$cond_ids = $this->getFormatedConditionsIds();
		foreach ($badges as $badge)
		{
			try 
			{
				//if badge already exist we update language data
				if (in_array($badge->id_ps_badge, $current_badges))
				{
					$bdg = new Badge($badge->id_ps_badge);
					$bdg->name[$id_lang] = $formated_badges_lang[$badge->id_ps_badge]['name'][$id_lang];
					$bdg->description[$id_lang] = $formated_badges_lang[$badge->id_ps_badge]['description'][$id_lang];
					$bdg->group_name[$id_lang] = $formated_badges_lang[$badge->id_ps_badge]['group_name'][$id_lang];
					$bdg->update();
					unset($current_badges[$badge->id_ps_badge]);
				}
				else
				{
					$badge_data = array_merge((array)$badge, $formated_badges_lang[$badge->id_ps_badge]);
					$bdg = new Badge();
					$bdg->hydrate($badge_data, (int)$id_lang);
					$bdg->add();
					
					foreach ($badge->conditions as $cond)
						Db::getInstance()->insert('condition_badge', array('id_condition' => $cond_ids[$cond], 'id_badge' => $bdg->id));				
				}
				unset($bdg);
			} catch (Exception $e) {
					continue;
			}
		}
		
		// Delete badges that are not in the file anymore
		foreach ($current_badges as $id_badge)
		{
			$bdg = new Badge($id_badge);
			$bdg->delete();
		}
	}
	
	public function processImportAdvices($advices, $advices_lang, $id_lang)
	{
		$formated_advices_lang = array();
		foreach ($advices_lang as $lang)
			$formated_advices_lang[$lang->id_ps_advice] = array('html' => array($id_lang => $lang->html));
		
		$current_advices = array();
		$result = Db::getInstance()->ExecuteS('SELECT id_advice, id_ps_advice FROM '._DB_PREFIX_.'advice');
		foreach ($result as $row)
			$current_advices[(int)$row['id_ps_advice']] = (int)$row['id_advice'];
		
		$cond_ids = $this->getFormatedConditionsIds();
		foreach ($advices as $advice)
		{
			try
			{
				//if advice already exist we update language data
				if (isset($current_advices[$advice->id_ps_advice]))
				{
					$adv = new Advice($current_advices[$advice->id_ps_advice]);
					$bdg->html[$id_lang] = $formated_advices_lang[$advice->id_ps_advice]['html'][$id_lang];
					$adv->update();
					$this->processAdviceAsso($adv->id, $advice->display_conditions, $advice->hide_conditions, $advice->tabs, $cond_ids);
					unset($current_advices[$advice->id_ps_advice]);
				}
				else
				{
					$advice_data = array_merge((array)$advice, $formated_advices_lang[$advice->id_ps_advice]);
					$adv = new Advice();
					$adv->hydrate($advice_data, (int)$id_lang);
					$adv->id_tab = (int)Tab::getIdFromClassName($advice->tab);
					
					$adv->add();
					
					$this->processAdviceAsso($adv->id, $advice->display_conditions, $advice->hide_conditions, $advice->tabs, $cond_ids);
				}
				unset($adv);
			} catch (Exception $e) {
				continue;
			}
		}
	}
	
	public function processAdviceAsso($id_advice, $display_conditions, $hide_conditions, $tabs, $cond_ids)
	{
		Db::getInstance()->delete('condition_advice', 'id_advice='.(int)$id_advice);
		if (is_array($display_conditions))
			foreach ($display_conditions as $cond)
				Db::getInstance()->insert('condition_advice', array('id_condition' => $cond_ids[$cond], 'id_advice' => $id_advice, 'display' => 1));
		
		if (is_array($hide_conditions))
			foreach ($hide_conditions as $cond)
				Db::getInstance()->insert('condition_advice', array('id_condition' => $cond_ids[$cond], 'id_advice' => $id_advice, 'display' => 0));
		
		Db::getInstance()->delete('tab_advice', 'id_advice='.(int)$id_advice);
		if (isset($tabs) && is_array($tabs) && count($tabs))
			foreach ($tabs as $tab)
				Db::getInstance()->insert('tab_advice', array('id_tab' => (int)Tab::getIdFromClassName($tab), 'id_advice' => $id_advice));
	}
	
	public function getFormatedConditionsIds()
	{
		$cond_ids = array();
		$result = Db::getInstance()->executeS('SELECT `id_condition`, `id_ps_condition` FROM `'._DB_PREFIX_.'condition` ');

		foreach ($result as $res) 
			$cond_ids[$res['id_ps_condition']] = $res['id_condition'];
		
		return $cond_ids;
	}
	
	public function isFresh($file, $timeout = 604800000)
	{
		if (file_exists($file))
		{
			if (filesize($file) < 1)
				return false;
			return ((time() - filemtime($file)) < $timeout);
		}
		else
			return false;
	}
}
