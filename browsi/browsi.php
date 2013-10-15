<?php
/*
* 2013 Brow.si
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
*  @author MySiteApp Ltd. <support@mysiteapp.com>
*  @copyright  2013 MySiteApp Ltd.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of MySiteApp Ltd
*/

if (!defined('_PS_VERSION_'))
	exit;

class Browsi extends Module
{
	public function __construct()
	{
		$this->name = 'browsi';
		$this->tab = 'mobile';
		$this->version = '1.0.1';
		$this->author = 'MySiteApp Ltd.';
		$this->need_instance = 1;

		parent::__construct();

		$this->displayName = $this->l('Brow.si');
		$this->description = $this->l('Generate more traffic, increase customer engagement and sell more on mobile with Brow.si.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your Brow.si settings?');

		if (!$this->isValidSiteId(Configuration::get('BROWSI_SITEID')))
			$this->warning = $this->l('Site ID should be configured for customizing and analytics aggregation.');
        /* Backward compatibility */
        if (_PS_VERSION_ < 1.5)
            require(_PS_MODULE_DIR_.'browsi/backward_compatibility/backward.php');
	}

	public function install()
	{
		return parent::install()
				&& $this->registerHook('footer')
				&& $this->registerHook('displayMobileFooterChoice')
				&& Configuration::updateValue('BROWSI_SITEID', '');
	}

	public function uninstall()
	{
		return parent::uninstall() &&
			Configuration::deleteByName('BROWSI_SITEID');
	}

	public function hookFooter($params)
	{
		$this->smarty->assign('browsi_site_id', Configuration::get('BROWSI_SITEID'));
        // Specifying full path to template, to support v1.4
		return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
	}

	public function hookDisplayMobileFooterChoice($params)
	{
		return $this->hookFooter($params);
	}


	public function getContent()
	{
		$output = '';

		if (Tools::isSubmit('submitBrowsi'))
		{
			$browsi_site_id = (string)Tools::getValue('browsi_site_id');
			if (!empty($browsi_site_id) && !$this->isValidSiteId($browsi_site_id))
				$output = '<div class="error">'.$this->l('Invalid Brow.si Site ID').'</div>';
			else
			{
				// Allow empty siteId -> like having none.
				Configuration::updateValue('BROWSI_SITEID', $browsi_site_id);
				$output = '<div class="conf confirm">'.$this->l('Settings updated').'</div>';
			}
		}
		$this->context->smarty->assign(array(
			'browsi_form' => './index.php?tab=AdminModules&configure=browsi&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=browsi',
            'browsi_tracking' => 'http://www.prestashop.com/modules/browsi.png?url_site='.Tools::safeOutput($_SERVER['SERVER_NAME']).'&amp;id_lang='.(int)$this->context->cookie->id_lang,
			'browsi_site_id' => Configuration::get('BROWSI_SITEID'),
			'browsi_register_link' => 'http://l.brow.si/15xc6TM',
			'browsi_message' => $output));
		return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
	}


	/**
	 * Returns whether the supplied site id is valid.
	 *
	 * @param string $str User input
	 * @return bool
	 */
	private function isValidSiteId($str)
	{
		return !empty($str) && preg_match('/^[a-z0-9]{1,7}$/i', $str);
	}
}
