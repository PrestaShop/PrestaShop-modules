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
		<label>E-MAIL*</label>
		<br>
		    <input type="text" name="pagseguro_email" id="pagseguro_email" value="{$email|escape}" maxlength="60" hint="Para oferecer o PagSeguro em sua loja é preciso ter uma conta do tipo vendedor ou empresarial. Se você ainda não tem uma conta PagSeguro &lt;a href=&quot;https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&amp;tipo=cadastro#!vendedor&quot; target=&quot;_blank&quot;&gt; clique aqui &lt;/a&gt;, caso contrário informe neste campo o e-mail associado à sua conta PagSeguro." onchange="validarForm('pagseguro_email')">
		<br>
		<label>TOKEN*</label>
		<br>
		    <input type="text" name="pagseguro_token" id="pagseguro_token" value="{$token|escape}" maxlength="32" hint="Para utilizar qualquer serviço de integração do PagSeguro, é necessário ter um token de segurança. O token é um código único, gerado pelo PagSeguro. Caso não tenha um token &lt;a href=&quot;https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml&quot; target=&quot;_blank&quot;&gt; clique aqui &lt;/a&gt;, para gerar." onchange="validarForm('pagseguro_token')">
		<br>
		<label>CHARSET</label>
		<br>
		    <select id="pagseguro_charset" name="pagseguro_charset" class="select" hint="Informe a codificação utilizada pelo seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.">
		            {html_options values=$keychartset output=$valueschartset selected=$escolhacharset|escape:'none'}
		    </select>
		<br>
		<label>CHECKOUT</label>
		<br>
		    <select id="pagseguro_checkout" name="pagseguro_checkout" class="select" hint="No checkout padrão o comprador, após escolher os produtos e/ou serviços, é redirecionado para fazer o pagamento no PagSeguro.">
		            {html_options values=$keycheckout output=$valuescheckout selected=$escolhacheckout|escape:'none'}
		    </select>
		<br>
		<p class="small">* Campos obrigatórios</p>
	</div>
	<div class="col-md-6">
		
		<div class="hintps _config"></div>	
	</div>
</div>

