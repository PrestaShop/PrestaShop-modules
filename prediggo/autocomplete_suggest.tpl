{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

{if !empty($sSuggestWord)}
	<a href="{$link->getPageLink('modules/prediggo/prediggo_search.php')}?q={$sSuggestWord|escape:'htmlall':'UTF-8'}">{$sSuggestWord|escape:'htmlall':'UTF-8'}</a>
{/if}