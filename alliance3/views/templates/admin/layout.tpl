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

<form action="{$formLayout|escape:'htmlall':'UTF-8'}" method="POST" id="merchantWareHouseLayout">
<fieldset>
  <h4>{$layoutTitle|escape:'htmlall':'UTF-8'}</h4>
  <p>{$layoutText|escape:'htmlall':'UTF-8'}</p>

				<label for="authorizeach_demo_mode">Enable ACH:</label>
				<div class="margin-form" id="authorizeach_enable">
<input type="radio" name="allianceach_enable" value="0" style="vertical-align: middle;" {if $ALLIANCEACH_ENABLE==0}checked="checked"{/if} />
					<span style="color: #080;">Disabled</span>
<input type="radio" name="allianceach_enable" value="1" style="vertical-align: middle;" {if $ALLIANCEACH_ENABLE==1}checked="checked"{/if} />
					<span style="color: #900;">Enabled</span>
				</div>

	<label for="allianceach_login">ACH Username</label>
				<div class="margin-form"><input type="text" size="20" id="allianceach_login" name="allianceach_login" value="{$ALLIANCEACH_LOGIN|escape:'htmlall':'UTF-8'}" /></div>

				<label for="allianceach_pass">ACH Password</label>
				<div class="margin-form"><input type="text" size="20" id="allianceach_pass" name="allianceach_pass" value="{$ALLIANCEACH_PASS|escape:'htmlall':'UTF-8'}" /></div>

				<label for="allianceach_terminal">Terminal</label>
				<div class="margin-form"><input type="text" size="20" id="allianceach_terminal" name="allianceach_terminal" value="{$ALLIANCEACH_TERMINAL|escape:'htmlall':'UTF-8'}" /></div>

				<label for="authorizeach_identity">Require Identity:</label>
				<div class="margin-form" id="allianceach_identitysetting">
					<input type="radio" name="allianceach_identity" value="0" style="vertical-align: middle;" 
{if $ALLIANCEACH_IDENTITY ==0} checked="checked" {/if}
/>
					<span style="color: #080;">no</span>
					<input type="radio" name="allianceach_identity" value="1" style="vertical-align: middle;" '
{if $ALLIANCEACH_IDENTITY ==1} checked="checked" {/if} />
					<span style="color: #900;">yes</span>
				</div>

				<label for="authorizeach_driver">Require Driver’s License #</label>
				<div class="margin-form" id="allianceach_driversetting">
					<input type="radio" name="allianceach_driver" value="0" style="vertical-align: middle;" 
{if $ALLIANCEACH_DRIVER ==0} checked="checked" {/if} />
					<span style="color: #080;">no</span>
					<input type="radio" name="allianceach_driver" value="1" style="vertical-align: middle;" 
{if $ALLIANCEACH_DRIVER ==1} checked="checked" {/if} />
					<span style="color: #900;">yes</span>
				</div>
  <div class="margin-form">
    <input type="submit" class="button" value="{l s='Save' mod='alliance3'}" />
  </div>
</fieldset>
</form>
