<?php
/*
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class AdminGlobKurier extends AdminTab {
	const URL_CONTROLLER = 'AdminModules';
	const URL_CONFIGURE = 'globkurier';
	const URL_TAB_MODULE = 'shipping_logistics';
	public function __construct()
	{
		$this->table = 'globkurier_order';
		parent::__construct();
		$redirect = 'index.php?controller='.self::URL_CONTROLLER;
		$redirect .= '&token='.Tools::getAdminTokenLite('AdminModules');
		$redirect .= '&configure='.self::URL_CONFIGURE;
		$redirect .= '&tab_module='.self::URL_TAB_MODULE;
		$redirect .= '&module_name='.self::URL_CONFIGURE;
		Tools::redirectAdmin($redirect);
	}
}
?>