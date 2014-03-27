/*
* 2013 Give.it
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@give.it so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 Give.it
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of Give.it
*/

if(typeof window.getProductAttribute == 'function')
{
    var _getProductAttribute = window.getProductAttribute;
    
    window.getProductAttribute = function(){
        _getProductAttribute();
        displayOrHideGiveItButton();
    };
}
else
{
    var _findCombination = window.findCombination;
    
    window.findCombination = function(){
        _findCombination();
        displayOrHideGiveItButton();
    };
}

function displayOrHideGiveItButton()
{
    $('.giveit_button_container').hide();
    $('.giveit_button_container[rel="'+$('#idCombination').val()+'"]').show();
}

$(document).ready(function(){
    product_attribute_id = $('#idCombination').val();
    if(product_attribute_id != "")
        displayOrHideGiveItButton();
});