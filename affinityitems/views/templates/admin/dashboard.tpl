{*
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future. If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*}

<script>
{literal}
var progressBarWidth;

var checkIp = /((^\s*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\s*$)|(^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$))/;


function isNumber(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}

function progress() {
	$.ajax({
		{/literal}{if $ajaxController}{literal}
		url: "index.php?controller=AEAjax&configure=affinityitems&ajax",
		{/literal}{else}{literal}
		url: "{/literal}{$module_dir|escape:'htmlall':'UTF-8'}{literal}ajax/synchronize.php",
		{/literal}{/if}{literal}
		data: {"synchronize" : true, "getInformation" : true, "token" : "{/literal}{$prestashopToken|escape:'htmlall':'UTF-8'}{literal}"},
		type: "POST",
		async: true,
		success: function (e, t, n) {
			var response = jQuery.parseJSON(e);
			if(response._ok == true) {
				if(!response._lock && response._percentage == 100) {
					$("#progressBar").hide();
					$("#tsync").text("{/literal}{l s='Your system is synchronized' mod='affinityitems'}{literal}");
				} else {
					progressBarWidth = parseInt(response._percentage) * $("#progressBar").width() / 100;
					$("#progressBar").find("div").css("width", progressBarWidth).html(parseInt(response._percentage) + "% ");
					$("#tsync").text("{/literal}{l s='Install in progress, please wait' mod='affinityitems'}...{literal}");
				}
				$("#syncStep li").each(function( index ) {
					if((index+1) == response._step) {
						if(response._lock) {
							$( this ).attr('class', 'aeloading');
						} else {
							$( this ).attr('class', 'aechecked');
						}
					} else if((index+1) < response._step) {
						$( this ).attr('class', 'aechecked');
					}
				});
			}
		},
		error: function (e, t, n) {}
	});
	setTimeout("progress()", 60000);
}

function addRemoteIp(ip) {
	var ipList = [];
	if(Object.prototype.toString.call(ip) === '[object Array]') {
		ip = ip[0];
		$( "#remoteList option" ).each(function( index ) {
			ipList.push( $(this).val() );
		});
		if(checkIp.test(ip)) {
			if($.inArray(ip, ipList) < 0) {
				ipList.push( ip );
				$.ajax({
					{/literal}{if $ajaxController}{literal}
					url: "index.php?controller=AEAjax&configure=affinityitems&ajax",
					{/literal}{else}{literal}
					url: "{/literal}{$module_dir|escape:'htmlall':'UTF-8'}{literal}ajax/host.php",
					{/literal}{/if}{literal}
					type: "POST",
					data : {"ipList" : ipList, "type" : "remote", "token" : "{/literal}{$prestashopToken|escape:'htmlall':'UTF-8'}{literal}", "aetoken" : '{/literal}{$aetoken}{literal}'},
					async: false,
					success: function (e, t, n) {
						var response = jQuery.parseJSON(e);
						if(response._ok == true) {
							$('#remoteList').append('<option value="'+ip+'">'+ip+'</option>');
						}
					},
					error: function (e, t, n) {}
				});
			}
		}
	}
}

function removeRemoteIp(ip) {
	var ipList = [];
	if(Object.prototype.toString.call(ip) === '[object Array]') {
		ip = ip[0];
		$( "#remoteList option" ).each(function( index ) {
			if($(this).val() !== ip) {
				ipList.push( $(this).val() );
			}
		});
		$.ajax({
			{/literal}{if $ajaxController}{literal}
			url: "index.php?controller=AEAjax&configure=affinityitems&ajax",
			{/literal}{else}{literal}
			url: "{/literal}{$module_dir|escape:'htmlall':'UTF-8'}{literal}ajax/host.php",
			{/literal}{/if}{literal}
			type: "POST",
			data : {"ipList" : ipList, "type" : "remote", "token" : "{/literal}{$prestashopToken|escape:'htmlall':'UTF-8'}{literal}", "aetoken" : '{/literal}{$aetoken}{literal}'},
			async: false,
			success: function (e, t, n) {
				var response = jQuery.parseJSON(e);
				if(response._ok == true) {
					$('#remoteList').empty();
					ipList.forEach(function(key) {
						$('#remoteList').append('<option value="'+key+'">'+key+'</option>');
					});
				}
			},
			error: function (e, t, n) {}
		});
	}
}

