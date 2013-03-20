<?php

include_once dirname(__FILE__).'/../../classes/Condition.php';

class AdminGamificationController extends ModuleAdminController
{
	public function __construct()
	{
		$this->display = 'view';
		$this->meta_title = $this->l('Your Merchant Expertise');
		parent::__construct();
	}
	
	public function setMedia()
	{
		$this->addJqueryUI('ui.progressbar');
		$this->addJS('/modules/gamification/views/js/bubble-popup.js');
		$this->addJS('/modules/gamification/views/js/gamification.js');
		$this->addCSS('/modules/gamification/views/css/bubble-popup.css');
		
		return parent::setMedia();
	}
	
	public function initToolBarTitle()
	{
		$this->toolbar_title = $this->l('Your Merchant Expertise');
	}
	
	public function initToolBar()
	{
		return true;
	}
	
	public function renderView()
	{
		$badges_feature = new Collection('badge', $this->context->language->id);
		$badges_feature->where('type', '=', 'feature');
		
		$badges_achievement = new Collection('badge', $this->context->language->id);
		$badges_achievement->where('type', '=', 'achievement');
		
		$badges_international = new Collection('badge', $this->context->language->id);
		$badges_international->where('type', '=', 'international');
		
		$badges_type = array(
			'badges_feature' => array('name' => $this->l('Feature'), 'badges' => $badges_feature),
			'badges_achievement' => array('name' => $this->l('Achievement'), 'badges' => $badges_achievement),
			'badges_international' => array('name' => $this->l('International'), 'badges' => $badges_international),
			);
		
		$this->tpl_view_vars = array(
			'badges_type' => $badges_type,
			'current_level_percent' => (int)Configuration::get('GF_CURRENT_LEVEL_PERCENT'),
			'current_level' => (int)Configuration::get('GF_CURRENT_LEVEL')
			);
		return parent::renderView();
	}
	
	public function ajaxProcessDisableNotification()
	{
		Configuration::updateGlobalValue('GF_NOTIFICATION', 0);
	}
	
	public function ajaxProcessGamificationTasks()
	{
		if (!Configuration::get('GF_INSTALL_CALC'))
		{
			$this->processRefreshData();
			$this->processInstallCalculation();
			Configuration::updateGlobalValue('GF_INSTALL_CALC', 1);
		}
			
		die(Tools::jsonEncode(array(
			'refresh_data' => $this->processRefreshData(),
			'daily_calculation' => $this->processMakeDailyCalculation(),
			'advice_validation' => $this->processAdviceValidation(),
			'advices_to_display' => $this->processGetAdvicesToDisplay(),
			'level_badge_validation' => $this->processLevelAndBadgeValidation(),
			'header_notification' => $this->module->renderHeaderNotification(),
			)));
	}
	
	public function processRefreshData()
	{
		return $this->module->refreshDatas();
	}
	
	public function processGetAdvicesToDisplay()
	{
		$return = array('advices' => array());
		$id_tab = (int)Tools::getValue('id_tab');
		$advices = Advice::getValidatedByIdTab($id_tab);

		foreach ($advices as $advice)
			$return['advices'][] = array('selector' => $advice->selector, 'html' => GamificationTools::parseMetaData($advice->html), 'location' => $advice->selector);

		return $return;
	}
	
	public function processMakeDailyCalculation()
	{
		$return = true;
		$condition_ids = Condition::getIdsDailyCalculation();
		foreach ($condition_ids as $id)
		{
			$cond = new Condition((int)$id);
			$return &= $cond->processCalculation();
		}
		return $return;
	}
	
	public function processAdviceValidation()
	{
		$return = true;
		$advices_to_validate = Advice::getIdsAdviceToValidate();
		$advices_to_unvalidate = Advice::getIdsAdviceToUnvalidate();
		
		foreach ($advices_to_validate as $id)
		{
			$advice = new Advice((int)$id);
			$advice->validated = 1;
			$return &= $advice->save();
		}
		
		foreach ($advices_to_unvalidate as $id)
		{
			$advice = new Advice((int)$id);
			$advice->validated = 0;
			$return &= $advice->save();
		}
		return $return;
	}
	
	public function processLevelAndBadgeValidation()
	{
		$return = true;
		$current_level = (int)Configuration::get('GF_CURRENT_LEVEL');
		$current_level_percent = (int)Configuration::get('GF_CURRENT_LEVEL_PERCENT');
		
		$not_viewed_badge = explode('|', ltrim(Configuration::get('GF_NOT_VIEWED_BADGE', ''), ''));
		$nbr_notif = Configuration::get('GF_NOTIFICATION', 0);
		
		$ids_badge = Badge::getIdsBadgesToValidate();
		if (count($ids_badge))
			$not_viewed_badge = array(); //reset the last badge only if there is new badge to validate
				
		foreach ($ids_badge as $id)
		{
			$badge = new Badge((int)$id);
			if (($badge->scoring + $current_level_percent) >= 100)
			{
				$current_level ++;
				$current_level_percent = $badge->scoring + $current_level_percent - 100;
			}
			else
				$current_level_percent += $badge->scoring;
			
			$badge->validated = 1;
			$return &= $badge->save();
			$nbr_notif ++;
			$not_viewed_badge[] = $badge->id;
		}
		
		Configuration::updateGlobalValue('GF_NOTIFICATION', (int)$nbr_notif);
		Configuration::updateGlobalValue('GF_NOT_VIEWED_BADGE', implode('|', array_unique($not_viewed_badge)));
		Configuration::updateGlobalValue('GF_CURRENT_LEVEL', (int)$current_level);
		Configuration::updateGlobalValue('GF_CURRENT_LEVEL_PERCENT', (int)$current_level_percent);
		return $return;
	}
	
	public function processInstallCalculation()
	{
		$group_position = 1;
		do
		{
			$condition_ids = Condition::getIdsByBadgeGroupPosition($group_position);
			foreach ($condition_ids as $id)
			{
				$cond = new Condition((int)$id);
				$cond->processCalculation();
				unset($cond);
			}
			$group_position ++;
		}while(count($condition_ids));
	}
}
