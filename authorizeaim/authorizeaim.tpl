{*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<link rel="shortcut icon" type="image/x-icon" href="{$module_dir}secure.png" />
<p class="payment_module" >

	{if $isFailed == 1}
		<p style="color: red;">
			{if !empty($smarty.get.message)}
				{l s='Error detail from AuthorizeAIM : ' mod='authorizeaim'}{$smarty.get.message|htmlentities}
			{else}
				{l s='Error, please verify the card information' mod='authorizeaim'}
			{/if}
		</p>
	{/if}

	<form name="authorizeaim_form" id="authorizeaim_form" action="{$module_dir}validation.php" method="post">
		<span style="border: 1px solid #595A5E;display: block;padding: 0.6em;text-decoration: none;margin-left: 0.7em;">
			<a id="click_authorizeaim" href="#" title="{l s='Pay with AuthorizeAIM' mod='authorizeaim'}" style="display: block;text-decoration: none; font-weight: bold;">
				{if $cards.visa == 1}<img src="{$module_dir}cards/visa.gif" alt="{l s='Visa Logo' mod='authorizeaim'}" style="vertical-align: middle;" />{/if}
				{if $cards.mastercard == 1}<img src="{$module_dir}cards/mastercard.gif" alt="{l s='Mastercard Logo' mod='authorizeaim'}" style="vertical-align: middle;" />{/if}
				{if $cards.discover == 1}<img src="{$module_dir}cards/discover.gif" alt="{l s='Discover Logo' mod='authorizeaim'}" style="vertical-align: middle;" />{/if}
				{if $cards.ax == 1}<img src="{$module_dir}cards/ax.gif" alt="{l s='American Express Logo' mod='authorizeaim'}" style="vertical-align: middle;" />{/if}
				&nbsp;&nbsp;{l s='Secured credit card payment with Authorize.net' mod='authorizeaim'}
			</a>

				{if $isFailed == 0}
						<div id="aut2"style="display:none">
				{else}
						<div id="aut2">
				{/if}
				<br /><br />

				<div style="width: 136px; height: 145px; float: left; padding-top:40px; padding-right: 20px; border-right: 1px solid #DDD;">
					<img src="{$module_dir}logoa.gif" alt="secure payment" />
				</div>

				<input type="hidden" name="x_solution_ID" value="A1000006" />
				<input type="hidden" name="x_invoice_num" value="{$x_invoice_num}" />

				<label style="margin-top: 4px; margin-left: 35px;display: block;width: 90px;float: left;">{l s='Full name' mod='authorizeaim'}</label> <input type="text" name="name" id="fullname" size="30" maxlength="25S" /><img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;" /><br /><br />

				<label style="margin-top: 4px; margin-left: 35px; display: block;width: 90px;float: left;">{l s='Card Type' mod='authorizeaim'}</label>
				<select id="cardType">
					{if $cards.ax == 1}<option value="AmEx">American Express</option>{/if}
					{if $cards.visa == 1}<option value="Visa">Visa</option>{/if}
					{if $cards.mastercard == 1}<option value="MasterCard">MasterCard</option>{/if}
					{if $cards.discover == 1}<option value="Discover">Discover</option>{/if}
				</select>
				<img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;" /><br /><br />

				<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Card number' mod='authorizeaim'}</label> <input type="text" name="x_card_num" id="cardnum" size="30" maxlength="16" autocomplete="Off" /><img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;" /><br /><br />
				<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Expiration date' mod='authorizeaim'}</label>
				<select id="x_exp_date_m" name="x_exp_date_m" style="width:60px;">{section name=date_m start=01 loop=13}
					<option value="{$smarty.section.date_m.index}">{$smarty.section.date_m.index}</option>{/section}
				</select>
				 /
				<select name="x_exp_date_y">{section name=date_y start=0 loop=9}
					<option value="{'Y'|date + $smarty.section.date_y.index}">{'Y'|date + $smarty.section.date_y.index}</option>{/section}
				</select>
				<img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;" /><br /><br />
				<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px;">{l s='CVV' mod='authorizeaim'}</label> <input type="text" name="x_card_code" id="x_card_code" size="4" maxlength="4" /><img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;"/> <img src="{$module_dir}help.png" id="cvv_help" title="{l s='the 3 last digits on the back of your credit card' mod='authorizeaim'}" alt="" /><br /><br />
			<img src="{$module_dir}cvv.png" id="cvv_help_img" alt=""style="display: none;margin-left: 211px;" />
				<input type="button" id="asubmit" value="{l s='Validate order' mod='authorizeaim'}" style="margin-left: 129px; padding-left: 25px; padding-right: 25px; float: left;" class="button" />
				<br class="clear" />
			</div>
		</span>
	</form>
</p>
<script type="text/javascript">
	var mess_error = "{l s='Please check your credit card information (Credit card type, number and expiration date)' mod='authorizeaim' js=1}";
	var mess_error2 = "{l s='Please specify your Full Name' mod='authorizeaim' js=1}";
	{literal}
		$(document).ready(function(){
			$('#x_exp_date_m').children('option').each(function()
			{
				if ($(this).val() < 10)
				{
					$(this).val('0' + $(this).val());
					$(this).html($(this).val())
				}
			});
			$('#click_authorizeaim').click(function(e){
				e.preventDefault();
				$('#click_authorizeaim').fadeOut("fast",function(){
					$("#aut2").show();
					$('#click_authorizeaim').fadeIn('fast');
				});
				$('#click_authorizeaim').unbind();
				$('#click_authorizeaim').click(function(e){
					e.preventDefault();
				});
			});

			$('#cvv_help').click(function(){
				$("#cvv_help_img").show();
				$('#cvv_help').unbind();
			});

			$('#asubmit').click(function()
				{
				if ($('#fullname').val() == '')
				{
					alert(mess_error2);
				}
				else if (!validateCC($('#cardnum').val(), $('#cardType').val()) || $('#x_card_code').val() == '')
				{
					alert(mess_error);
				}
				else
				{
					$('#authorizeaim_form').submit();
				}
				return false;
			});
		});

	{/literal}
</script>