$(document).ready(function() {
	$("#tabs").tabs();
	progress();
	$('.aenotification').slideDown();
	$("a#parentToogle").click(function() {
		$(".childToogle").slideUp("slow");
		if($(this).closest("tr").next().find("div#childToogle").css('display')=='none') {
			$(this).closest("tr").next().find("div#childToogle").slideToggle("slow");
		}
	});

	$( ".setNotificationRead" ).click(function() {
		var parent = $(this).parent();
		$.ajax({
			{/literal}{if $ajaxController}{literal}
			url: "index.php?controller=AEAjax&configure=affinityitems&ajax",
			{/literal}{else}{literal}
			url: "{/literal}{$module_dir|escape:'htmlall':'UTF-8'}{literal}ajax/notification.php",
			{/literal}{/if}{literal}
			type: "POST",
			data : {"notificationId" : $(this).val(), "aetoken" : '{/literal}{$aetoken}{literal}'},
			async: false,
			success: function (e, t, n) {
				var response = jQuery.parseJSON(e);
				if(response._ok == true) {
					parent.fadeOut();
				}
			},
			error: function (e, t, n) {}
		});
	});

	$('.aenumber').bind('keypress', function (event) {
		var charCode = event.which;
		var keyChar = String.fromCharCode(charCode); 
		return /[0-9]/.test(keyChar);
	});

	$('#submitLocalIp').click(function () {
		var ipList = [];
		if(checkIp.test($('#aeip').val())) {
			$( "#localList option" ).each(function( index ) {
				ipList.push( $(this).val() );
			});
			if($.inArray($('#aeip').val(), ipList) < 0) {
				$.ajax({
					{/literal}{if $ajaxController}{literal}
					url: "index.php?controller=AEAjax&configure=affinityitems&ajax",
					{/literal}{else}{literal}
					url: "{/literal}{$module_dir|escape:'htmlall':'UTF-8'}{literal}ajax/host.php",
					{/literal}{/if}{literal}
					type: "POST",
					data : {"ip" : $('#aeip').val(), "type" : "local", "token" : "{/literal}{$prestashopToken|escape:'htmlall':'UTF-8'}{literal}", "aetoken" : '{/literal}{$aetoken}{literal}'},
					async: false,
					success: function (e, t, n) {
						var response = jQuery.parseJSON(e);
						if(response._ok == true) {
							$('#localList').append('<option value="'+$('#aeip').val()+'">'+$('#aeip').val()+'</option>');
							$('#aeip').val("");
						}
					},
					error: function (e, t, n) {}
				});
			}
		}
	});

	$("#abtesting").noUiSlider({
		range: [10, 90]
		,start: {/literal}{$abtestingPercentage}{literal}
		,step: 5
		,direction: "ltr"
		,handles: 1
		,serialization: {
			resolution: 1
			,to: [ $('#value'), 'text' ]
		}
	}).change( function(){
		$.ajax({
			{/literal}{if $ajaxController}{literal}
			url: "index.php?controller=AEAjax&configure=affinityitems&ajax",
			{/literal}{else}{literal}
			url: "{/literal}{$module_dir|escape:'htmlall':'UTF-8'}{literal}ajax/abtesting.php",
			{/literal}{/if}{literal}
			type: "POST",
			data : {"percentage" : $("#value").text(), "token" : "{/literal}{$prestashopToken|escape:'htmlall':'UTF-8'}{literal}", "aetoken" : '{/literal}{$aetoken}{literal}'},
			async: false,
			success: function (e, t, n) {
				var response = jQuery.parseJSON(e);
				if(response._ok == true) {}
			},
			error: function (e, t, n) {}
		});
	});

	$('.abtestingdetails').powerTip({
		placement: 's',
		smartPlacement: true
	});

});

