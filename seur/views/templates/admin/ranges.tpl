{*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @version  Release: 0.4.4
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
<div id="range_configuration">
	<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" name="ranges_form">
		<fieldset>
			<legend>
				<img src="{$img_path|escape:'htmlall':'UTF-8'}tarifas.png" alt="{l s='Ranges' mod='seur'}" title="{l s='Ranges' mod='seur'}" /> {l s='Configurate ranges' mod='seur'}
			</legend>
			<p class="alertatarifa">{l s='Did you install the rangues that are set up by default?' mod='seur'}</p>
			<div>
				<p class="alertaconfiguracion">
					{l s='NOTICE' mod='seur'}:<br />
					{l s='If you click on install rates, deactivate all zones, transporters and ranges of weight that have configured in your shop to install those SEUR provides default, if you work with other carriers might not work.' mod='seur'}</p>
			</div>
			<ul>
				<li>
					<input type="button" name="yes_button" value="{l s='Yes' mod='seur'}" class="yes_button" />
					<div id="install_ranges">
						<input type="submit" name="submitWithRanges" value="{l s='Install' mod='seur'}" class="buttoninstalar" />
					</div>
				</li>
				<li>
					<input type="submit" name="submitWithoutRanges" value="{l s='No' mod='seur'}" class="no_button" />
				</li>
			</ul>
		</fieldset>
	</form>
</div>