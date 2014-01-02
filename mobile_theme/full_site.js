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

function appendFullsiteLink(i, h) {
    if (h && $(this).attr('rel') != 'external' && h.indexOf('ps_full_site=1') == -1)
    {
	if (h.indexOf('#') == -1)
	    return h + (h.indexOf('?') != -1 ? '&' : '?') + 'ps_full_site=1' + (location.search.indexOf('mobile_iframe=1') != -1 ? '&mobile_iframe=1' : '');
	else if ($(this).attr('data-ajax') == 'false')
	    return h.substr(0, h.indexOf('#')) + (h.indexOf('?') != -1 ? '&' : '?') + 'ps_full_site=1' + h.substr(h.indexOf('#'), h.length - 1) + (location.search.indexOf('mobile_iframe=1') != -1 ? '&mobile_iframe=1' : '');
    }
    if (h && $(this).attr('id') == 'ps_mobile_site' && location.search.indexOf('mobile_iframe=1') != -1)
	return h + '&mobile_iframe=1';

    return h;
}

$(function() {
    $('a').attr('href', appendFullsiteLink);
    $('form').attr('action', appendFullsiteLink);
});
