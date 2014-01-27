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

<p>{l s='The following parameters were provided to you by TNT' mod='tntcarrier'}. {l s='If you are not yet registered, click ' mod='tntcarrier'} <a style="color:blue;text-decoration:underline" href="https://www.tnt.fr/public/utilisateurs/adminExt/new.do">{l s='here' mod='tntcarrier'}</a><br/>
<a target="_BLANK" href="../modules/{$glob.module_name}/tntDocumentation.pdf">{l s='(cf. Configuration guide attached)' mod='tntcarrier'}</a></p>
	<form action="index.php?tab={$glob.tab}&configure={$glob.configure}&token={$glob.token}&tab_module={$glob.tab_module}&module_name={$glob.module_name}&id_tab=1&section=account" method="post" class="form" id="configFormAccount">
		<fieldset style="border: 0px;">
			<h4>{l s='Account TNT' mod='tntcarrier'} :</h4>
			<label>{l s='Login' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="tnt_carrier_login" value="{$varAccount.login|escape:'htmlall'}" /></div>
			<label>{l s='Password' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="password" size="20" name="tnt_carrier_password" value="{$varAccount.password|escape:'htmlall'}" /></div>
			<label>{l s='Number account' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="tnt_carrier_number_account" value="{$varAccount.account|escape:'htmlall'}" /></div>
		</fieldset>
	<div class="margin-form"><input class="button" name="submitSave" type="submit" value="{l s='save' mod='tntcarrier'}"></div>
</form>
