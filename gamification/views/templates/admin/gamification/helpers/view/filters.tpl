<div id="badges_filters">
{if $type == 'badges_feature' || $type == 'badges_achievement'}
	<label>{l s="Group:"}</label>
	<select id="group_select_{$type}" onchange="filterBadge('{$type}');">
			<option value="badge_all">{l s="All"}</option>
		{foreach from=$groups.$type key=id_group item=group}
			<option value="group_{$id_group}">{$group}</option>
		{/foreach}
	</select>
	<div class="clear"></div>
{/if}	
	<label>{l s="Status:"}</label>
	<select id="status_select_{$type}" onchange="filterBadge('{$type}');">
		<option value="badge_all">{l s="All"}</option>
		<option value="validated">{l s="Validated"}</option>
		<option value="not_validated">{l s="Not Validated"}</option>
	</select>
	<div class="clear"></div>
{if $type == 'badges_feature' || $type == 'badges_achievement'}
	<label>{l s="Level:"}</label>
	<select id="level_select_{$type}" onchange="filterBadge('{$type}');">
			<option value="badge_all">{l s="All"}</option>
		{foreach from=$levels key=id_level item=level}
			<option value="level_{$id_level}">{$level}</option>
		{/foreach}
	</select>
{/if}
</div>
<div class="clear"></div>


