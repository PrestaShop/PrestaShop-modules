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

<link href="{$css|escape:htmlall:'UTF-8'}" rel="stylesheet" type="text/css">
<script type="text/javascript">
	{literal}
		/* Fancybox */
		$('a.interpay-video-btn').live('click', function()
		{
			$.fancybox({
				'type': 'iframe',
				'href': this.href.replace(new RegExp("watch\\?v=", "i"), 'embed') + '?rel=0&autoplay=1',
				'swf': {'allowfullscreen': 'true', 'wmode': 'transparent'},
				'overlayShow': true,
				'centerOnScroll': true,
				'speedIn': 100,
				'speedOut': 50,
				'width': 853,
				'height': 480
			});
			return false;
		});
	{/literal}
</script>

<div class="interpay-wrapper">
	<a href="http://www.gointerpay.com/prestashop" class="interpay-logo" target="_blank"><img src="{$logo}" alt="Interpay" border="0" /></a>
	<p class="interpay-intro">{l s='Your Global E-Commerce Payment Processing & Shipping Partner' mod='gointerpay'}</p>
	<a href="http://www.gointerpay.com/prestashop" class="interpay-apply-link" target="_blank">{l s='Apply now!' mod='gointerpay'}</a>
	<div class="interpay-content">
		<h3>{l s='GoInterpay Makes Global E-commerce Easy for Everyone' mod='gointerpay'}</h3>
		<div class="interpay-video">
			<a href="http://www.youtube.com/embed/blJferWYOqM" class="interpay-video-btn"><img src="{$module_dir}img/video-screen.jpg" alt="interpay Global Shipping" /><img src="{$module_dir}img/btn-video.png" alt="" class="video-icon" /></a>
			<a href="http://www.gointerpay.com/prestashop" class="interpay-link"  target="_blank" >{l s='Apply for your FREE account today!' mod='gointerpay'}</a>
		</div>
		<div class="interpay-leftCol">
			<p>{l s='GoInterpay provides all of your e-commerce payment and shipping needs in one simple, affordable and safe solution.  With GoInterpay, merchants gain instant access to global markets without having to worry about exchange rates, tax compliance and/or complicated logistics.' mod='gointerpay'}</p>
			<p class="MB20"><a href="http://www.gointerpay.com/prestashop" class="interpay-link" target="_blank" >{l s='Sign up now for your FREE account!' mod='gointerpay'}</a></p>
			<div class="halfCol floatLeft">
				<h4>{l s='Why you\'ll love GoInterpay...' mod='gointerpay'}</h4>
				<ul>
					<li>{l s='Guaranteed payments in USD' mod='gointerpay'}</li>
					<li>{l s='Fraud free' mod='gointerpay'}</li>
					<li>{l s='Automated tax compliance' mod='gointerpay'}</li>
					<li>{l s='Increased sales and customer loyalty' mod='gointerpay'}</li>
					<li>{l s='Extremely affordable' mod='gointerpay'}</li>
				</ul>
			</div>
			<div class="halfCol floatRight">
				<h4>{l s='Global shipping and logistics...' mod='gointerpay'}</h4>
				<ul>
					<li>{l s='Localized payments for shoppers' mod='gointerpay'}</li>
					<li>{l s='Real-time inventory and order tracking' mod='gointerpay'}</li>
					<li>{l s='Eliminate stock-outs and excess inventory' mod='gointerpay'}</li>
					<li>{l s='A fully supported returns and exchange process' mod='gointerpay'}</li>
					<li>{l s='One simple, integrated solution' mod='gointerpay'}</li>
				</ul>
			</div>
		</div>
		<div class="interpay-img">
			<h4>{l s='GoInterpay currently accepts more than 80 localized payment methods around the world.' mod='gointerpay'}</h4>
			<img src="{$module_dir}img/cc-logos.png" alt="Credit Cards" class="cc-logos" />
		</div>
	</div>

	<ul id="menuTab">
		{foreach from=$tab item=li}
			<li id="menuTab{$li.tab|escape:htmlall:'UTF-8'}" class="menuTabButton {if $li.selected}selected{/if}"><img src="{$li.icon|escape:htmlall:'UTF-8'}" alt="{$li.title|escape:htmlall:'UTF-8'}"/> {$li.title|escape:htmlall:'UTF-8'}</li>
			{/foreach}
	</ul>

	<div id="tabList">
		{foreach from=$tab item=div}
			<div id="menuTab{$div.tab|escape:htmlall:'UTF-8'}Sheet" class="tabItem {if $div.selected}selected{/if}">
				{$div.content}
			</div>
		{/foreach}
	</div>
