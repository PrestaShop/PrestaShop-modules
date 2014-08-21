<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

require_once(_PS_MODULE_DIR_.'seur/AdminSeur.php');

class AdminSeur15Controller extends ModuleAdminController {

	public function initContent()
	{		
		$admin_seur = new AdminSeur(false);

		if (!$admin_seur->module_enabled_and_configured)
		{
			$admin_seur->displayModuleConfigurationWarning();
			$this->content = $admin_seur->content;
			return parent::initContent();
		}

		$this->display = 'view';
		$this->module_instance = Module::getInstanceByName('seur');

		Context::getContext()->controller->addJqueryUI('ui.datepicker');

		if (Tools::getValue('verDetalle'))
		{
			$response = Expedition::getExpeditions($admin_seur->getExpeditionData());
			$this->tpl_view_vars = array('datos' => $admin_seur->displayFormDeliveries($response, true));
		}
		elseif (Tools::getValue('createPickup'))
		{
			$error_response = Pickup::createPickup();

			if (!empty($error_response))
				$this->tpl_view_vars = array('datos' => $admin_seur->displayFormDeliveries(null, null, $error_response));
			else
				$this->tpl_view_vars = array('datos' => $admin_seur->displayFormDeliveries());
		}
		elseif (Tools::getValue('submitFilter'))
		{
			$response = Expedition::getExpeditions($admin_seur->getExpeditionData());
			$this->tpl_view_vars = array('datos' => $admin_seur->displayFormDeliveries($response, false));
		}
		else
			$this->tpl_view_vars = array('datos' => $admin_seur->displayFormDeliveries());

		$this->content = $admin_seur->content;
		$this->fields_list = $admin_seur->fields_list;

		parent::initContent();
	}
}