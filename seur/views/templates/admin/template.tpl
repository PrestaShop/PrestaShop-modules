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
{if isset($email_warning_message)}
	{include file=$smarty.const._PS_MODULE_DIR_|escape:'htmlall':'UTF-8'|cat:'seur/views/templates/admin/warning_message.tpl' seur_warning_message=$email_warning_message|escape:'htmlall':'UTF-8'}
{/if}

<div id="seur_module" class="{$ps_version|escape:'htmlall':'UTF-8'}">
	<fieldset>
		<legend>
			<img src="{$img_path|escape:'htmlall':'UTF-8'}logonew.png" alt="{l s='SEUR' mod='seur'}" title="{l s='SEUR' mod='seur'}" border="0" />
		</legend>
		
		<input type="hidden" name="module_dir" id="module_dir" value="{$module_path|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="module_non_ssl_href" id="module_non_ssl_href" value="{$module_local_path|escape:'htmlall':'UTF-8'}" />

		{if $configured eq 0}
			{if $state_configured eq 0}
				<div id="is_customer" class="seurBlock {$ps_version|escape:'htmlall':'UTF-8'}">
					<fieldset>
						<div id="newclientsseur">
							<div id="leftnewuser">
							<h1>{l s='Everything for your online business.' mod='seur'}</h1>
							<p>{l s='In e-commerce, the experience of your customers is key. If your customers have a good shopping experience, repeated and will recommend to others. SEUR e-solutions is the option you need to get it.' mod='seur'}</p>
							<p>{l s='By linking your online store with our transportation network, each time a customer makes a purchase will be automatically registered in our system. ' mod='seur'}</p>
							<p>{l s='In this way you will have shuttle service that you need for each order.' mod='seur'}</p>
							<div id="manualnewuser">
								<a href="{$module_path|escape:'htmlall':'UTF-8'}manual/seur_manual.pdf" target="_blank" ><img src="{$img_path|escape:'htmlall':'UTF-8'}manualdownload.png" alt="{l s='Manual' mod='seur'}" /></a>
				
								<div id="downloadmanual">
									<a id="manual_download" href="{$module_path|escape:'htmlall':'UTF-8'}manual/seur_manual.pdf" target="_blank" >
										<img src="{$img_path|escape:'htmlall':'UTF-8'}ico_descargar.png" alt="{l s='Download manual' mod='seur'}" /> {l s='Download manuals' mod='seur'}</a>
									</div>
									<span class="textdownloadmanual">{l s='Find out how to configure this module.' mod='seur'}</span>
								</div>
							</div>
							
							<div id="rightuser">
								<div id="functionsnewuser">
									<h2>{l s='Features' mod='seur'}</h2>
									<ul>
										<li>{l s='Express installation: running in less than 1 minute!' mod='seur'}</li>
										<li>{l s='Integration of shipments automatically' mod='seur'}</li>
										<li>{l s='Pick-ups' mod='seur'}</li>
										<li>{l s='National and International Shipping.' mod='seur'}</li>
										<li>{l s='Home delivery or pick ups or drops off SEUR Shopping Network.' mod='seur'}</li>
										<li>{l s='Includes COD Service.' mod='seur'}</li>
										<li>{l s='Printing labels in pdf or thermal printer including your company logo.' mod='seur'}</li>
										<li>{l s='Consultation and Setup fees ..' mod='seur'}</li>
										<li>{l s='Delivery Check online for different search criteria: type of situation, date, by order number and issue number SEUR. Sending SMS and / or EMAIL delivery notice and notice delivery to recipients.' mod='seur'}</li>
										<li>{l s='Downloading the Proof of Delivery in pdf format.' mod='seur'}</li>
										<li>{l s='Compatible with other transportation options SEUR.' mod='seur'}</li>
										<li>{l s='Compatible with Prestashop versions 1.4 and 1.5' mod='seur'}</li>
									</ul>
								</div>
								
								<div id="importantnewuser">
									<p><span>{l s='Important:' mod='seur'}</span> {l s='To use this module is a must have with SEUR Account Code. If you need to open our account contact:' mod='seur'}</p>
									<p><a href="http://www.seur.com/seur-esolutions.do" target="_blank">{l s='http://www.seur.com/seur-esolutions.do' mod='seur'}</a></p>
				
									<p id="pnewuser">
										<input type="button" name="yes_button" value="{l s='I am a customer' mod='seur'}" class="yes_button" />
										<input type="button" name="btnNo" value="{l s='Become a customer' mod='seur'}" class="no_button" />
									</p>
								</div>
				
							</div>
						</div>
					</fieldset>
				</div>
				{include file="$module_local_path/views/templates/admin/account.tpl"}
			{elseif $state_configured eq 1}
				{include file="$module_local_path/views/templates/admin/ranges.tpl"}
			{/if}
		{elseif $configured eq 1}
			{include file="$module_local_path/views/templates/admin/configuration.tpl"}
		{/if}
	</fieldset>
</div>