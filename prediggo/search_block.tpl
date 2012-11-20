{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

<div id="prediggo_search">
	<form method="get" action="{$link->getPageLink('modules/prediggo/prediggo_search.php')}">
		<p>
			<input type="text" name="q" value="{if isset($smarty.get.q)}{$smarty.get.q|htmlentities:$ENT_QUOTES:'utf-8'|stripslashes}{/if}" />
			<input type="submit" value="{l s='Search' mod='prediggo'}" />
		</p>
	</form>
</div>