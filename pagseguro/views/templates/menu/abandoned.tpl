{*
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
*  @version  Release: $Revision: 6594 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="row">
    <div class="col-md-12">
        <h2 id="title-align">{$titulo|escape:'none'}</h2>
    </div>
</div>

{if $is_recovery_cart}

<input type='hidden' id='adminToken' value='{$adminToken|escape}' />
<input type='hidden' id='urlAdminOrder' value='{$urlAdminOrder|escape}' />

    {if $errorMsg && count($errorMsg)}
    <div class="row">
        <div class="col-md-12">
            <a href="javascript:void(0)" class="pagseguro-button green-theme normal" id="search_abandoned_button">{l s='Realizar Nova Pesquisa' mod='pagseguro'}</a>
            {foreach from=$errorMsg key=error_key item=error_value}
                <div class="error">
                        {$error_value|escape:'none'}
                </div>
            {/foreach}
        </div>
    </div>

    {else}
    <div class="row">
        <div class="col-md-12">
            <a href="javascript:void(0)" class="pagseguro-button green-theme normal" id="search_abandoned_button">{l s='Pesquisar' mod='pagseguro'}</a>
            <a href="javascript:void(0)" class="pagseguro-button green-theme normal" id="send_email_button">{l s='Envio em massa' mod='pagseguro'}</a>
        </div>
    </div>
    {/if}
    <div class="row">
        <div class="col-md-12">
            <table id='htmlgrid_abandoned' class='gridAbandoned abandoned-table' cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th class="col-md-1 bg-remove">Enviar</th>
                        <th class="col-md-2">Data do Pedido</th>
                        <th class="col-md-3">ID PrestaShop</th>
                        <th class="col-md-2">Validade do link</th>
                        <th class="col-md-1">Enviar e-mail</th>
                        <th class="col-md-1">Visualizar</th>
                        
                    </tr>
                </thead>
                <tbody id="resultTable">
                    {$tableResult|escape:'none'}
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6">Validade do(s) link(s) para envio de e-mail:  {$days_recovery|escape:'none'} dias</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="down-foot"></div>
        </div>
    </div>

    <script type="text/javascript">
        {literal}
            /****
             * AJAX: Search for Abandoned Transactions
             */
            jQuery('#search_abandoned_button').click(function () {
                blockModal(1);        
                jQuery.ajax({
                    type: "POST",
                    url: '../modules/pagseguro/features/abandoned/abandoned.php',
                    dataType : "html",
                    data: {'getAbandoned':true},
                    success: function(response) {

                        var nResponse = JSON.parse(response);
                        var array = new Array();
                        if ( nResponse.tableResult.length > 0 ) {
                            for (var i = 0; i < nResponse.tableResult.length; i++)
                            {
                                var checkbox = '<input type="checkbox" id="send_'+i+'" name="send_emails[]" value="customer='+nResponse.tableResult[i].customer+'&amp;reference='+nResponse.tableResult[i].reference+'&amp;recovery='+nResponse.tableResult[i].recovery_code+'">';

                                var email = "<a href='javascript:void(0)' onclick='javascript:sendSingleEmail(&quot;customer="+nResponse.tableResult[i].customer+"&amp;reference="+nResponse.tableResult[i].reference+"&amp;recovery="+nResponse.tableResult[i].recovery_code+"&quot;);'> <img src='../img/admin/email.gif' title='Enviar Email'></a>";

                                var visualizar = '<a onclick="editRedirect('+nResponse.tableResult[i].reference+')" style="cursor:pointer"><img src="../img/admin/details.gif" border="0" alt="edit" title="Editar"></a>';

                                array[i] = [[checkbox],[nResponse.tableResult[i].data_add_cart],[nResponse.tableResult[i].masked_reference],[nResponse.tableResult[i].data_expired],[email],[visualizar]];
                            }

                            jQuery('#htmlgrid_abandoned').dataTable().fnClearTable(true);           
                            jQuery('#htmlgrid_abandoned').dataTable().fnAddData(array);
                        } else {
                            jQuery('#htmlgrid_abandoned').dataTable().fnClearTable(true);
                        }
                        blockModal(0);
                    }
                });
                return false;
            });
            
            /****
             * AJAX: Send Single E-mail
             */
            function sendSingleEmail(content) {
                blockModal(1);    
         
                jQuery.ajax({
                    type: "GET",
                    url: '../modules/pagseguro/features/abandoned/ajax-abandoned.php',
                    data: 'action=singleemail&'+content,
                    success: function(response) {
                        blockModal(0);
;
                        jQuery( ".psplugin" ).before(response);
                            
                    }
                });
                return false;
            }

            /****
             * AJAX: Send Multiple E-mails
             */
            jQuery('#send_email_button').click(function () {

                var checkboxValues = new Array();
                jQuery('input[name="send_emails[]"]:checked').each(function() {
                    checkboxValues.push(jQuery(this).val());
                });
                
                if(!checkboxValues.length == 0) {
                    blockModal(1);
                
                    jQuery.ajax({
                        type: "GET",
                        url: '../modules/pagseguro/features/abandoned/ajax-abandoned.php',
                        data: 'action=multiemails&'+jQuery('input[name="send_emails[]"]').serialize(),
                        dataType: 'json',
                        success: function(response) {
                            jQuery( ".psplugin" ).before(response.divError);
                            blockModal(0);
                        },
                        error: function(response) {
                            console.log(response.responseText);
                            jQuery( ".psplugin" ).before(response.responseText);
                            blockModal(0);
                            
                        }
                    });
                } else {
                    jQuery( ".psplugin" ).before('<div class="module_error alert error" style="width: 896px"> Selecione pelo menos um email </div>');
                }
                return false;
            });
            
            /****
             * AJAX: DataTables 
             */
             var i = 0;
            jQuery(document).ready(function(){ 
              var flow = 0;  
              var totalRows = 0;
              jQuery('#htmlgrid_abandoned').dataTable(
                {       
                    "bStateSave": true,    
                    "info": false,
                    "lengthChange": false,
                    "searching": false,
                    "pageLength": 10,
                     "aoColumnDefs": [
                           { 'bSortable': false, 'aTargets': [ 0, 4, 5 ] },
                           { "sClass": "tabela", 'aTargets': [ 0, 1, 2, 3, 4, 5 ] }
                       ],
                   "oLanguage": {
                        "sEmptyTable":"Realize uma pesquisa.",
                        "oPaginate": {
                            "sNext": 'Próximo',
                            "sLast": 'Último',
                            "sFirst": 'Primeiro',
                            "sPrevious": 'Anterior'
                         }
                    },

                    "fnDrawCallback": function(oSettings) {

                        var table = jQuery('#htmlgrid_abandoned').DataTable();
                        var info = table.page.info();

                        var oTable = jQuery('#htmlgrid_abandoned').dataTable();
                        totalRows = oTable.fnGetData().length;

                        if(totalRows >= 11){                                                                        
                            document.getElementById('htmlgrid_abandoned_paginate').style.display = "block";
                            flow = 1;              
                        } else {                                                                                                
                            document.getElementById("htmlgrid_abandoned_paginate").style.display="none";
                            flow = 0;
                        } 

                        if (info.page == 0) {
                            jQuery('a#htmlgrid_abandoned_previous').css('display','none');
                        }       
                        if ((info.page+1) == info.pages) {
                            jQuery('a#htmlgrid_abandoned_next').css('display','none');
                        }       
                        i++;    

                    }    

                });
            });

        {/literal}
    </script>
  
{else}
    <div class="warn">
        <p class="small text-center">Ative a opção "Recuperação de Carrinho" para poder desfrutar a nova funcionalidade. </p>
    </div>

    <table id="htmlgrid" cellspacing="0" width="100%"></table>
{/if}


