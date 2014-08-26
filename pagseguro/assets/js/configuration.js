/**
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$().ready(function(){
    pagseguro_config.constructor();
});

var pagseguro_config = ({
    
    constructor : function(){
        pagseguro_config.initBinds();
        pagseguro_config.initialVerification();
    },
    
    initBinds : function(){
        $('select#pagseguro_log').bind('change', function(){
            if ($(this).val() == '1')
                $('table tr#logDir').show();
            else 
                $('table tr#logDir').hide();
        });
    },
    
    initialVerification : function(){
        $('select#pagseguro_log').change();
    }

});

