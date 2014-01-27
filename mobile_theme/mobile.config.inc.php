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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (_THEME_NAME_ == 'prestashop_mobile' || (isset($_GET['ps_mobile_site']) && $_GET['ps_mobile_site'] == 1))
{
  /* Do not allow One-page Checkout and Guest Checkout with Mobile template */
	Configuration::set('PS_ORDER_PROCESS_TYPE', 0);
	Configuration::set('PS_GUEST_CHECKOUT_ENABLED', 0);

	if (version_compare(_PS_VERSION_, '1.4', '<') && !method_exists('Product', 'convertAndFormatPrice'))
	{
		function convertAndFormatPrice($price, $currency = false)
		{
			if (!$currency)
				$currency = Currency::getCurrent();
			return Tools::displayPrice(Tools::convertPrice($price, $currency), $currency);
		}
		$smarty->register_modifier('convertAndFormatPrice', 'smarty_modifier_truncate');
	}
}
