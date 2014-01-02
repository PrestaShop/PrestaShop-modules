{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*
** Compatibility code for Prestashop older than 1.4.2 using a recent theme
** Ignore list isn't require here
** $address exist in every PrestaShop version
*}

{capture name=path}{if !isset($email_create)}{l s='Login'}{else}{l s='Create your account'}{/if}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

{assign var='current_step' value='login'}

{include file="$tpl_dir./errors.tpl"}

{assign var='stateExist' value=false}

{if !isset($email_create)}
	<div data-role="navbar">
		<ul>
		  <li><a data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADINGS}" href="#jqm_page_authentication_login" class="link_page_authenticate_login">{l s='Login'}</a></li>
		  <li><a data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADINGS}" href="#jqm_page_authentication_register" class="link_page_authenticate_register">{l s='Register'}</a></li>
		</ul>
		<br class="clear" />	
	</div>
{/if}
