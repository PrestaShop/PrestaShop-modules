{*
*  @author Coccinet <web@coccinet.com>
*  @copyright  2007-2013 Coccinet
*}
{if $status == 'ok'}
<p><strong>{l s='Your payment has been successful and your order on <em>%s</em> is complete.' sprintf=$shop_name mod='realexredirect'}</strong>		
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='realexredirect'} <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team. ' mod='realexredirect'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='realexredirect'} 
		<a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team. ' mod='realexredirect'}</a>.
	</p>
{/if}
