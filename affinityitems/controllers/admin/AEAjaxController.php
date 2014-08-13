<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future.If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/../../loader.php');

class AEAjaxController extends ModuleAdminController {

	public function displayAjax()
	{
		parent::displayAjax();
		if (Tools::safeOutput(Tools::getValue('action')) == 'register' || Tools::safeOutput(Tools::getValue('action')) == 'login')
		{
			if (Shop::getContext() == Shop::CONTEXT_SHOP)
				echo AEAjaxAdapter::authentication();
			else
			{
				$ret = array();
				$ret['_ok'] = false;
				$ret['_errorMessage'] = 'Please select a store to authenticate';
				echo Tools::jsonEncode($ret);
			}
		}
		else if (Tools::getIsset('percentage'))
			echo AEAjaxAdapter::setAbTestingPercentage();
		else if ((Tools::getIsset('ip') || Tools::getIsset('ipList')) && Tools::getIsset('type'))
			echo AEAjaxAdapter::setHosts();
		else if (Tools::getIsset('synchronize'))
			echo AEAjaxAdapter::synchronize();
		else if (Tools::getIsset('productId') && Tools::getIsset('action'))
			echo AEAjaxAdapter::postAction();
		else if (Tools::getIsset('notificationId'))
			echo AEAjaxAdapter::syncNotification();
	}
}

