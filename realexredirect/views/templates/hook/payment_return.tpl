{*
*  @author Coccinet <web@coccinet.com>
*  @copyright  2007-2014 Coccinet
*}
{if $status == 'ok'}
<p class="realexresponse"><strong>{l s='Your payment has been successful and your order is complete.' mod='realexredirect'}</strong>		
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. Please return to the checkout and try again ' mod='realexredirect'} 
		<a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='or contact the store administrator.' mod='realexredirect'}</a>
	</p>
{/if}
