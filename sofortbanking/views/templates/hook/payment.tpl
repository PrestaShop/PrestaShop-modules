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

<p class="payment_module">
	<a href="{if method_exists('Link','getModuleLink')}{$link->getModuleLink('sofortbanking', 'payment', ['token' => $static_token])}{else}{$this_path_ssl|escape:'htmlall':'UTF-8'}payment.php?token={$static_token|escape:'htmlall':'UTF-8'}{/if}" title="{l s='Pay with sofortbanking' mod='sofortbanking'}">
	{if $cprotect == "Y" && $lang_iso == "de"}
		<img src="{$module_template_dir|escape:'htmlall':'UTF-8'}img/{$mod_lang|escape:'htmlall':'UTF-8'}/banner_400x100_ks.png" alt="banner_400x100_ks.png" title="{l s='Buy secure with customer protection by sofortbanking' mod='sofortbanking'}" width="400" height="100" />
		{l s='Buy secure with customer protection by sofortbanking' mod='sofortbanking'}
	{else}
		<img src="{$module_template_dir|escape:'htmlall':'UTF-8'}img/{$mod_lang|escape:'htmlall':'UTF-8'}/banner_300x100.png" alt="banner_300x100.png" title="{l s='Pay easy and secure with SOFORT Banking.' mod='sofortbanking'}" width="300" height="100" />
		{l s='Pay easy and secure with SOFORT Banking.' mod='sofortbanking'}
	{/if}
	<br class="clear" />
	</a>
</p>