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
		<h2>{$titulo|escape:'none'}</h2>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<p class="text-justified"><small>Esta consulta permite obter as transações recebidas por você em um intervalo de datas. Ela pode ser usada periodicamente para verificar se o seu sistema recebeu todas as notificações de transações enviadas pelo PagSeguro, de forma a conciliar as transações 
armazenadas em seu sistema com o PagSeguro.</small></p>
	</div>
</div>

<input type='hidden' id='adminToken' value='{$adminToken|escape}' />
<input type='hidden' id='urlAdminOrder' value='{$urlAdminOrder|escape}' />

{if (!$regError)}
	<div class="row">
		<div class="col-md-12">
			<div id="conciliationSearch" class="form-group">
				<div class="row">
					<div class="col-md-12">
				    	<label>DIAS</label>
				    </div>
			    </div>

			    <div class="row">
					<div class="col-md-12">

					    <select id='pagseguro_dias_btn' name='pagseguro_dias' class='select'>
					        {$dias|escape:'none'}
					    </select>

					    <input class="pagseguro-button green-theme normal" type='button' name='search' value='Pesquisar' />
				    </div>
			    </div>

		    </div>
    	</div>
    </div>
    
    <div class="row">
		<div class="col-md-12">
		    <table id='htmlgrid' class='gridConciliacao conciliation-table' cellspacing="0" width="100%">
		        <thead>
		            <tr>
		                <th class="col-md-1">Data</th>
		                <th class="col-md-2">ID PrestaShop</th>
		                <th class="col-md-3">ID PagSeguro</th>
		                <th class="col-md-2">Status PrestaShop</th>
		                <th class="col-md-2">Status PagSeguro</th>
		                <th class="col-md-1">Editar</th>
		                <th class="col-md-1">Atualizar</th>
		            </tr>
		        </thead>
		        <tbody id="resultTable">
		            {$tableResult|escape:'none'}
		        </tbody>
		    </table>
    	</div>
    </div>

    <!-- JQuery and Javascripts for Conciliation -->
    <script type="text/javascript">
        {literal}

        	/***
        	 *	Doc.Ready();
        	 */
            var i = 0; 
            var currentPage = 0;
            jQuery(document).ready(function(){ 
              var flow = 0;  
              var totalRows = 0;
              jQuery('#htmlgrid').dataTable(
                {       
                    "bStateSave": true,
                    "info": false,
                    "lengthChange": false,
                    "searching": false,
                    "pageLength": 10,
                     "aoColumnDefs": [
                           { 'bSortable': false, 'aTargets': [ 5, 6 ] },
                           { "sClass": "tabela", 'aTargets': [ 1, 2, 3, 4, 5, 6 ] }
                       ],
                   "oLanguage": {
                        "sEmptyTable":"Nenhuma transação a ser conciliada. Realize uma pesquisa.",
                        "oPaginate": {
                            "sNext": 'Próximo',
                            "sLast": 'Último',
                            "sFirst": 'Primeiro',
                            "sPrevious": 'Anterior'
                         }
                    },

                    "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                            if ( aData[3] == aData[4])
                            {
                                jQuery(nRow).css('color', 'green')
                                jQuery(nRow).css('fontSize', '12px')
                                jQuery(nRow).css('textAlign', 'center')
                            } else {
                                jQuery(nRow).css('color', 'red')
                                jQuery(nRow).css('fontSize', '12px')
                                jQuery(nRow).css('textAlign', 'center')
                            }
                            
                    },

                    "fnDrawCallback": function(oSettings) {

                        var table = jQuery('#htmlgrid').DataTable();
                        var info = table.page.info();

                        var oTable = jQuery('#htmlgrid').dataTable();
                        totalRows = oTable.fnGetData().length;
                        
                        if(totalRows >= 11){                                                                        
                            document.getElementById('htmlgrid_paginate').style.display = "block";
                            flow = 1;              
                        } else {                                                                                                
                            document.getElementById("htmlgrid_paginate").style.display="none";
                            flow = 0;
                        } 

                        if (info.page == 0) {
                            jQuery('a#htmlgrid_previous').css('display','none');
                        }       
                        if ((info.page+1) == info.pages) {
                            jQuery('a#htmlgrid_next').css('display','none');
                        }       
                        i++;    

                        page  = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength); 
                        if (page != 0) 
                            currentPage = page; 
                    },
                    "initComplete": function (oSettings) {              
                        oTable = this;  
                        oTable.fnPageChange(currentPage);   
                    }
                });	
            });

        {/literal}
    </script>
    <!-- /JQuery and Javascripts for Conciliation -->

    <div class="row">
		<div class="col-md-12">
		    <p class='info' style="text-align: center;">Não encontra suas antigas transações para conciliar? 
		        <a data-tooltip='Na instalação do módulo do PagSeguro é criada uma referência com cinco (5) caracteres aleatórios por exemplo - #asf9 - que serão enviados na hora da compra. Caso não exista a referência de sua loja o PagSeguro não irá retornar compras a serem conciliadas.'>
		            <img src='../img/admin/help.png' alt='ajuda' />
		        </a>
		    </p>
	    </div>
    </div>
  
{else}
	<div class="row">
		<div class="col-md-12">
		    <div class="warn">
		        <p class="small text-center">Para conciliar transações é necessário configurar um email e token válidos.</p>
		    </div>
	    </div>
    </div>

    <div class="row">
		<div class="col-md-12">
    		<table id="htmlgrid" class="table" cellspacing="0"></table>
    	</div>
    </div>
{/if}


