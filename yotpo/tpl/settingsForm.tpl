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
			<div class="y-label">{l s='Select widget language' mod='yotpo'}
				<select name="yotpo_language">
					<option value="en" {if $yotpo_widgetLanguage == "en"}selected{/if}>{l s='English' mod='yotpo'}</option>
					<option value="de" {if $yotpo_widgetLanguage == "de"}selected{/if}>{l s='German' mod='yotpo'}</option>
					<option value="es" {if $yotpo_widgetLanguage == "es"}selected{/if}>{l s='Spanish' mod='yotpo'}</option>
					<option value="fr" {if $yotpo_widgetLanguage == "fr"}selected{/if}>{l s='French' mod='yotpo'}</option>
					<option value="he" {if $yotpo_widgetLanguage == "he"}selected{/if}>{l s='Hebrew' mod='yotpo'}</option>
					<option value="hr" {if $yotpo_widgetLanguage == "hr"}selected{/if}>{l s='Croatian' mod='yotpo'}</option>
					<option value="it" {if $yotpo_widgetLanguage == "it"}selected{/if}>{l s='Italian' mod='yotpo'}</option>
					<option value="ja" {if $yotpo_widgetLanguage == "ja"}selected{/if}>{l s='Japanese' mod='yotpo'}</option>
					<option value="nl" {if $yotpo_widgetLanguage == "nl"}selected{/if}>{l s='Dutch' mod='yotpo'}</option>
					<option value="pt" {if $yotpo_widgetLanguage == "pt"}selected{/if}>{l s='Portuguese' mod='yotpo'}</option>
					<option value="sv" {if $yotpo_widgetLanguage == "sv"}selected{/if}>{l s='Swedish' mod='yotpo'}</option>
					<option value="vi" {if $yotpo_widgetLanguage == "vi"}selected{/if}>{l s='Vietnamese' mod='yotpo'}</option>
					<option value="da" {if $yotpo_widgetLanguage == "da"}selected{/if}>{l s='Danish' mod='yotpo'}</option>
					<option value="ru" {if $yotpo_widgetLanguage == "ru"}selected{/if}>{l s='Russian' mod='yotpo'}</option>
					<option value="tr" {if $yotpo_widgetLanguage == "tr"}selected{/if}>{l s='Turkish' mod='yotpo'}</option>
				</select>
			</div>			
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
					&lt;div class=&quot;yotpo reviews&quot; </br>
					data-appkey=&quot;{$yotpoAppkey|escape:'htmlall':'UTF-8'}&quot;</br>
					data-domain=&quot;{$yotpoDomain|escape:'htmlall':'UTF-8'}&quot;</br>
					data-product-id=&quot;{$yotpoProductId|intval}&quot;</br>
					data-product-models=&quot;{$yotpoProductModel|escape:'htmlall':'UTF-8'}&quot; </br>
					data-name=&quot;{$yotpoProductName|escape:'htmlall':'UTF-8'}&quot; </br>
					data-url=&quot;{$link-&gt;getProductLink($smarty.get.id_product, $smarty.get.id_product.link_rewrite)|escape:&#39;htmlall&#39;:&#39;UTF-8&#39;}&quot; </br>
					data-image-url=&quot;{$yotpoProductImageUrl|escape:'htmlall':'UTF-8'}&quot; </br>
					data-description=&quot;{$yotpoProductDescription|escape:'htmlall':'UTF-8'}&quot; </br>
					data-bread-crumbs=&quot;{$yotpoProductBreadCrumbs|escape:'htmlall':'UTF-8'}&quot;</br>
					data-lang=&quot;{$yotpoLanguage|escape:'htmlall':'UTF-8'}&quot;&gt; </br>
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
	            &lt;div class=&quot;yotpo bottomLine&quot; <br>
	               data-appkey=&quot;{$yotpoAppkey|escape:'htmlall':'UTF-8'}&quot;<br>
	               data-domain=&quot;{$yotpoDomain|escape:'htmlall':'UTF-8'}&quot;<br>
	               data-product-id=&quot;{$yotpoProductId|intval}&quot;<br>
	               data-product-models=&quot;{$yotpoProductModel|escape:'htmlall':'UTF-8'}&quot; <br>
	               data-name=&quot;{$yotpoProductName|escape:'htmlall':'UTF-8'}&quot; <br>
	               data-url=&quot;{$link-&gt;getProductLink($smarty.get.id_product, $smarty.get.id_product.link_rewrite)|escape:&#39;htmlall&#39;:&#39;UTF-8&#39;}&quot; <br>
	               data-image-url=&quot;{$yotpoProductImageUrl|escape:'htmlall':'UTF-8'}&quot; <br>
	               data-description=&quot;{$yotpoProductDescription|escape:'htmlall':'UTF-8'}&quot; <br>
	               data-bread-crumbs=&quot;{$yotpoProductBreadCrumbs|escape:'htmlall':'UTF-8'}&quot;&gt;<br>
	               data-lang=&quot;{$yotpoLanguage|escape:'htmlall':'UTF-8'}&quot;&gt; <br>
	              &lt;/div&gt;
	           {/literal}
	         </div>
	        </div>
	        {/if} 	               	
		</fieldset>

		<div class="y-footer">
			{if $yotpo_showPastOrdersButton}<input type="submit" name="yotpo_past_orders" value="{l s='Submit past orders' mod='yotpo'}" class="y-submit-btn">{/if}
			<input type="submit" name="yotpo_settings" value="{l s='Update' mod='yotpo'}" class="y-submit-btn" />
		</div>
	</form>
</div>