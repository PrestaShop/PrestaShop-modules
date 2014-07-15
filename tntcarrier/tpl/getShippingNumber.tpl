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

<br/>
<fieldset style="width:400px">
	<legend><img src="../img/admin/delivery.gif" />{l s='Shipping information'}</legend>
	{if isset($weight)}
	{l s='Each package must be at' mod='tntcarrier'} {$weight} {l s='Kg' mod='tntcarrier'}.<br/><br/>
	{/if}
	{$var.error}
	<span style="float:right;pointer:cursor;color:blue" onclick="document.getElementById('formParameter').style.display=''">{l s='modify information' mod='tntcarrier'}</span><br/><br/>
	<form class='form' method="POST" action="{$var.currentIndex}&id_order={$var.id_order}&view{$var.table}&token={$var.token}&action=getpackagenumber">
	<div id="formParameter" style="display:none">
	<fieldset><legend>{l s='receiver' mod='tntcarrier'}</legend>
		<div id="receiverTntForm">
			<label>{l s='Type' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverType" value="{$var.type}" /></div>
			{if $var.info[4] != null}
			<label>{l s='Type ID' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverTypeId" value="{$var.info[4]['code']}" /></div>
			<label>{l s='Comany Name' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverCampanyName" value="{$var.info[4]['name']}" /></div>
			<label>{l s='Address 1' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverAddress1" value="{$var.info[4]['address']}" /></div>
			<label>{l s='Address 2' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverAddress2" value="" /></div>
			<label>{l s='Postal Code' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverZipCode" value="{$var.info[4]['zipcode']}" /></div>
			<label>{l s='City' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverCity" value="{$var.info[4]['city']}" /></div>
			{else}
			<label>{l s='Type ID' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverTypeId" value="" /></div>
			<label>{l s='Company Name' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverCampanyName" value="{$var.info[0]['company']}" /></div>
			<label>{l s='Address 1' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverAddress1" value="{$var.info[0]['address1']}" /></div>
			<label>{l s='Address 2' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverAddress2" value="{$var.info[0]['address2']}" /></div>
			<label>{l s='Postal Code' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverZipCode" value="{$var.info[0]['postcode']}" /></div>
			<label>{l s='City' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverCity" value="{$var.info[0]['city']}" /></div>
			{/if}
			<label>{l s='Instruction' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverInstruction" value="" /></div>
			<label>{l s='Contact Last Name' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverLastName" value="{$var.info[0]['lastname']}" /></div>
			<label>{l s='Contact First Name' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverFirstName" value="{$var.info[0]['firstname']}" /></div>
			<label>{l s='Email' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverEmail" value="{$var.info[0]['email']}" /></div>
			<label>{l s='Phone' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverPhone" value="{if isset($var.info[0]['phone_mobile']) && $var.info[0]['phone_mobile'] != ''}{$var.info[0]['phone_mobile']}{else}{$var.info[0]['phone']}{/if}" /></div>
			<label>{l s='Access Code' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverAccessCode" value="" /></div>
			<label>{l s='Floor Number' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverFloorNumber" value="" /></div>
			<label>{l s='Building Id' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverBuildingId" value="" /></div>
			<label>{l s='Send Notification' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="ReceiverSendNotification" value="" /></div>
		</div>
	</fieldset><br/>
	<fieldset><legend>{l s='Sender' mod='tntcarrier'}</legend>
			<label>{l s='Type' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderType" value="{$var.sender['type']}" /></div>
			<label>{l s='Type ID' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderTypeId" value="{$var.sender['typeId']}" /></div>
			<label>{l s='Company Name' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderCampanyName" value="{$var.sender['name']}" /></div>
			<label>{l s='Address 1' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderAddress1" value="{$var.sender['address1']}" /></div>
			<label>{l s='Address 2' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderAddress2" value="{$var.sender['address2']}" /></div>
			<label>{l s='Postal Code' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderZipCode" value="{$var.sender['zipCode']}" /></div>
			<label>{l s='City' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderCity" value="{$var.sender['city']}" /></div>
			<label>{l s='Contac Last Name' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderLastName" value="{$var.sender['contactLastName']}" /></div>
			<label>{l s='Contact First Name' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderFirstName" value="{$var.sender['contactFirstName']}" /></div>
			<label>{l s='Email' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderEmail" value="{$var.sender['emailAddress']}" /></div>
			<label>{l s='Phone' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderPhone" value="{$var.sender['phoneNumber']}" /></div>
			<label>{l s='Fax' mod='tntcarrier'} : </label>
			<div class="margin-form"><input type="text" size="20" name="SenderFax" value="{$var.sender['faxNumber']}" /></div>
	</fieldset><br/>
	<fieldset><legend>{l s='Parcel Request' mod='tntcarrier'}</legend>
	{foreach from=$var.info[1]['weight'] key=k item=v}
		<fieldset><legend>{l s='Parcel' mod='tntcarrier'} {$k}</legend>
		<input type="hidden" size="20" id="Parcel_{$k}_sequence" value="{$k + 1}" />
		<label>{l s='Customer Reference' mod='tntcarrier'}</label>
		<div class="margin-form"><input type="text" size="20" name="Parcel_{$k}_Reference" value="{$var.info[0]['id_customer']}" /></div>
		<label>{l s='Weight' mod='tntcarrier'}</label>
		<div class="margin-form"><input type="text" size="20" name="Parcel_{$k}_weight" value="{$v}" /></div>
		<label>{l s='Insurance Amount' mod='tntcarrier'}</label>
		<div class="margin-form"><input type="text" size="20" name="Parcel_{$k}_insurance" value="" /></div>
		<label>{l s='Priority Guarantee' mod='tntcarrier'}</label>
		<div class="margin-form"><input type="text" size="20" name="Parcel_{$k}_priority" value="" /></div>
		<label>{l s='Comment' mod='tntcarrier'}</label>
		<div class="margin-form"><input type="text" size="20" name="Parcel_{$k}_comment" value="" /></div>
		</fieldset>
	{/foreach}
	</fieldset><br/>
	<fieldset><legend>{l s='Pick Up Request' mod='tntcarrier'}</legend>
	<label>{l s='Media' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestMedia" value="{$var.pickUp['Media']}" /></div>
	<label>{l s='Fax Number' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestFax" value="{$var.pickUp['FaxNumber']}" /></div>
	<label>{l s='Email' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestEmailAddress" value="{$var.pickUp['EmailAddress']}" /></div>
	<label>{l s='Notify Success' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestNotify Success" value="{$var.pickUp['NotifySuccess']}" /></div>
	<label>{l s='Service' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestService" value="{$var.pickUp['Service']}" /></div>
	<label>{l s='Last Name' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestLastName" value="{$var.pickUp['LastName']}" /></div>
	<label>{l s='First Name' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestFirstName" value="{$var.pickUp['FirstName']}" /></div>
	<label>{l s='Phone Number' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestPhoneNumber" value="{$var.pickUp['PhoneNumber']}" /></div>
	<label>{l s='Instruction' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="PickUpRequestInstruction" value="{$var.pickUp['Instructions']}" /></div>
	</fieldset><br/>
	<fieldset><legend>{l s='Parameters' mod='tntcarrier'}</legend>
	<label>{l s='Shipping Date' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="ParametersShippingDate'" value="{$var.parameters['shippingDate']}" /></div>
	<label>{l s='Account Number' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="ParametersAccountNumber'" value="{$var.parameters['accountNumber']}" /></div>
	<label>{l s='Service Code' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="ParametersServiceCode'" value="{$var.parameters['serviceCode']}" /></div>
	<label>{l s='Quantity' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="ParametersQuantity'" value="{$var.parameters['quantity']}" /></div>
	<label>{l s='Saturday Delivery' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="ParametersSaturdayDelivery'" value="{$var.parameters['saturdayDelivery']}" /></div>
	<label>{l s='Label Format' mod='tntcarrier'}</label>
	<div class="margin-form"><input type="text" size="20" name="ParametersLabelFormat'" value="{$var.parameters['labelFormat']}" /></div>
	</fieldset><br/>
	</div>
	<input type="submit" value="{l s='get a shipping number' mod='tntcarrier'}"/>
	</form>
</fieldset>
