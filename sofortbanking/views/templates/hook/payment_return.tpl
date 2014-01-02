{**
 * $Id$
 *
 * sofortbanking Module
 *
 * Copyright (c) 2009 touchdesign
 *
 * @category Payment
 * @version 2.0
 * @copyright 19.08.2009, touchdesign
 * @author Christin Gruber, <www.touchdesign.de>
 * @link http://www.touchdesign.de/loesungen/prestashop/sofortueberweisung.htm
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * Description:
 *
 * Payment module sofortbanking
 *
 * --
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@touchdesign.de so we can send you a copy immediately.
 *
 *}

<!-- sofortbanking module | http://www.touchdesign.de/loesungen/prestashop/sofortueberweisung.htm -->
{if $status == 'accepted' || $status == 'pending'}
	<p>
		{l s='Your order on' mod='sofortbanking'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='is complete.' mod='sofortbanking'}
		<br /><br />
		{l s='The total amount of this order is' mod='sofortbanking'} <span class="price">{$total_to_pay|escape:'htmlall':'UTF-8'}</span>
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='sofortbanking'} 
		<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}contact-form.php">{l s='customer support' mod='sofortbanking'}</a>.
	</p>
{/if}
<!-- sofortbanking module | http://www.touchdesign.de/loesungen/prestashop/sofortueberweisung.htm -->