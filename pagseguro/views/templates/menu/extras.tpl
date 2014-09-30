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
    <div class="col-md-6">
 
            <label>URL DE NOTIFICAÇÃO</label>
            <br>
                <input type="text" name="pagseguro_notification_url" id="pagseguro_notification_url" value="{$urlNotification|escape:'none'}" maxlength="255" hint="Sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo.">
            <br>
            <label>URL DE REDIRECIONAMENTO</label>
            <br>
                <input type="text" name="pagseguro_url_redirect" id="pagseguro_url_redirect" value="{$urlRedirection|escape:'none'}" maxlength="255" hint="Ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado de volta para sua loja ou para a URL que você informar neste campo. Para utilizar essa funcionalidade você deve configurar sua conta para aceitar somente requisições de pagamentos gerados via API. &lt;a href=&quot;https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml&quot; target=&quot;_blank&quot;&gt; Clique aqui &lt;/a&gt; para ativar este serviço.">
            <br>
            <label>LOG</label>
            <br>
                <select id="pagseguro_log" name="pagseguro_log" class="select" hint="Deseja habilitar a geração de log?" >
                    {html_options values=$keylogactive output=$valueslogactive selected=$escolhalogactive|escape:'none'}
                </select>
            <br>
            <div id="directory-log" name="directory-log">
            <label>DIRETÓRIO</label>
            <br>
                <input type="text" name="pagseguro_log_dir" id="pagseguro_log_dir" value="{$fileLocation|escape:'none'}" hint= "Diretório a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/log_ps.log"/>
            <br>
            </div>
           
	    <div id="abandoned" name="abandoned">	
		
		 <label>LISTAR TRANSAÇÕES ABANDONADAS?</label>
            <br>
                <select id="pagseguro_recovery" name="pagseguro_recovery" class="select" hint="Ao ativar esta funcionalidade, você poderá listar as transações abandonadas e disparar, manualmente, um e-mail para seu comprador. Este e-mail conterá um link que o redirecionará para o fluxo de pagamento, exatamente no ponto onde ele parou." >
                    {html_options values=$keyrecoveryactive output=$valuesrecoveryactive selected=$escolharecoveryactive|escape:'none'}
                </select>
            <br>
            <div id="directory-val-link" name="directory-val-link">
            <label>TRANSAÇÕES INICIADAS HÁ NO MÁXIMO (dias)</label>
            <br>
                <select id="pagseguro_days_recovery" name="pagseguro_days_recovery" class="select" hint="Somente as transações iniciadas há no máximo a quantidade de dias definidas neste campo serão consideradas para a listagem de recuperação de checkout." >
                    {html_options values=$keydaystorecovery output=$valuesdaystorecovery selected=$escolhadaystorecovery|escape:'none'}
                </select>
            <br>
		</div>


            </div>
            
        </div>
	<div class="col-md-6">
		
		<div class="hintps _config"></div>	
	</div>
</div>

<script type="text/javascript">
    {literal}
        if ($("select[name=pagseguro_log] option:selected").val() == 0) {
            $("#directory-log").toggle(300);
        }
        if ($("select[name=pagseguro_recovery] option:selected").val() == 0) {
            $("#directory-val-link").toggle(300);
        }
    {/literal}
</script>
