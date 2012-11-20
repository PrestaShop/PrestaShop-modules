{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

{if !empty($oSuggestedWords)}
	<a href="{$link->getPageLink('modules/prediggo/prediggo_search.php')}?q={$oSuggestedWords->getWord()|escape:'htmlall':'UTF-8'}">{$oSuggestedWords->getWord()|escape:'htmlall':'UTF-8'}</a>
{/if}