{/literal}
</script>

<div class="aewrapper">
	<div class="aeheader">
		<div class="aelogo"></div>  
	</div>
	<div class="aealert aehide"></div>
	{if $recommendation == 0}
	<div class="aegeneral-activation"> 
	<p class='ae-warning-text'>{l s='Warning: the recommendation is not yet activated' mod='affinityitems'}</p> 
	<p>{l s='After having configured the different areas, make sure to enable the general recommendation in the Configuration Tab.' mod='affinityitems'}</p></div>
	{/if}
	<div id="tabs">
		<ul>
			<li><a href="#home">{l s='Home' mod='affinityitems'}</a></li>
			<li><a href="#synchronization">{l s='Synchronization' mod='affinityitems'}</a></li>
			<li><a href="#configuration">{l s='Configuration' mod='affinityitems'}</a></li>
			<li><a href="#servers">{l s='Advanced configuration' mod='affinityitems'}</a></li>
			<li><a href="#account">{l s='Account and support' mod='affinityitems'}</a></li>
			<li><a href="#logs">{l s='Logs' mod='affinityitems'}</a></li>
		</ul>
	</div>

	<div id='home'>

		<div class="aenotification">
			{foreach from=$notifications item=notification}
			<div class="aenotice">
				<button type="button" class="setNotificationRead" data-dismiss="alert" value="{$notification.id_notification|escape:'htmlall':'UTF-8'}" aria-hidden="true">×</button>
				<strong>{$notification.title|escape:'htmlall':'UTF-8'}</strong><br />
				<p>{$notification.text|escape:'htmlall':'UTF-8'}</p>
			</div>
			{/foreach}
		</div>

		<div class='aemodule-description'>

			<div class='aemodule-text-content'>
				<strong class='aewhite aemodule-text'>{l s='Improve your sales by up to 50%' mod='affinityitems'} <br> {l s='thanks to personalized recommendations.' mod='affinityitems'}</strong>
				<br />
				<p class='aewhite aemodule-text'>{l s='Give each visitor the products that fit his tastes' mod='affinityitems'}
					<br />{l s='& needs and benefit from higher transformation rate' mod='affinityitems'}
					<br />{l s='average basket and visitors loyalty.' mod='affinityitems'}</p>
					<p class='aewhite aemodule-text'>{l s='Easy to install, this service has no fixed costs, requires no commitment, and drives a big bunch of profits.' mod='affinityitems'}
						<br />
						{l s='And a free trial offer for 1 month.' mod='affinityitems'}
					</p>
					<br />
					<strong class='aewhite aemodule-text'>{l s='Take no risks try and see !' mod='affinityitems'}</strong>
					<br /><br />
				</div>

				<object type="text/html" data="http://www.youtube.com/embed/AIEfj2UV-qU" width="400" height="236"></object>

				<div class='clear'></div>

			</div>

		<h2 class="aepurple aestat-title">{l s='Statistics' mod='affinityitems'}</h2>

		<div class="aeblock">
			<span class="aetitleblock"><div class='aepurple'>% {l s='guests with' mod='affinityitems'} <br> <span class="aelittle">{l s='recommendations' mod='affinityitems'}</span></div></span>
			<span class='aedarkblue' id="value"></span><span class='aedarkblue'> %</span>
			<div id="abtesting"></div>
			<a href="#" id="abtestingdetails" class="abtestingdetails" title="{l s='The outcome measurement is based on the AB Testing method, an unbiased and reliable impact measure, no matter the conditions' mod='affinityitems'}
			<br>{l s='In this method, a control group of visitors is not eligible for the recommandation.' mod='affinityitems'}
			<br>{l s='You can control the AB Testing groups' mod='affinityitems'} :
			<br>• {l s='First test our solution with a low rate of recommandation' mod='affinityitems'}
			<br>• {l s='Maximize the rate to benefit from the full recommandation impact' mod='affinityitems'}<br>">{l s='More about' mod='affinityitems'}</a>
		
		</div>
		<div class="aeblock">
			<span class="aetitleblock"><div class='aepurple'>{l s='Monthly recommendations' mod='affinityitems'}</div></span>
			<div class='aedarkblue'> {if isset($data->recommendation)}{$data->recommendation} recos{else} <img src="{$module_dir|escape:'htmlall':'UTF-8'}/resources/img/error.png"> {/if}</div>
		</div>
		<div class="aeblock">
			<span class="aetitleblock"><div class='aepurple'>{l s='Sales impact' mod='affinityitems'}</div></span>
			<div class='aedarkblue'>{if !empty($statistics)} {if $statistics->salesImpactByPercentage > 0} + {/if} {$statistics->salesImpactByPercentage|string_format:"%.2f"} % {else} 
				{l s='Impact statistics under construction' mod='affinityitems'}{/if}</div>
				<a href="#" id="abtestingdetails" class="abtestingdetails" title="
				{l s='The outcome measurement becomes significant after an observation period of 2-6 weeks, depending on the frequency of the customers orders on your site and the impact of the personnalization' mod='affinityitems'} <br> {l s='The outcome measurement is automatically displayed when the significance test is conclusive.' mod='affinityitems'}
				<br>{l s='The percentage value shows the turnover increase by website visitor benefiting from the recommandation, compared to those without recommandation.' mod='affinityitems'}
				<br>{l s='This "AB Testing" method gives an unbiased measurement: the external factors like seasonal sales and weather, as well as your marketing activities, SEO or traffic acquisition, have no influence on the measure.' mod='affinityitems'}
				<br>{l s='It only quantifies the global impact of the personnalization offered by Affinity Items.' mod='affinityitems'}<br>">{l s='More about' mod='affinityitems'}</a>
			</div>
			<div class="clear"></div>

			{if !empty($statistics)}

			<div class="aemblock">
				<span class="aetitleblock"><div class='aepurple'>{l s='Detailed statistics.' mod='affinityitems'} <span class="aelittle">{l s='Recommendation effect on the website performance' mod='affinityitems'}</span></div></span>

			<div class="aelblock">
				<div class="aetitlelblock">{l s='Turnover' mod='affinityitems'}</div>
				<div class="aetitlelegend">{$statistics->sales|string_format:"%.2f"} €</div>
				<div class="aelpercentage">{if $statistics->salesImpactByPercentage > 0} + {/if} {$statistics->salesImpactByPercentage|string_format:"%.2f"} %</div>
				<div class='aeldetail'>{if $statistics->salesImpact > 0} + {/if} {$statistics->salesImpact|string_format:"%.2f"} €</div>
			</div>
			<div class="aelblock">
				<div class="aetitlelblock">{l s='Conversion rate' mod='affinityitems'}</div>
				<div class="aetitlelegend">{$statistics->conversionRate|string_format:"%.2f"} %</div>
				<div class="aelpercentage">{if $statistics->conversionRateImpactByPercentage > 0} + {/if} {$statistics->conversionRateImpactByPercentage|string_format:"%.2f"} %</div>
				<div class='aeldetail'>{if $statistics->orderImpact > 0} + {/if} {$statistics->orderImpact|string_format:"%.2f"} {l mod='affinityitems' s='paniers'}</div>
			</div>
			<div class="aelblock">
				<div class="aetitlelblock">{l s='Average invoice' mod='affinityitems'}</div>
				<div class="aetitlelegend">{$statistics->averageOrderImpact|string_format:"%.2f"} €</div>
				<div class="aelpercentage">{if $statistics->averageOrderImpactByPercentage > 0} + {/if} {$statistics->averageOrderImpactByPercentage|string_format:"%.2f"} %</div>
				<div class='aeldetail'>{if $statistics->averageOrderImpactByAmount > 0} + {/if} {$statistics->averageOrderImpactByAmount|string_format:"%.2f"} {l mod='affinityitems' s='€/panier'}</div>
			</div>

			<div class="clear"></div>
		</div>

		{/if}

	</div>
	<div id='synchronization'>

		<div id="progressBar"><div></div></div>

		<div class="aesync">
			<h2 id="tsync" class="tsync aepurple"></h2>
		</div>
		
		<div class="syncStepDescription">{l s='The personalization service analyzes your catalog and your sales history to compute the profiles of your products and your users. After this initialization step, the customization service can suggest relevant recommendations to each visitor, even if they come on your website for the first time. The new products and members are then synchronized along the way. The first step is few minutes to few hours long, depending on the size of your database and the performances of your server.' mod='affinityitems'}</div>

		<div class="aesync">
			<h2 class="syncStepTitle aepurple">{l s='Installation steps' mod='affinityitems'}</h2>
		</div>

		<div class="syncStep">
			<ul id="syncStep">
				<li class="aeunchecked">{l s='Categories synchronization' mod='affinityitems'}</li>
				<li class="aeunchecked">{l s='Products synchronization' mod='affinityitems'}</li>
				<li class="aeunchecked">{l s='Carts synchronization' mod='affinityitems'}</li>
				<li class="aeunchecked">{l s='Orders synchronization' mod='affinityitems'}</li>
				<li class="aeunchecked">{l s='Actions synchronization' mod='affinityitems'}</li>
			</ul>
		</div>
	</div>
	<div id='configuration'>
		<form action='#configuration' method='POST'/>

		<input type="hidden" name="configuration" value="true">

		<div class="general-activation">

			<strong class="aepurple aeactivation">{l s='General activation' mod='affinityitems'}</strong>

			<div class="onoffswitch">
				<input type="checkbox" name="recommendation" class="onoffswitch-checkbox" id="myonoffswitch" {if $recommendation == "1"} checked {/if}>
				<label class="onoffswitch-label" for="myonoffswitch">
					<div class="onoffswitch-inner"></div>
					<div class="onoffswitch-switch"></div>
				</label>
			</div>
		<a class="display-recommendation" href="{$baseUrl}?aeabtesting=A" target="_blank">{l s='Display recommendation' mod='affinityitems'}</a>

		</div>
		<div id="clear"></div>
		<br />

		<table class="aetable"> 
			<tr> 
				<th></th> 
				<th> {l s='Activation' mod='affinityitems'} </th> 
				<th> {l s='Recommendation area label' mod='affinityitems'} </th> 
				<th> {l s='Number of displayed products' mod='affinityitems'} </th>
				<th> {l s='Pictures size' mod='affinityitems'} </th> 
				<th> {l s='Settings' mod='affinityitems'} </th> 
			</tr> 

			{foreach from=$hookList item=hookName}

			<tr class="optionnalRecommendation"> 
				<th> {$hookName|escape:'htmlall':'UTF-8'} </th>
				<th>  
					<input type="radio" name="reco{$hookName|escape:'htmlall':'UTF-8'}" value="1" {if $configuration.{$hookName}->reco{$hookName} == "1"} checked="checked" {/if}>
					<label class="t"><img src="{$module_dir|escape:'htmlall':'UTF-8'}/resources/img/enabled.png" alt="Activé" title="Activé"></label>
					<input type="radio" name="reco{$hookName}" value="0" {if $configuration.{$hookName}->reco{$hookName} == "0"} checked="checked" {/if}>
					<label class="t"><img src="{$module_dir|escape:'htmlall':'UTF-8'}/resources/img/disabled.png" alt="Désactivé" title="Désactivé"></label>
				</th> 
				<th> <input type='text' name="label{$hookName|escape:'htmlall':'UTF-8'}" value="{$configuration.{$hookName}->label{$hookName}}">  </th> 
				<th> <input type='text' name="recoSize{$hookName|escape:'htmlall':'UTF-8'}" value="{$configuration.{$hookName}->recoSize{$hookName}}" class="aenumber"> </th> 
				<th> 
					<select name="imgSize{$hookName|escape:'htmlall':'UTF-8'}">
						{foreach from=$imgSizeList item=size}
							<option value="{$size.name|escape:'htmlall':'UTF-8'}" {if $size.name == $configuration.{$hookName}->imgSize{$hookName}} selected {/if}>{$size.name}</option>
						{/foreach}
					</select> 
				</th> 
				<th class="aetoogle"> <a href="#" id="parentToogle" class="aetoogle" onClick="return false;">{l s='Display settings' mod='affinityitems'}</a> </th> 
			</tr>

			<tr>
				<td colspan="6">
						{if $hookName=="Category" || $hookName=="Search"}
						<div id="childToogle" class="childToogle ae-child-huge">
						<p><label class="aepurple">{l s='Selector' mod='affinityitems'} : </label><input type="text" value="{$configuration.{$hookName}->selector{$hookName}}" name="selector{$hookName|escape:'htmlall':'UTF-8'}" /></p>
						<p><label class="aepurple">{l s='Position selector' mod='affinityitems'} :</label>
							<select name="selectorPosition{$hookName|escape:'htmlall':'UTF-8'}" class='ae-selector-position'>
								<option value="before" {if $configuration.{$hookName}->selectorPosition{$hookName} == "before"} selected {/if}>Before</option>
								<option value="after" {if $configuration.{$hookName}->selectorPosition{$hookName} == "after"} selected {/if}>After</option>
							</select>
						</p>
						{else}
						<div id="childToogle" class="childToogle ae-child-little">
						{/if}
						<fieldset>
							<legend><strong class="aepurple">&lt;div&gt;</strong> id='<input type="text" value="{$configuration.{$hookName}->parentId{$hookName}}" name="parentId{$hookName|escape:'htmlall':'UTF-8'}" />' 
								class='<input type="text" value="{$configuration.{$hookName}->classParent{$hookName}}" name="classParent{$hookName|escape:'htmlall':'UTF-8'}" />'</legend>  
							
							<p class="aeclasstitle"><strong class="aepurple">{l s='Title' mod='affinityitems'}</strong> class='<input type="text" value="{$configuration.{$hookName}->classTitle{$hookName}}" name="classTitle{$hookName|escape:'htmlall':'UTF-8'}" />'</p> 

							<fieldset>
								<legend><strong class="aepurple">&lt;div&gt;</strong> id='<input type="text" value="{$configuration.{$hookName}->contentId{$hookName}}" name="contentId{$hookName|escape:'htmlall':'UTF-8'}" />' class='<input type="text" value="{$configuration.{$hookName}->classContent{$hookName}}" name="classContent{$hookName|escape:'htmlall':'UTF-8'}" />'</legend>
								<fieldset>
									<legend>
										<strong class="aepurple">&lt;ul&gt;</strong> 
											id='<input type="text" value="{$configuration.{$hookName}->listId{$hookName}}" name="listId{$hookName}" />'class='<input type="text" value="{$configuration.{$hookName}->classList{$hookName}}" name="classList{$hookName|escape:'htmlall':'UTF-8'}" />'</legend>
											<fieldset class='little-fieldset'>
											<legend> <strong class="aepurple">&lt;li&gt;</strong>id='<input type="text"  value="{$configuration.{$hookName}->elementListId{$hookName}}" name="elementListId{$hookName|escape:'htmlall':'UTF-8'}" />' class='<input type="text"  value="{$configuration.{$hookName}->classElementList{$hookName}}" name="classElementList{$hookName|escape:'htmlall':'UTF-8'}" />' </legend>

												<p><strong class="aepurple">Product image class :</strong><input type="text" class="aeright" value="{$configuration.{$hookName}->classElementImage{$hookName}}" name="classElementImage{$hookName|escape:'htmlall':'UTF-8'}" /></p>
												
												<p><strong class="aepurple">Product name class :</strong><input type="text" class="aeright" value="{$configuration.{$hookName}->classElementName{$hookName}}" name="classElementName{$hookName|escape:'htmlall':'UTF-8'}" /></p>
												
												<p><strong class="aepurple">Product description class :</strong><input type="text" class="aeright" value="{$configuration.{$hookName}->classElementDescription{$hookName}}" name="classElementDescription{$hookName|escape:'htmlall':'UTF-8'}" /></p>

												<p><strong class="aepurple">Price container class :</strong><input type="text" class="aeright" value="{$configuration.{$hookName}->classPriceContainer{$hookName}}" name="classPriceContainer{$hookName|escape:'htmlall':'UTF-8'}" /></p>
												
												<p><strong class="aepurple">Price class :</strong><input type="text" class="aeright" value="{$configuration.{$hookName}->classPrice{$hookName}}" name="classPrice{$hookName|escape:'htmlall':'UTF-8'}" /></p>

											</fieldset>
								</fieldset>
							</fieldset>
						</fieldset>
					</div>
				</td>
			</tr>

			{/foreach}

		</table>

		<div class="clear"></div>

		<input type="submit" value="{l s='Save' mod='affinityitems'}" name="submit" class="aebutton aeright submit">
		
		<div class="clear"></div>
		
	</form>
