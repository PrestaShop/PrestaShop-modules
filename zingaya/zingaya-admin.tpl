<div id="zingaya-wrap">
{* Zingaya Introduction *}
<img src="{$zingaya_tracking}" alt="tracking" style="display: none;" />
<div class="zingaya-header">
	<img src="{$module_dir}img/logo.png" alt="" class="zingaya-logo" />
	<h1>{l s='Give your customers the power of instant contact with this revolutionary click-to-call feature.' mod='zingaya'}</h1>
</div>
<div class="zingaya-more">
	<div class="zingaya-video">
		<h2>{l s='How it works' mod='zingaya'}</h2>
		<p>{l s='It\'s simple. If your customers have any questions or issues, they\'re just one click away from help. Customers simply click (or tap) the "Call" button and they will instantly be connected to a member of your support team.' mod='zingaya'}</p>
		<iframe src="http://player.vimeo.com/video/42040181?title=0&amp;byline=0&amp;portrait=0" width="500" height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
	</div>
	<div class="zingaya-info">
		<h2>{l s='Get started in three simple steps...' mod='zingaya'}</h2>
		<h3 class="nb1">{l s='Sign up for a free trial' mod='zingaya'}</h3>
		<p>{l s='No credit card. No obligations. Simply fill out the form and you can start using Zingaya\'s click-to-call feature in minutes!' mod='zingaya'}</p>
		<h3 class="nb2">{l s='Configure your module' mod='zingaya'}</h3>
		<p>{l s='In the credentials tab below, enter the username and password created during the sign-up process.' mod='zingaya'}</p>
		<h3 class="nb3">{l s='Customize your widget' mod='zingaya'}</h3>
		<p>{l s='Zingaya\'s widget wizard will guide you through the customization process. You can add up to three phone numbers for call forwarding, turn on voice recording and voicemail, setup geo-targeting, add Google Analytics functionalities and determine your widget\'s appearance to match your brand\'s identity.' mod='zingaya'}</p>
		<a target="_blank" href="http://zingaya.com/signup/?partner_code=c8238b0a53" class="zingaya-signup"><span>{l s='Get Started!' mod='zingaya'}</span></a>
	</div>
</div>
{* END - Zingaya Introduction *}
{if $zingaya_confirmation}
<div class="conf confirm">
	{foreach from=$zingaya_confirmation item=confirmation}
	{$confirmation}<br/>
	{/foreach}
</div>
<br/>{/if}
{if $zingaya_warning}
<div class="warn warning">
	{foreach from=$zingaya_warning item=warning}
	 {$warning}<br/>
	{/foreach}
</div>
<br/>{/if}
{if $zingaya_errors}
<div class="error">
	{foreach from=$zingaya_errors item=error}
	{$error}<br/>
	{/foreach}
</div>
<br/>{/if}
{* Zingaya Tabs *}
<ul id="zingaya-tabs">
	<li{if !isset($smarty.post.zingaya_tab) || $smarty.post.zingaya_tab == 1} class="selected"{/if}><img src="{$module_dir}/img/credentials.gif" alt="" /> {l s='Credentials & Account Information' mod='zingaya'}</li>
	{if isset($zingaya_widgets) && $zingaya_user_id}
	<li{if isset($smarty.post.zingaya_tab) && $smarty.post.zingaya_tab == 2} class="selected"{/if}><img src="{$module_dir}/logo.gif" alt="" /> {l s='Widgets & Call Buttons' mod='zingaya'}</li>
	{/if}
	{if isset($zingaya_call_history) && $zingaya_user_id}
	<li{if isset($smarty.post.zingaya_tab) && $smarty.post.zingaya_tab == 3} class="selected"{/if}><img src="{$module_dir}/img/history.png" alt="" /> {l s='Call History' mod='zingaya'}</li>
	{/if}
	{if isset($zingaya_voicemails) && $zingaya_user_id}
	<li{if isset($smarty.post.zingaya_tab) && $smarty.post.zingaya_tab == 4} class="selected"{/if}><img src="{$module_dir}/img/voice.png" alt="" /> {l s='Voicemails' mod='zingaya'}</li>
	{/if}
