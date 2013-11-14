{*
 * Ferbuy payment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @category	Payment
 * @package	 Ferbuy
 * @author	  FerBuy, <info@ferbuy.com>
 * @copyright   Copyright (c) 2013 (http://www.ferbuy.com)
 * @license	 http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *}
{if $status == 'ok'}
<p>
	<strong>{l s='Transaction completed' mod='ferbuy'}</strong><br /><br />
	{l s='The transaction for your order has been processed.' mod='ferbuy'}<br />
	{l s='You will receive an e-mail when the transaction has been completed or you can track the status of your order on our site.' mod='ferbuy'}
</p>
{elseif $status == 'canceled'}
<p>
	<strong>{l s='Your transaction has been canceled' mod='ferbuy'}</strong><br /><br />
	{l s='The order has been placed but the transaction is canceled.' mod='ferbuy'}<br />
	{l s='You will receive an e-mail when the transaction has been completed or you can track the status of your order on our site.' mod='ferbuy'}
</p>
{elseif $status == 'failed'}
<p>
	<strong>{l s='Transaction failed' mod='ferbuy'}</strong><br /><br />
	{l s='The order has been placed but the transaction failed.' mod='ferbuy'}<br />
	{l s='You will receive an e-mail when the transaction has been completed or you can track the status of your order on our site.' mod='ferbuy'}
</p>
{/if}