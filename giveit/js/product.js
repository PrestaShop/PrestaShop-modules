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

$(document).ready(function(){
    $('input[name="saveGiveItProductSettings"], button[name="saveGiveItProductSettings"]').live('click', function(){
        $('input[name="saveGiveItProductSettings"]').attr('disabled', 'disabled');
        $('#giveit_messages_container div').slideUp();
        $('#ajax_running').show();
        var params = '';
        $('select[name^="combinations["]').each(function(){
            params += '&'+$(this).attr('name')+'='+$(this).val();
        });

        $.ajax({
            type: "POST",
            async: false,
            dataType: 'json',
            url: giveit_ajax_url,
            data: 'updateCombinationSettings=true&token=' + encodeURIComponent(giveit_token) + '&id_product=' + encodeURIComponent(id_product) + '&id_shop=' + id_shop + params,
            success: function(resp)
            {
                if ('error' in resp)
                {
                    $('#giveit_messages_container .error').text(resp.error).slideDown();
                }
                else if ('success' in resp)
                {
                    $('#giveit_messages_container .conf').text(resp.success).slideDown();
                }
                
                $('input[name="saveGiveItProductSettings"]').removeAttr('disabled');
                $.scrollTo('#giveit_messages_container', 1200, {offset: -100});
            }
        });
        
        $('#ajax_running').hide();
    });
});