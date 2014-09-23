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

<style>
	.payu-button {
cursor:pointer;
    background-image: url("./img/payu-btn.gif");
    background-position: center top;
    background-repeat: repeat-x;
    border-radius: 4px 4px 4px 4px;
    color: #FFFFFF;
    font-size: 16px;
    height: 45px;
    line-height: 45px;
    text-align: center;
    text-shadow: 0 1px 1px #1B5C8B;
    vertical-align: middle;
    width: 280px;
}
.payu-button:hover, a.payu-button:active { background-position: center bottom; color: #FFF; text-decoration: none; }

</style>
<link href="{$css|escape:'htmlall':'UTF-8'}payu.css" rel="stylesheet" type="text/css">
<div class="div-redirect">
{if isset($error)}
<p class="md-error">{l s='An error occured, please try again later.' mod='payulatam'}</p>
{else}
<p class="tx-redirect">{l s='You are going to be redirected to PayU\'s website for your payment.' mod='payulatam'}</p>
<form action="{$formLink|escape:'htmlall':'UTF-8'}" method="POST" id="formPayU">
  {foreach from=$payURedirection item=value}
  <input type="hidden" value="{$value.value|escape:'htmlall':'UTF-8'}" name="{$value.name|escape:'htmlall':'UTF-8'}"/>
  {/foreach}
  <input class="payu-button" id="payuSubmit" type="button" value="{l s='Please click here' mod='payulatam'}" />
</form>
</div>
{literal}
<script type="text/javascript">
$('#payuSubmit').click(
  function()
  {
    $.ajax(
	{
	    type : 'GET',
	    url : './payment.php?create-pending-order',
	    dataType: 'html',
	    success: function(data)
	    {
	    	$('#formPayU').submit();
	    }
	});

  }
);
</script>
{/literal}
{/if}