</div>
<center{if !$interpay_configured} style="display: none;"{/if}>
	<div id="all_products" style=" width:500px; text-align:center; margin-bottom:30px; margin-top:30px; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; border:solid 2px #25aae1" align="center">
		<form action="{$formTool|escape:htmlall:'UTF-8'}" method="post">
			<table>
				<tr align="center" border="2">
					<td width="180px" style=" font-size:14px" align="left">{l s='MULTISELECT TOOL' mod='gointerpay'}</td>
					<td width="200px">{l s='SELECT products to be' mod='gointerpay'} <br />{l s='AVAILABLE FOR EXPORT' mod='gointerpay'}</td>
					<td width="100px"><input type="button" name="allprodS" value="{l s='SELECT' mod='gointerpay'}" style="width:90px; height:30px; background-color:#25aae1; color:#FFF;" /></td>

				</tr>	
				{if isset($msg)}
					<tr>
						<td colspan=6 align="center"><br /><strong>{$msg|escape:htmlall:'UTF-8'}</strong><br /></td>
					</tr>
				{/if}				
			</table>
		</form>
		<div id="select_products_for_export" style="display: none; max-height: 500px; max-width: 600px;">
			<ul>
				<li class="menuTabButton selected">{l s='Products list' mod='gointerpay'}</li>
			</ul>
			<form method="post" action="#" id="export_product_list">
				<div id="tabList" style="width: 600px; background: none repeat scroll 0 0 #FFFFF0; ">
					<div class="selected product_list" style="max-height: 500px; max-width: 600px; overflow-y: scroll;">
					</div>
					<table style="margin: 0px 31%; height: 40px;">
						<tr>
							<td colspan="2">
								<input type="hidden" name="allprodS" value="1"/>
								<span class="button save_export_product" style="margin:0 5px; cursor: pointer">{l s='Save' mod='gointerpay'}</span>
								<span class="close button" style="margin:0 5px; cursor: pointer">{l s='Cancel' mod='gointerpay'}</span>
								<span class="check button" style="margin:0 5px; width: 65px; cursor: pointer">{l s='Check All' mod='gointerpay'}</span></td>
						</tr>
					</table>
				</div>
			</form>	
		</div>
		<div id="response_products_for_export" style="display: none; height: 100px; width: 600px; background-color: #fff;">
			<center><br /><br /><span id="response" style="margin: 20px 0"></span><br /><br />
				<span class="close button">{l s='Close' mod='gointerpay'}</span></center>
		</div>
	</div>
</center>
<script type="text/javascript">
	{literal}
		{
			$('#menuTab li').click(function()
			{
				/* Tab Buttons */
				$(this).siblings().removeClass('selected');
				$(this).addClass('selected');

				/* Tab Content */
				$('#tabList div').removeClass('selected');
				$('#tabList #menuTab' + ($(this).index() + 1) + 'Sheet').addClass('selected');
			});

			$('input:button[name=allprodS]').click(function() {
				$.ajax({
					type: "POST",
					url: "#",
					data: {ajaxCall: true},
					success: function(page) {
						$('.product_list').html(page);
						$('#select_products_for_export').lightbox_me({centered: false});
					}
				});

			});
			
			$(".save_export_product").click(function() {
				$.ajax({
					type: "POST",
					url: "#",
					data: $('form').serializeArray(),
					success: (function(result) {
						$('#select_products_for_export').trigger('close');
						$('#response').html(result);
						$('#response_products_for_export').lightbox_me({centered: false});
					})
				});
			});

			$('.check').toggle(function() {
				$('input[name="id_product[]"]').attr('checked', 'checked');
				$(this).html('uncheck all');
			}, function() {
				$('input[name="id_product[]"]').removeAttr('checked');
				$(this).html('check all');
			});

			$('.category_products').toggle(function() {
				$(this).closest('fieldset').find(':checkbox').prop('checked', this.checked);
			});
		}
	{/literal}
</script>