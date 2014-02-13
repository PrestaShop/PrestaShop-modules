{*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*}
<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/js/jquery.bpopup.min.js" type="text/javascript"></script>
<script>
    var textmaster_token = '{$token|escape:'htmlall':'UTF-8'}';
    var textmaster_ajax_uri = '{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/textmaster.ajax.php';
    var module_url = '{$module_url|escape:'UTF-8'}';
    var id_document_selected = 0;
    var message_sent = '{l s='Message was successfully sent' mod='textmaster'}';
    var id_shop = '{$project->id_shop|escape:'htmlall':'UTF-8'}';
    
    $(document).ready(function(){
        
        if ($('#ajax_running').length == 0)
        {
            $('body').prepend('<div id="ajax_running"><img alt="" src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/img/ajax-loader-yellow.gif"> {l s='Loading' mod='textmaster'}...</div>');
        }
        
        $('img.approve').click(function(e){
            e.stopPropagation();
        });

        $('#project_view table.document tbody tr').click(function(){
            $('#ajax_running').show();
            $('#reponse_message').hide();
            $(this).addClass('active').siblings('tr').removeClass('active');

            id_document_selected = $(this).attr('id').split("_");
            id_document_selected = id_document_selected[2];
            
            $.ajax({
                type: "POST",
                async: false,
                dataType: 'json',
                url: textmaster_ajax_uri,
                data: 'ajax=true&token=' + encodeURIComponent(textmaster_token) + '&get_document=true&id_shop=' + id_shop + '&id_document=' + encodeURIComponent(id_document_selected) + '&id_project='+encodeURIComponent('{$smarty.get.id_project|escape:'htmlall':'UTF-8'}'),
                success: function(resp)
                {
                    if ('original_content' in resp.api)
                    {
                        var $tbody = $('#document_source table tbody');
                        $tbody.html('');
                        $.each(resp.api.original_content, function(element, value) {
                            $tr = $('<tr />');
                            $('<td />').html(element).appendTo($tr);
                            $('<td />').html(value.original_phrase).appendTo($tr);
                            if ('author_work' in resp.api && resp.api.author_work != null && element in resp.api.author_work)
                                $('<td />').html(resp.api.author_work[element]).appendTo($tr);
                            else
                                $('<td />').html('--').appendTo($tr);
                            $tbody.append($tr); // $tr.text()
                            //$('#showTableHtml').slideDown('slow');
                        });
                    }

                        
                    $('#document_source').show();
                    if (resp.comments != '')
                    {    
                        $('#document_communication_history').html(resp.comments);
                        $('#document_communication').show();
                    }
                    else
                    {
                        $('#document_communication').hide();
                    }
                }
            });
            
            $('#ajax_running').hide();
        });
        
        $('#sendMessage').click(function(){
            $('#ajax_running').show();
            $('#reponse_message').hide();
            $.ajax({
                type: "POST",
                async: false,
                dataType: 'json',
                url: textmaster_ajax_uri,
                data: 'ajax=true&token=' + encodeURIComponent(textmaster_token) + '&submitComment=true' + '&id_document='+id_document_selected+'&message=' + encodeURIComponent($('#messageText').val()),
                success: function(resp)
                {
                    if (resp.errors)
                        $('#reponse_message').removeClass('message_success').addClass('message_error').text(resp.error).show();
                    else
                    {
                        $('#messageText').val('');
                        $('#document_communication_history').html(resp.messages);    
                        $('#reponse_message').removeClass('message_error').addClass('message_success').text(message_sent).show();
                    }
                }
            });
            
            $('#ajax_running').hide();
        });
        
        $('#document .approve').click(function(e){
            e.stopPropagation();
        });
    });
    
    function updateDocument(id_product, id_document, confirmText)
    {
        var approveUrl = module_url+'&id_document='+encodeURIComponent(id_document)+'&approveDocument&update_product_only=1';
        if (confirmText != '' && id_product != '')
        {
            if (confirm(confirmText))
            {
                window.location = approveUrl+'&id_product='+encodeURIComponent(id_product);
                return true;
            }
            else return false;   
        }
        window.location = approveUrl;
    }
    
    function approveDocument(id_product, id_document, confirmText)
    {
        var approveUrl = module_url+'&id_document='+encodeURIComponent(id_document)+'&approveDocument';
        if (confirmText != '' && id_product != '')
        {
            if (confirm(confirmText))
            {
                window.location = approveUrl+'&id_product='+encodeURIComponent(id_product);
                return true;
            }
            else return false;   
        }
        window.location = approveUrl;
    }
    
    function showTableHtml()
    {
        var html = $('#document_source').html();
        html = htmlspecialchars_decode(html);
        $('#tableToDisplayHtmlContent').html(html);
        $('#tableToDisplayHtml').bPopup();
    }
    
    function htmlspecialchars_decode (string, quote_style) {
        var optTemp = 0,
          i = 0,
          noquotes = false;
        if (typeof quote_style === 'undefined') {
          quote_style = 2;
        }
        string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
        var OPTS = {
          'ENT_NOQUOTES': 0,
          'ENT_HTML_QUOTE_SINGLE': 1,
          'ENT_HTML_QUOTE_DOUBLE': 2,
          'ENT_COMPAT': 2,
          'ENT_QUOTES': 3,
          'ENT_IGNORE': 4
        };
        if (quote_style === 0) {
          noquotes = true;
        }
        if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
          quote_style = [].concat(quote_style);
          for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
              noquotes = true;
            } else if (OPTS[quote_style[i]]) {
              optTemp = optTemp | OPTS[quote_style[i]];
            }
          }
          quote_style = optTemp;
        }
        if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
          string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
          // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
        }
        if (!noquotes) {
          string = string.replace(/&quot;/g, '"');
        }
        // Put this in last place to avoid escape being double-decoded
        string = string.replace(/&amp;/g, '&');
      
        return string;
    }

</script>

<div id="project_view">
    <div style="float:left; margin-right:30px">
        <fieldset>
            <legend>{l s='Project details' mod='textmaster'}</legend>
            {$project->project_briefing|escape:'htmlall':'UTF-8'}
        </fieldset>
        <br />
        <fieldset>
            <legend>{l s='Project documents' mod='textmaster'}</legend>
            <p class="preference_description">{l s='Click on a document to see the results below, and communication history on the right' mod='textmaster'}</p>
            {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/project/documents.tpl"}
        </fieldset>
        <br />
        <div id="document_source" style="margin-bottom: 15px; display:none;">
            <fieldset>
                <legend>{l s='Content' mod='textmaster'}</legend>
                
                <table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table">
                    <thead>
                        <tr style="height: 40px">
                            <th>{l s='Element' mod='textmaster'}</th>
                            <th>{l s='Source text' mod='textmaster'}</th>
                            <th>{l s='Result text' mod='textmaster'}</th>
                        </td>
                    </thead>
                    <tbody id="documents_list">
                    </tbody>
                </table>
            </fieldset>
        </div>
    </div>
    
    <div id="document_communication" style="float:left; display:none">
        <fieldset>
            <legend>{l s='Communication with the author' mod='textmaster'}</legend>
                <textarea style="width:746px" rows="15" id="messageText"></textarea>
            </textarea>
            <br /><br />
            <input type="button" value="{l s='Send' mod='textmaster'}" class="button" id="sendMessage" />
            <div id="reponse_message" style="float:right; display:none;"></div>
            <br /><br />
            <div id="document_communication_history"></div>
        </fieldset>
    </div>
    <div class="clear"></div>
</div>
