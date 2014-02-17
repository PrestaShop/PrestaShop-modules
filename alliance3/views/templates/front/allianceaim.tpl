{*
* 2007-2013 PrestaShop
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
<link rel="shortcut icon" type="image/x-icon" href="{$module_dir}img/secure.png" />
<p class="payment_module" >
	{if $isFailed == 1}
		<p style="color: red;">
			{if !empty($smarty.get.message)}
				{l s='Error detail from AuthorizeAIM : ' mod='alliance3'}{$smarty.get.message|escape:'htmlall':'UTF-8'}
			{else}
				{l s='Error, please verify the card information' mod='alliance3'}
			{/if}
		</p>
	{/if}

	<form name="authorizeaim_form" id="authorizeaim_form" action="{$module_dir}validation.php" method="post">
		<span style="border: 1px solid #595A5E;display: block;padding: 0.6em;text-decoration: none;margin-left: 0.7em;">
			<a id="click_authorizeaim" href="#" title="{l s='Pay with AuthorizeAIM' mod='alliance3'}" style="display: block;text-decoration: none; font-weight: bold;">
				{if $cards.visa == 1}<img src="{$module_dir}img/cards/visa.gif" alt="{l s='Visa Logo' mod='alliance3'}" style="vertical-align: middle;" />{/if}
				{if $cards.mastercard == 1}<img src="{$module_dir}img/cards/mastercard.gif" alt="{l s='Mastercard Logo' mod='alliance3'}" style="vertical-align: middle;" />{/if}
				{if $cards.discover == 1}<img src="{$module_dir}img/cards/discover.gif" alt="{l s='Discover Logo' mod='alliance3'}" style="vertical-align: middle;" />{/if}
				{if $cards.ax == 1}<img src="{$module_dir}img/cards/ax.gif" alt="{l s='American Express Logo' mod='alliance3'}" style="vertical-align: middle;" />{/if}
				&nbsp;&nbsp;{l s='Secured Payment Processing' mod='alliance3'}
			</a>

						<!--<div id="aut2"style="display:none">-->
				{if $isFailed == 0}
						<div id="aut2">
				{else}
						<div id="aut2">
				{/if}
				<br /><br />

				<div style="width: 136px; height: 145px; float: left; padding-top:40px; padding-right: 20px; border-right: 1px solid #DDD;">
					<img src="{$module_dir}img/logo.png" width=120 alt="secure payment" />
				</div>
	<ul style="color: red;" id='allianceError'>

	</ul>

Choose Payment Type:<br />
				<input type="hidden" name="x_solution_ID" value="A1000006" />
				<input type="hidden" name="x_invoice_num" value="{$x_invoice_num|escape:'htmlall':'UTF-8'}" />
					{if $midtype.authnet == 1 && $midtype.achmod == 1}
				<input type=radio onClick="Hide('didfv1', this); Reveal('div2', this)" id="check_valueCC" name='alliancepay'   value='ccauth' checked=checked>Credit Card
				<input type=radio onClick="Hide('div2', this); Reveal('didfv1', this)" id="check_valueACH" name='alliancepay'
                 value='achauth' >Electronic Check
					{elseif $midtype.authnet == 1}
				<input type=hidden id="check_valueCC" name='alliancepay'   value='ccauth'>
					{elseif $midtype.achmod == 1}
				<input type=hidden id="check_valueCC" name='alliancepay'   value='achauth'>
					{/if}



<br /> <br />
{if $midtype.authnet == 1} 

<div id=div2>
                    <tr><td><font color='red'><f6 id='f6'></f6></font></td></tr>

				<label style="margin-top: 4px; margin-left: 35px;display: block;width: 90px;float: left;">{l s='Full name' mod='alliance3'}</label> <input type="text" name="name" id="fullname" size="30" maxlength="25S" /><img src="{$module_dir}img/secure.png" alt="" style="margin-left: 5px;" /><br /><br />

				<label style="margin-top: 4px; margin-left: 35px; display: block;width: 90px;float: left;">{l s='Card Type' mod='alliance3'}</label>
				<select id="cardType">
					{if $cards.ax == 1}<option value="AmEx">American Express</option>{/if}
					{if $cards.visa == 1}<option value="Visa">Visa</option>{/if}
					{if $cards.mastercard == 1}<option value="MasterCard">MasterCard</option>{/if}
					{if $cards.discover == 1}<option value="Discover">Discover</option>{/if}
				</select>
				<img src="{$module_dir}img/secure.png" alt="" style="margin-left: 5px;" /><br /><br />

				<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Card number' mod='alliance3'}</label> <input type="text" name="x_card_num" value="" id="cardnum" size="30" maxlength="16" autocomplete="Off" /><img src="{$module_dir}img/secure.png" alt="" style="margin-left: 5px;" /><br /><br />
				<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Expiration date' mod='alliance3'}</label>
				<select id="x_exp_date_m" name="x_exp_date_m" style="width:60px;">{section name=date_m start=01 loop=13}
					<option value="{$smarty.section.date_m.index|escape:'htmlall':'UTF-8'}">{$smarty.section.date_m.index|escape:'htmlall':'UTF-8'}</option>{/section}
				</select>
				 /
				<select name="x_exp_date_y">{section name=date_y start=11 loop=20}
					<option value="{$smarty.section.date_y.index|escape:'htmlall':'UTF-8'}">20{$smarty.section.date_y.index|escape:'htmlall':'UTF-8'}</option>{/section}
				</select>
				<img src="{$module_dir}img/secure.png" alt="" style="margin-left: 5px;" /><br />
				<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='CVV' mod='alliance3'}</label> <input type="text" name="x_card_code" id="x_card_code" size="4" maxlength="4" /><img src="{$module_dir}img/secure.png" alt="" style="margin-left: 5px;"/> <img src="{$module_dir}img/help.png" id="cvv_help" title="{l s='the 3 last digits on the back of your credit card' mod='alliance3'}" alt="" /></label>
			<img src="{$module_dir}cvv.png" id="cvv_help_img" alt=""style="display: none;margin-left: 211px;" />
