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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<form action="{$formCredential|escape:'htmlall':'UTF-8'}" method="POST">
	<fieldset class="merchant-warehouse-fixFiedlset">
		<h4>{$credentialTitle|escape:'htmlall':'UTF-8'}</h4>
		<p>{$credentialText|escape:'htmlall':'UTF-8'}</p>

				<label for="authorizeaim_enable">Enable Credit Cards:</label>
				<div class="margin-form" id="authorizeaim_enable">
<input type="radio" name="authorizeaim_enable" value="0" style="vertical-align: middle;" {if $ALLIANCE_ENABLE==0}checked="checked"{/if} />
					<span style="color: #080;">Disabled</span>
<input type="radio" name="authorizeaim_enable" value="1" style="vertical-align: middle;" {if $ALLIANCE_ENABLE==1}checked="checked"{/if} />
					<span style="color: #900;">Enabled</span>
				</div>

<label for="authorizeaim_demo_mode">Production:</label>

				<div class="margin-form" id="authorizeaim_demo">
<input type="radio" name="authorizeaim_demo_mode" value="0" style="vertical-align: middle;" {if $ALLIANCE_DEMO==0}checked="checked"{/if} />
					<span style="color: #080;">Production</span>
<input type="radio" name="authorizeaim_demo_mode" value="1" style="vertical-align: middle;" {if $ALLIANCE_DEMO==1}checked="checked"{/if} />
					<span style="color: #900;">Test</span>
				</div>

				<label for="authorizeaim_login_id">Login ID</label>
				<div class="margin-form">
					<input type="text" size="20" id="authorizeaim_login_id" name="authorizeaim_login_id" value="{$ALLIANCE_LOGIN_ID|escape:'htmlall':'UTF-8'}" />
				</div>

				<label for="authorizeaim_key">API Key</label>
				<div class="margin-form">
					<input type="text" size="20" id="authorizeaim_login_id" name="authorizeaim_key" value="{$ALLIANCE_KEY|escape:'htmlall':'UTF-8'}" />
				</div>

				<div class="margin-form">
					&nbsp;<hr>
				</div>

				<label for="authorizeaim_cards">Cards:</label>
				<div class="margin-form" id="authorizeaim_cards">
					<input type="checkbox" name="authorizeaim_card_visa" {if $ALLIANCE_CARD_VISA!='off'}checked="checked"{/if} />
						<img src="../modules/alliance3/img/cards/visa.gif" alt="visa" />
					<input type="checkbox" name="authorizeaim_card_mastercard" {if $ALLIANCE_CARD_MASTERCARD!='off'}checked="checked"{/if} />
						<img src="../modules/alliance3/img/cards/mastercard.gif" alt="visa" />
					<input type="checkbox" name="authorizeaim_card_discover" {if $ALLIANCE_CARD_DISCOVER!='off'} checked="checked" {/if} />
						<img src="../modules/alliance3/img/cards/discover.gif" alt="visa" />
					<input type="checkbox" name="authorizeaim_card_ax" {if $ALLIANCE_CARD_AX!='off'}checked="checked"{/if} />
						<img src="../modules/alliance3/img/cards/ax.gif" alt="visa" />
				</div>

				<label for="authorizeaim_hold_review_os">Order status:  "Hold for Review"</label>
				<div class="margin-form">
								<select id="authorizeaim_hold_review_os" name="authorizeaim_hold_review_os">';
		// Hold for Review order state selection
{foreach from=$os_order item=orderS}
<option value="{$orderS.id_order_state|escape:'htmlall':'UTF-8'}"{if $orderS.id_order_state==$ALLIANCE_HOLD_REVIEW_OS} selected='selected'{/if}> {$orderS.name|escape:'htmlall':'UTF-8'}</option>
{/foreach}
		</select></div>

		<div class="margin-form">
			<input type="submit" class="button" value="{l s='Save' mod='alliance3'}" />
		</div>
	</fieldset>
</form>
