<link href="{$module_dir}css/firstdata.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="{$module_dir}firstdata-prestashop.js"></script>
<a name="firstdata-anchor"></a>
<div class="payment_module"{if $firstdata_ps_version < '1.5'} style="border: 1px solid #595A5E; padding: 0.6em; margin-left: 0.7em;"{/if}>
	<img src="{$module_dir}img/logo-firstdata.png" class="firstdata-logo" alt="First Data" />
	<h3 class="stripe_title"><img alt="" src="{$module_dir}img/secure-icon.png" />{l s='Secure payment by credit card with FirstData' mod='firstdata'}</h3>
	<form action="{$module_dir}validation.php" method="post" name="firstdata_form" id="firstdata_form">
		<div id="firstdataFrame">
		     	{if isset($smarty.get.firstdataError)}<p style="color: red;">{$smarty.get.firstdataError|escape:'htmlall':'UTF-8'}</p>{/if}
			<label>{l s='Card holder name' mod='firstdata'}</label><br/>
			<input type="text" name="firstdata_card_holder" id="firstdata_card_holder" size="30" style="width: 200px;" />
			<br/>
			<div class="block-left">
				<label>{l s='Card number' mod='firstdata'}</label><br/>
				<input type="text" name="x_card_num" id="firstdata_cardnum" size="30" maxlength="16" autocomplete="Off" style="width: 200px;" />
			</div>
			<div class="block-left">
				<label>{l s='Card Type' mod='firstdata'}</label><br />
				<img class="cc-firstdata-icon cc-firstdata-disable" rel="Visa" alt="" src="{$module_dir}img/cc-visa.png" />
				<img class="cc-firstdata-icon cc-firstdata-disable" rel="MasterCard" alt="" src="{$module_dir}img/cc-mastercard.png" />
				<img class="cc-firstdata-icon cc-firstdata-disable" rel="Discover" alt="" src="{$module_dir}img/cc-discover.png" />
				<img class="cc-firstdata-icon cc-firstdata-disable" rel="American Express" alt="" src="{$module_dir}img/cc-amex.png" />
				<img class="cc-firstdata-icon cc-firstdata-disable" rel="JCB" alt="" src="{$module_dir}img/cc-jcb.png" />
				<img class="cc-firstdata-icon cc-firstdata-disable" rel="Diners Club" alt="" src="{$module_dir}img/cc-diners.png" />
			</div>
			<div class="clear"></div>
			<div class="block-left">
				<label>{l s='Expiration date' mod='firstdata'}</label><br />
				<select id="firstdata_exp_date_m" name="x_exp_date_m" style="width:60px;">
				{section name=date_m start=01 loop=13}
					<option value="{$smarty.section.date_m.index|escape}">{$smarty.section.date_m.index|escape}</option>
				{/section}
				</select> / <select name="x_exp_date_y">
				{section name=date_y start=13 loop=20}
					<option value="{$smarty.section.date_y.index|escape}">20{$smarty.section.date_y.index|escape}</option>
				{/section}
				</select>
			</div>
			<div class="block-left">
				<label>{l s='CVV' mod='firstdata'}</label><br />
				<input type="text" name="firstdata_card_code" id="firstdata_card_code" size="4" maxlength="4" />
				<a href="javascript:void(0)" class="firstdata-card-cvc-info" style="border: none;">
					<img src="{$module_dir|escape}img/help.png" id="firstdata_cvv_help" title="{l s='What\'s this?' mod='firstdata'}" alt="" />{l s='What\'s this?' mod='firstdata'}
					<div class="cvc-info">
						<img src="{$module_dir|escape}img/cvv.png" id="firstdata_cvv_help_img"/>
					</div>
				</a>
			</div>
			<div class="clear"></div>
			<input type="submit" id="firstdata_submit" value="{l s='Validate order' mod='firstdata'}" class="button" />
			<div style="display: none;" id="firstdata_submitload"><img src="{$img_ps_dir|escape}loader.gif" /></div>
			<div class="clear"></div>
		</div>
	</form>
</div>