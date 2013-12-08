{*
 * 2007-2013 PrestaShop
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
 *  @copyright  2007-2013 PrestaShop SA
 *  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
 <form action="" method="post">
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
								<select name="sender_country" style="width: 137px;" />
									<option value="0">{l s='Polska' mod='globkurier'}</option>
									{foreach from=$country key=k item=i}
										<option value="{$i.id_globkurier_country|escape:'htmlall':'UTF-8'}" {if $sender_country eq $i.id_globkurier_country}selected{else}{/if}>{$i.name|escape:'htmlall':'UTF-8'}</option>
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
								<select name="recipient_country" style="width: 137px;" />
									<option value="0">{l s='Polska' mod='globkurier'}</option>
									{foreach from=$country key=k item=i}
										<option value="{$i.id_globkurier_country|escape:'htmlall':'UTF-8'}" {if $recipient_country eq $i.id_globkurier_country}selected{else}{/if}>{$i.name|escape:'htmlall':'UTF-8'}</option>
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
			<div class="gk-order gk-orderdetails">
				<table class="order_details" style="float:left;">
					<thead>
						<tr>
							<td colspan=2>{l s='Order details' mod='globkurier'}</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{l s='Number of packages' mod='globkurier'}</td>
							<td>
								<select name="parcel_count">
									{for $i=1 to 10}
										<option value="{$i|escape:'htmlall':'UTF-8'}" {if $i eq $parcel_count}selected{else}{/if}>{$i|escape:'htmlall':'UTF-8'}</option>
									{/for}
								<select>
							</td>
						</tr>
						<tr>
							<td>{l s='Weight' mod='globkurier'}(kg)</td>
							<td><input type="text" name="parcel_weight" value="{$parcel_weight|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr>
							<td>{l s='Length' mod='globkurier'}(cm)</td>
							<td><input type="text" name="parcel_lenght" value="{$parcel_lenght|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr>
							<td>{l s='Width' mod='globkurier'}(cm)</td>
							<td><input type="text" name="parcel_width" value="{$parcel_width|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr>
							<td>{l s='Height' mod='globkurier'}(cm)</td>
							<td><input type="text" name="parcel_height" value="{$parcel_height|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr>
							<td>{l s='Content' mod='globkurier'}</td>
							<td><input type="text" name="parcel_content" value="{$parcel_content|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr>
							<td>{l s='Pickup date' mod='globkurier'}</td>
							<td><input type="text" readonly name="parcel_pickup_date" value="{$parcel_pickup_date|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
					</tbody>
				</table>
				<div class="gk-pricing">
					<input type="submit" value="{l s='Pricing' mod='globkurier'}" name="gk_price_order" class="button price_me" />
				</div>
			</div>
			{if !empty($arr_order_err)}
				{foreach from=$arr_order_err item=i}
					<div class="gk-order-errors">
						<div class="error">{l s={$i|escape:'htmlall':'UTF-8'} mod='globkurier'}</div>
					</div>
				{/foreach}
			{/if}
			{if !empty($arr_order_products)}
			<div class="header addons_all" style="display:block;">
					<p class="gk-label">
						<img src="../modules/globkurier/img/cart_put.png" />{l s='Select service' mod='globkurier'}
					</p>
				</div>
				<div class="product_normal">
					{foreach from=$arr_order_products key=k item=i}
						{if is_numeric($k)} 
							{if $i.timely_delivery eq 0}
								<div class="gk-order-products" producent="{$i.producent|escape:'htmlall':'UTF-8'}">
									<label>
										<p class="input"><input type="radio" name="parcel_symbol" value="{$i.symbol|escape:'htmlall':'UTF-8'}" /><p>
										<p class="name" style="font-size:12px">{$i.product|escape:'htmlall':'UTF-8'}<p>
										{if $i.area eq 'international'}
											{if $i.service eq 'ES'}
												<p style="font-size:10px">({l s='Economic' mod='globkurier'})</p>
											{/if}
											{if $i.service eq 'AH'}
												<p style="font-size:10px">({l s='Priority' mod='globkurier'})</p>
											{/if}
										{else}
											<p style="font-size:10px"></p>
										{/if}
										<p class="img"><img src="../modules/globkurier/img/carriers/{$i.courier|escape:'htmlall':'UTF-8'}.png"><p>
										<ul>
											<li style="font-size:15px">{$i.price_net|escape:'htmlall':'UTF-8'} PLN<span style="font-size:12px;">({l s='net' mod='globkurier'})</span></li>
										</ul>
									</label>
								</div>
							{elseif $i.timely_delivery gt 0}
								<div class="gk-order-products product_timely" producent="{$i.producent|escape:'htmlall':'UTF-8'}" style="display: none">
									<label>
										<p class="input"><input type="radio" name="parcel_symbol" value="{$i.symbol|escape:'htmlall':'UTF-8'}" /><p>
										<p class="name" style="font-size:12px">{$i.product|escape:'htmlall':'UTF-8'}<p>
										<p class="img"><img src="../modules/globkurier/img/carriers/{$i.courier|escape:'htmlall':'UTF-8'}.png"><p>
										<ul>
											<li style="font-size:15px">{$i.price_net|escape:'htmlall':'UTF-8'} PLN<span style="font-size:12px;">({l s='net' mod='globkurier'})</span></li>
										</ul>
									</label>
								</div>
							{/if}
						{/if}
					{/foreach}
				</div>
				{if !empty($arr_order_products.btn_more_products) && $arr_order_products.btn_more_products eq true}
					<div class="show_btn" style="float:left;">
						<p class="show_more_products">{l s='Show me more' mod='globkurier'}</p>
					</div>
				{/if}
				<p class="gk-fuelcharge">*{l s='The system automatically calculates the volumetric weight. If the proposed service are located in the higher weight classes, this means that the volumetric weight was recognized.' mod='globkurier'}</p>
				<p class="gk-fuelcharge">**{l s='Fuel charges available on www.globkurier.pl' mod='globkurier'}</p>
				{if $sender_country eq 0 && $recipient_country eq 0}
					<div class="header addons_all" style="display:none;">
						<p class="gk-label">
							<img src="../modules/globkurier/img/brick_add.png" />{l s='Do you want to choose an addition to shipping?' mod='globkurier'}
							<span style="margin-left:50px;">
								<input type="radio" name="gk-addons" value=1>{l s='yes' mod='globkurier'}
							</span>
							<span style="margin-left:50px;">
								<input type="radio" name="gk-addons" value=0>{l s='no' mod='globkurier'}
							</span>
						</p>
					</div>
					<div class="addons check_addons" style="display:none;">
						<div class="addons-in-box">
						{if !empty($arr_addons_result)}
							<ul>
								{foreach from=$arr_addons_result key=k item=i}
									<li>
										<p>
											<label>
												<input type="checkbox" name="{$i.symbol|escape:'htmlall':'UTF-8'}" value="{$i.symbol|escape:'htmlall':'UTF-8'}">{l s=$i.name|escape:'htmlall':'UTF-8' mod='globkurier'} +{$i.price|escape:'htmlall':'UTF-8'} PLN
												<span class="{$i.symbol|escape:'htmlall':'UTF-8'}" style="display:none">...</span>
											</label>
										</p>
									</li>
								{/foreach}
							</ul>
							<div class="addons">
								<ul class="insurance" style="display:none;">
									<li>
										<p>
											<label>{l s='Insurance' mod='globkurier'}(PLN)</label>
											<input type="text" name="insurance-value" value="" style="width:200px;"/>
											<span class="input-warning-insurance-value"></span>
										</p>
									</li>
								</ul>
								<ul class="cod" style="display:none;">
									<li>
										<p>
											<label>{l s='COD' mod='globkurier'}(PLN)</label>
											<input type="text" name="cod-value" value="" style="width:200px;"/>
											<span class="input-warning-cod-value"></span>
										</p>
									</li>
									<li>
										<p>
											<label>{l s='Bank account number' mod='globkurier'}</label>
											<input type="text" name="cod-account" value="{$iban|escape:'htmlall':'UTF-8'}" style="width:200px;"/>
											<span class="input-warning-cod-account"></span>
										</p>
									</li>
								</ul>
								<span class="gk-next gk-next-local" style="margin-left: 480px;">{l s='Next' mod='globkurier'}</span>
							</div>
						{/if}
						</div>
					</div>
				{else}
					<div class="header addons_all" style="display:none;">
						<p class="gk-label">
							<img src="../modules/globkurier/img/brick_add.png" />{l s='Enter the declared value for international shipment: ' mod='globkurier'}
						</p>
					</div>
					<div class="header addons_all" style="display:none;">
						<p class="gk-label">
							<input type="text" name="declared-value" value="" />PLN
							<span class="input-warning-declared-value"></span>
							<span class="gk-next gk-next-mnd">{l s='Next' mod='globkurier'}</span>
						</p>
					</div>
				{/if}
			{/if}
		</div>
		{if !empty($arr_order_products) && !empty($arr_login_result)}
				<div class="header payment_all" style="display:none;">
					<p class="gk-label">
						<img src="../modules/globkurier/img/money.png" />{l s='Select a payment method' mod='globkurier'}:
					</p>
					<div class="gk-order-payment gk-order-payment-on">
						<label>
							<p>
								<input type="radio" name="payment" value="T" />
							</p>
							<p>{l s='Bank Transfer' mod='globkurier'}</p>
						</label>
					</div>
					<div class="gk-order-payment gk-order-payment-on">
						<label>
							<p>
								<input type="radio" name="payment" value="O" />
							</p>
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
					<input type="submit" value="{l s='Place an order' mod='globkurier'}" name="gk_process_order" class="button process_me" />
				</div>
		{/if}
	</fieldset>
 </form>