</ul>
{* END - Zingaya Tabs *}
<div id="zingaya-tabs-details">
	<div id="zingaya-tab1"{if !isset($smarty.post.zingaya_tab) || $smarty.post.zingaya_tab == 1} class="selected"{/if}>
		<div id="zingaya-step-1">
			<div id="zingaya-step-1-left">
				<h4>{l s='Log in' mod='zingaya'}</h4>
				<p class="MB10">{l s='In order to use this module, please enter the username and password created during the sign-up process.' mod='zingaya'}</p>
				<form action="{$zingaya_base_link|escape:'htmlall':'UTF-8'}" method="post">
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td><label>{l s='Username' mod='zingaya'}</label></td>
							<td><input type="text" class="text" id="zingaya_username" name="zingaya_username" value="{$zingaya_username|escape:'htmlall':'UTF-8'}" /> <sup>*</sup></td>
						</tr>
						<tr>
							<td><label>{l s='Password' mod='zingaya'}</label></td>
							<td><input type="password" class="password" name="zingaya_api_password" value="{$zingaya_api_password|escape:'htmlall':'UTF-8'}" /> <sup>*</sup></td>
						</tr>
						<tr>
							<td></td>
							<td><input class="button submit" type="submit" name="SubmitZingayaStep1" value="{l s='Save' mod='zingaya'}" /></td>
						</tr>
					</table>
					<input type="hidden" name="zingaya_tab" value="1" />
				</form>
				<span class="small"><sup>*</sup> {l s='Required Fields' mod='zingaya'}</span>
			</div>{if isset($zingaya_account_info) && $zingaya_user_id}
			<div id="zingaya-step-1-right">
				<h4>{l s='Account Information' mod='zingaya'}</h4>
				<table class="table" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td>{l s='Current user' mod='zingaya'}</td>
						<td>{$zingaya_username|escape:'htmlall':'UTF-8'}</td>
					</tr>
					<tr>
						<td>{l s='Current plan' mod='zingaya'}</td>
						<td>{$zingaya_account_info->result->tariff_name|escape:'htmlall':'UTF-8'} - {$zingaya_account_info->result->minutes_included|escape:'htmlall':'UTF-8'} {l s='minutes included' mod='zingaya'}&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;<a href="https://zingaya.com/cp/" target="_blank">{l s='Upgrade' mod='zingaya'}</a></td>
					</tr>
					<tr>
						<td>{l s='Current balance' mod='zingaya'}</td>
						<td><b>${$zingaya_account_info->result->balance|escape:'htmlall':'UTF-8'}</b></td>
					</tr>
					<tr>
						<td>{l s='Minutes left' mod='zingaya'}</td>
						<td><b>{$zingaya_account_info->result->minutes_left|escape:'htmlall':'UTF-8'}</b></td>
					</tr>
					{if $zingaya_account_info->result->trial}
						<tr>
							<td>{l s='Trial' mod='zingaya'}</td>
							<td><b>{$zingaya_account_info->result->trial_days_left|escape:'htmlall':'UTF-8'} {l s='day(s) left' mod='zingaya'}</b></td>
						</tr>
					{/if}						
					<tr>
						<td>{l s='Monthly Payment' mod='zingaya'}</td>
						<td>{if $zingaya_account_info->result->auto_charge}{l s='Yes' mod='zingaya'}{else}{l s='No' mod='zingaya'}{/if}</td>
					</tr>
				</table>
			</div>{/if}
			<div class="clear"></div>
		</div>
	</div>
	{if isset($zingaya_widgets) && $zingaya_user_id}
	<div id="zingaya-tab2"{if isset($smarty.post.zingaya_tab) && $smarty.post.zingaya_tab == 2} class="selected"{/if}>
		<h4>{l s='Widget:' mod='zingaya'}</h4>
		<p class="MB10">{l s='A click-to-call button that can easily be embedded anywhere in your store.' mod='zingaya'}</p>
		<table id="zingaya-widget-table" cellpadding="0" cellspacing="0" class="table" width="100%">
			<tr>
				<th>{l s='ID' mod='zingaya'}</th>
				<th>{l s='Name' mod='zingaya'}</th>
				<th class="center">{l s='Record Calls' mod='zingaya'}</th>
				<th class="center">{l s='Voicemail' mod='zingaya'}</th>
				<th class="center">{l s='Active' mod='zingaya'}</th>
				<th>{l s='Created on' mod='zingaya'}</th>
				<th>{l s='Last Modified on' mod='zingaya'}</th>
				<th>{l s='Actions' mod='zingaya'}</th>
			</tr>
			{if $zingaya_widgets|@count}
				{foreach from=$zingaya_widgets->result item=zingaya_widget}
				<tr>
					<td>{$zingaya_widget->widget_id|intval}</td>
					<td>{$zingaya_widget->widget_name|escape:'htmlall':'UTF-8'}</td>
					<td class="center">{if $zingaya_widget->record_calls}<img src="{$module_dir}../../img/admin/enabled.gif" alt="{l s='Yes' mod='zingaya'}" />{else}<img src="{$module_dir}../../img/admin/disabled.gif" alt="{l s='No' mod='zingaya'}" />{/if}</td>
					<td class="center">{if $zingaya_widget->voicemail}<img src="{$module_dir}../../img/admin/enabled.gif" alt="{l s='Yes' mod='zingaya'}" />{else}<img src="{$module_dir}../../img/admin/disabled.gif" alt="{l s='No' mod='zingaya'}" />{/if}</td>
					<td class="center">{if $zingaya_widget->active}<img src="{$module_dir}../../img/admin/enabled.gif" alt="{l s='Yes' mod='zingaya'}" />{else}<img src="{$module_dir}../../img/admin/disabled.gif" alt="{l s='No' mod='zingaya'}" />{/if}</td>
					<td>{$zingaya_widget->created|escape:'htmlall':'UTF-8'}</td>
					<td>{$zingaya_widget->modified|escape:'htmlall':'UTF-8'}</td>
					<td><a href="{$zingaya_base_link|escape:'htmlall':'UTF-8'}&SubmitZingayaLoadWidget=1&zingaya_widget_id={$zingaya_widget->widget_id}">{l s='Edit' mod='zingaya'}</a>&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;<a rel="{$zingaya_widget->widget_id}" href="javascript:void(0)" onclick="return zingaya_delete(this);">{l s='Delete' mod='zingaya'}</a></td>
				</tr>
				{/foreach}
			{else}
				<tr>
					<td colspan="8">{l s='No widget available, create your first one.' mod='zingaya'}</td>
				</tr>
			{/if}
		</table>
		<input class="button submit" type="button" value="{l s='Add a new Widget' mod='zingaya'}" onclick="{literal}$('#zingaya-add-new-widget').show(500); $('#zingaya-add-new-widget :input:not(:checkbox):not(:button):not(:submit)').val('');$('.timePicker:text[value=\'\']').datetimepicker('setDate',(new Date('2013-01-01 00:00:00')));{/literal}" style="margin-top: 10px;" />
		<div id="zingaya-add-new-widget" style="{if !isset($zingaya_widget_edit_id) || $zingaya_widget_edit_id == 0}display: none;{/if}">
			<form action="{$zingaya_base_link|escape:'htmlall':'UTF-8'}" method="post">
				<h4>{l s='Button General Settings' mod='zingaya'}</h4>
				<label for="zingaya_widget_name">{l s='Name' mod='zingaya'}</label>
				<div class="margin-form">
					<input type="text" id="zingaya_widget_name" name="zingaya_widget_name" value="{if isset($zingaya_widget_name)}{$zingaya_widget_name|escape:'htmlall':'UTF-8'}{/if}" /> <sup>*</sup>
				</div>
				<label for="zingaya_widget_record_calls">{l s='Record Calls' mod='zingaya'}</label>
				<div class="margin-form">
					<input type="checkbox" id="zingaya_widget_record_calls" name="zingaya_widget_record_calls" value="1"{if isset($zingaya_widget_record_calls) && $zingaya_widget_record_calls == 1} checked="checked"{/if} />
				</div>
				<label for="zingaya_widget_voicemail">{l s='Voicemail service' mod='zingaya'}</label>
				<div class="margin-form">
					<input type="checkbox" id="zingaya_widget_voicemail" name="zingaya_widget_voicemail" value="1"{if isset($zingaya_widget_voicemail) && $zingaya_widget_voicemail == 1} checked="checked"{/if} />
				</div>
				<label for="zingaya_widget_keypad">{l s='Show Keypad during Call' mod='zingaya'}</label>
				<div class="margin-form">
					<input type="checkbox" id="zingaya_widget_keypad" name="zingaya_widget_keypad" value="1"{if isset($zingaya_widget_keypad) && $zingaya_widget_keypad} checked="checked"{/if} />
				</div>
				<label for="zingaya_widget_ganalytics">{l s='Google Analytics ID' mod='zingaya'}</label>
				<div class="margin-form">
					<input type="text" id="zingaya_widget_ganalytics" name="zingaya_widget_ganalytics" value="{if isset($zingaya_widget_ganalytics)}{$zingaya_widget_ganalytics|escape:'htmlall':'UTF-8'}{/if}" />
				</div>
				<label for="zingaya_widget_phone">{l s='Phone Number' mod='zingaya'}</label>
				<div class="margin-form">
					{if isset($zingaya_callme_id)}<input type="hidden" name="zingaya_callme_id" value="{$zingaya_callme_id}" />{/if}
					<input type="text" id="zingaya_widget_phone" name="zingaya_widget_phone" value="{if isset($zingaya_widget_phone)}{$zingaya_widget_phone|escape:'htmlall':'UTF-8'}{/if}" /> <sup>*</sup> 
					<span>{l s='Without the country code +1 (eg. for "+1-888-111-1111" just enter "8881111111")' mod='zingaya'}</span>
				</div>
				<label>{l s='Phone Number Hours' mod='zingaya'}</label>
				<div class="margin-form">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<th class="resetBorder">{l s='MON' mod='zingaya'}</th>
							<th>{l s='TUE' mod='zingaya'}</th>
							<th>{l s='WED' mod='zingaya'}</th>
							<th>{l s='THU' mod='zingaya'}</th>
							<th>{l s='FRI' mod='zingaya'}</th>
							<th>{l s='SAT' mod='zingaya'}</th>
							<th>{l s='SUN' mod='zingaya'}</th>
						</tr>
						<tr class="hours">
							<td class="resetBorder">
								<input class="timePicker" type="text" name="zingaya_widget_hours_mo_am" value="{if isset($zingaya_widget_hours->MON[0])}{displayUSHour hour=$zingaya_widget_hours->MON[0]}{/if}" /><br />
								{l s='to' mod='zingaya'}<br />
								<input class="timePicker" type="text" name="zingaya_widget_hours_mo_pm" value="{if isset($zingaya_widget_hours->MON[1])}{displayUSHour hour=$zingaya_widget_hours->MON[1]}{/if}" />
							</td>
							<td>
								<input class="timePicker" type="text" name="zingaya_widget_hours_tu_am" value="{if isset($zingaya_widget_hours->TUE[0])}{displayUSHour hour=$zingaya_widget_hours->TUE[0]}{/if}" /><br />
								{l s='to' mod='zingaya'}<br />
								<input class="timePicker" type="text" name="zingaya_widget_hours_tu_pm" value="{if isset($zingaya_widget_hours->TUE[1])}{displayUSHour hour=$zingaya_widget_hours->TUE[1]}{/if}" />
							</td>
							<td>
								<input class="timePicker" type="text" name="zingaya_widget_hours_we_am" value="{if isset($zingaya_widget_hours->WED[0])}{displayUSHour hour=$zingaya_widget_hours->WED[0]}{/if}" /><br />
								{l s='to' mod='zingaya'}<br />
								<input class="timePicker" type="text" name="zingaya_widget_hours_we_pm" value="{if isset($zingaya_widget_hours->WED[1])}{displayUSHour hour=$zingaya_widget_hours->WED[1]}{/if}" />
							</td>
							<td>
								<input class="timePicker" type="text" name="zingaya_widget_hours_th_am" value="{if isset($zingaya_widget_hours->THU[0])}{displayUSHour hour=$zingaya_widget_hours->THU[0]}{/if}" /><br />
								{l s='to' mod='zingaya'}<br />
								<input class="timePicker" type="text" name="zingaya_widget_hours_th_pm" value="{if isset($zingaya_widget_hours->THU[1])}{displayUSHour hour=$zingaya_widget_hours->THU[1]}{/if}" />
							</td>
							<td>
								<input class="timePicker" type="text" name="zingaya_widget_hours_fr_am" value="{if isset($zingaya_widget_hours->FRI[0])}{displayUSHour hour=$zingaya_widget_hours->FRI[0]}{/if}" /><br />
								{l s='to' mod='zingaya'}<br />
								<input class="timePicker" type="text" name="zingaya_widget_hours_fr_pm" value="{if isset($zingaya_widget_hours->FRI[1])}{displayUSHour hour=$zingaya_widget_hours->FRI[1]}{/if}" />
							</td>
							<td>
								<input class="timePicker" type="text" name="zingaya_widget_hours_sa_am" value="{if isset($zingaya_widget_hours->SAT[0])}{displayUSHour hour=$zingaya_widget_hours->SAT[0]}{/if}" /><br />
								{l s='to' mod='zingaya'}<br />
								<input class="timePicker" type="text" name="zingaya_widget_hours_sa_pm" value="{if isset($zingaya_widget_hours->SAT[1])}{displayUSHour hour=$zingaya_widget_hours->SAT[1]}{/if}" />
							</td>
							<td>
								<input class="timePicker" type="text" name="zingaya_widget_hours_su_am" value="{if isset($zingaya_widget_hours->SUN[0])}{displayUSHour hour=$zingaya_widget_hours->SUN[0]}{/if}" /><br />
								{l s='to' mod='zingaya'}<br />
								<input class="timePicker" type="text" name="zingaya_widget_hours_su_pm" value="{if isset($zingaya_widget_hours->SUN[1])}{displayUSHour hour=$zingaya_widget_hours->SUN[1]}{/if}" />
							</td>
						</tr>
					</table>
				</div>
				<div class="clear"></div>
				<div id="zingaya-button-style">
					<h4>{l s='Customize your button' mod='zingaya'}</h4>
					<p class="MB10">{l s='Here you can modified your click-to-call button\'s visual appearance!' mod='zingaya'}</p>
					<label for="zingaya_widget_button_size">{l s='Size' mod='zingaya'}</label>
					<div class="margin-form">
						<select id="zingaya_widget_button_size" name="zingaya_widget_button_size" style="width: 135px;">
							<option value="small"{if isset($zingaya_widget_button_size) && $zingaya_widget_button_size == 'small'} selected="selected"{/if}>{l s='Small' mod='zingaya'}</option>
							<option value="medium"{if isset($zingaya_widget_button_size) && $zingaya_widget_button_size == 'medium'} selected="selected"{/if}>{l s='Medium' mod='zingaya'}</option>
							<option value="big"{if isset($zingaya_widget_button_size) && $zingaya_widget_button_size == 'big'} selected="selected"{/if}>{l s='Big' mod='zingaya'}</option>
						</select>
					</div>
					<label for="zingaya_widget_button_color">{l s='Color Scheme' mod='zingaya'}</label>
					<div class="margin-form">
						<select id="zingaya_widget_button_color" name="zingaya_widget_button_color" style="width: 135px;">
							<option value="light"{if isset($zingaya_widget_button_color) && $zingaya_widget_button_color == 'light'} selected="selected"{/if}>{l s='light' mod='zingaya'}</option>
							<option value="dark"{if isset($zingaya_widget_button_color) && $zingaya_widget_button_color == 'dark'} selected="selected"{/if}>{l s='dark' mod='zingaya'}</option>
						</select>
					</div>
					<label for="zingaya_widget_button_text">{l s='Text' mod='zingaya'}</label>
					<div class="margin-form">
						<input type="text" id="zingaya_widget_button_text" name="zingaya_widget_button_text" value="{if isset($zingaya_widget_button_text)}{$zingaya_widget_button_text|escape:'htmlall':'UTF-8'}{/if}" maxlength="100" />
					</div>
					<label for="zingaya_widget_button_text_color_1">{l s='Text color 1' mod='zingaya'}</label>
					<div class="margin-form">
						<input class="colorSelector" type="text" id="zingaya_widget_button_text_color_1" name="zingaya_widget_button_text_color_1" value="{if isset($zingaya_widget_button_text_color_1)}{$zingaya_widget_button_text_color_1|escape:'htmlall':'UTF-8'}{/if}" />
					</div>
					<label for="zingaya_widget_button_text_color_2">{l s='Text color 2' mod='zingaya'}</label>
					<div class="margin-form">
						<input class="colorSelector" type="text" id="zingaya_widget_button_text_color_2" name="zingaya_widget_button_text_color_2" value="{if isset($zingaya_widget_button_text_color_2)}{$zingaya_widget_button_text_color_2|escape:'htmlall':'UTF-8'}{/if}" />
					</div>
					<label for="zingaya_widget_button_shadow">{l s='Add a shadow to the text' mod='zingaya'}</label>
					<div class="margin-form">
						<input type="checkbox" id="zingaya_widget_button_shadow" name="zingaya_widget_button_shadow" value="1"{if isset($zingaya_widget_button_shadow) && $zingaya_widget_button_shadow} checked="checked"{/if} />
					</div>
					<label for="zingaya_widget_button_foreground_color_1">{l s='Foreground color' mod='zingaya'}</label>
					<div class="margin-form">
						<input class="colorSelector" type="text" id="zingaya_widget_button_foreground_color_1" name="zingaya_widget_button_foreground_color_1" value="{if isset($zingaya_widget_button_foreground_color_1)}{$zingaya_widget_button_foreground_color_1|escape:'htmlall':'UTF-8'}{/if}" />
					</div>
					<label for="zingaya_widget_button_foreground_color_2">{l s='Foreground color with gradient' mod='zingaya'}</label>
					<div class="margin-form">
						<input class="colorSelector" type="text" id="zingaya_widget_button_foreground_color_2" name="zingaya_widget_button_foreground_color_2" value="{if isset($zingaya_widget_button_foreground_color_2)}{$zingaya_widget_button_foreground_color_2|escape:'htmlall':'UTF-8'}{/if}" />
					</div>
					<label for="zingaya_widget_button_foreground_hover_color_1">{l s='Foreground roll-over color' mod='zingaya'}</label>
					<div class="margin-form">
						<input class="colorSelector" type="text" id="zingaya_widget_button_foreground_hover_color_1" name="zingaya_widget_button_foreground_hover_color_1" value="{if isset($zingaya_widget_button_foreground_hover_color_1)}{$zingaya_widget_button_foreground_hover_color_1|escape:'htmlall':'UTF-8'}{/if}" />
					</div>
					<label for="zingaya_widget_button_foreground_hover_color_2">{l s='Foreground roll-over color with gradient' mod='zingaya'}</label>
					<div class="margin-form">
						<input class="colorSelector" type="text" id="zingaya_widget_button_foreground_hover_color_2" name="zingaya_widget_button_foreground_hover_color_2" value="{if isset($zingaya_widget_button_foreground_hover_color_2)}{$zingaya_widget_button_foreground_hover_color_2|escape:'htmlall':'UTF-8'}{/if}" />
					</div>
					<label for="zingaya_widget_button_radius">{l s='Corner radius' mod='zingaya'}</label>
					<div class="margin-form">
						<select id="zingaya_widget_button_radius" name="zingaya_widget_button_radius" style="width: 135px;">
							<option value="0"{if isset($zingaya_widget_button_radius) && $zingaya_widget_button_radius == '0'} selected="selected"{/if}>{l s='Square' mod='zingaya'}</option>
							<option value="5"{if isset($zingaya_widget_button_radius) && $zingaya_widget_button_radius == '5'} selected="selected"{/if}>{l s='Soft-rounded' mod='zingaya'}</option>
							<option value="10"{if isset($zingaya_widget_button_radius) && $zingaya_widget_button_radius == '10'} selected="selected"{/if}>{l s='Soft-rounded +' mod='zingaya'}</option>
							<option value="15"{if isset($zingaya_widget_button_radius) && $zingaya_widget_button_radius == '15'} selected="selected"{/if}>{l s='Medium-rounded' mod='zingaya'}</option>
							<option value="20"{if isset($zingaya_widget_button_radius) && $zingaya_widget_button_radius == '20'} selected="selected"{/if}>{l s='Medium-rounded+' mod='zingaya'}</option>
							<option value="25"{if isset($zingaya_widget_button_radius) && $zingaya_widget_button_radius == '25'} selected="selected"{/if}>{l s='Rounded' mod='zingaya'}</option>
							<option value="30"{if isset($zingaya_widget_button_radius) && $zingaya_widget_button_radius == '30'} selected="selected"{/if}>{l s='Rounded+' mod='zingaya'}</option>
						</select>
					</div>
					<label>{l s='Display it on' mod='zingaya'}</label>
					<div class="margin-form fix-checkbox">
						<input type="checkbox" name="zingaya_widget_hook[]"{if isset($zingaya_hooks) && in_array('7', $zingaya_hooks)} checked="checked"{/if} value="7" /> 
						<label class="inner-label">{l s='Left Column' mod='zingaya'}</label>
						<input type="checkbox" name="zingaya_widget_hook[]"{if isset($zingaya_hooks) && in_array('6', $zingaya_hooks)} checked="checked"{/if} value="6" /> 
						<label class="inner-label">{l s='Right Column' mod='zingaya'}</label>
						<input type="checkbox" name="zingaya_widget_hook[]"{if isset($zingaya_hooks) && in_array('8', $zingaya_hooks)} checked="checked"{/if} value="8" /> 
						<label class="inner-label">{l s='Homepage' mod='zingaya'}</label>
						<input type="checkbox" name="zingaya_widget_hook[]"{if isset($zingaya_hooks) && in_array('17', $zingaya_hooks)} checked="checked"{/if} value="17" /> 
						<label class="inner-label">{l s='Product page (Footer)' mod='zingaya'}</label>
						<input type="checkbox" name="zingaya_widget_hook[]"{if isset($zingaya_hooks) && in_array('35', $zingaya_hooks)} checked="checked"{/if} value="35" /> 
						<label class="inner-label">{l s='Product page (Actions)' mod='zingaya'}</label>
						<input type="checkbox" name="zingaya_widget_hook[]"{if isset($zingaya_hooks) && in_array('14', $zingaya_hooks)} checked="checked"{/if} value="14" /> 
						<label class="inner-label">{l s='Top of pages' mod='zingaya'}</label>
					</div>
					<div class="margin-form">
						<input class="button submit" type="submit" value="{l s='Save' mod='zingaya'}" name="SubmitZingayaNewWidget" />
						{if isset($zingaya_widget_edit_id) && $zingaya_widget_edit_id}<input type="hidden" name="zingaya_widget_edit_id" value="{$zingaya_widget_edit_id|intval}" />{/if}
						<input type="hidden" name="zingaya_tab" value="2" />
					</div>
				</div>
			</form>
			<div class="zingaya-widget-block">
				<h4>{l s='Button Preview' mod='zingaya'}</h4>
				{if isset($zingaya_widget_url)}<a id="zingayaCallButtonPreview" href="{$zingaya_widget_url}" style="display:block" onclick="return popup(this);"></a>{else}<div id="zingayaCallButtonPreview"></div>{/if}
			</div>
			<span class="small"><sup>*</sup> {l s='Required Fields' mod='zingaya'}</span>
		</div>
	</div>{/if}
	{if isset($zingaya_call_history)}
	<div id="zingaya-tab3"{if isset($smarty.post.zingaya_tab) && $smarty.post.zingaya_tab == 3} class="selected"{/if}>
		<form action="{$zingaya_base_link|escape:'htmlall':'UTF-8'}" method="post">
			<div class="border-bottom">
				<label>{l s='From:' mod='zingaya'}</label>
				<input type="text" name="zingaya_ch_from" id="zingaya_ch_from" value="{if isset($smarty.post.zingaya_ch_from)}{$smarty.post.zingaya_ch_from|escape:'htmlall':'UTF-8'}{/if}" />
				<label>{l s='To:' mod='zingaya'}</label>
				<input type="text" name="zingaya_ch_to" id="zingaya_ch_to" value="{if isset($smarty.post.zingaya_ch_to)}{$smarty.post.zingaya_ch_to|escape:'htmlall':'UTF-8'}{/if}" />
				<input type="hidden" name="zingaya_tab" value="3" />
				<input type="submit" class="button" name="SubmitZingayaCallHistory" value="{l s='Update' mod='zingaya'}" />
			</div>
		</form>
	{if $zingaya_call_history|@count}
		<p class="MB10">{l s='Here you can view your call log:' mod='zingaya'} <strong>{$zingaya_call_history|@count}</strong> {l s='calls are available for the selected period' mod='zingaya'}</p>
		<table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
			<tr>
				<th>{l s='ID' mod='zingaya'}</th>
				<th>{l s='Date' mod='zingaya'}</th>
				<th>{l s='Duration' mod='zingaya'}</th>
				<th>{l s='Caller' mod='zingaya'}</th>
				<th>{l s='Cost' mod='zingaya'}</th>
				<th>{l s='Location' mod='zingaya'}</th>
				<th>{l s='Call IP address' mod='zingaya'}</th>
				<th>{l s='Recorded Call' mod='zingaya'}</th>
				<th>{l s='Widget' mod='zingaya'}</th>
				<th>{l s='Status' mod='zingaya'}</th>
			</tr>
			{foreach from=$zingaya_call_history name=zingaya_call_history item=zingaya_ch}
			<tr{if $smarty.foreach.zingaya_call_history.index > 50} style="display: none;"{/if}>
				<td>{$zingaya_ch->id|intval}</td>
				<td>{$zingaya_ch->call_date|escape:'htmlall':'UTF-8'}</td>
				<td>{if $zingaya_ch->duration > 3600}{if $zingaya_ch->duration / 3600 < 10}0{/if}{$zingaya_ch->duration / 3600}{else}00{/if}:{if ($zingaya_ch->duration / 60) % 60 > 0}{if ($zingaya_ch->duration / 60) % 60 < 10}0{/if}{($zingaya_ch->duration / 60) % 60}{else}00{/if}:{if $zingaya_ch->duration % 60 < 10}0{/if}{$zingaya_ch->duration % 60}</td>
				<td>{if isset($zingaya_ch->phone)}{displayUSPhoneNumber number=$zingaya_ch->phone}{else}{l s='N/A' mod='zingaya'}{/if}</td>
				<td>${$zingaya_ch->price|escape:'htmlall':'UTF-8'}</td>
				<td>{if isset($zingaya_ch->direction)}{$zingaya_ch->direction|escape:'htmlall':'UTF-8'}{else}--{/if}</td>
				<td>{$zingaya_ch->client_ip|escape:'htmlall':'UTF-8'}</td>
				<td>{if isset($zingaya_ch->record_url)}<a href="{$zingaya_ch->record_url|escape:'htmlall':'UTF-8'}">{l s='Download file' mod='zingaya'}</a>{else}--{/if}</td>
				<td>{if substr($zingaya_ch->widget_page_url, 0, 4) == 'http'}<a href="{$zingaya_ch->widget_page_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='View' mod='zingaya'}</a>{else}--{/if}</td>
				<td>{if $zingaya_ch->successful}{l s='OK' mod='zingaya'}{else}{l s='Customer did not hear any response' mod='zingaya'}{/if}</td>
			</tr>
			{/foreach}
		</table>
		{if $zingaya_call_history|@count > 50}
			<input type="button" name="ZingayaCallHistoryShowAll" value="{l s='Show all (200 max)' mod='zingaya'}" onclick="$('#zingaya-tab3 table tr').show();" />
		{/if}
	{else}
		<p>{l s='You have no call history at this time or the Call History feature is not enabled on your plan.' mod='zingaya'}</p>
	{/if}
	</div>
	{/if}
	{if isset($zingaya_voicemails)}
	<div id="zingaya-tab4"{if isset($smarty.post.zingaya_tab) && $smarty.post.zingaya_tab == 4} class="selected"{/if}>
		<form action="{$zingaya_base_link|escape:'htmlall':'UTF-8'}" method="post">
			<div class="border-bottom">
				<label>{l s='From:' mod='zingaya'}</label>
				<input type="text" name="zingaya_vm_from" id="zingaya_vm_from" value="{if isset($smarty.post.zingaya_vm_from)}{$smarty.post.zingaya_vm_from|escape:'htmlall':'UTF-8'}{/if}" />
				<label>{l s='To:' mod='zingaya'}</label>
				<input type="text" name="zingaya_vm_to" id="zingaya_vm_to" value="{if isset($smarty.post.zingaya_vm_to)}{$smarty.post.zingaya_vm_to|escape:'htmlall':'UTF-8'}{/if}" />
				<input type="hidden" name="zingaya_tab" value="4" />
				<input type="submit" class="button" name="SubmitZingayaVoicemails" value="{l s='Update' mod='zingaya'}" />
			</div>
		</form>
		{if $zingaya_voicemails|@count}
			<p class="MB10">{l s='Here you can view your voicemails:' mod='zingaya'} <strong>{$zingaya_voicemails|@count}</strong> {l s='voicemails are available for the selected period' mod='zingaya'}</p>
			<table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
				<tr>
					<th>{l s='ID' mod='zingaya'}</th>
					<th>{l s='Date' mod='zingaya'}</th>
					<th>{l s='Duration' mod='zingaya'}</th>
					<th>{l s='Listen' mod='zingaya'}</th>
					<th>{l s='Already Listened' mod='zingaya'}</th>
				</tr>
				{foreach from=$zingaya_voicemails name=zingaya_voicemails item=zingaya_voicemail}
				<tr{if $smarty.foreach.zingaya_voicemails.index > 50} style="display: none;"{/if}>
					<td>{$zingaya_voicemail->voicemail_id|intval}</td>
					<td>{$zingaya_voicemail->date|escape:'htmlall':'UTF-8'}</td>
					<td>{if $zingaya_voicemail->duration > 3600}{if $zingaya_voicemail->duration / 3600 < 10}0{/if}{$zingaya_voicemail->duration / 3600}{else}00{/if}:{if ($zingaya_voicemail->duration / 60) % 60 > 0}{if ($zingaya_voicemail->duration / 60) % 60 < 10}0{/if}{($zingaya_voicemail->duration / 60) % 60}{else}00{/if}:{if $zingaya_voicemail->duration % 60 < 10}0{/if}{$zingaya_voicemail->duration % 60}</td>
					<td><a href="{$zingaya_voicemail->record_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Download voicemail file' mod='zingaya'}</a></td>
					<td>{if $zingaya_voicemail->listened}{l s='Yes' mod='zingaya'}{else}{l s='No' mod='zingaya'}{/if}</td>
				</tr>
				{/foreach}
			</table>
			{if $zingaya_voicemails|@count > 50}
				<input type="button" name="ZingayaVoicemailsShowAll" value="{l s='Show all (200 max)' mod='zingaya'}" onclick="$('#zingaya-tab4 table tr').show();" />
			{/if}
		{else}
			<p>{l s='You have no voicemails at this time or the Voicemails feature is not enabled on your plan.' mod='zingaya'}</p>
		{/if}
	</div>
	{/if}
	</div>
