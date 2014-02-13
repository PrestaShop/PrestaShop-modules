

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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2014 PrestaShop SA
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<table width="100%" cellspacing="0" cellpadding="0" style="margin-top:15px;margin-bottom:15px;" class="table form-data">
   <thead>
      <tr>
         <th colspan="2">{l s='Manage SMS' mod='sendinblue'}</th>
      </tr>
   </thead>
   <tbody>
      <tr>
        
                     <td class="row1" style="border:none; padding-top:10px;">
                     
                     {if isset($prs_version) && $prs_version >= 1.5}
                     {if $current_credits_sms >= 10}
                        <span style="margin-bottom:10px; color:#000; font-weight:bold;color: #585A69;">{l s='Currently you have ' mod='sendinblue'}<strong style="color: #000000;"> {$current_credits_sms}</strong> {l s=' credits sms. To buy more credits, please click' mod='sendinblue'}<a target="_blank" href ="{l s='https://www.sendinblue.com/pricing' mod='sendinblue'}">{l s=' here' mod='sendinblue'}</a>.</span>
                        {else}
                        <span style="margin-bottom:10px; font-weight:bold; color: #585A69;">{l s='Currently you have ' mod='sendinblue'} <strong  style="color:#F00; ">{$current_credits_sms}</strong> {l s=' credits sms. To buy more credits, please click' mod='sendinblue'}<a target="_blank" href ="{l s='https://www.sendinblue.com/pricing' mod='sendinblue'}" >{l s=' here' mod='sendinblue'}</a>.</span>
                        {/if}
                     {else}
                     		{if $current_credits_sms >= 10}
                            <span style="margin-bottom:10px; color:#000; font-weight:bold;color: #996633;">{l s='Currently you have ' mod='sendinblue'}<strong style="color: #000000;"> {$current_credits_sms}</strong> {l s=' credits sms. To buy more credits, please click' mod='sendinblue'}<a target="_blank" href ="{l s='https://www.sendinblue.com/pricing' mod='sendinblue'}">{l s=' here' mod='sendinblue'}</a>.</span>
                            {else}
                            <span style="margin-bottom:10px; font-weight:bold; color: #996633;">{l s='Currently you have ' mod='sendinblue'} <strong  style="color:#F00; ">{$current_credits_sms}</strong> {l s=' credits sms. To buy more credits, please click' mod='sendinblue'}<a target="_blank" href ="{l s='https://www.sendinblue.com/pricing' mod='sendinblue'}" >{l s=' here' mod='sendinblue'}</a>.</span>
                            {/if}
                     {/if}   
                        <br/><br/>
                       <label>{l s='You want to be notified by e-mail when you do not have enough credits?' mod='sendinblue'}</label>

                           <input name="sms_credit" {if isset($sms_credit_status) && $sms_credit_status == 1}checked="checked"{/if} type="radio" value="1" class="sms_credit radio_nospaceing" />
                           {l s='Yes' mod='sendinblue'}
                           <input name="sms_credit" {if isset($sms_credit_status) && $sms_credit_status == 0}checked="checked"{/if}  type="radio" value="0" class="sms_credit radio_spaceing2" />
                           {l s='No' mod='sendinblue'}
                  
                        <div class="hideCredit" id="div_email_test" style="padding-top:20px;">
                         <form action="{$form_url}" method="POST" name="notify_sms_mail_form" >
                   
                           <div id="errmsg" style="color:#F00"></div>
                           <p  class="form-data"><label>{l s='Email' mod='sendinblue'}</label>
                            <input name="sendin_notify_email" id="sendin_notify_email" type="text" value="{$Sendin_Notify_Email}" size="40" /></p>
							<p  class="form-data"> <label>{l s='Limit' mod='sendinblue'}</label>
                           <input name="sendin_notify_value" id="sendin_notify_value" type="text" value="{$Sendin_Notify_Value}" size="40" /><span style="position:absolute;" class="toolTip" title="{l s='Alert threshold for remaining credits' mod='sendinblue'}"></span></p>
							<p  class="form-data"><input name="notify_sms_mail" type="submit"  onClick="return validate('{l s='Please provide valid Email!' mod='sendinblue'}','{l s='Please provide a limit greater than 0' mod='sendinblue'}');" value="{l s='Save' mod='sendinblue'}" class="button" style=" margin-left: 658px;" /></p>
							<p  class="mrgin-left">{l s='To get the email notification, you should run ' mod='sendinblue'}{$link}{l s=' atleast one time per day. ' mod='sendinblue'}
							<span class="toolTip" title="{l s='Note that if you change the name of your Shop (currently ' mod='sendinblue'}
			        {$site_name}{l s=') the token value changes.' mod='sendinblue'}">&nbsp;</span></p>
                          
                          </form>
                        </div>
                    
                     </td>
                  </tr>
		<tr>
         <td>
            <div id="tabs_wrapper">
               <div id="tabs_container">
                  <ul id="tabs">
                     <li class="active"><a href="#tab1">{l s='Send SMS after order confirmation' mod='sendinblue'}</a></li>
                     <li><a class="icon_accept" href="#tab2">{l s='Send a SMS confirmation for the shipment of the order' mod='sendinblue'}</a></li>
                     <li><a href="#tab3">{l s='Send a campaign SMS' mod='sendinblue'}</a></li>
                  </ul>
               </div>
               <div id="tabs_content_container">
                  <div id="tab1" class="tab_content" style="display: block;">
                     <div class="wrapper" style="margin-top:15px;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-data2">
                      
                           <tr>
                              <td class="row1">
                                 <label class="r_label" style="padding-top:0px;"><strong>{l s='Send SMS after order confirmation' mod='sendinblue'}</strong></label>
                                 <div class="radio_bx"> <span style="margin:0 0px 0 0;">
                                    <input name="sms_order_setting" {if isset($sms_order_status) && $sms_order_status == 1}checked="checked"{/if}  class="sms_order_setting radio_spaceing" type="radio" value="1" />
                                    {l s='Yes' mod='sendinblue'}</span> <span>
                                    <input name="sms_order_setting" class="sms_order_setting radio_spaceing2" {if isset($sms_order_status) && $sms_order_status == 0}checked="checked"{/if}  type="radio" value="0" />
                                    {l s='No' mod='sendinblue'}</span>
                                 </div>
                                 <div class="hideOrder">
                                
                                 <div class="form_table" style="margin-top:33px;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-data">

                                       <tr>
                                       <td style="padding:0">
										 
										 <form action="{$form_url}" method="POST" name="sender_order_form">

										<table width="100%" border="0" cellspacing="0" cellpadding="0">
										<tr><td valign="top" style="padding-left:0; width:250px"><h4 style="margin-top:0px;">{l s='SMS settings' mod='sendinblue'}:</h4></td><td >&nbsp</td></tr>
										<tr>
                                          <td valign="top">
                                          <label>{l s='Sender' mod='sendinblue'}</label></td>
                                          <td>
                                             <input name="sender_order"  id="sender_order" type="text" value="{$Sendin_Sender_Order}" class="input_bx" />
                                             <span class="toolTip" title="{l s='This field allows you to personalize the SMS sender. Attention, there is a limited number of characters.If you enter a name, it is limited to 11 characters, and special characters (é, à ...) are not accepted. If you enter a phone number, it is limited to 17 characters; the number should be preceded by 00 and the country code (for instance, for France mobile 06 12 34 56 78 use 0033612345678 with 0033 is France prefix ).' mod='sendinblue'}">&nbsp;</span>
                                             <div class="hintmsg"><em>{l s='Number of characters left: ' mod='sendinblue'}<span id="sender_order_text">17	</span></em>
                                             </div>
                                             
                                          </td>
                                       </tr>
                                       <tr>
                                          <td valign="top"><label>{l s='Message' mod='sendinblue'}</label></td>
                                          <td>
                                             <textarea name="sender_order_message" id="sender_order_message" cols="45" rows="5" class="textarea_bx">{$Sendin_Sender_Order_Message}</textarea>
                                             <span class="toolTip" style="margin-top:35px;" title="{l s=' Create the content of your SMS with the limit of 160-character.Beyond 160 characters, it will be counted as a second SMS. Thus, if you write  SMS of 240 characters, it will be recorded using two SMS.' mod='sendinblue'}">&nbsp;</span>
                                             <div class="clear"></div>
                                              <span style="line-height:16px; margin-bottom:15px; width:333px;">{l s='Number of SMS used: ' mod='sendinblue'}<span id="sender_order_message_text_count">0</span>
                                             <div class="hintmsg"><em>{l s='Number of characters left: ' mod='sendinblue'}</em><span id="sender_order_message_text">160</span></div>
                                             <div class="hintmsg"><em>{l s='Attention line break is counted as a single character.' mod='sendinblue'}</em>
                                             </div><br>
                                               <div class="hintmsg"><em>{l s='If you want to personalize the SMS, you can use the variables below:' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For civility use {civility}' mod='sendinblue'}</em></div>
                                                <div class="hintmsg"><em>{l s='- For first name use {first_name}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For last name use {last_name}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For order reference id use {order_reference}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For order price use {order_price}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For order date use {order_date}' mod='sendinblue'}</em></div>
                                             <input name="sender_order_save" type="submit" value="{l s='Save' mod='sendinblue'}" class="button" style="margin-top:10px; margin-left:249px;"  />
                                          </td>
                                       </tr>
                                       </table>
                                        </form> 
                                       </td>
                                       </tr>
                                      
                                       <tr>
                                       <td colspan="2" valign="top" border="0">
										<form  name="sender_order_testForm">

										<table width="100%" border="0" cellspacing="0" cellpadding="0">
                                       <tr>
                                          <td>
                                             
                                             <div class="hintmsg"><em>{l s='Sending a test SMS will be deducted from your SMS credits.' mod='sendinblue'}</em></div>
                                             <label style="padding-top:5px;">{l s='Send a test SMS' mod='sendinblue'}</label>
                                             <input name="sender_order_number" id="sender_order_number" maxlength="17" type="text" value="" class="input_bx" />
                                             <span class="toolTip" title="{l s=' The phone number should be in this form: 0033663309741 for this France mobile 06 63 30 97 41 (0033 is France prefix)' mod='sendinblue'}">&nbsp;</span>
                                             <input name="sender_order_submit" id="sender_order_submit" type="button" onclick="return testsmssend();" value="{l s='Send' mod='sendinblue'}" class="button"  />
                                          </td>
                                       </tr>
                                       
                                        </table>
                                        </form> 
                                       </td>
                                       </tr>
                                    </table>
                                 </div>
                              </td>
                           </tr>
                        </table>
                     </div></div>
                  </div>
                  <div id="tab2" class="tab_content">
                     <div class="wrapper"  style="margin-top:15px;margin-bottom: 6px;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                       
                           <tr>
                              <td class="row1">
                                 <label class="r_label" style="margin-left: 7px; padding-top:0px;"><strong>{l s='Send a SMS confirmation for the shipment of the order' mod='sendinblue'}</strong></label>
                                 <div class="radio_bx"> <span>
                                    <input name="sms_shiping_setting" class="sms_shiping_setting radio_spaceing" {if isset($sms_shipment_status) && $sms_shipment_status == 1}checked="checked"{/if} type="radio" value="1" />
                                    {l s='Yes' mod='sendinblue'}</span> <span>
                                    <input name="sms_shiping_setting" type="radio" value="0" {if isset($sms_shipment_status) && $sms_shipment_status == 0}checked="checked"{/if}  class="sms_shiping_setting radio_spaceing2"/>
                                    {l s='No' mod='sendinblue'}</span>
                                 </div>
                                 <div class="hideShiping" style="margin-top:33px;">
								
                                 <div class="form_table ">
                                 <table width="100%" border="0" cellspacing="0" cellpadding="0">
                           
                                       <tr>
                                       <td >
										 
										 <form action="{$form_url}" method="POST" name="sender_shipment_form">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr><td valign="top"  style="padding-left:0" ><h4 style="margin-top:0px;">{l s='SMS settings' mod='sendinblue'}:</h4></td><td >&nbsp</td></tr>
                                       <tr>
                                          <td style="width:250px"  valign="top"><label>{l s='Sender' mod='sendinblue'}</label></td>
                                          <td >
                                             <input name="sender_shipment" maxlength="17" id="sender_shipment" type="text" value="{$Sendin_Sender_Shipment}" class="input_bx" />
                                             <span class="toolTip" title="{l s='This field allows you to personalize the SMS sender. Attention, there is a limited number of characters.If you enter a name, it is limited to 11 characters, and special characters (é, à ...) are not accepted. If you enter a phone number, it is limited to 17 characters; the number should be preceded by 00 and the country code (for instance, for France mobile 06 12 34 56 78 use 0033612345678 with 0033 is France prefix ).' mod='sendinblue'}">&nbsp;</span>
                                             <div class="hintmsg"><em>{l s='Number of characters left: ' mod='sendinblue'}<span id="sender_shipment_text">17</span></em>
                                             </div>
                                             
                                          </td>
                                       </tr>
                                       <tr>
                                          <td valign="top"><label>{l s='Message' mod='sendinblue'}</label></td>
                                          <td>
                                             <textarea name="sender_shipment_message" id="sender_shipment_message" cols="45" rows="5" class="textarea_bx">{$Sendin_Sender_Shipment_Message}</textarea>
                                             <span class="toolTip" style="margin-top:35px;" title="{l s=' Create the content of your SMS with the limit of 160-character.Beyond 160 characters, it will be counted as a second SMS. Thus, if you write  SMS of 240 characters, it will be recorded using two SMS.' mod='sendinblue'}">&nbsp;</span>
                                                <div class="clear"></div>                                        
                                             <span style="line-height:16px; margin-bottom:15px; width:333px;">{l s='Number of SMS used: ' mod='sendinblue'}<span id="sender_shipment_message_text_count">0</span>
                                             <div class="hintmsg"><em>{l s='Number of characters left: ' mod='sendinblue'}</em><span id="sender_shipment_message_text">160</span></div>
                                             <div class="hintmsg"><em>{l s='Attention line break is counted as a single character.' mod='sendinblue'}</em>
                                             </div><br/>
                                            <div class="hintmsg"><em>{l s='If you want to personalize the SMS, you can use the variables below:' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For civility use {civility}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For first name use {first_name}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For last name use {last_name}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For order reference id use {order_reference}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For order price use {order_price}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For order date use {order_date}' mod='sendinblue'}</em></div>
                                             <input name="sender_shipment_save" type="submit" value="{l s='Save' mod='sendinblue'}" class="button" style="margin-top:10px;   margin-left: 249px;" />
                                          </td>
                                       </tr>
                                        </table>
                                        </form> 
                                       </td>
                                       </tr>
                                       
                                        <tr>
                                       <td colspan="2" valign="top" border="0">
										<form action="{$form_url}" method="POST" name="sender_order_testForm">

										<table width="100%" border="0" cellspacing="0" cellpadding="0">
                                       <tr>
                                          <td valign="top" colspan="2">
                                             
                                             <div class="hintmsg"><em>{l s='Sending a test SMS will be deducted from your SMS credits.' mod='sendinblue'}</em></div>
                                             <label style="padding-top:5px;">{l s='Send a test SMS' mod='sendinblue'}</label>
                                             <input name="sender_shipment_number" id="sender_shipment_number" maxlength="17" type="text" value="" class="input_bx" />
                                             <span class="toolTip" title="{l s=' The phone number should be in this form: 0033663309741 for this France mobile 06 63 30 97 41 (0033 is France prefix)' mod='sendinblue'}">&nbsp;</span>
                                             <input name="sender_shipment_submit" id="sender_shipment_submit" type="button" onclick="return testSmsShipped();" value="{l s='Send' mod='sendinblue'}" class="button"  />
                                          </td>
                                       </tr>
                                        </table>
                                        </form> 
                                       </td>
                                       </tr>
                                    </table>
                                 </div>
                              </td>
                           </tr>
                        </table>
                     </div> </div>
                  </div>
                  <div id="tab3" class="tab_content"> 
                     <div class="wrapper" style="margin-top:15px;margin-bottom: 6px;">
                     
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-data">
                    
                           <tr>
                              <td class="row1">
                                 <label class="r_label"  style="margin-left: 7px; padding-top:0px;"><strong>{l s='Send a campaign SMS' mod='sendinblue'}</strong></label>
                                 <div class="radio_bx"> <span>
                                    <input name="sms_campaign_setting" class="sms_campaign_setting radio_spaceing" {if isset($sms_campaign_status) && $sms_campaign_status == 1}checked="checked"{/if} type="radio" value="1" />
                                    {l s='Yes' mod='sendinblue'}</span> <span>
                                    <input name="sms_campaign_setting" class="sms_campaign_setting radio_spaceing2" {if isset($sms_campaign_status) && $sms_campaign_status == 0}checked="checked"{/if} type="radio" value="0" />
                                    {l s='No' mod='sendinblue'}</span>
                                 </div>
							<div class="hideCampaign">
							
                                 <div class="form_table " style="margin-top:33px;">
                                 
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
                                       <td>
										 
										 <form action="{$form_url}" method="POST" name="sender_order_form">

										<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-data no-padding">

                                        <tr>
                                          <td valign="top" style="width:250px"> <label> {l s='To' mod='sendinblue'}</label></td>
                                         <td >
                                             <input name="Sendin_Sms_Choice"  checked="checked" class="Sendin_Sms_Choice radio_nospaceing" type="radio" value="1" />
                                            {l s='A single contact' mod='sendinblue'}
                                              &nbsp;&nbsp;
                                             <input name="Sendin_Sms_Choice" class="Sendin_Sms_Choice radio_spaceing" type="radio" value="0" />
                                             {l s='All my PrestaShop customers' mod='sendinblue'}
                                             &nbsp;&nbsp;
                                             <input name="Sendin_Sms_Choice" class="Sendin_Sms_Choice radio_spaceing" type="radio" value="2" />
                                             {l s='Only subscribed customers' mod='sendinblue'}
                                          </td>
                                       </tr>
                                       <tr><td valign="top"  style="padding-left:0" ><h4 style="margin-top:0px;">{l s='SMS Settings' mod='sendinblue'}:</h4></td><td>&nbsp</td></tr>
                                       <tr class="singlechoice">
                                          <td valign="top"><label>{l s='Phone number of the contact' mod='sendinblue'}</label></td>
                                          <td>
                                             <input name="singlechoice" id="singlechoice" maxlength="17" type="text" value="" class="input_bx" />
                                             <span class="toolTip" title="{l s=' The phone number should be in this form: 0033663309741 for this France mobile 06 63 30 97 41 (0033 is France prefix)' mod='sendinblue'}">&nbsp;</span>
                                          </td>
                                       </tr>
                                       
                                       <tr>
                                          <td valign="top"><label>{l s='Sender' mod='sendinblue'}</label></td>
                                          <td>
                                             <input name="sender_campaign" maxlength="17" id="sender_campaign" type="text" value="" class="input_bx" />
                                             <span class="toolTip" title="{l s='This field allows you to personalize the SMS sender. Attention, there is a limited number of characters.If you enter a name, it is limited to 11 characters, and special characters (é, à ...) are not accepted. If you enter a phone number, it is limited to 17 characters; the number should be preceded by 00 and the country code (for instance, for France mobile 06 12 34 56 78 use 0033612345678 with 0033 is France prefix ).' mod='sendinblue'}">&nbsp;</span>
                                             <div class="hintmsg"><em>{l s='Number of characters left: ' mod='sendinblue'}<span id="sender_campaign_text">17</span></em>
                                             </div>
                                             
                                          </td>
                                       </tr>
                                       <tr>
                                          <td valign="top"><label>{l s='Message' mod='sendinblue'}</label></td>
                                          <td>
                                             <textarea name="sender_campaign_message" id="sender_campaign_message" cols="45" rows="5" class="textarea_bx"></textarea>
                                             <span class="toolTip" style=" margin-top:35px;" title="{l s=' Create the content of your SMS with the limit of 160-character.Beyond 160 characters, it will be counted as a second SMS. Thus, if you write  SMS of 240 characters, it will be recorded using two SMS.' mod='sendinblue'}">&nbsp;</span>
                                             <div class="clear"></div>
                                             <span style="line-height:16px; margin-bottom:15px; width:333px;">{l s='Number of SMS used: ' mod='sendinblue'}<span id="sender_campaign_message_text_count">0</span>
                                             <div class="hintmsg"><em>{l s='Number of characters left: ' mod='sendinblue'}</em><span id="sender_campaign_message_text">160</span></div>
                                             <div class="hintmsg"><em>{l s='Attention line break is counted as a single character.' mod='sendinblue'}</em>
                                             </div><br>
                                               <div class="hintmsg"><em>{l s='If you want to personalize the SMS, you can use the variables below:' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For civility use {civility}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For first name use {first_name}' mod='sendinblue'}</em></div>
                                               <div class="hintmsg"><em>{l s='- For last name use {last_name}' mod='sendinblue'}</em></div>
                                              
                                             <input type="submit" style="margin-top:10px; margin-left: 249px;" class="button" value="{l s='Send the campaign' mod='sendinblue'}" name="sender_campaign_save">
                                          </td>
                                       </tr>
                                      
                                      
                                       
                                         </table>
                                          <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                       <tr>
                                          <td valign="top" colspan="2">
                                             
                                             <div class="hintmsg"><em>{l s='Sending a test SMS will be deducted from your SMS credits.' mod='sendinblue'}</em></div>
                                             <label style="padding-top:5px;">{l s='Send a test SMS' mod='sendinblue'}</label>
                                             <input name="sender_campaign_number_test" id="sender_campaign_number_test" maxlength="17" type="text" value="" class="input_bx" />
                                             <span class="toolTip" title="{l s=' The phone number should be in this form: 0033663309741 for this France mobile 06 63 30 97 41 (0033 is France prefix)' mod='sendinblue'}">&nbsp;</span>
                                             <input name="sender_campaign_test_submit" id="sender_campaign_test_submit" onclick="return testSmsCampaign('{l s='Please fill the sender field' mod='sendinblue'}','{l s='Please fill the Mobile Phone field' mod='sendinblue'}','{l s='Please fill the message field' mod='sendinblue'}');" type="button" value="{l s='Send' mod='sendinblue'}" class="button"  />
                                          </td>
                                       </tr>
                                        </table>
                                        </form> 
                                       </td>
                                       </tr>
                                    </table>
                                 </div>
                              </td>
                           </tr>
                        </table>
                     </div>
                     </div>
                  </div>
               </div>
            </div>
         </td>
      </tr>
   </tbody>
</table>

