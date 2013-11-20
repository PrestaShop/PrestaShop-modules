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

class TextMasterSettingsView extends TextMasterView
{
	protected $pricings;
	private   $current_page = '';
	protected $tpl = 'settings/settings.tpl';
	
	public function displayForm()
	{
		$this->collectTemplateData();
		return parent::display();
	}
	
	public function collectTemplateData()
	{
		if (Tools::getValue('menu') == 'create_project' && Tools::getValue('id_project'))
			$values = new TextMasterDataWithCookiesManager;
		else
			$values = $this->settings_obj;
		
		$this->context->smarty->assign(
			array(	'values' 					=> $values,
					'pricings'					=> $this->textmasterAPI->getPricings(),
					'TEXTMASTER_PRICING_URL' 	=> TEXTMASTER_PRICING_URL,
					'languages' 				=> $this->textmasterAPI->getLanguages(),
					'categories'				=> $this->textmasterAPI->getCategories(),
					'audiences'					=> $this->textmasterAPI->getSelectOf('audiences'),
					'grammatical_persons' 		=> $this->textmasterAPI->getSelectOf('grammatical_persons'),
					'language_levels'			=> $this->textmasterAPI->getSelectOf('service_levels'),
					'vocabulary_levels'			=> $this->textmasterAPI->getSelectOf('vocabulary_levels'),
					'saveAction' 				=> TextMaster::CURRENT_INDEX.Tools::getValue('token').'&menu='.Tools::getValue('menu', 'settings').'&configure='.$this->module_instance->name.'&token='.Tools::getValue('token').'&savesettings=1')
		);
	}
}