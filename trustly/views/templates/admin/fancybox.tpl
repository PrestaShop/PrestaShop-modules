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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{literal}
	<script type="text/javascript">
		$(document).ready(function() {
			var personal_content = $("#trustly_personal_content").html();
			$.fancybox(personal_content, {type: 'html', autoDimensions: true, minWidth: 500, height: 510, padding: 20, modal: false, hideOnOverlayClick: true});
	
			$('input[name=submitPersonalAskMeLater]').on('click', function() {
				$.fancybox.close();
				return false;
			});
		});
	</script>
{/literal}
<div id="trustly_personal_content" style="display: none;">
	<div style="text-align: left; margin:0; padding: 0">
		<img src="{$smarty.const._MODULE_DIR_}trustly/trustly.png" /> <h2 style="display: inline; vertical-align: middle; margin-left: 6px;"></h2>
	</div>
	<br />
	<hr style="display: block; border-bottom: 1px solid #DDD;">
	<br />
	<p style="text-align: justify;">{l s='In order to ensure correct use for this module you need to complete this form.' mod='trustly'}</p>

	<form action="" method="post" style="margin-top: 30px; text-align: center">
		<dl style="margin: 0 auto; width: auto; text-align: left">
			<dt style="width: 40%"><label for="personal_phone" style="width: 100%; line-height: 18px; vertical-align: middle">{l s='Phone number' mod='trustly'}* :</label></dt>
			<dd><input type="text" value="{if isset($phone)}{$phone|escape:'htmlall':'UTF-8'}{else}{$shop_phone|escape:'htmlall':'UTF-8'}{/if}" name="TRUSTLY_PHONE" id="trustly_phone" />
				&nbsp;&nbsp;<em style="font-size: .8em; {if isset($personal_data_phone_error) && $personal_data_phone_error} color: red; {else} color: #999;{/if}">({l s='Example : 0140183004' mod='trustly'})</em>
			</dd><br />
		</dl>
		
		<p>
			{if isset($phone_error)}<span style="color: red">{l s='The phone number must be valid.' mod='trustly'}</span>{/if}
		</p>

		<input type="submit" class="button" name="submitPersonalSave" value="{l s='Confirm' mod='trustly'}" style="float: right; margin-top: 30px; padding: 10px 20px" />
		<input type="submit" class="button" name="submitPersonalAskMeLater" value="{l s='Ask me later' mod='trustly'}" style="float: right; margin-top: 30px; margin-right: 15px; padding: 10px 20px" />
	</form>
	<form action="" method="post">
		<input type="submit" class="button" name="submitPersonalCancel" value="{l s='Cancel' mod='trustly'}" style="float: right; padding: 10px 20px; margin: 30px 15px 0 0" />
	</form>
</div>
