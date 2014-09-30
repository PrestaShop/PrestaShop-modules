{*
* 2007-2011 PrestaShop
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

<link type="text/css" rel="stylesheet" href="{$module_dir|escape:'none'}assets/css/jquery.dataTables.min.css" />
<link type="text/css" rel="stylesheet" href="{$css_version|escape:'none'}" />
{if $cheats}
<link type="text/css" rel="stylesheet" href="{$module_dir|escape:'none'}assets/css/firefox-cheats.css" />
{/if}
<script type="text/javascript" src="{$module_dir|escape:'none'}assets/js/jquery.min.js"></script>
<script type="text/javascript" src="{$module_dir|escape:'none'}assets/js/jquery.blockUI.js"></script>
<script type="text/javascript" src="{$module_dir|escape:'none'}assets/js/jquery-1102.min.js"></script>
<script type="text/javascript" charset="utf8" src="{$module_dir|escape:'none'}assets/js/jquery.dataTables.js"></script>

<form class="psplugin" id="psplugin" action="{$action_post|escape:'none'}" method="POST">
    <h1>
        <img src="{$module_dir|escape:'none'}assets/images/logops_228x56.png" />
        <span class="btn-register">
            <a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor" target="_blank" class="pagseguro-button green-theme normal">
                {l s='Faça seu cadastro' mod='pagseguro'}
            </a>
        </span>
    </h1>    
    <ul id="menuTab">
    {foreach from=$tab item=li}
        <li id="menuTab{$li.tab|escape:'htmlall':'UTF-8'}" class="menuTabButton {if $li.selected}selected{/if}">{if $li.icon != ''}<img src="{$li.icon|escape:'htmlall':'UTF-8'}" alt="{$li.title|escape:'htmlall':'UTF-8'}"/>{/if} {$li.title|escape:'htmlall':'UTF-8'}</li>
    {/foreach}
    </ul>
    <div id="tabList">
    {foreach from=$tab item=div}
        <div id="menuTab{$div.tab|escape:'htmlall':'UTF-8'}Sheet" class="tabItem {if $div.selected}selected{/if}">
            {$div.content|escape:'none'}
        </div>
    {/foreach}
    </div>

    <div id="divSalvar">
        <p class="center" id="pSalvar">
        	<input type="submit" id='update' class='pagseguro-button green-theme normal' name='btnSubmit' value="Salvar" />
        </p>
    </div>

	<input type='hidden' id='hiddenMenuTab' name='menuTab' value='{$menu_tab|escape:"none"}' />

</form>
<br>
<script type="text/javascript">
    {literal}

        jQuery( document ).ready(function() {
            jQuery('#content').removeClass("nobootstrap"); 
            jQuery('#content').addClass("nobootstrap-ps"); 
        });

        var tip = 'O botão salvar só será habilitado quando os campos E-mail e Token forem preenchidos.';

        if(document.getElementById('pagseguro_email').value.length == ""){

                document.getElementById("pSalvar").innerHTML = "<a data-tooltip='"+this.tip+"' id='tooltip'><button id='update' class='pagseguro-button green-theme normal' name='btnSubmit' disabled />Salvar</button></a>";

        }

        if(document.getElementById('pagseguro_token').value.length == ""){

                document.getElementById("pSalvar").innerHTML = "<a data-tooltip='"+this.tip+"' id='tooltip'><button id='update' class='pagseguro-button green-theme normal' name='btnSubmit' disabled />Salvar</button></a>";

        }

        function validarForm(formInput){ 

            if (formInput == "pagseguro_email")
            {
                var nInput = 'pagseguro_token';
            } else {
                var nInput = 'pagseguro_email';
            }

            if(document.getElementById(formInput).value.length == ""){

                document.getElementById("pSalvar").innerHTML = "<a data-tooltip='"+this.tip+"' id='tooltip'><button id='update' class='pagseguro-button green-theme normal' name='btnSubmit' disabled />Salvar</button></a>";

            } else if( (document.getElementById(formInput).value.length != "") & (document.getElementById(nInput).value.length != "")) {
                 document.getElementById('update').disabled=false;
                 document.getElementById("pSalvar").innerHTML = "<button id='update' class='pagseguro-button green-theme normal' name='btnSubmit' />Salvar</button>";
            }
        }

        var url = location.href;  
        var baseURL = url.substring(0, url.indexOf('/', 18));
        var paginaAtual = 1;
        var menuTab = 'menuTab1';

        
        jQuery('.menuTabButton').on('click',
            function () {
                jQuery('.menuTabButton.selected').removeClass('selected');
                jQuery(this).addClass('selected');
                jQuery('.tabItem.selected').removeClass('selected');
                jQuery('#' + this.id + 'Sheet').addClass('selected');
                menuTab = this.id;
                document.getElementById('menuTab').value = menuTab;
                jQuery("input[name=menuTab]").val(menuTab);
                hideInput(this.id);
        });

        
        function hideInput(menuTab) {

            if (menuTab == 'menuTab2') {
                if (jQuery('select#pagseguro_log').val() == '0') {
                    if(jQuery('#directory-log').is(':visible')) {
                         jQuery('#directory-log').hide();
                     }
                }
                if (jQuery('select#pagseguro_recovery').val() == '0') {
                    if(jQuery('#directory-val-link').is(':visible')) {
                        jQuery('#directory-val-link').hide();
                    }
                }
            }
        }

        jQuery('#pagseguro_log').on('change',
            function(e) {
                jQuery('#directory-log').toggle(300);
            }
        );

        jQuery('#pagseguro_recovery').on('change',
            function(e) {
                jQuery('#directory-val-link').toggle(300);
            }
        );
        
        function blockModal(block) {
            if(block == 1) {
                jQuery.blockUI({
                    message: '<h1>Carregando '+'<img class="blockUImg" src="../modules/pagseguro/assets/images/loading.gif" />'+'</h1>',
                    css: {
                        border: 'none',
                        padding: '15px',
                        backgroundColor: '#4f7743',
                        'border-radius': '10px',
                        '-webkit-border-radius': '10px',
                        '-moz-border-radius': '10px',
                        color: '#90e874'
                    },
                    overlayCSS: { backgroundColor: 'gray' }
                });
            } else {
                setTimeout(jQuery.unblockUI, 1000);
            }
        }
        
        jQuery("input[name = 'search']").on('click',
            function() {
                blockModal(1);
                reloadTable();             
        });

        function reloadTable() {

                jQuery.ajax({
                    type: 'POST',
                    url: '../modules/pagseguro/features/conciliation/conciliation.php',
                    dataType : "json",
                    data: {dias: jQuery('#pagseguro_dias_btn').val()},
                    success: function(result) {
                        if (result != "") {
                            jQuery('#htmlgrid').dataTable().fnClearTable(true);           
                            jQuery('#htmlgrid').dataTable().fnAddData(result);
                            jQuery('#htmlgrid').dataTable()._fnInitComplete();
                        }

                        blockModal(0);
                    },
                    error: function() {
                        blockModal(0);
                    }
                });
        }
        
        function editRedirect(rowId){
            var token = jQuery('#adminToken').val();
            var url = jQuery('#urlAdminOrder').val();

            window.open(url + '&id_order='+rowId+'&vieworder&token='+token);
            
        }
        
        function duplicateStatus(rowId,rowIdStatusPagSeg,rowIdStatusPreShop){

            if(rowIdStatusPagSeg != rowIdStatusPreShop && rowIdStatusPagSeg != ""){
                blockModal(1);
                jQuery.ajax({
                    type: 'POST',
                    url: '../modules/pagseguro/features/conciliation/conciliation.php',
                    data: {idOrder: rowId, newIdStatus: rowIdStatusPagSeg, orderDays: jQuery('#pagseguro_dias_btn').val() },
                    success: function(result) {

                        reloadTable();
                    },
                    error: function() {
                        blockModal(0);
                        alert('Não foi possível corrigir o Status.\nTente novamente');
                    }
                });
            }
        }

        jQuery('#pagseguro_checkout').on('change',
            function(e) {
                if(jQuery('option:selected', this).attr('value') == 0) {
                    jQuery('#pagseguro_checkout').attr('hint','No checkout padrão o comprador, após escolher os produtos e/ou serviços, é redirecionado para fazer o pagamento no PagSeguro.');
                } else {          
                    jQuery('#pagseguro_checkout').attr('hint','No checkout lightbox o comprador, após escolher os produtos e/ou serviços, fará o pagamento em uma janela que se sobrepõe a sua loja.');
                }
                jQuery('#pagseguro_checkout').focus();
            }
        );

        jQuery('input, select').on('focus',
            function(e) {
                _jQuerythis = jQuery(this);
                jQuery(this).addClass('focus');
                jQuery(this).parent().parent().find('.hintps').fadeOut(210, function() {
                    jQuery(this).html(_jQuerythis.attr('hint')).fadeIn(210);
                });
            }
        );

        jQuery('input, select').on('blur',
            function(e) {
                jQuery(this).removeClass('focus');
            }
        );
        
        jQuery(".tab").on('click',
            function(e){
                jQuery(this).parent().parent().find('.hintps').fadeOut(5);
        });

        jQuery('.alert, .conf').insertBefore('#mainps');

        jQuery('.alert, .conf').on('click',
            function() {
                    jQuery(this).fadeOut(450);
            }
        );

        setTimeout(function() {
            jQuery('.conf').fadeOut(450);
        }, 3000);

    {/literal}
</script>
