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

<fieldset style="margin-top:10px;" id="statsForm">
	<legend>{l s='Ebay Data Usage' mod='ebay'}</legend>
	<div class="label">{l s='Help us improve the eBay Module by sending anonymous usage stats' mod='ebay'} : </div>
    <form action="#" method="POST">
	<div class="margin-form">
            <div class="input">
            	<input type="radio" name="stats" value="0"> {l s='No thanks' mod='ebay'}
            </div>
            <div class="input">
            	<input type="radio" name="stats" value="1" checked> {l s='I agree' mod='ebay'}
            </div>
            <input type="submit" name="submitSave" value="Continue" class="button btn btn-primary primary">
	</div>
    </form>                
	<div style="clear:both;"></div>
</fieldset>