{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

{if sizeof($aPrediggoWarnings)}
	{foreach from=$aPrediggoWarnings item="sPrediggoWarning"}
		<div class="warn"><img src="../img/admin/warning.gif"/>{$sPrediggoWarning|escape:'UTF-8'}</div>
	{/foreach}
{/if}
{if sizeof($aPrediggoConfirmations)}
	{foreach from=$aPrediggoConfirmations item="sPrediggoConfirmation"}
		<div class="conf confirm"><img src="../img/admin/ok.gif"/>{$sPrediggoConfirmation|escape:'UTF-8'}</div>
	{/foreach}
{/if}
{if sizeof($aPrediggoErrors)}
	{foreach from=$aPrediggoErrors item="sPrediggoError"}
		<div class="error"><img src="../img/admin/error.png"/>{$sPrediggoError|escape:'UTF-8'}</div>
	{/foreach}
{/if}