</div>
{/if}

				<hr />
{if $midtype.achmod == 1 && $midtype.authnet != 1}
	<div class="row" id="didfv1" style="display:inline">

{else}
<div class="row" id="didfv1" style="display:none">
					{/if}
				checking account info <br />
                <table>
                

                    
                     <tr><td><font color='red'><f3 id='f3'></f3></font></td></tr>
                                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    Routing Number</label> <input type="text" name="routingnumber" id="routingnumber" value="" size="30" maxlength="16" autocomplete="Off"  onblur="ABAMod10()"/> 
                    </td></tr>
                    
                    
                    <tr><td><font color='red'><f4 id='f4'></f4></font></td></tr>
                       <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    Account Number</label> <input type="text" name="accountnumber" id="accountnumber" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    
                    <tr><td><font color='red'><f5 id='f5'></f5></font></td></tr>
                                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                     Check Number</label> <input type="text" name="checknumber" id="checknumber" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    <tr><td><font color='red'><f6 id='f6'></f6></font></td></tr>
                                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    First Name</label> <input type="text" name="firstname" id="firstname" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    
                    <tr><td><font color='red'><f7 id='f7'></f7></font></td></tr>
                                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    Last Name</label> <input type="text" name="lastname" id="lastname" 
                    value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    
                    <tr><td><font color='red'><f8 id='f8'></f8></font></td></tr>
                                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    Address1</label> <input type="text" name="address1" id="address1" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    <tr><td><font color='red'><f9 id='f9'></f9></font></td></tr>
                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    Address2</label> <input type="text" name="address2" id="address2" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    
                    <tr><td><font color='red'><f11 id='f11'></f11></font></td></tr>
                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    City</label> <input type="text" name="city" id="city" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    <tr><td><font color='red'><f12 id='f12'></f12></font></td></tr>
                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    State</label> <input type="text" name="state" id="state" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    <tr><td><font color='red'><f13 id='f13'></f13></font></td></tr>
                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    Zip</label> <input type="text" name="zip" id="zip" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                    
                    <tr><td><font color='red'><f14 id='f14'></f14></font></td></tr>
                    <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    Phone Number</label> <input type="text" name="phonemumber" id="phonemumber" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>

{if $achsetting.driver == 1}
                      <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    DL State</label> <input type="text" name="dlstate" id="dlstate" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
                      <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    DL Number</label> <input type="text" name="dlnumber" id="dlnumber" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
{/if}
{if $achsetting.identity == 1}
                      <tr><td>
                	<label style="margin-top: 4px; margin-left: 35px; display: block; width: 110px; float: left;">
                    Year of Birth</label> <input type="text" name="identifier" id="identifier" value="" size="30" maxlength="16" autocomplete="Off" /> 
                    </td></tr>
{/if}
          </table>                    
          
</div>
				<input type="button" id="asubmit" value="{l s='Validate order' mod='alliance3'}" style="margin-left: 129px; padding-left: 25px; padding-right: 25px; float: left;" class="button" />
				<br class="clear" />
			</div>
		</span>
	</form>
</p>
<script type="text/javascript">
	var mess_error = "{l s='Please check your credit card information (Credit card type, number and expiration date)' mod='alliance3' js=1}";
	var mess_error2 = "{l s='Please specify your Full Name' mod='alliance3' js=1}";
	{literal}

function Reveal (it, box) {
var vis = (box.checked) ? "block" : "none";
document.getElementById(it).style.display = vis;
}



