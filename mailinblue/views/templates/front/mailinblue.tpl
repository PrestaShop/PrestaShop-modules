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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2014 PrestaShop SA
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<!-- mailin Newsletter module-->

<div id="newsletter_block_left" class="block" >

	<h4>{l s='Newsletter' mod='mailinblue'}</h4>

	<div class="block_content">	
		{if isset($msg) && $msg}
			<p class="{if $nw_error}warning_inline{else}success_inline{/if}" {if $nw_error} style="padding-top: 10px; font-size:10px;color:#DA0F00" {else} style="padding-top: 10px; font-size:10px;color:#008000" {/if} style="padding-top: 10px; font-size:10px;color:#DA0F00">{$msg}</p>
		{/if}
	
		<form action="{$link->getPageLink('index.php')}" method="post">
			<p style="padding-top:10px;">
				<input style="width: 170px;" type="text" name="email" size="18" value="{if isset($value) && $value}{$value}{else}{l s='your e-mail' mod='mailinblue'}{/if}" onfocus="javascript:if(this.value=='{l s='your e-mail' mod='mailinblue'}')this.value='';" onblur="javascript:if(this.value=='')this.value='{l s='your e-mail' mod='mailinblue'}';" />
			</p>
			
			{if isset($Mailin_dropdown) && $Mailin_dropdown==1}
				<p style="padding-top:10px;">
					<select name="action">
						<option value="0"{if isset($action) && $action == 0} selected="selected"{/if}>{l s='Subscribe' mod='mailinblue'}</option>
						<option value="1"{if isset($action) && $action == 1} selected="selected"{/if}>{l s='Unsubscribe' mod='mailinblue'}</option>
					</select>
				</p>
			{/if}
			
			<p style="padding-top:10px;">
				<input type="submit" value="ok" class="button_mini" name="submitNewsletter" />
			</p>
		</form>
	</div>
</div>

<!-- /mailin Newsletter module-->
