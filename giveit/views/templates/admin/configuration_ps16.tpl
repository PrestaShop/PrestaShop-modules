{*
* 2013 Give.it
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@give.it so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 Give.it
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of Give.it
*}

{if $menu == 'technical_settings'}
    <iframe src="{$smarty.const._GIVEIT_EXTERNAL_ACCESS_URI_}?shop_id={$shop_id|escape:'htmlall':'UTF-8'}&website={$base_dir|escape:'htmlall':'UTF-8'}&name={$shop_name|escape:'htmlall':'UTF-8'}&currency={$iso_code|escape:'htmlall':'UTF-8'}&user_first_name={$first_name|escape:'htmlall':'UTF-8'}&user_last_name={$last_name|escape:'htmlall':'UTF-8'}&user_email={$email|escape:'htmlall':'UTF-8'}" style="border: 1px solid #CCCED7;" width="100%" height="800" frameborder="0" scrolling="no" marginwidth="5" marginheight="5"></iframe>
    <br /><br />
{else}
    <div id="portal" class="panel">
		<h3>
			<i class="icon-cog"></i>
			{l s='Settings & Orders' mod='giveit'}
		</h3>
        
		<div class="form-group">
			<div class="col-lg-9 col-lg-offset-3">
				<div class="alert alert-info">
					{l s='Log in to your personal Give.it portal to manage your sales and settings.' mod='giveit'}
					{l s='You can retrieve your keys from the settings page : Settings > Technical. Copy the keys here and press "save".' mod='giveit'}
				</div>
			</div>
		</div>
		
		<div class="form-group">
			<label class="control-label col-lg-3"></label>
			<a class='button' href="{$smarty.const._GIVE_IT_LOGIN_URI_|escape:'htmlall':'UTF-8'}" target="_blank">
				<button class="btn btn-default" type="button">
					<i class="icon-random"></i>
					{l s='Log in to the Give.it admin portal' mod='giveit'}
				</button>
			</a>
		</div>
	</div>
{/if}

