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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
<form action="" method="post" id="order_form">
<fieldset class="gk-main-wrapper"><legend><img src="../modules/globkurier/img/gk.png" />{l s='GlobKurier Quick Order' mod='globkurier'}</legend>
	<div id="gk-main-box">
		<div class="gk-order">
			<table class="order_sender">
				<thead>
					<tr>
						<td colspan=2>{l s='Sender' mod='globkurier'}</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>{l s='Name' mod='globkurier'}</td>
						<td><input type="text" name="sender_name" value="{$sender_name|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='E-mail' mod='globkurier'}</td>
						<td><input type="text" name="sender_email" value="{$sender_email|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Address' mod='globkurier'}</td>
						<td><input type="text" name="sender_address1" value="{$sender_address1|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Address cont.' mod='globkurier'}</td>
						<td><input type="text" name="sender_address2" value="{$sender_address2|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='City' mod='globkurier'}</td>
						<td><input type="text" name="sender_city" value="{$sender_city|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Postal code' mod='globkurier'}</td>
						<td><input type="text" name="sender_zipcode" value="{$sender_zipcode|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Country' mod='globkurier'}</td>
						<td>
							<select name="sender_country"/>
								<option country="PL" value="0">{l s='Polska' mod='globkurier'}</option>
								{foreach from=$country key=k item=i}
									<option country="{$i.country_code|escape:'htmlall':'UTF-8'}" value="{$i.id_globkurier_country|escape:'htmlall':'UTF-8'}" {if $sender_country eq $i.id_globkurier_country}selected{else}{/if}>{$i.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td>{l s='Contact person' mod='globkurier'}</td>
						<td><input type="text" name="sender_contact_person" value="{$sender_contact_person|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Phone' mod='globkurier'}</td>
						<td><input type="text" name="sender_phone" value="{$sender_phone|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="gk-order">
			<table class="order_recipient">
				<thead>
					<tr>
						<td colspan=2>{l s='Recipient' mod='globkurier'}</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>{l s='Name' mod='globkurier'}</td>
						<td><input type="text" name="recipient_name" value="{$recipient_name|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='E-mail' mod='globkurier'}</td>
						<td><input type="text" name="recipient_email" value="{$recipient_email|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Address' mod='globkurier'}</td>
						<td><input type="text" name="recipient_address1" value="{$recipient_address1|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Address cont.' mod='globkurier'}</td>
						<td><input type="text" name="recipient_address2" value="{$recipient_address2|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='City' mod='globkurier'}</td>
						<td><input type="text" name="recipient_city" value="{$recipient_city|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Postal code' mod='globkurier'}</td>
						<td><input type="text" name="recipient_zipcode" value="{$recipient_zipcode|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Country' mod='globkurier'}</td>
						<td>
							<select name="recipient_country" />
								<option country="PL" value="0">{l s='Polska' mod='globkurier'}</option>
								{foreach from=$country key=k item=i}
									<option country="{$i.country_code|escape:'htmlall':'UTF-8'}" value="{$i.id_globkurier_country|escape:'htmlall':'UTF-8'}" {if $recipient_country eq $i.id_globkurier_country}selected{else}{/if}>{$i.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td>{l s='Contact person' mod='globkurier'}</td>
						<td><input type="text" name="recipient_contact_person" value="{$recipient_contact_person|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
					<tr>
						<td>{l s='Phone' mod='globkurier'}</td>
						<td><input type="text" name="recipient_phone" value="{$recipient_phone|escape:'htmlall':'UTF-8'}" /></td>
					</tr>
				</tbody>
			</table>
		</div>
		{if !empty($arr_order_err)}
			{foreach from=$arr_order_err item=i}
				<div class="gk-order-errors" style="margin-top:10px;">
					<div class="error">{$i|escape:'htmlall':'UTF-8'}</div>
				</div>
			{/foreach}
		{/if}
		<div class="gk-order gk-orderdetails">
			<div class="product-loading" style="display:none">
				<img src="../modules/globkurier/img/loading.gif" />
			</div>
			<p class="gk-label">
				<img src="../modules/globkurier/img/cart_put.png" />{l s='Order details' mod='globkurier'}
			</p>
			<div class="gk-message-box"></div>
			<input type="hidden" name="client_id" value="{$client_id|escape:'htmlall':'UTF-8'}" />
			<div class="gk-orderdetails-box1">
				<div class="weight">
					<p class="product-label">{l s='Weight' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="medium-input" name="parcel_weight" value="{$parcel_weight|escape:'htmlall':'UTF-8'}" placeholder="kg"/>
					<span>(kg)</span>
				</div>
				<div class="lenght">
					<p class="product-label">{l s='Length' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="small-input" name="parcel_lenght" value="{$parcel_lenght|escape:'htmlall':'UTF-8'}" placeholder="cm"/>
					<span>x</span>
				</div>
				<div class="width">
					<p class="product-label">{l s='Width' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="small-input" name="parcel_width" value="{$parcel_width|escape:'htmlall':'UTF-8'}" placeholder="cm"/>
					<span>x</span>
				</div>
				<div class="height">
					<p class="product-label">{l s='Height' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="small-input" name="parcel_height" value="{$parcel_height|escape:'htmlall':'UTF-8'}" placeholder="cm"/>
					<span>(cm)</span>
				</div>
				<div class="parcel_count">
					<p class="product-label">{l s='Number of packages' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="medium-input" name="parcel_count" value="{$parcel_count|escape:'htmlall':'UTF-8'}" placeholder="szt."/>
					<span>(szt.)</span>
				</div>
			</div>
			<div class="gk-orderdetails-box1">
				<div class="content">
					<p class="product-label">{l s='Content' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="large-input" name="parcel_content" value="{$parcel_content|escape:'htmlall':'UTF-8'}" placeholder="{l s='ex. iPhone' mod='globkurier'}"/>
				</div>
				<div class="pricing-button">
					<input type="submit" value="{l s='Pricing' mod='globkurier'}" name="gk_price_order" id="btn_pricing" class="button price_me" />
				</div>
			</div>
		</div>
		<div class="gk-products"></div>
		<div class="gk-addons" style="display:none">
			<p class="gk-label"><img src="../modules/globkurier/img/money.png" />{l s='Order addons' mod='globkurier'}:</p>
			<div class="gk-addons-message-box"></div>
			<div class="addons-loading" style="display:none">
				<img src="../modules/globkurier/img/loading.gif" />
			</div>
			<input type="hidden" name="additional_services_tmp" value="">
			<input type="hidden" name="cod_amount_tmp" value="">
			<input type="hidden" name="cod_account_number_tmp" value="">
			<input type="hidden" name="declared_value_tmp" value="">
			<input type="hidden" name="insurance_amount_tmp" value="">
			<div class="gk-addons-domestic"></div>
			<div class="gk-addons-international"></div>
		</div>	
		<div class="gk-datetime" style="display:none;">
				<p class="gk-label"><img src="../modules/globkurier/img/money.png" />{l s='Order pickup' mod='globkurier'}:</p>
				<div class="parcel_pickup">
					<p class="product-label">{l s='Pickup date' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="medium-input product_timely" name="pickup_date" value="{$pickup_date|escape:'htmlall':'UTF-8'}" placeholder="{l s='yyyy-mm-dd' mod='globkurier'}"/>
				</div>
				<div class="parcel_pickup">
					<p class="product-label">{l s='Time from' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="medium-input" name="pickup_time_from" value="{$pickup_time_from|escape:'htmlall':'UTF-8'}" placeholder="{l s='hh:mm' mod='globkurier'}"/>
				</div>
				<div class="parcel_pickup">
					<p class="product-label">{l s='Time to' mod='globkurier'}<span class="mandatory">*</span></p>
					<br />
					<input type="text" class="medium-input" name="pickup_time_to" value="{$pickup_time_to|escape:'htmlall':'UTF-8'}" placeholder="{l s='hh:mm' mod='globkurier'}"/>
				</div>
		</div>
		<div class="gk-payment" style="display:none;">
			<p class="gk-label">
				<img src="../modules/globkurier/img/money.png" />{l s='Select a payment method' mod='globkurier'}:
			</p>
			<div class="gk-order-payment gk-order-payment-on">
				<label>
					<p><input type="radio" name="payment" value="T" /></p>
					<p>{l s='Bank Transfer' mod='globkurier'}</p>
				</label>
			</div>
			<div class="gk-order-payment gk-order-payment-on">
				<label>
					<p><input type="radio" name="payment" value="O" /></p>
					<p>{l s='Online Payment' mod='globkurier'}</p>
				</label>
			</div>
			{if $arr_login_result.prepaid_am gt 0}
				<div class="gk-order-payment gk-order-payment-on">
					<label>
						<p><input type="radio" name="payment" value="P" /></p>
						<p>{l s='Prepaid' mod='globkurier'}</p>
					</label>
				</div>
			{else}
				<div class="gk-order-payment">
					<label>
						<p><input type="radio" disabled /></p>
						<p>{l s='Prepaid' mod='globkurier'}</p>
						<p style="font-size:9px;">({l s='You do not have enough money on your prepaid' mod='globkurier'})</p>
					</label>
				</div>
			{/if}
			{if $arr_login_result.platnosc_odroczona eq 1 && $arr_login_result.platnosc_odroczona_dni gt 0}
				<div class="gk-order-payment gk-order-payment-on">
					<label>
						<p><input type="radio" name="payment" value="D" /></p>
						<p>{l s='Deferred Payment' mod='globkurier'}</p>
					</label>
				</div>
			{else}
				<div class="gk-order-payment">
					<label>
						<p><input type="radio" disabled /></p>
						<p>{l s='Deferred Payment' mod='globkurier'}</p>
						<p style="font-size:9px; margin-left: -8px;">({l s='Do not have a contract to use this service' mod='globkurier'})</p>
					</label>
				</div>
			{/if}
		</div>
		<div class="gk-order gk-process" style="display:none;">
                    <input type="submit" name="gk_process_order" value="{l s='Place an order' mod='globkurier'}" class="button process_me" />
		</div>
	</div>
</fieldset>
</form>
