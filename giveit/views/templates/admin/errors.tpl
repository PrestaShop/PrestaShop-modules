{*
* 2013 Give.it
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@give.it so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 Give.it
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of Give.it
*}
{if count($errors)}
	<div class="error">
		<span style="float:right">
			<a id="hideError" href="#"><img alt="X" src="../img/admin/close.png" /></a>
		</span>
		
		{if count($errors) == 1}
			{$errors[0]|escape:'htmlall':'UTF-8'}
		{else}
			{l s='%d errors' mod='giveit' sprintf=$errors|count}
			<br/>
			<ol>
				{foreach $errors as $error}
					<li>{$error|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ol>
		{/if}
	</div>
{/if}