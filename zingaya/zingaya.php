<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 1.1 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Zingaya extends Module
{
	private $_zingayaErrors = array();
	private $_zingayaWarning = array();
	private $_confirmation = array();

	public function __construct()
	{
		$this->name = 'zingaya';
		$this->tab = 'administration';
		$this->version = '1.1';
		$this->author = 'PrestaShop';
		parent::__construct();

		$this->displayName = $this->l('Zingaya - Click-to-call');
		$this->description = $this->l('Zingaya enables voice calls through any computer, right from a webpage. No download or phone is required!');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
	}

	public function install()
	{
		return parent::install() && $this->registerHook('backOfficeHeader') && $this->registerHook('rightColumn')
		&& $this->registerHook('leftColumn') && $this->registerHook('home') && $this->registerHook('top')
		&& $this->registerHook('productFooter') && $this->registerHook('productActions') && Configuration::updateValue('ZINGAYA_TOKEN', Tools::passwdGen(16))
		&& Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'zingaya_hook_widget` (`id_hook` INT(10) NOT NULL, `id_widget` INT(10) NOT NULL) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');
	}

	public function uninstall()
	{
		return parent::uninstall() && Configuration::deleteByName('ZINGAYA_USERNAME') && Configuration::deleteByName('ZINGAYA_PASSWORD')
		&& Configuration::deleteByName('ZINGAYA_USER_ID') && Configuration::deleteByName('ZINGAYA_API_KEY') && Configuration::deleteByName('ZINGAYA_TOKEN')
		&& Configuration::deleteByName('ZINGAYA_WIDGETS') && Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'zingaya_hook_widget`');
	}

	public function getContent()
	{
		$this->context->controller->addJQueryUI('ui.datepicker');
		$this->context->controller->addJQueryUI('ui.slider');
		$this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js');
		$this->context->controller->addCSS(_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.css');

		smartyRegisterFunction($this->context->smarty, 'function', 'displayUSPhoneNumber', array('Zingaya', 'displayUSPhoneNumberSmarty'));
		smartyRegisterFunction($this->context->smarty, 'function', 'displayUSHour', array('Zingaya', 'displayUSHoursSmarty'));

		$this->_processPost();

		$this->context->smarty->assign($this->_retrieveInformation());
		$this->context->smarty->assign(array(
			'zingaya_errors' => $this->_zingayaErrors,
			'zingaya_warning' => $this->_zingayaWarning,
			'zingaya_confirmation' => $this->_confirmation,
			'zingaya_username' => Configuration::get('ZINGAYA_USERNAME'),
			'zingaya_user_id' => Configuration::get('ZINGAYA_USER_ID'),
			'zingaya_api_password' => Configuration::get('ZINGAYA_PASSWORD'),
			'zingaya_token' => Configuration::get('ZINGAYA_TOKEN'),
			'zingaya_base_link' => 'index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'zingaya_tracking' => 'http://www.prestashop.com/modules/zingaya.png?url_site='.Tools::safeOutput($_SERVER['SERVER_NAME']).'&amp;id_lang='.(int)$this->context->cookie->id_lang));

		return $this->display(__FILE__, 'zingaya-admin.tpl');
	}

	private function _retrieveInformation()
	{
		if (!Configuration::get('ZINGAYA_API_KEY') || !Configuration::get('ZINGAYA_USER_ID'))
			return false;

		/* Retrieve Account information */
		$account_information = $this->_apiCall('GetAccountInfo');

		if ($account_information->result->frozen)
			$this->_zingayaWarning[] = $this->l('You are currently using a frozen account, please upgrade. The Zingaya button will not work until your account has been upgraded to the paid plan.');
		elseif ($account_information->result->trial)
			$this->_zingayaWarning[] = $this->l('You have a trial account valid for 30-days. If you would like to upgrade to a paid plan, please ').' <a href="https://zingaya.com/cp/billing/upgrade" target="_blank">'.$this->l('click here').'.</a>';
		elseif (is_array($account_information->result) && isset($account_information->result[0]->active) && $account_information->result[0]->active)
			$this->_zingayaWarning[] = $this->l('Your account is not active.');

		/* Retrieve Voicemails */
		$voicemail_params = array('count' => 200, 'output' => 'json');
		if (Tools::isSubmit('SubmitZingayaVoicemails'))
		{
			if (Tools::getValue('zingaya_vm_from') != '')
				$voicemail_params['from_date'] = urlencode(Tools::getValue('zingaya_vm_from').':00');
			if (Tools::getValue('zingaya_vm_to') != '')
				$voicemail_params['to_date'] = urlencode(Tools::getValue('zingaya_vm_to').':00');
		}

		/* Retrieve Call History */
		$call_history_params = array('count' => 200, 'output' => 'json');
		if (Tools::isSubmit('SubmitZingayaCallHistory'))
		{
			if (Tools::getValue('zingaya_ch_from') != '')
				$call_history_params['from_date'] = urlencode(Tools::getValue('zingaya_ch_from').':00');
			if (Tools::getValue('zingaya_ch_to') != '')
				$call_history_params['to_date'] = urlencode(Tools::getValue('zingaya_ch_to').':00');
		}

		/* Retrieve Voicemails */
		$voicemails = $this->_apiCall('GetVoicemails', $voicemail_params);
		if (!isset($voicemails->result))
			$this->_zingayaErrors[] = $this->l('An error occured while trying to retrieve your voicemails, please contact Zingaya');


		/* Retrieve Call History */
		$call_history = $this->_apiCall('GetCallHistory', $call_history_params);
		if (!isset($call_history->result))
			$this->_zingayaErrors[] = $this->l('An error occured while trying to retrieve your call history, please contact Zingaya');

		return array('zingaya_voicemails' => $voicemails->result, 'zingaya_call_history' => $call_history->result, 'zingaya_widgets' => $this->_apiCall('GetWidgets'), 'zingaya_account_info' => $account_information);
	}

	private function _processPost()
	{
	//d($_POST);
		/* Check new Credentials */
		if (Tools::isSubmit('SubmitZingayaStep1'))
		{
			Configuration::updateValue('ZINGAYA_USERNAME', Tools::getValue('zingaya_username'));
			Configuration::updateValue('ZINGAYA_PASSWORD', Tools::getValue('zingaya_api_password'));
			Configuration::deleteByName('ZINGAYA_USER_ID');

			$attach_child = $this->_apiCall('AttachChildUser');
			if (!isset($attach_child->result))
				$this->_zingayaErrors[] = $this->l('Authentication Failed, wrong credentials');
			else
			{
				Configuration::updateValue('ZINGAYA_API_KEY', $attach_child->api_key);

				$user_id = $this->_apiCall('GetCurrentUserID');
				if (!isset($user_id->result))
					$this->_zingayaErrors[] = $this->l('Authentication Failed while trying to retrieve your User ID');
				else
				{
					Configuration::updateValue('ZINGAYA_USER_ID', $user_id->result);
					$this->_confirmation[] = $this->l('Congratulations, you authentification is successfull');
				}
			}
		}

		/* Add a new Widget or Edit an existing one */
		if (Tools::isSubmit('SubmitZingayaNewWidget'))
		{
			$_POST['zingaya_tab'] = 2;
			$command = Tools::getValue('zingaya_widget_edit_id') ? 'SetWidgetInfo' : 'AddWidget';

			$params_graphics = 'size:'.urlencode(Tools::getValue('zingaya_widget_button_size')).';'.
			'button_type:'.urlencode(Tools::getValue('zingaya_widget_button_color')).';'.
			'text:'.urlencode(Tools::getValue('zingaya_widget_button_text')).';'.
			'textcolor1:'.urlencode(Tools::getValue('zingaya_widget_button_text_color_1')).';'.
			'textcolor2:'.urlencode(Tools::getValue('zingaya_widget_button_text_color_2')).';'.
			'text_shadow:'.((bool)Tools::getValue('zingaya_widget_button_shadow') ? 'true' : 'false').';'.
			'foreground:'.urlencode(Tools::getValue('zingaya_widget_button_foreground_color_1')).';'.
			'foreground2:'.urlencode(Tools::getValue('zingaya_widget_button_foreground_color_2')).';'.
			'foreground_hover:'.urlencode(Tools::getValue('zingaya_widget_button_foreground_hover_color_1')).';'.
			'foreground_hover2:'.urlencode(Tools::getValue('zingaya_widget_button_foreground_hover_color_2')).';'.
			'corner_radius:'.(int)Tools::getValue('zingaya_widget_button_radius').';';
			
			$params = array(
			'widget_name' => urlencode(Tools::getValue('zingaya_widget_name')),
			'record_calls' => (bool)Tools::getValue('zingaya_widget_record_calls') ? 'true' : 'false',
			'voicemail' => (bool)Tools::getValue('zingaya_widget_voicemail') ? 'true' : 'false',
			'google_analytics' => Tools::getValue('zingaya_widget_ganalytics'),
			'graphics' => 'dtmf_keypad:'.((bool)Tools::getValue('zingaya_widget_keypad') ? 'true' : 'false'),
			'button_graphics' => urlencode($params_graphics));

			if (Tools::getValue('zingaya_widget_edit_id'))
				$params['widget_id'] = (int)Tools::getValue('zingaya_widget_edit_id');

			$add_widget = $this->_apiCall($command, $params);

			if (isset($add_widget->result))
				$this->_confirmation[] = $this->l('Widget created successfully');
			else
			{
				$this->_zingayaErrors[] = $this->l('An error occured while creating this widget, please contact Zingaya');
				return false;
			}

			if (Tools::getValue('zingaya_widget_edit_id'))
				$widget_id = (int)Tools::getValue('zingaya_widget_edit_id');
			else
				$widget_id = (int)$add_widget->widget_id;

			if (Tools::getValue('zingaya_callme_id'))
				$add_number = $this->_apiCall('SetCallmeNumber', array('callme_number_id' => (int)Tools::getValue('zingaya_callme_id'), 'callme_number' => '1'.Tools::getValue('zingaya_widget_phone')));
			else
				$add_number = $this->_apiCall('AddCallmeNumber', array('widget_id' => (int)$widget_id, 'callme_number' => '1'.Tools::getValue('zingaya_widget_phone')));

			if (isset($add_number->result))
				$this->_confirmation[] = $this->l('Phone number added succesfully to your widget');
			else
			{
				$this->_zingayaErrors[] = $this->l('An error occured while adding the phone number to the widget');
				return false;
			}

			if (Tools::getValue('zingaya_callme_id'))
				$callme_number = (int)Tools::getValue('zingaya_callme_id');
			else
				$callme_number = (int)$add_number->callme_number_id;

			$hours = array('callme_number_id' => (int)$callme_number);
			$week_days = array('MON' => 'mo', 'TUE' => 'tu', 'WED' => 'we', 'THU' => 'th', 'FRI' => 'fr', 'SAT' => 'sa', 'SUN' => 'su');

			foreach ($week_days as $day_up => $day_low)
				if ($_POST['zingaya_widget_hours_'.$day_low.'_am'] || $_POST['zingaya_widget_hours_'.$day_low.'_pm'])
				{
					$_POST['zingaya_widget_hours_'.$day_low.'_pm'] += 12;
					$hours[$day_up] = (int)$this->_getHourFormat(Tools::getValue('zingaya_widget_hours_'.$day_low.'_am')).'-'.(int)$this->_getHourFormat(Tools::getValue('zingaya_widget_hours_'.$day_low.'_pm'));
				}

			$set_hours = $this->_apiCall('SetWorkingHours', $hours);

			if (isset($set_hours->result))
				$this->_confirmation[] = $this->l('Availibility time added succesfully to your widget');
			else
			{
				$this->_zingayaErrors[] = $this->l('An error occured while setting the hours for your phone number');
				return false;
			}

			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'zingaya_hook_widget` WHERE `id_widget` = '.(int)$widget_id);
			foreach ($_POST['zingaya_widget_hook'] as $hook_id)
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'zingaya_hook_widget` (`id_hook`, `id_widget`) VALUES ('.(int)$hook_id.', '.(int)$widget_id.')');

		}

		/* Add a new Widget */
		if (Tools::isSubmit('SubmitZingayaLoadWidget') && (int)Tools::getValue('zingaya_widget_id') != 0)
		{
			$_POST['zingaya_tab'] = 2;
			$current_widget = $this->_apiCall('GetWidgets', array('widget_id' => (int)Tools::getValue('zingaya_widget_id')));

			if (isset($current_widget->result[0]))
			{

				$this->context->smarty->assign('zingaya_widget_edit_id', (int)$current_widget->result[0]->widget_id);
				$this->context->smarty->assign('zingaya_widget_record_calls', (int)$current_widget->result[0]->record_calls);
				$this->context->smarty->assign('zingaya_widget_voicemail', (int)$current_widget->result[0]->voicemail);
				$this->context->smarty->assign('zingaya_widget_name', $current_widget->result[0]->widget_name);

				if (isset($current_widget->result[0]->callme_numbers[0]->callme_number_id))
					$this->context->smarty->assign('zingaya_callme_id', $current_widget->result[0]->callme_numbers[0]->callme_number_id);
				if (isset($current_widget->result[0]->callme_numbers[0]->callme_number))
					$this->context->smarty->assign('zingaya_widget_phone', substr($current_widget->result[0]->callme_numbers[0]->callme_number, 1));
				if (isset($current_widget->result[0]->callme_numbers[0]->hours))
					$this->context->smarty->assign('zingaya_widget_hours', $current_widget->result[0]->callme_numbers[0]->hours);

				if (isset($current_widget->result[0]->google_analytics))
					$this->context->smarty->assign('zingaya_widget_ganalytics', $current_widget->result[0]->google_analytics);

				if (isset($current_widget->result[0]->graphics))
				{
					$tmpTab2 = explode(':', $current_widget->result[0]->graphics);
					if (isset($tmpTab2[0]) && $tmpTab2[0] == 'dtmf_keypad' && isset($tmpTab2[1]))
					{
						$tmpTab2[1] = str_replace(';', '', $tmpTab2[1]);
						if ($tmpTab2[1] == 'true')
							$this->context->smarty->assign('zingaya_widget_keypad', 1);
						else
							$this->context->smarty->assign('zingaya_widget_keypad', 0);
					}
				}

				if (isset($current_widget->result[0]->button_graphics))
				{
					$tmpTab = explode(';', $current_widget->result[0]->button_graphics);
					$associated_keys = array('text_shadow' => 'zingaya_widget_button_shadow',
					'text' => 'zingaya_widget_button_text',
					'button_type' => 'zingaya_widget_button_color',
					'size' => 'zingaya_widget_button_size',
					'corner_radius' => 'zingaya_widget_button_radius',
					'textcolor1' => 'zingaya_widget_button_text_color_1',
					'textcolor2' => 'zingaya_widget_button_text_color_2',
					'foreground' => 'zingaya_widget_button_foreground_color_1',
					'foreground2' => 'zingaya_widget_button_foreground_color_2',
					'foreground_hover' => 'zingaya_widget_button_foreground_hover_color_1',
					'foreground_hover2' => 'zingaya_widget_button_foreground_hover_color_2');

					foreach ($tmpTab as $val)
						if (!empty($val))
						{
							$tmpTab2 = explode(':', $val);
							if (isset($tmpTab2[0]) && isset($tmpTab2[1]) && isset($associated_keys[$tmpTab2[0]]))
								$this->context->smarty->assign($associated_keys[$tmpTab2[0]], urldecode($tmpTab2[1]));
						}
					if (isset($tmpTab['text_shadow']))
						$this->context->smarty->assign('zingaya_widget_button_shadow', (int)$tmpTab['text_shadow']);
				}

				$widget_url = $this->_apiCall('GetWidgetURL', array('widget_id' => (int)Tools::getValue('zingaya_widget_id')));
				if (isset($widget_url->result))
					$this->context->smarty->assign('zingaya_widget_url', Tools::safeOutput($widget_url->result));
				else
					$this->_zingayaErrors[] = $this->l('An error occured while retreiving the widget url, please contact Zingaya');

				$id_hooks = Db::getInstance()->ExecuteS('SELECT `id_hook` FROM `'._DB_PREFIX_.'zingaya_hook_widget` WHERE `id_widget` = '.(int)$current_widget->result[0]->widget_id);
				$hooks = array();
				foreach ($id_hooks as $id_hook)
					$hooks[] = (int)$id_hook['id_hook'];
				$this->context->smarty->assign('zingaya_hooks', $hooks);
			}
			else
				$this->_zingayaErrors[] = $this->l('An error occured while loading this widget, please contact Zingaya');
		}
	}

	private function _apiCall($command, $params = array(), $json = true)
	{
		$allowed_commands = array('Logon', 'Logout', 'GetCurrentUserID', 'GetAllowedTariffs', 'AddChildUser', 'GetUsers', 'SetUserInfo', 'SetBankCardSettings', 'GetAccountInfo',
		'UpgradeTariff', 'GetRobokassaPaymentURL', 'DistributeMoney', 'GetChildAccountsToBeCharged', 'PersonalCharge', 'AddWidget', 'DelWidget', 'GetWidgets', 'SetWidgetInfo',
		'GetWidgetURL', 'GenerateWidgetImage', 'AddCallmeNumber', 'ConfirmMobileNumberActivation', 'DelCallmeNumber', 'GetCallmeNumbers', 'SendActivationCodeForMobileNumber',
		'SetCallmeNumber', 'GetWorkingHours', 'SetWorkingHours', 'GetCallHistory', 'SetCallExtraInfo', 'GetCallStatistics', 'GetTransactionHistory', 'GetTransactionInvoice',
		'DelVoicemail', 'GetVoicemails', 'SetVoicemailInfo', 'AddBlackList', 'DelBlackList', 'GetBlackList', 'AddSIPAccount', 'DelSIPAccount', 'GetSIPAccounts',
		'SetSIPAccount', 'Version', 'AddPartner', 'AttachChildUser', 'IsCallmeNumberAvailable', 'IsEmailRegistered', 'IsPartnerCodeRegistered', 'IsPartnerEmailRegistered',
		'IsUserRegistered', 'GetCountryTimeZones', 'GetOverallCallStatistics', 'GetPaidTariffs', 'GetTrialTariffs', 'ResendActivationLetter');

		if (empty($command) || !in_array($command, $allowed_commands))
			return false;

		$ch = curl_init();
		$url = 'https://api.zingaya.com/ZingayaAPI2/?cmd='.$command.'&user_name='.Configuration::get('ZINGAYA_USERNAME');
		if ($command == 'AttachChildUser')
			$url .= '&password='.Configuration::get('ZINGAYA_PASSWORD');
		if (Configuration::get('ZINGAYA_USER_ID') && $command != 'GetCurrentUserID')
			$url .= '&user_id='.Configuration::get('ZINGAYA_USER_ID');
		if ($command == 'AttachChildUser')
			$url .= '&super_user_name=prestashopapi';
		if (Configuration::get('ZINGAYA_API_KEY'))
			$url .= '&api_key='.Configuration::get('ZINGAYA_API_KEY');
		if (count($params))
			foreach ($params as $k => $v)
				$url .= '&'.$k.'='.$v;

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	
		$response = curl_exec($ch);
		curl_close($ch);

		if (!$json)
			return $response;

		$response_json = Tools::jsondecode($response);

		/*if ($response === false || isset($response_json->error))
			echo 'API Error '.$response_json->error->code.' ('.$url.'): '.Tools::safeOutput($response_json->error->msg);*/

		return $response_json;
	}

	public function deleteWidget($id_widget)
	{
		/* Delete a Widget */
		$del_widget = $this->_apiCall('DelWidget', array('widget_id' => (int)$id_widget));
		if (isset($del_widget->result))
		{
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'zingaya_hook_widget` WHERE `id_widget` = '.(int)Tools::getValue('zingaya_widget_id'));
			return $this->l('Widget deleted successfully');
		}
		else
			return $this->l('An error occured while deleting this widget, please contact Zingaya');
	}

	public function generateButton()
	{

		$params = 'size:'.urlencode(Tools::getValue('size')).';'.
		'button_type:'.urlencode(Tools::getValue('button_type')).';'.
		'text:'.urlencode(Tools::getValue('text')).';'.
		'textcolor1:'.urlencode(Tools::getValue('textcolor1')).';'.
		'textcolor2:'.urlencode(Tools::getValue('textcolor2')).';'.
		'text_shadow:'.((bool)Tools::getValue('text_shadow') ? 'true' : 'false').';'.
		'foreground:'.urlencode(Tools::getValue('foreground')).';'.
		'foreground2:'.urlencode(Tools::getValue('foreground2')).';'.
		'foreground_hover:'.urlencode(Tools::getValue('foreground_hover')).';'.
		'foreground_hover2:'.urlencode(Tools::getValue('foreground_hover2')).';'.
		'corner_radius:'.(int)Tools::getValue('corner_radius').';';

		return base64_encode($this->_apiCall('GenerateWidgetImage', array('button_graphics' => urlencode($params), 'output' => 'png'), false));
	}

	static public function displayUSHoursSmarty($params, &$smarty)
	{
		if (isset($params['hour']))
		{
			if ($params['hour'] == 12)
				return '12 PM';
			elseif ($params['hour'] == 24 || $params['hour'] == 0)
				return '12 AM';
			elseif ($params['hour'] > 12)
				return ($params['hour'] - 12).' PM';
			return $params['hour'].' AM';
		}
	}

	static public function displayUSPhoneNumberSmarty($params, &$smarty)
	{
		if (isset($params['number']))
			return '+1 ('.substr(Tools::safeOutput($params['number']), 1, 3).') '.substr(Tools::safeOutput($params['number']), 4, 3).'-'.substr(Tools::safeOutput($params['number']), 7);
	}

	private function _getHourFormat($str)
	{
		if ($str == '')
			return 0;
		$hour = (int)substr($str, 0, 2);
		$ampm = strlen($str) > 5 ? Tools::strtolower(substr($str, 6, 2)) : Tools::strtolower(substr($str, 3, 2));
		if ($ampm == 'pm' && $hour == 12)
			return 12;
		elseif ($ampm == 'am' && $hour == 12)
			return 0;
		elseif ($ampm == 'pm')
			return $hour + 12;
		return $hour;
	}

	/* Front-end display */
	public function hookRightColumn($params){ return $this->_displayButton($params, 6); }
	public function hookLeftColumn($params){ return $this->_displayButton($params, 7); }
	public function hookHome($params){ return $this->_displayButton($params, 8); }
	public function hookTop($params){ return $this->_displayButton($params, 14); }
	public function hookProductFooter($params){ return $this->_displayButton($params, 17); }
	public function hookProductActions($params){ return $this->_displayButton($params, 35); }

	private function _displayButton($params, $id_hook)
	{
		if (!$this->active)
			return false;

		$id_widgets = Db::getInstance()->ExecuteS('SELECT `id_widget` FROM `'._DB_PREFIX_.'zingaya_hook_widget` WHERE `id_hook` = '.(int)$id_hook);

		if (empty($id_widgets))
			return false;

		$widgets = array();
		foreach ($id_widgets as $id_widget)
		{
			$current_widget = $this->_apiCall('GetWidgets', array('widget_id' => (int)$id_widget['id_widget']));
			if (isset($current_widget->result[0]->button_graphics))
			{
				$button_graphics = explode(';', $current_widget->result[0]->button_graphics);
				$size = 'small';
				foreach ($button_graphics as $val)
				{
					$tab = explode(':', $val);
					if ($tab[0] == 'size')
						$size = $tab[1];
				}
				$size_array = array('small' => '34px', 'medium' => '44px', 'big' => '54px');
				$widget_url = $this->_apiCall('GetWidgetURL', array('widget_id' => (int)$id_widget['id_widget']));

				if (isset($widget_url->result))
					$widgets[] = array('url' => $widget_url->result, 'src' => base64_encode($this->_apiCall('GenerateWidgetImage', array('button_graphics' => urlencode($current_widget->result[0]->button_graphics), 'output' => 'png'), false)), 'height' => $size_array[$size], 'size' => $size);
			}
		}
		$this->context->smarty->assign('zingaya_widgets', $widgets);
		return $this->display(__FILE__, 'zingaya-front.tpl');
	}

	public function hookBackOfficeHeader($params)
	{
		return '<link href="'.__PS_BASE_URI__.'modules/'.$this->name.'/css/colorpicker.css" rel="stylesheet" type="text/css" />';
	}
}