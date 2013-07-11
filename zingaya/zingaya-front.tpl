<style type="text/css">
	.zingaya_link_small, .zingaya_link_medium, .zingaya_link_big { background-position: left 0px; }
	.zingaya_link_small:hover { background-position: left -36px; }
	.zingaya_link_small:active { background-position: left -72px; }
	.zingaya_link_medium:hover { background-position: left -46px; }
	.zingaya_link_medium:active { background-position: left -92px; }
	.zingaya_link_big:hover { background-position: left -58px; }
	.zingaya_link_big:active { background-position: left -118px; }
</style>

<div class="block">
{foreach from=$zingaya_widgets item=widget}
	<a href="#" class="zingaya_link_{$widget.size|escape:'htmlall':'UTF-8'}" style="display: block; width: 100%; height: {$widget.height|escape:'htmlall':'UTF-8'}; background-image: url('data:image/png;base64, {$widget.src|escape:'htmlall':'UTF-8'}'); background-repeat: no-repeat;" onclick="window.open('{$widget.url|escape:'htmlall':'UTF-8'}', 'Call us', 'width=400,height=200');"></a>
{/foreach}
</div>