function Hide (it, box) {
var vis = (box.checked) ? "none" : "none";
document.getElementById(it).style.display = vis;
}

	function pk_show_hide_ach() {
						var f = document.forms.form1;
						if (document.getElementById("pk_ach").checked  == true) {
							// show billing
							for (i=1; i<=9; i++) {
								document.getElementById("pkdiv" + i).style.visibility = "visible";
							}
			
							document.getElementById("pkdiv10").style.visibility = "visible";
							document.getElementById("pkdiv10").style.display = "block";
						} else {
							// hide billing
							for (i=1; i<=9; i++) {
								document.getElementById("pkdiv" + i).style.visibility = "hidden";
							}
							
							document.getElementById("pkdiv10").style.visibility = "hidden";
							document.getElementById("pkdiv10").style.display = "none";
						}
					}

function ABAMod10() { //v2.0

var aba = document.getElementById("routingnumber").value;
 var f3 = document.getElementById('f3');
f3.innerHTML = '';
var valid = "0123456789";
var len = aba.length;
var bNum = true;
var iABA = parseInt(aba);
var sABA = aba.toString();
var url = "abaDisplay2.asp?aba=" + sABA;
var iTotal = 0;
var bResult = false;
var temp;

// alert(aba);
for (var j=0; j<len; j++) {
temp = "" + aba.substring(j, j+1);
// temp = "" + document.abaForm.aba.value.substring(j, j+1);
if (valid.indexOf(temp) == "-1") bNum = false;
}
if(!bNum){
	
	//alert("Not a Number");
	f3.innerHTML = '* Not a Number';
	}
if(len !=0) { // incase they omit the number entirely.
if(len != 9) {
//alert("This is not a proper ABA length");
f3.innerHTML = '* This is not a proper ABA length';
return false;
} else {
for (var i=0; i<len; i += 3) {
iTotal += parseInt(sABA.charAt(i), 10) * 3
+ parseInt(sABA.charAt(i + 1), 10) * 7
+ parseInt(sABA.charAt(i + 2), 10);
}
if (iTotal != 0 && iTotal % 10 == 0){
	f3.innerHTML = '';
bResult = true;

// used for AJAX posting of data
// get(this.parentNode);
} else {
//alert("This is NOT a valid ABA Routing Number!");
f3.innerHTML = '* This is NOT a valid ABA Routing Number!';
// bResult = false;
return false;
}
}
} else {
// zero length do nothing
f3.innerHTML = '';
}
// reset the frame detail.
if (!bResult) {
// used for AJAX posting of data
//document.getElementById('myspan').innerHTML = "";
} else {
// alert("This COULD BE a valid ABA Routing Number!");
return true;
}
// end of not shown in page version of code
f3.innerHTML = '';
return bResult;
}



		$(document).ready(function(){
		
			$('#x_exp_date_m').children('option').each(function()
			{
				if ($(this).val() < 10)
				{
					$(this).val('0' + $(this).val());
					$(this).html($(this).val())
				}
			});
			$('#click_authorizeaim').click(function(e){
				e.preventDefault();
				$('#click_authorizeaim').fadeOut("fast",function(){
					$("#aut2").show();
					$('#click_authorizeaim').fadeIn('fast');
				});
				$('#click_authorizeaim').unbind();
				$('#click_authorizeaim').click(function(e){
					e.preventDefault();
				});
			});

			$('#cvv_help').click(function(){
				$("#cvv_help_img").show();
				$('#cvv_help').unbind();
			});

			$('#asubmit').click(function()
				{
				flag=true;
				errorMsg='';
				if($('#check_valueCC').is(':checked')) { 
					if($("#fullname").val()==''){
						flag=false;
						errorMsg= errorMsg+"<li>Please enter Full Name</li>";
					}
					if($("#x_card_num").val()==''){
						flag=false;
						errorMsg= errorMsg+"<li>Card number can not be empty</li>";
					}
					if($("#x_card_code").val()==''){
						flag=false;
						errorMsg= errorMsg+"<li>CVV code can not be empty</li>";
					}
					
				}
				if($('#check_valueACH').is(':checked')) { 
					if($("#firstname").val()==''){
						flag=false;
						errorMsg= errorMsg+"<li>Please enter First Name</li>";
					}
					if($("#lastname").val()==''){
						flag=false;
						errorMsg= errorMsg+"<li>Please enter Last Name</li>";
					}

					if($("#routingnumber").val()==''){
						flag=false;
						errorMsg= errorMsg+"<li>Please enter Routing Number</li>";
					}
					if($("#checknumber").val()==''){
						flag=false;
						errorMsg= errorMsg+"<li>Please enter Check Number</li>";
					}
					if($("#accountnumber").val()==''){
						flag=false;
						errorMsg= errorMsg+"<li>Please enter Account Number</li>";
					}
				}
				if(flag==false){
					$("#allianceError").html(errorMsg);
					return false;
				}
					$('#authorizeaim_form').submit();
				
			});
		});

	{/literal}
</script>
