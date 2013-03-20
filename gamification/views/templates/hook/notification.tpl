<script>
	var current_id_tab = {$current_id_tab|intval};
	var current_level_percent = {$current_level_percent|intval};
	var current_level = {$current_level|intval};
	var gamification_level = '{l s='Level' js=1}';
</script>
<div id="gamification_notif" class="notifs">
		{if $notification}
		<span id="gamification_notif_number_wrapper" class="number_wrapper" style="display: inline;">
			<span id="gamification_notif_value">{$notification|intval}</span>
		</span>
		{/if}
	<div id="gamification_notif_wrapper" class="notifs_wrapper" style="width:340px">
		<div id="gamification_top">
			<h3>{l s='Your Merchant Expertise'}</h3>
		</div>
		<div id="gamification_progressbar"><span class="gamification_progress-label">{l s='Level'} {$current_level|intval} : {$current_level_percent|intval} %</span></div>
		<div id="gamification_badges_container">
			<ul id="gamification_badges_list" style="{if $badges_to_display|count <= 2} height:140px;{/if}">
				{foreach from=$badges_to_display name=badge_list item=badge}
				{if $badge->id}
					<li class="{if $badge->validated} unlocked {else} locked {/if}" style="float:left;">
						<span class="{if $badge->validated} unlocked_img {else} locked_img {/if}"></span>
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
		</div>
		<a id="gamification_see_more" href="{$link->getAdminLink('AdminGamification')}">{l s='View my complete profile'}</a>
	</div>
</div>
