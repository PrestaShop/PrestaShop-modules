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
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Klarna payment' mod='klarnaprestashop'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
<h2>{l s='Order summary' mod='klarnaprestashop'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
{if isset($error)}<div style="background-color: #FAE2E3;border: 1px solid #EC9B9B;line-height: 20px;margin: 0 0 10px;padding: 10px 15px;">{$error}</div>{/if}
{if isset($nbProducts) && $nbProducts <= 0}
    					 <p class="warning">{l s='Your shopping cart is empty.'}</p>
					 {else}
					 {if $country->iso_code == 'NL' && $payment_type == 'account'}
  <img src="./img/warning.jpg" style="width:100%" alt="{l s='Warning' mod='klarnaprestashop'}"/>
  {/if}
  <h3>{l s='Klarna payment' mod='klarnaprestashop'}</h3>
  <form action="{$this_path_ssl}payment.php?type={$payment_type}" method="post">
    <p>
      <img src="./img/klarna_{$payment_type}_{$iso_code}.png" alt="{l s='klarnaprestashop' mod='klarnaprestashop'}" style="float:left; margin: 0px 10px 5px 0px;width:150px;" />
      {l s='You have chosen to pay by klarna.' mod='klarnaprestashop'}
      <br/>
    </p>
    <p style="margin-top:20px;">
      {l s='The total amount of your order is' mod='klarnaprestashop'}
      <span id="amount" class="price">{displayPrice price=$total_fee}</span>{if $fee != 0}<br/><br/><span id="amount">{l s='This includes the invoice cost' mod='klarnaprestashop'} {displayPrice price=$fee}</span>{/if}
      {if $use_taxes == 1}
      {l s='(tax incl.)' mod='klarnaprestashop'}
      {/if}
    </p>
    <p>
      {if $payment_type == 'invoice'}
      {l s='You will receive an invoice from Klarna, not from the webshop. This invoice will be sent out via email or it will be shipped with the order.' mod='klarnaprestashop'}
      {else}
      {l s='You will receive your order directly from the webshop, if this is the first time you shop with Klarna Account, you will receive an account agreement after a few days. You will receive a combined invoice with all your klarna purchases later during the month' mod='klarnaprestashop'}
      {/if}
    </p>
    <p>
      {l s='To pay with Klarna, it is necessary to enter ' mod='klarnaprestashop'}
      {if $country->iso_code == 'DE' || $country->iso_code == 'NL'}
      {l s='your birth date.' mod='klarnaprestashop'}
      {else}
      {l s='your Personel number.' mod='klarnaprestashop'}
      {/if}
      {l s=' Klarna will check your personal information directly online.' mod='klarnaprestashop'}
    </p>
    {if isset($accountPrice)}
    <br/>
    <select name="paymentAccount">
      {foreach from=$accountPrice item=val key=k}
      <option value="{$k}">{displayPrice price=$val.price} {$val.description}</option>
      {/foreach}
    </select>
    <br/>
    {/if}
    <br />
    <p>
      {if isset($gender)}
      <label>{l s='Gender:' mod='klarnaprestashop'}</label>
      <input type="radio" name="klarna_gender" checked="checked" value="{$gender[0]->id_gender}" /> {$gender[0]->name}
      <input type="radio" name="klarna_gender" value="{$gender[1]->id_gender}" /> {$gender[1]->name}
      <input type="radio" name="klarna_gender" value="{$gender[2]->id_gender}" /> {$gender[2]->name}
      <br/>
      <br/>
      {/if}
      {if $country->iso_code == 'DE' || $country->iso_code == 'NL'}
      {if $customer_day != 0 && $customer_month != 0 && $customer_year != 0}
      <input type="hidden" name="klarna_pno_day" value="{$customer_day}" />
      <input type="hidden" name="klarna_pno_month" value="{$customer_month}" />
      <input type="hidden" name="klarna_pno_year" value="{$customer_year}" />
      {else}
      <label>{l s='Birthdate:' mod='klarnaprestashop'}</label>
      <select name="klarna_pno_day">
	<option value="0">{l s='Day' mod='klarnaprestashop'}</option>
	{foreach from=$days item=day}
	<option value="{$day}" {if $day == $customer_day}selected="selected"{/if}>{$day}</option>
	{/foreach}
      </select>
      <select name="klarna_pno_month">
	<option value="0">{l s='Month' mod='klarnaprestashop'}</option>
	{foreach from=$months item=month}
	<option value="{$month}" {if $month == $customer_month}selected="selected"{/if}>{$month}</option>
	{/foreach}
      </select>
      <select name="klarna_pno_year">
	<option value="0">{l s='Year' mod='klarnaprestashop'}</option>
	{foreach from=$years item=year}
	<option value="{$year}" {if $year == $customer_year}selected="selected"{/if}>{$year}</option>
	{/foreach}
      </select>
      {/if}
      {else}
      <label>{l s='PNO/SSN/CRN:' mod='klarnaprestashop'}</label>
      <input type="text" name="klarna_pno" value=""/> {$pnoValue}{/if}
      <br /><br/>
      {if $country->iso_code == 'DE' || $country->iso_code == 'NL'}
      {if $street_number != ''}
      <input type="hidden" name="klarna_house_number" value="{$street_number}"/>
      {else}
      <label>Your house number:</label>
      <input type="text" name="klarna_house_number"/><br /><br/>
      {/if}
      {/if}
      {if $country->iso_code == 'NL'}
      {if $house_ext != ''}
      <input type="hidden" name="klarna_house_ext" value="{$house_ext}"/>
      {else}
      <label>Your house extension:</label>
      <input type="text" name="klarna_house_ext"/><br /><br/>
      {/if}
      {/if}
      <a id="klarna_link_terms_condition" href="#" rel="{$linkTermsCond}">{if $payment_type == 'invoice'}{l s='Invoice terms' mod='klarnaprestashop'}{else}{l s='Klarna Account terms' mod='klarnaprestashop'}{/if}</a>
      <br/>
      <br/>
      {if $country->iso_code == 'DE'}
      <input type="checkbox" name="klarna_de_accept"/> {l s='Mit der Übermittlung der für die Abwicklung des Rechnungskaufes und einer Identitäts- und Bonitätsprüfung erforderlichen Daten an Klarna bin ich einverstanden. Meine' mod='klarnaprestashop'} <a style="color:blue" id="klarna_link_germany" href="#">{l s='Einwilligung' mod='klarnaprestashop'}</a> {l s='kann ich jederzeit mit Wirkung für die Zukunft widerrufen.' mod='klarnaprestashop'}
      <br/><br/>
      {/if}
  <b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='klarnaprestashop'}.</b>
    </p>
    <p class="cart_navigation">
      <a href="{$link->getPageLink('order.php', true)}?step=3" class="button_large hideOnSubmit">{l s='Other payment methods' mod='klarnaprestashop'}</a>
      <input type="submit" name="submit" value="{l s='I confirm my order' mod='klarnaprestashop'}" class="exclusive_large hideOnSubmit" />
    </p>
  </form>
  <div id="klarna_terms_condition" style="display:none;position:absolute;top:30px;left:50%;margin-left:-300px;background-color: #FFFFFF;border: 1px solid black;border-radius: 2px 2px 2px 2px;box-shadow: 4px 4px 4px #888888;padding: 0 0 10px;z-index: 9999;"><iframe style="width: 550px;height:680px;border:0" src="{$linkTermsCond}"></iframe><br/><p style="cursor:pointer" onclick="closeIframe('klarna_terms_condition')">{l s='Close' mod='klarnaprestashop'}</p></div>
  {if $country->iso_code == 'DE'}
  <div id="klarna_consent_de" style="display:none;position:absolute;top:30px;left:50%;margin-left:-300px;background-color: #FFFFFF;border: 1px solid black;border-radius: 2px 2px 2px 2px;box-shadow: 4px 4px 4px #888888;padding: 0 0 10px;z-index: 9999;"><iframe style="width: 550px;height:680px;border:0" src="https://online.klarna.com/consent_de.yaws"></iframe><br/><p style="cursor:pointer" onclick="closeIframe('klarna_consent_de')">{l s='Close' mod='klarnaprestashop'}</p></div>
  {/if}
  <script type="text/javascript">
    $(document).ready(function()
    {
      $('#klarna_link_terms_condition').attr('href', 'Javascript:void(0)');
      $('#klarna_link_terms_condition').click(function(){
	  $("#klarna_terms_condition").show();
      });

      $('#klarna_link_germany').attr('href', 'Javascript:void(0)');
      $('#klarna_link_germany').click(function(){
	  $("#klarna_consent_de").show();
      });
  });
function closeIframe(id)
{
    $('#'+id).hide();
}
</script>
{/if}
