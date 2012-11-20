{if $status == 'ok'}
	<p>{l s='Your order on' mod='receiveandpay'} {$shop_name} {l s='has been registered successfuly.' mod='kwixo'}</p>
	<p>{l s='For any extra question or information, please contact our' mod='kwixo'} <a href="{$base_dir}contact-form.php">{l s='customer support' mod='kwixo'}</a>.</p>
{else}
	<p>{l s='We noticed a trouble during your order. If you think it is an error, you can contact our' mod='kwixo'} <a href="{$base_dir}contact-form.php">{l s='customer support' mod='kwixo'}</a>.</p>
{/if}