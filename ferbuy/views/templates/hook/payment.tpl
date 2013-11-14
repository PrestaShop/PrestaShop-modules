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
<p class="payment_module">
	<a href="#" title="{l s='FerBuy (Buy Now, Pay Later)' mod='ferbuy'}" onclick="$('#ferbuy_payment').submit();return false;">
		<img src="{$this_path|escape:'htmlall':'UTF-8'}img/ferbuy.png" alt="{l s='FerBuy (Buy Now, Pay Later)' mod='ferbuy'}" width="90" height="32"/>
		{l s='FerBuy (Buy Now, Pay Later)' mod='ferbuy'}
	</a>
	<form id="ferbuy_payment" action="{$gateway|escape:'htmlall':'UTF-8'}" method="post">
		{foreach from=$fields key=name item=field}
			<input type="hidden" name="{$name|escape:'htmlall':'UTF-8'}" value="{$field|escape:'htmlall':'UTF-8'}" />
		{/foreach}
	</form>
</p>