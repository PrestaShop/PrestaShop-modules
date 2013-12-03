<div class="y-settings-white-box">
	<form action="{$yotpo_action|escape:'htmlall':'UTF-8'}" method="post">
		<div class="y-page-header">
			<i class="y-logo"></i>{l s='Settings' mod='yotpo'}</div>
			{if !$yotpo_finishedRegistration && !$yotpo_allreadyUsingYotpo}<div class="y-settings-title">{l s='To customize the look and feel of the widget, and to edit your Mail After Purchase settings, just head to the' mod='yotpo'} 
				{if $yotpo_appKey && $yotpo_appKey != '' && $yotpo_oauthToken && $yotpo_oauthToken != ''}
					<a class="y-href" href="https://api.yotpo.com/users/b2blogin?app_key={$yotpo_appKey|escape:'htmlall':'UTF-8'}&secret={$yotpo_oauthToken|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Yotpo Dashboard.' mod='yotpo'}</a></div> 
				{else}
					<a class="y-href" href="https://www.yotpo.com/?login=true" target="_blank">{l s='Yotpo Dashboard.' mod='yotpo'}</a></div> 
				{/if}
			{/if}
		{if $yotpo_allreadyUsingYotpo}<div class="y-settings-title">{l s='To get your api key and secret token' mod='yotpo'} 
		<a class="y-href" href="https://www.yotpo.com/?login=true" target="_blank">{l s='log in here' mod='yotpo'}</a>{l s=', And go to your account settings.' mod='yotpo'}</div>{/if}

		{if $yotpo_finishedRegistration}<div class="y-settings-title">{l s='All set! The Yotpo widget is now properly installed on your shop.' mod='yotpo'}<br />
			{l s='To customize the look and feel of the widget, and to edit your Mail After Purchase settings, just head to the' mod='yotpo'} 
			{if $yotpo_appKey && $yotpo_appKey != '' && $yotpo_oauthToken && $yotpo_oauthToken != ''}
				<a class="y-href" href="https://api.yotpo.com/users/b2blogin?app_key={$yotpo_appKey|escape:'htmlall':'UTF-8'}&secret={$yotpo_oauthToken|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Yotpo Dashboard.' mod='yotpo'}</a></div> 
			{else}
				<a class="y-href" href="https://www.yotpo.com/?login=true" target="_blank">{l s='Yotpo Dashboard.' mod='yotpo'}</a></div> 
			{/if}
		{/if}

		<fieldset id="y-fieldset">
	        <div class="y-label">{l s='Enable Rich snippets' mod='yotpo'}
               <input type="checkbox" name="yotpo_rich_snippets" value="1" {if $yotpo_rich_snippets}checked="checked"{/if} />
            </div> 
            {if $yotpo_appKey && $yotpo_appKey != '' && $yotpo_oauthToken && $yotpo_oauthToken != ''}
            	<p class="y-notification"> * In order to activate Rich Snippets you will also need to check the Rich Snippet tick box in your <a class="y-href" href="https://api.yotpo.com/users/b2blogin?app_key={$yotpo_appKey|escape:'htmlall':'UTF-8'}&secret={$yotpo_oauthToken|escape:'htmlall':'UTF-8'}&redirect=/customize/seo&utm_source=customers_prestashop_admin&utm_medium=link&utm_campaign=prestashop_rich_snippets" target="_blank">{l s='Yotpo admin.' mod='yotpo'}</a> </p>				 
			{/if}
                   
	        <div class="y-label">{l s='For multipule-language sites, mark this check box. This will choose the language according to the user\'s site language' mod='yotpo'}
               <input type="checkbox" name="yotpo_language_as_site" value="1" {if $yotpo_language_as_site}checked="checked"{/if} />
            </div> 
            <div class="y-label">{l s='If you would like to choose a set language, please type the language code here. You can find the supported langauge codes ' mod='yotpo'}<a class="y-href" href="http://support.yotpo.com/entries/21861473-Languages-Customization-" target="_blank">{l s='here.' mod='yotpo'}</a></div>
    	    <div class="y-input"><input type="text" class="yotpo_language_code_text" name="yotpo_widget_language_code" maxlength="5" value="{$yotpo_widget_language_code|escape:'htmlall':'UTF-8'}" /></div>			
			<div class="y-label">{l s='Select widget location' mod='yotpo'}
				<select name="yotpo_widget_location">
					<option value="footer" {if $yotpo_widgetLocation == 'footer'}selected{/if}>{l s='Page footer' mod='yotpo'}</option>
					<option value="tab" {if $yotpo_widgetLocation == 'tab'}selected{/if}>{l s='Tab' mod='yotpo'}</option>
					<option value="other" {if $yotpo_widgetLocation == 'other'}selected{/if}>{l s='Other (click update to see instructions)' mod='yotpo'}</option>
				</select>
			</div>

			
			{if $yotpo_widgetLocation == 'other'}
				<div class="y-label">{l s='In order to locate the widget in a custom position, please open the "root" folder, then enter the "themes" library. Locate the specific theme you would like the widget to show up on, and in this specific themes folder, locate the file "product.tpl". Add the code here, wherever you would like it placed.' mod='yotpo'}<br> <br> 
					<div class="y-code">
					{literal}
					&lt;div class="yotpo reviews" </br>
					data-appkey="{$yotpoAppkey|escape:'htmlall':'UTF-8'}"</br>
					data-domain="{$yotpoDomain|escape:'htmlall':'UTF-8'}"</br>
					data-product-id="{$yotpoProductId|intval}"</br>
					data-product-models="{$yotpoProductModel|escape:'htmlall':'UTF-8'}" </br>
					data-name="{$yotpoProductName|escape:'htmlall':'UTF-8'}" </br>
					data-url="{$link-&gt;getProductLink($smarty.get.id_product, $smarty.get.id_product.link_rewrite)|escape:'htmlall':'UTF-8'}" </br>
					data-image-url="{$yotpoProductImageUrl|escape:'htmlall':'UTF-8'}" </br>
					data-description="{$yotpoProductDescription|escape:'htmlall':'UTF-8'}" </br>
					data-bread-crumbs="{$yotpoProductBreadCrumbs|escape:'htmlall':'UTF-8'}"</br>
					data-lang="{$yotpoLanguage|escape:'htmlall':'UTF-8'}"&gt; </br>
					{$richSnippetsCode|escape:'UTF-8'} <br>
					&lt;/div&gt;
					{/literal}
					</div>
				</div>
			{/if}

			<div class="y-label">{l s='Select tab name' mod='yotpo'}</div>
			<div class="y-input"><input type="text" name="yotpo_widget_tab_name" value="{$yotpo_tabName|escape:'htmlall':'UTF-8'}" /></div>
			<div class="y-label">{l s='App key' mod='yotpo'}</div>
			<div class="y-input"><input type="text" name="yotpo_app_key" value="{$yotpo_appKey|escape:'htmlall':'UTF-8'}" /></div>
			<div class="y-label">{l s='Secret token' mod='yotpo'}</div>
			<div class="y-input"><input type="text" name="yotpo_oauth_token" value="{$yotpo_oauthToken|escape:'htmlall':'UTF-8'}"/></div>
			<div class="y-label">{l s='Enable bottom line' mod='yotpo'}
            	<input type="checkbox" name="yotpo_bottom_line_enabled" value="1" {if $yotpo_bottomLineEnabled}checked="checked"{/if} />
        	</div> 
	        <div class="y-label">{l s='Select bottom Line location' mod='yotpo'}
	          <select name="yotpo_bottom_line_location">
	            <option value="right_column" {if $yotpo_bottomLineLocation == "right_column"}selected{/if}>{l s='Right column' mod='yotpo'}</option>
	            <option value="left_column" {if $yotpo_bottomLineLocation == "left_column"}selected{/if}>{l s='Left column' mod='yotpo'}</option>
	            <option value="other" {if $yotpo_bottomLineLocation == "other"}selected{/if}>{l s='Other (click update to see instructions)' mod='yotpo'}</option>
	          </select>
	        </div> 
	        {if $yotpo_bottomLineLocation == 'other'}
	        <div class="y-label">{l s='In order to locate the bottom line in a custom position, please open the "root" folder, then enter the "themes" library. Locate the specific theme you would like the widget to show up on, and in this specific themes folder, locate the file "product.tpl". Add the code here, wherever you would like it placed.' mod='yotpo'}<br /><br /> 
	          <div class="y-code">
	            {literal}
	            &lt;div class="yotpo bottomLine" <br>
	               data-appkey="{$yotpoAppkey|escape:'htmlall':'UTF-8'}"<br>
	               data-domain="{$yotpoDomain|escape:'htmlall':'UTF-8'}"<br>
	               data-product-id="{$yotpoProductId|intval}"<br>
	               data-product-models="{$yotpoProductModel|escape:'htmlall':'UTF-8'}" <br>
	               data-name="{$yotpoProductName|escape:'htmlall':'UTF-8'}" <br>
	               data-url="{$link-&gt;getProductLink($smarty.get.id_product, $smarty.get.id_product.link_rewrite)|escape:'htmlall':'UTF-8'}" <br>
	               data-image-url="{$yotpoProductImageUrl|escape:'htmlall':'UTF-8'}" <br>
	               data-description="{$yotpoProductDescription|escape:'htmlall':'UTF-8'}" <br>
	               data-bread-crumbs="{$yotpoProductBreadCrumbs|escape:'htmlall':'UTF-8'}"&gt;<br>
	               data-lang="{$yotpoLanguage|escape:'htmlall':'UTF-8'}"&gt; <br>
	              &lt;/div&gt;
	           {/literal}
	         </div>
	        </div>
	        {/if} 	               	
		</fieldset>

		<div class="y-footer">
			{if $yotpo_showPastOrdersButton}<input type="submit" name="yotpo_past_orders" value="{l s='Submit past orders' mod='yotpo'}" class="y-submit-btn" {if empty($yotpo_appKey) || empty($yotpo_oauthToken)} disabled {/if}>{/if}
			<input type="submit" name="yotpo_settings" value="{l s='Update' mod='yotpo'}" class="y-submit-btn" />
		</div>
	</form>
</div>