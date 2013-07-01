<div style="width: 700px; margin: 0 auto;">
</div>
{if isset($smarty.get.validation)}
	<div class="conf confirm" style="width: 710px; margin: 0 auto;">
		{l s='Your Sitemaps were successfully created. Please do not forget to setup the url' mod='gsitemap'} <a href="{$gsitemap_store_url|escape:'htmlall':'UTF-8'}{$shop->id|intval}_index_sitemap.xml" target="_blank"><span style="text-decoration: underline;">{$gsitemap_store_url|escape:'htmlall':'UTF-8'}{$shop->id|intval}_index_sitemap.xml</a></span> {l s='in your Google Webmaster account' mod='gsitemap'}.
</div>
<br/>
{/if}
{if isset($google_maps_error)}
	<div class="error" style="width: 710px; margin: 0 auto;">		
		{$google_maps_error|escape:'htmlall':'UTF-8'}<br />	
	</div>
{/if}
{if isset($gsitemap_refresh_page)}
	<fieldset style="width: 700px; margin: 0 auto; text-align: center;">
		<legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}logo.gif" alt="" />{l s='Your Sitemaps' mod='gsitemap'}</legend>
		<p>{$gsitemap_number|intval} {l s='Sitemaps were already created.' mod='gsitemap'}<br/>
		</p>
		<br/>
		<form action="{$gsitemap_refresh_page|escape:'htmlall':'UTF-8'}" method="post" id="gsitemap_generate_sitmap">
			<img src="../img/loader.gif" alt=""/>
			<input type="submit" class="button" value="{l s='Continue' mod='gsitemap'}" style="display: none;"/>
		</form>
	</fieldset>
{else}
	{if $gsitemap_links}
		<fieldset style="width: 700px; margin: 0 auto;">
			<legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}logo.gif" alt="" />{l s='Your Sitemaps' mod='gsitemap'}</legend>
			{l s='Please set up the following Sitemap URL in your Google Webmaster account:' mod='gsitemap'}<br/> 
			<a href="{$gsitemap_store_url|escape:'htmlall':'UTF-8'}{$shop->id|intval}_index_sitemap.xml" target="_blank"><span style="color: blue;">{$gsitemap_store_url|escape:'htmlall':'UTF-8'}{$shop->id|intval}_index_sitemap.xml</span></a><br/><br/>
			{l s='This URL is the master Sitemap and refers to:' mod='gsitemap'}
			<div style="max-height: 220px; overflow: auto;">
				<ul>
					{foreach from=$gsitemap_links item=gsitemap_link}
						<li><a target="_blank" style="color: blue;" href="{$gsitemap_store_url|escape:'htmlall':'UTF-8'}{$gsitemap_link.link|escape:'htmlall':'UTF-8'}">{$gsitemap_link.link|escape:'htmlall':'UTF-8'}</a></li>
						{/foreach}
				</ul>
			</div>
			<p>{l s='Your last update was:' mod='gsitemap'} {$gsitemap_last_export|escape:'htmlall':'UTF-8'}</p>
		</fieldset>
	{/if}
	<br/>
	{if ($gsitemap_customer_limit.max_exec_time < 30 && $gsitemap_customer_limit.max_exec_time > 0) || ($gsitemap_customer_limit.memory_limit < 128 && $gsitemap_customer_limit.memory_limit > 0)}
		<div class="warn" style="width: 700px; margin: 0 auto;">
			<p>{l s='For a better use of the module, please make sure that you have' mod='gsitemap'}<br/>
			<ul>
				{if $gsitemap_customer_limit.memory_limit < 128 && $gsitemap_customer_limit.memory_limit > 0}
					<li>{l s='a minimum memory limit of 128MB' mod='gsitemap'}</li>
					{/if}
					{if $gsitemap_customer_limit.max_exec_time < 30 && $gsitemap_customer_limit.max_exec_time > 0}
					<li>{l s='a minimum max execution time of 30 sec' mod='gsitemap'}</li>
					{/if}
			</ul>
			{l s='You can edit these limits in your php.ini. For more details, please contact your hosting providers.' mod='gsitemap'}</p>
	</div>
{/if}
<br/>
<form action="{$gsitemap_form|escape:'htmlall':'UTF-8'}" method="post">
	<fieldset style="width: 700px; margin: 0 auto;">
		<legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}logo.gif" alt="" />{l s='Configure your Sitemap' mod='gsitemap'}</legend>
		<p>{l s='Several Sitemaps will be generated depending on how your server is configured and on the number of products activated in your catalog.' mod='gsitemap'}<br/></p>
		<div class="margin-form">
			<label for="gsitemap_frequency" style="width: 235px;">{l s='How often do you update your store?' mod='gsitemap'}
				<select name="gsitemap_frequency">
					<option{if $gsitemap_frequency == 'always'} selected="selected"{/if} value='always'>{l s='always' mod='gsitemap'}</option>
					<option{if $gsitemap_frequency == 'hourly'} selected="selected"{/if} value='hourly'>{l s='hourly' mod='gsitemap'}</option>
					<option{if $gsitemap_frequency == 'daily'} selected="selected"{/if} value='daily'>{l s='daily' mod='gsitemap'}</option>
					<option{if $gsitemap_frequency == 'weekly' || $gsitemap_frequency == ''} selected="selected"{/if} value='weekly'>{l s='weekly' mod='gsitemap'}</option>
					<option{if $gsitemap_frequency == 'monthly'} selected="selected"{/if} value='monthly'>{l s='monthly' mod='gsitemap'}</option>
					<option{if $gsitemap_frequency == 'yearly'} selected="selected"{/if} value='yearly'>{l s='yearly' mod='gsitemap'}</option>
					<option{if $gsitemap_frequency == 'never'} selected="selected"{/if} value='never'>{l s='never' mod='gsitemap'}</option>
				</select></label>
		</div>
		<label for="ggsitemap_check_image_file" style="width: 526px;">{l s='Check this box if you wish to check the presence of the image files on the server' mod='gsitemap'}
			<input type="checkbox" name="gsitemap_check_image_file" value="1" {if $gsitemap_check_image_file}checked{/if}></label>
		<label for="ggsitemap_check_all" style="width: 526px;"><span>{l s='check all' mod='gsitemap'}</span>
			<input type="checkbox" name="gsitemap_check_all" value="1" class="check"></label>
		<br class="clear" />
		<p for="gsitemap_meta">{l s='Which page don\'t you want to include in your Sitemap:' mod='gsitemap'}</p>
		<ul>
			{foreach from=$store_metas item=store_meta}
				<li style="float: left; width: 200px; margin: 1px;">
					<input type="checkbox" class="gsitemap_metas" name="gsitemap_meta[]"{if in_array($store_meta.id_meta, $gsitemap_disable_metas)} checked="checked"{/if} value="{$store_meta.id_meta|intval}" /> {$store_meta.title|escape:'htmlall':'UTF-8'} [{$store_meta.page|escape:'htmlall':'UTF-8'}]
				</li>
			{/foreach}
		</ul>
		<br/>
		<div class="margin-form" style="clear: both;">
			<input type="submit" style="margin: 20px;" class="button" name="SubmitGsitemap" onclick="$('#gsitemap_loader').show();" value="{l s='Generate Sitemap' mod='gsitemap'}" />{l s='This can take several minutes' mod='gsitemap'}
		</div>
		<p id="gsitemap_loader" style="text-align: center; display: none;"><img src="../img/loader.gif" alt=""/></p>
	</fieldset>
