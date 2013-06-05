<script type="text/javascript">
var token = "{$glob.fidbag_token|escape:'htmlall':'UTF-8'}";
var price = "{$price|escape:'htmlall':'UTF-8'}";
var discount = parseFloat({$discount});
var totalCart = parseFloat({$total_cart});
var shipping = parseFloat({$shipping});
var fidbagRedirect = "{$redirect|escape:'htmlall':'UTF-8'}";
</script>

<div style="display:block; border: 1px solid #BBB; margin-bottom: 20px;" id="fidbag_form">
	<div style="float:left; width:100px;margin-top:10px">
		<a target="_blank" href="http://www.fidbag.com/AccountCreation.aspx">
			<img border="0" src="{$glob.path}logo.png" alt="logo" style="margin:2px 0 10px 10px;border: 1px solid gray" id="logo_fidbag">
		</a>
	</div>
	<br />
	
	
	<p style="margin-left: 20px">
		{l s='Fid\'Bag is a new consumer loyalty program for everyone.' mod='fidbag'}<br />
		{l s='The way it works is simple : rather than have a loyalty card for each of your favorite stores,' mod='fidbag'}<br />
	 	{l s='you get credit points in a single Fid\'Bag account.' mod='fidbag'}<br />
	 	<br />
	 	{l s='These credit points then allow you to have immediate discounts on future purchases,' mod='fidbag'}<br />
	 	{l s='or you can access our catalogs of good deals (cinema, travel, leisure) on' mod='fidbag'} <a href="http://www.fidbag.com">www.fidbag.com</a>.<br />
	 	<br />
		{l s='You have a Fid\'Bag account, connect it to your account.' mod='fidbag'}<br />
		{l s='No account? Create your Fid\'Bag account now, it\'s fast an free!' mod='fidbag'}
	</p>

	<div>
		<div id="fidbag_voucher_form" style='display:none' class="rebate">
			<a style="float:right;" onclick='logOutFidBagAccount()' class="fidbag_button">{l s='Log out' mod='fidbag'}</a>
			<h4 id="fidbag_title">{l s='Immediate rebate' mod='fidbag'}</h4><br/>
			<strong>{l s='Total amount available:' mod='fidbag'}</strong> <span id='fidbag_user_earned_money'></span> € - <i><span id='fidbag_user_earned_point'></span> {l s='loyalty points' mod='fidbag'}</i><br/>
			<label><strong>{l s='Thanks to your Fid\'bag rewards you own a discount of:' mod='fidbag'}</strong></label>
			<input type="hidden" id="fidbag_client_card_number"/>
			<input style='margin-left:10px;width:30px;background: none repeat scroll 0 0 white;border: 1px solid gray; color:black;margin:2px;padding:3px;' type="text" name="fidbag_discount" id="fidbag_discount"/> € <input type="button" class="fidbag_button" onclick="getImmediateRebate()" value="{l s='Use now!' mod='fidbag'}" /><br/>
			<div id="fidbag_info_form" style='display:none;margin-top:5px'>
				<span id="fidbag_info_discount"><strong>{l s='Discount value:' mod='fidbag'}</strong> <span id="fidbag_discount_amount"></span> {$currency->sign}</span><br/>
				<div id="fidbag_total_due"><strong>{l s='Total due after discount:' mod='fidbag'}</strong> <span id="fidbag_user_ttc">{$total_cart}</span> {$currency->sign}<br/></div>
				<span id="fidbag_discount_error" style="display:none;color:red"></span>
			</div>
		</div>
		<div id="fidbag_log_form">
			<ul id="fidbag_menuTab">
				<li id="fidbag_menuTab1" class="fidbag_menuTabButton">{l s='Creation of a Fid\'Bag loyalty account' mod='fidbag'}</li>
				<li id="fidbag_menuTab2" class="fidbag_menuTabButton selected">{l s='Connecting to your Fid\'Bag account' mod='fidbag'}</li>
			</ul>
			<div id="fidbag_tabList">
				<div id="fidbag_menuTab1Sheet" class="fidbag_tabList">{include file="{$glob.module}views/templates/hook/subscription.tpl"}</div>
				<div id="fidbag_menuTab2Sheet" class="fidbag_tabList selected">{include file="{$glob.module}views/templates/hook/login.tpl"}</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>