<form id="configuration_form" class="defaultForm" action="{$saveAction|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
	<div id="connectivity" class="panel form-horizontal">
		<h3>
			<i class="icon-cog"></i>
			{l s='Technical settings' mod='giveit'}
		</h3>
		
		<div class="form-group">
			<div class="col-lg-9 col-lg-offset-3">
				<div class="alert alert-info">
					{l s='These fields contain the "API keys" you received when signing up for Give.it. If you have forgotten your keys please' mod='giveit'}
					<a href="{$smarty.const._GIVE_IT_LOGIN_URI_|escape:'htmlall':'UTF-8'}" target="_blank">{l s='log in here' mod='giveit'}</a>
					{l s='and retrieve your keys from the settings page : Settings > Technical. Copy the keys here and press "save".' mod='giveit'}
				</div>
			</div>
		</div>
		
		<div class="form-group">
			<label for="public_key" class="control-label col-lg-3">
				{l s='Public Key' mod='giveit'}
			</label>
			<div class="col-lg-3">
				<input type="text" size=32" value="{$public_key|escape:'htmlall':'UTF-8'}" name="public_key" id="public_key" />
			</div>
		</div>
		
		<div class="form-group">
			<label for="data_key" class="control-label col-lg-3">
				{l s='Data Key' mod='giveit'}
			</label>
			<div class="col-lg-3">
				<input type="text" size=32" value="{$data_key|escape:'htmlall':'UTF-8'}" name="data_key" id="data_key" />
			</div>
		</div>
		
		<div class="form-group">
			<label for="private_key" class="control-label col-lg-3">
				{l s='Private Key' mod='giveit'}
			</label>
			<div class="col-lg-3">
				<input type="text" size=32" value="{$private_key|escape:'htmlall':'UTF-8'}" name="private_key" id="private_key" />
			</div>
		</div>
		
		<div class="panel-footer">
			<button class="btn btn-default pull-right" name="{if $menu != 'technical_settings'}saveConfiguration{else}saveApiKeys{/if}" type="submit">
				<i class="process-icon-save"></i>
				{l s='Save' mod='giveit'}
			</button>
		</div>
	</div>
	
    {if $menu != 'technical_settings'}
		<div id="global_settings" class="panel form-horizontal">
			<h3>
				<i class="icon-cog"></i>
				{l s='Global settings' mod='giveit'}
			</h3>
			
			<div class="form-group">
				<label for="button_active" class="control-label col-lg-3">
					{l s='Mode' mod='giveit'}
				</label>
				<div class="col-lg-9">
					<div class="radio">
						<label for="production_mode">
							<input type="radio" {if $mode == GiveIt::PRODUCTION}checked="checked" {/if}value="{GiveIt::PRODUCTION}" id="production_mode" name="{GiveIt::MODE}" />
							{l s='Production' mod='giveit'}
						</label>
					</div>
					
					<div class="radio">
						<label for="debug_mode">
							<input type="radio" {if $mode == GiveIt::DEBUG}checked="checked" {/if}value="{GiveIt::DEBUG}" id="debug_mode" name="{GiveIt::MODE}" />
							{l s='Debug' mod='giveit'}
						</label>
					</div>
				</div>
			</div>
			
			<div class="form-group">
				<label for="button_active" class="control-label col-lg-3">
					{l s='Give.it button accessibility' mod='giveit'}
				</label>
				<div class="col-lg-5">
					<span class="switch prestashop-switch">
						<input type="radio" {if $button_active}checked="checked" {/if}value="1" id="button_active_on" name="button_active">
						<label class="radioCheck" for="button_active_on">
							{l s='Enabled' mod='giveit'}
						</label>
						<input type="radio" {if !$button_active}checked="checked" {/if}value="0" id="button_active_off" name="button_active">
						<label class="radioCheck" for="button_active_off">
							{l s='Disabled' mod='giveit'}
						</label>
						<a class="slide-button btn"></a>
					</span>
					
					<p class="help-block">
						{l s='With this setting you can disable the Give.it button in the current store.When the button is enabled you can still disable buttons for a category of products in the "Category settings" or for a single product in "Product settings"' mod='giveit'}
					</p>
				</div>
			</div>
			
			<div class="panel-footer">
				<button class="btn btn-default pull-right" name="saveConfiguration" type="submit">
					<i class="process-icon-save"></i>
					{l s='Save' mod='giveit'}
				</button>
			</div>
		</div>
		
		<div id="appearance" class="panel form-horizontal">
			<h3>
				<i class="icon-cog"></i>
				{l s='Appearance' mod='giveit'}
			</h3>
			
			<div class="form-group">
				<div class="col-lg-9 col-lg-offset-3">
					<div class="alert alert-info">
						{l s='By default the Give.it button can be positioned on your product description page on four different fixed locations. If you want more control on where the button should be positioned and how the button looks you can also manually change the template source code. Documentation for that can be found' mod='giveit' mod='giveit'}
						<a href="{$smarty.const._GIVE_IT_DOCUMENTATION_URI_|escape:'htmlall':'UTF-8'}" target="_blank">
							{l s='here' mod='giveit'}
						</a>
					</div>
				</div>
			</div>
			
			<div class="form-group">
				<label for="button_active" class="control-label col-lg-3"></label>
				<div class="col-lg-9">
					<div class="radio">
						<label for="left_column">
							<input type="radio" {if $button_position == GiveIt::EXTRA_LEFT}checked="checked" {/if}value="{GiveIt::EXTRA_LEFT}" id="left_column" name="button_position" />
							{l s='Left column underneath product' mod='giveit'}
						</label>
						<p class="help-block">
							{l s='This hook displays new elements in the left-hand column of the product page' mod='giveit'}
						</p>
					</div>
					
					<div class="radio">
						<label for="product_actions">
							<input type="radio" {if $button_position == GiveIt::PRODUCT_ACTIONS}checked="checked" {/if}value="{GiveIt::PRODUCT_ACTIONS}" id="product_actions" name="button_position" />
							{l s='Product actions' mod='giveit'}
						</label>
						<p class="help-block">
							{l s='This hook adds new action buttons on the product page' mod='giveit'}
						</p>
					</div>
					
					<div class="radio">
						<label for="right_column">
							<input type="radio" {if $button_position == GiveIt::EXTRA_RIGHT}checked="checked" {/if}value="{GiveIt::EXTRA_RIGHT}" id="right_column" name="button_position" />
							{l s='Right column underneath product' mod='giveit'}
						</label>
						<p class="help-block">
							{l s='This hook displays new elements in the right-hand column of the product page' mod='giveit'}
						</p>
					</div>
					
					<div class="radio">
						<label for="product_footer">
							<input type="radio" {if $button_position == GiveIt::PRODUCT_FOOTER}checked="checked" {/if}value="{GiveIt::PRODUCT_FOOTER}" id="product_footer" name="button_position" />
							{l s='Footer' mod='giveit'}
						</label>
						<p class="help-block">
							{l s='This hook adds new blocks under the product\'s description' mod='giveit'}
						</p>
					</div>
					
					<div class="radio">
						<label for="custom_position">
							<input type="radio" {if $button_position == GiveIt::CUSTOM_POSITION}checked="checked" {/if}value="{GiveIt::CUSTOM_POSITION}" id="custom_position" name="button_position" />
							{l s='Custom position' mod='giveit'}
						</label>
						<p class="help-block">
							{l s='Displays button in custom position' mod='giveit'}
						</p>
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<button class="btn btn-default pull-right" name="saveConfiguration" type="submit">
					<i class="process-icon-save"></i>
					{l s='Save' mod='giveit'}
				</button>
			</div>
		</div>
    {/if}
</form>