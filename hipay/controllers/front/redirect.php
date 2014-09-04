<?php
/*
* 2007-2014 PrestaShop 
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
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */

class HipayRedirectModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	
	public function __construct()
	{
		parent::__construct();
		$this->display_column_left = false;
	}

	public function initContent()
	{
		parent::initContent();
		if (!Context::getContext()->customer)
			Tools::redirect('index.php?controller=authentication&back=order.php');
		$hipay = Module::getInstanceByName('hipay');
		if (Validate::isLoadedObject($hipay))
			if ($hipay->payment() === false)
				return $this->setTemplate('redirect.tpl');
	}
}