</div>

<style type="text/css">
	#zingaya-wrap { overflow: hidden; margin: 0px auto; padding-top: 10px; padding-bottom: 30px; width: 980px; }
	#zingaya-step-1-left { width: 360px; float: left; margin-right: 10px; }
	#zingaya-step-1-left table td { padding: 0px 10px 10px 0px; text-align: left; vertical-align: top; width: auto; }
	span.small { color: #7F7F7F; }
	span.small sup { color: #CC0000; font-weight: bold; vertical-align: text-top; }
	#zingaya-step-1-right { width: 552px; float: left; border-left: 1px solid #BBB; padding-left: 35px; }
	#zingaya-add-new-widget { border: solid 1px #CCC; margin-top: 10px; padding: 10px; }
	#zingaya-tabs-details h4 { font-size: 1.2em; margin-top: 0px; }
	#zingaya-add-new-widget h4 { border-bottom: solid 1px #CCC; font-size: 1.2em; margin-top: 0px; padding-bottom: 5px; }
	.zingaya-widget-block { background-color: #FFF; border: solid 1px #CCC; float: right; padding: 10px; margin-top: -385px; width: 270px; }
	#zingaya-add-new-widget th, #zingaya-add-new-widget td { border-left: solid 1px #CCC; text-align: center; vertical-align: middle; }
	#zingaya-add-new-widget .resetBorder { border-left: none; }
	#zingaya-add-new-widget td { padding: 5px; }
	#zingaya-add-new-widget tr.hours input { font-size: 10px; width: 45px; }
	#zingaya-add-new-widget input[type="radio"], input[type="checkbox"] { margin-top: 3px; }
	#zingaya-add-new-widget .fix-checkbox input[type="checkbox"] { margin-top: 0px; vertical-align: text-top; }
	#zingaya-widget-table .center { text-align: center; }
	.zingaya-header { border-bottom: #6BACDE solid 1px; margin-bottom: 20px; padding-bottom: 20px; overflow: hidden; }
	.zingaya-header h1 { color: #6BACDE; display: block; float: left; font-size: 24px; font-weight: normal; line-height: 26px; margin: 0px; padding: 15px 0px 0px 0px; width: 550px; }
	.zingaya-logo { display: inline-block; float: left; margin: 0px 130px 0px 0px; }
	.zingaya-more { overflow: hidden; position: relative; margin: 0px 0px 20px 0px; }
	.zingaya-video { float: left; width: 500px; }
	.zingaya-info { margin-left: 530px; min-width: 400px; max-width: 990px; }
	.zingaya-more h2 { color: #6BACDE; font-size: 24px; font-weight: lighter; line-height: 24px; margin: 0px 0px 15px 0px; padding: 0px; }
	.zingaya-more h3 { color: #3269BB; font-size: 18px; font-weight: bold; line-height: 24px; margin: 0px 0px 5px 0px; padding: 0px 0px 0px 30px; }
	.zingaya-more h3.nb1 { background: url('{$module_dir}img/no1.png') left center no-repeat; }
	.zingaya-more h3.nb2 { background: url('{$module_dir}img/no2.png') left center no-repeat; }
	.zingaya-more h3.nb3 { background: url('{$module_dir}img/no3.png') left center no-repeat; }
	.zingaya-more p { color: #6A6E72; font-size: 14px; font-weight: lighter; line-height: 24px; margin: 0px 0px 15px 0px; padding: 0px; }
	.zingaya-signup { color: #FFF; display: inline-block; font-size: 18px; height: 49px; line-height: 30px; margin: 0px auto; padding: 0px 0px 0px 20px; text-shadow: 0px 1px 0px #AE5511; }
	.zingaya-signup span { display: inline-block; height: 31px; margin: 0px -20px 0px 0px; padding: 9px 20px 9px 0px; }
	.zingaya-signup, .zingaya-signup span { background-image: url('{$module_dir}img/signup_btn.png'); background-repeat: no-repeat; }
	.zingaya-signup { background-position: left top; }
	.zingaya-signup:hover { background-position: left -49px; color: #FFF; text-decoration: none; outline: none; }
	.zingaya-signup:active { background-position: left -98px; color: #FFF; text-decoration: none; outline: none; }
	.zingaya-signup span { background-position: right -147px; }
	.zingaya-signup span:hover { background-position: right -196px; }
	.zingaya-signup span:active { background-position: right -245px; }
	#zingaya-tabs { padding: 0; margin: 0; text-align: left; }
	#zingaya-tabs li { text-align: left; float: left; display: inline; padding: 5px 10px 5px 5px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; margin-left: 5px; height: 14px; }
	#zingaya-tabs li.selected { background: #FFFFF0; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; margin-bottom: -1px; padding-bottom: 6px; }
	#zingaya-tabs li img { margin-top: -2px; }
	#zingaya-tab1, #zingaya-tab2, #zingaya-tab3, #zingaya-tab4 { clear: left; display: none; }
	#zingaya-tab1.selected, #zingaya-tab2.selected, #zingaya-tab3.selected, #zingaya-tab4.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; }
	.button { cursor: pointer; }
	.MB10 { margin-bottom: 10px; }
	#zingaya-tabs-details label { color: #585A69; font-size: 1.1em; text-shadow: 0 1px 0 #FFFFFF; width: 275px; }
	#zingaya-tabs-details label.inner-label { color: #585A69; float: none; font-size: 1.1em; font-weight: normal; margin-right: 4px; padding: 0px; text-shadow: 0 1px 0 #FFFFFF; width: auto; }
	#zingaya-tabs-details .margin-form { padding-left: 285px; }
	#zingaya-tabs-details .table { border-bottom: none; }
	#zingaya-tabs-details .table tr td { color: #585A69; }
	#zingaya-tabs-details a { color: #0089CF; }
	#zingaya-tabs-details a:hover { text-decoration: underline; }
	#zingaya-step-1-left label, .resetLabel, .border-bottom label { float: none; width: auto; }
	.border-bottom input[type="text"] { margin-right: 10px; }
	.border-bottom .button { padding-bottom: 0px; padding-top: 2px; }
	.border-bottom { border-bottom: solid 1px #CCCCCC; padding-bottom: 8px; }
	.zingaya_link_small, .zingaya_link_medium, .zingaya_link_big { background-position: left 0px; }
	.zingaya_link_small:hover { background-position: left -36px; }
	.zingaya_link_small:active { background-position: left -72px; }
	.zingaya_link_medium:hover { background-position: left -46px; }
	.zingaya_link_medium:active { background-position: left -92px; }
	.zingaya_link_big:hover { background-position: left -58px; }
	.zingaya_link_big:active { background-position: left -118px; }
</style>

<script type="text/javascript" src="{$module_dir}js/colorpicker.js"></script>
<script type="text/javascript">
{literal}
$(document).ready(function() {
	{/literal}
	{if isset($smarty.get.zingaya_widget_id)}
		{literal}
		$('html,body').animate({scrollTop: $('#zingaya-add-new-widget').offset().top},'slow');
		{/literal}
	{/if}
	{literal}
	regenerateButton();
	$('#zingaya-tabs li').click(function ()
	{
		/* Tab Buttons */
		$(this).siblings().removeClass('selected');
		$(this).addClass('selected');

		/* Tab Content */
		$('#zingaya-tab1, #zingaya-tab2, #zingaya-tab3, #zingaya-tab4').removeClass('selected');
		$('#zingaya-tab'+($(this).index()+1)).addClass('selected');
	});
	$('.colorSelector').each(function(){
		var color = $(this).val();
		var obj = $(this);
		$(this).ColorPicker({
			color: '#8f478f',
			onShow: function (colpkr) { $(colpkr).fadeIn(500);return false; },
			onHide: function (colpkr) { $(colpkr).fadeOut(500);return false; },
			onChange: function (hsb, hex, rgb) { obj.val('#' + hex);regenerateButton(); }
		});
	});
});

function zingaya_delete(obj)
{
	if (!confirm('{/literal}{l s='Are you sure you want to delete this widget?' js=1 mod='zingaya'}{literal}'))
	   return false;
	$.get('../modules/zingaya/ajax.php?function=delete_widget&token={/literal}{$zingaya_token|escape:'htmlall':'UTF-8'}{literal}&widget='+$(obj).attr('rel'), function(result)
	{
		$(obj).parent().parent().remove();
	});
	return false;
}

function regenerateButton()
{
	var size = 'size='+$('select[name="zingaya_widget_button_size"]').val();
	var type = 'button_type='+$('select[name="zingaya_widget_button_color"]').val();
	var text = 'text='+$('input[name="zingaya_widget_button_text"]').val();
	var textcolor1 = 'textcolor1='+$('input[name="zingaya_widget_button_text_color_1"]').val().replace('#', '%23');
	var textcolor2 = 'textcolor2='+$('input[name="zingaya_widget_button_text_color_2"]').val().replace('#', '%23');
	var text_shadow = 'text_shadow='+$('input[name="zingaya_widget_button_shadow"]').is(':checked');
	var foreground = 'foreground='+$('input[name="zingaya_widget_button_foreground_color_1"]').val().replace('#', '%23');
	var foreground2 = 'foreground2='+$('input[name="zingaya_widget_button_foreground_color_2"]').val().replace('#', '%23');
	var foreground_hover = 'foreground_hover='+$('input[name="zingaya_widget_button_foreground_hover_color_1"]').val().replace('#', '%23');
	var foreground_hover2 = 'foreground_hover2='+$('input[name="zingaya_widget_button_foreground_hover_color_2"]').val().replace('#', '%23');
	var corner_radius = 'corner_radius='+$('select[name="zingaya_widget_button_radius"]').val(); 
	$.get('../modules/zingaya/ajax.php?function=regenerateButton&token={/literal}{$zingaya_token|escape:'htmlall':'UTF-8'}{literal}&'+size+'&'+type+'&'+text+'&'+textcolor1+'&'+textcolor2+'&'+text_shadow+'&'+foreground+'&'+foreground2+'&'+foreground_hover+'&'+foreground_hover2+'&'+corner_radius, function(result){
		$('#zingayaCallButtonPreview').removeClass();
		$('#zingayaCallButtonPreview').addClass('zingaya_link_'+$('select[name="zingaya_widget_button_size"]').val());
		$('#zingayaCallButtonPreview').css('background-image', 'url(data:image/png;base64,'+result+')');
		$('#zingayaCallButtonPreview').css('background-repeat', 'no-repeat');
		if ($('select[name="zingaya_widget_button_size"]').val() == 'small')
			$('#zingayaCallButtonPreview').css('height', '34px');
		else if ($('select[name="zingaya_widget_button_size"]').val() == 'medium')
			$('#zingayaCallButtonPreview').css('height', '44px');
		else if ($('select[name="zingaya_widget_button_size"]').val() == 'big')
			$('#zingayaCallButtonPreview').css('height', '54px');
	});
}		
			
function popup(mylink)
{
	if ($(mylink).attr('href') == '')
		alert('Sorry, You can\'t give a call.');
	else
		window.open($(mylink).attr('href'), 'zingaya', 'width=400,height=200,scrollbars=yes');
	return false;
}
		
$(document).ready(function()
{
	$('#zingaya-button-style').change(function(){
		regenerateButton();
	});

	$('.timePicker').timepicker({
		showMinute: false,
		ampm: true
	});

	$('#zingaya_ch_from').datetimepicker({
		prevText:'',
		nextText:'',
		dateFormat:'yy-mm-dd'
	});

	$('#zingaya_ch_to').datetimepicker({
		prevText:'',
		nextText:'',
		dateFormat:'yy-mm-dd',
	});

	$('#zingaya_vm_from').datetimepicker({
		prevText:'',
		nextText:'',
		dateFormat:'yy-mm-dd'
	});

	$('#zingaya_vm_to').datetimepicker({
		prevText:'',
		nextText:'',
		dateFormat:'yy-mm-dd',
	});

	$('.timePicker:text[value=""]').datetimepicker('setDate',(new Date('2013-01-01 00:00:00')));
});
{/literal}
</script>