{literal}
<style>
	#fidbag_title {font-size:1.2em}
	#fidbag_menuTab {float:left;padding:0;margin:0;margin-left:10px;text-align:center;}
	#fidbag_menuTab li {margin-left:-1px;text-align:left;float:left;display:inline;padding:5px 10px;background:#EFEFEF;font-weight:bold;cursor:pointer;border:1px solid #CCCCCC;-webkit-border-radius: 0px 5px 0px 0px;border-radius: 0px 5px 0px 0px}
	#fidbag_menuTab li.fidbag_menuTabButton.selected {background:#FEF6DD;border-bottom:1px solid #FEF6DD;}
	#fidbag_menuTab li:first-child {margin-left:0}
	#fidbag_tabList {padding-top:25px}
	#fidbag_submit_connect_result {margin-left:10px}
	.fidbag_tabList {display:none}
	.fidbag_tabList.selected {display:block;background:#FEF6DD;border:none;border-top:1px solid #CCCCCC;padding:10px}
	.div_form_fidbag {display:block;line-height:28px}
	.div_form_fidbag label {margin:0 10px 0 0 !important;padding:0}
	.div_form_fidbag input {background:none repeat scroll 0 0 white;border:1px solid gray;color:black;padding:3px;margin:2px}
	.div_form_fidbag.submit-block {text-align:center}
	.div_form_fidbag input[type=submit] {margin: 10px auto !important}
	.fidbag_tabList label {width:200px;text-align:right;float:left;margin:2px;font-size:12px}
	.fidbag_button {background:none repeat scroll 0 0 #D10452 !important;border:1px solid #B10432 !important;color: white !important; font-weight: bold; padding: 3px 10px !important}
	.rebate {background:none repeat scroll 0 0 #FEF6DD;border:none;border-top:1px solid #BBB;padding:10px;margin-top:10px}
</style>

<script type="text/javascript">

	var totalDiscount = 0;

	$(".fidbag_menuTabButton").click(function () {
		$(".fidbag_menuTabButton.selected").removeClass("selected");
		$(this).addClass("selected");
		$(".fidbag_tabList.selected").removeClass("selected");
		$("#" + this.id + "Sheet").addClass("selected");
	});

	function outPutInformationUser(data)
	{
		if(data.returnInfos.mCode == 0)
		{
			$("#fidbag_log_form").hide();
			$("#fidbag_voucher_form").show();
			$("#fidbag_discount_amount").html(discount);
			$("#fidbag_info_form").show();
			$("#fidbag_discount").val(discount == 0 ? data.fidcardInformations.ImmediateDiscount : discount);
			$("#fidbag_user_earned_money").html(data.fidcardInformations.ImmediateDiscount);
			$("#fidbag_user_earned_point").html(data.fidcardInformations.VerticalCredit);
			$("#fidbag_client_card_number").val(data.fidcardInformations.FidBagCardNumber);
			totalDiscount = data.fidcardInformations.ImmediateDiscount;
		}
	}

	function logOutFidBagAccount()
	{
		$("#fidbag_user_used_point").html('');
		$("#fidbag_discount").val('');
		$("#fidbag_log_form").show();
		$("#fidbag_voucher_form").hide();
		$("#fidbag_info_form").hide();
	}

	function getImmediateRebate()
	{	
	    $.ajax({
			url: mainUrl+"/modules/fidbag/consume_immediate_rebate.php",
			type: "POST",
			data: {
				cart : cart,
				customer : customer,
				rebate : $("#fidbag_discount").val(),
				token: "{/literal}{$fidbag_token|escape:'htmlall':'UTF-8'}{literal}",
			},
			dataType: "json",
			success: function(data)
			{
				console.log(data);
				if ((data.error != undefined) && (data.type == 'amount')) {
					$("#fidbag_discount_error").html("{/literal}{l s='The total amount of rebate cannot be greater than' mod='fidbag'}{literal} "+ data.value);
					$("#fidbag_discount_error").show();
				} else if ((data.error != undefined) && (data.type == 'user')) {
					$("#fidbag_discount_error").html("{/literal}{l s='User cannot be verified' mod='fidbag'}{literal}");
					$("#fidbag_discount_error").show();
				} else {
					window.location = fidbagRedirect;
			    }
			},
			error: function(er) {
			}
	    });
	}
</script>
{/literal}
