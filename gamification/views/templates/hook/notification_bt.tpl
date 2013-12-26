<script>
	var current_id_tab = {$current_id_tab|intval};
	var current_level_percent = {$current_level_percent|intval};
	var current_level = {$current_level|intval};
	var gamification_level = '{l s='Level' js=1}';
</script>
<li id="gamification_notif" style="background:none" class="dropdown">
	<a href="javascript:void(0);" class="dropdown-toggle gamification_notif" data-toggle="dropdown">
		<i class="icon-bookmark"></i>
		<span id="gamification_notif_number_wrapper" class="notifs_badge">
			<span id="gamification_notif_value">{$notification|intval}</span>
		</span>
	</a>
	<div class="dropdown-menu notifs_dropdown">
		<section id="gamification_notif_wrapper" class="notifs_panel" style="width:325px">
			<header class="notifs_panel_header">
				<h3>{l s='Your Merchant Expertise'}</h3>
			</header>
			<h4 style="margin-left:10px">
				<span class="label label-default">{l s='Level'} {$current_level|intval} : {$current_level_percent|intval} %</span>
			</h4>
			<div class="progress" style="margin: 10px">
				<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{$current_level_percent|intval}" aria-valuemin="0" aria-valuemax="100" style="width: {$current_level_percent|intval}%;">
				<span class="sr-only">{l s='Level'} {$current_level|intval} : {$current_level_percent|intval} %</span>
				</div>
			</div>
			<ul id="gamification_badges_list" style="{if $badges_to_display|count <= 2} height:155px;{/if} padding-left:0">
				{foreach from=$badges_to_display name=badge_list item=badge}
				{if $badge->id}
					<li class="{if $badge->validated} unlocked {else} locked {/if}" style="float:left;">
						<span class="{if $badge->validated} unlocked_img {else} locked_img {/if}" style="left: 12px;"></span>
						<div class="gamification_badges_title"><span>{if $badge->validated} {l s='Last badge :'} {else} {l s='Next badge :'} {/if}</span></div>
						<div class="gamification_badges_img"><img src="{$badge->getBadgeImgUrl()}"></div>
						<div class="gamification_badges_name">{$badge->name|escape:html:'UTF-8'}</div>
					</li>
				{else}
					<li style="height:130px"></li>
				{/if}
				{if $smarty.foreach.badge_list.iteration is not odd && $badges_to_display|count > 2}
						<div class="clear">&nbsp;</div>
					{/if}
				{/foreach}
			</ul>
			<footer class="panel-footer">
				<a href="{$link->getAdminLink('AdminGamification')}">{l s='View my complete profile' mod='gamification'}</a>
			</footer>
		</section>
	</div>
</li>