</form><br />

<p class="info" style="width: 680px; margin: 10px auto;{if $prestashop_version == '1.4'} background: url('../img/admin/help2.png') no-repeat scroll 6px 6px #BDE5F8; border: 1px solid #00529B; border-radius: 3px 3px 3px 3px; color: #00529B; font-family: Arial,Verdana,Helvetica,sans-serif; font-size: 12px; margin-bottom: 15px; min-height: 32px; padding: 10px 5px 5px 40px;{/if}">
	<b style="display: block; margin-top: 5px; margin-left: 3px;">{l s='You have two ways to generate Sitemap:' mod='gsitemap'}</b><br /><br />
	1. <b>{l s='Manually:' mod='gsitemap'}</b> {l s='using the form above (as often as needed)' mod='gsitemap'}<br />
	<br /><span style="font-style: italic;">{l s='-or-' mod='gsitemap'}</span><br /><br />
	2. <b>{l s='Automatically:' mod='gsitemap'}</b> {l s='Ask your hosting provider to setup a "Cron task" to load the following URL at the time you would like:' mod='gsitemap'}
	<a href="{$gsitemap_cron|escape:'htmlall':'UTF-8'}" target="_blank">{$gsitemap_cron|escape:'htmlall':'UTF-8'}</a><br /><br />
	{l s='It will automatically generate your XML Sitemaps.' mod='gsitemap'}<br /><br />
</p>
{/if}
<script type="text/javascript">
$(document).ready(function() {
	
	if ($('.gsitemap_metas:checked').length == $('.gsitemap_metas').length)
		$('.check').parent('label').children('span').html("{l s='uncheck all' mod='gsitemap'}");
	
	
	$('.check').toggle(function() {
		$('.gsitemap_metas').attr('checked', 'checked');
		$(this).parent('label').children('span').html("{l s='uncheck all' mod='gsitemap'}");
	}, function() {
		$('.gsitemap_metas').removeAttr('checked');
		$(this).parent('label').children('span').html("{l s='check all' mod='gsitemap'}");
	});
});
</script>