</div>

<div id='servers'>
	<div class="servers-description">{l s=' To secure access to the recommendations, please indicate the servers that are allowed to query these recommendations. Instructions: first create the server in the list of known server, then add it to the list of enabled servers' mod='affinityitems'}</div>

	<div class="aesrv-container">

	<div class="serverList">

		<div class="server-list-text">
					<p class="aepurple kserver">{l s='Known servers' mod='affinityitems'}</p><p class="aepurple eserver">{l s='Enabled servers' mod='affinityitems'}</p>
					<div class="clear"></div>
		</div>

		<select id="localList" multiple class='aeservers aeleft'>
			{foreach from=$localHosts item=host}
				<option value="{$host|escape:'htmlall':'UTF-8'}">{$host|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>

		<div class="betweensrv">
			<a href="#" class="add-server" onClick="addRemoteIp($('#localList').val());return false;"> {l s='Add' mod='affinityitems'} >> </a>
			<br />
			<a href="#" class="remove-server" onClick="removeRemoteIp($('#remoteList').val());return false;"> {l s='Delete' mod='affinityitems'} << </a>
		</div>


		{if isset($data->hosts)}
		<select id="remoteList" multiple class='aeservers aeright'>
			{foreach from=$data->hosts item=host}
			<option value="{$host|escape:'htmlall':'UTF-8'}">{$host|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
		{else} 
		<img class="servers-error" src="{$module_dir|escape:'htmlall':'UTF-8'}/resources/img/error.png"> 
		{/if}

	</div>

	<div class="clear"></div>

	<div class="srvinput">
		<input type="text" id="aeip" class="aeip"><input type="submit" id="submitLocalIp" value="+">
	</div>

	</div>

	<div class="clear clear-margin-thirty"></div>

	<form action='#servers' method='POST'/>
	
	<div class="aeblock">
		<span class="aetitleblock"><div class="aepurple">{l s='Rescind' mod='affinityitems'} <br /> <span class="aelittle">{l s='contract' mod='affinityitems'} </div></span>
		<div class="aedarkblue"> <span class="ae-rescind"> {l s='Check this box to have your website data deleted on our platform after the uninstallation' mod='affinityitems'}
			<input type="checkbox" name="breakContract"  {if $breakContract == "1"} checked="checked" {/if}></span> 
		</div>
		<a href="#" id="abtestingdetails" class="abtestingdetails" title="{l s='Do not check the box to avoid resynchronization when installing a new version of the module.' mod='affinityitems'}">{l s='More about' mod='affinityitems'}</a>
	</div>

	<div class="aeblock">
		<span class="aetitleblock"><div class="aepurple">{l s='A/B Testing' mod='affinityitems'} <br /> <span class="aelittle">{l s='IP Blacklist' mod='affinityitems'}</span></div></span>
		<div class="aedarkblue"> <span class='blackList'> {l s='IP list' mod='affinityitems'} </span> 
			<input type="text" name="blacklist" value="{if !empty($blacklist)}{foreach from=$blacklist item=ip}{$ip|escape:'htmlall':'UTF-8'};{/foreach}{/if}">
		</div>
		<a href="#" id="abtestingdetails" class="abtestingdetails" title="{l s='This feature lets you blacklist the IP addresses of your own company, so that the statistics are unbiased.' mod='affinityitems'}<br />{l s='For example, if a call center places orders directly on the site, these commands should not be taken into account in the AB Testing.' mod='affinityitems'}<br />{l s='Blacklisted IP addresses do not receive recommendations' mod='affinityitems'}">{l s='More about' mod='affinityitems'}</a>
	</div>

	<div class="aeblock">
		<span class="aetitleblock"><div class="aepurple">{l s='Frequency' mod='affinityitems'} <br /> <span class="aelittle">{l s='of the safety synchronization' mod='affinityitems'}</span></div></span>
		<div class="aedarkblue"> <span class='syncDiff'> {l s='Duration (minutes)' mod='affinityitems'} </span> 
			<input type="text" class="aenumber" name="syncDiff" value="{$syncDiff|escape:'htmlall':'UTF-8'}">
		</div>
		<a href="#" id="abtestingdetails" class="abtestingdetails" title="{l s='After the initial synchronization, the new events are synchronized along the way.' mod='affinityitems'} <br />{l s='However, a safety synchronization process regularly takes place to ensure that your system is up to date.' mod='affinityitems'}<br />
		{l s='Adjust here the frequency of the safety synchronization' mod='affinityitems'}">{l s='More about' mod='affinityitems'}</a>
	</div>

	<div class="clear clear-margin-thirty"></div>

	<input type="submit" value="{l s='Save' mod='affinityitems'}" name="submit" class="aebutton aeright submit">

	<div class="clear"></div>

</form>

</div>

<div id='account'>
	<div class="clear clear-margin-thirty"></div>
	
	<a class="aebutton account-button" target="_blank" href="http://manager.affinityitems.com/login/{$siteId|escape:'htmlall':'UTF-8'}/{$data->authToken|escape:'htmlall':'UTF-8'}">{l s='Access my account' mod='affinityitems'}</a>
	
	<div class="account-description"> 
		<h2 class='aepurple'>{l s='Account' mod='affinityitems'}</h2>
		{l s='Manage your payment options and access your invoices in the customer area. Also find all the system messages and more detailed statistics on the impact of your personnalization service.' mod='affinityitems'}
	</div>

	<div class="support-description">
		<h2 class='aepurple'>{l s='Support' mod='affinityitems'}</h2>
		{l s='If you\'re having a problem with your Affinity Items module, please read the' mod='affinityitems'} {l s='FAQ (Tab "Support" in the customer area)' mod='affinityitems'} {l s=' or contact' mod='affinityitems'} <a class="ae-email-color" href="mailto:mathieu@affinity-engine.fr">mathieu@affinity-engine.fr</a>
	</div>
	<div class="clear"></div>
</div>

<div id="logs">
	{foreach from=$logs item=log}
	<div class="aelog {if $log.severity == '[ERROR]'} aealert {else} aeinfo {/if}"><p>[{$log.date_add|escape:'htmlall':'UTF-8'}] {$log.severity|escape:'htmlall':'UTF-8'} {$log.message|escape:'htmlall':'UTF-8'}</p></div><br />
	{/foreach}
</div>

</div>