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
 *  @version  Release: $Revision: 14390 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
	exit;

class PayPalTools
{
	protected $name = null;

	public function __construct($module_name)
	{
		$this->name = $module_name;
	}

	public function moveTopPayments($position)
	{
		if (_PS_VERSION_ < '1.5')
			$hookPayment = (int)Hook::get('payment');
		else
			$hookPayment = (int)Hook::getIdByName('payment');

		$moduleInstance = Module::getInstanceByName($this->name);

		if (_PS_VERSION_ < '1.5')
			$moduleInfo = Hook::getModuleFromHook($hookPayment, $moduleInstance->id);
		else
			$moduleInfo = Hook::getModulesFromHook($hookPayment, $moduleInstance->id);

		if ((isset($moduleInfo['position']) && (int)$moduleInfo['position'] > (int)$position) ||
			(isset($moduleInfo['m.position']) && (int)$moduleInfo['m.position'] > (int)$position))
			return $moduleInstance->updatePosition($hookPayment, 0, (int)$position);
		return $moduleInstance->updatePosition($hookPayment, 1, (int)$position);
	}

	public function moveRightColumn($position)
	{
		if (_PS_VERSION_ < '1.5')
			$hookRight = (int)Hook::get('rightColumn');
		else
			$hookRight = (int)Hook::getIdByName('rightColumn');

		$moduleInstance = Module::getInstanceByName($this->name);

		if (_PS_VERSION_ < '1.5')
			$moduleInfo = Hook::getModuleFromHook($hookRight, $moduleInstance->id);
		else
			$moduleInfo = Hook::getModulesFromHook($hookRight, $moduleInstance->id);

		if ((isset($moduleInfo['position']) && (int)$moduleInfo['position'] > (int)$position) ||
			(isset($moduleInfo['m.position']) && (int)$moduleInfo['m.position'] > (int)$position))
			return $moduleInstance->updatePosition($hookRight, 0, (int)$position);
		return $moduleInstance->updatePosition($hookRight, 1, (int)$position);
	}
}
