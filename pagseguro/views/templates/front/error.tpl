{*
* 2007-2011 PrestaShop 
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $version == 6}
    <style type="text/css" media="all">{literal}div#center_column{ width: 75%; }{/literal}</style>
{else if $version == 5}
    <style type="text/css" media="all">{literal}div#center_column{ width: 100%; }{/literal}</style>
{else if $version == 4}
    <style type="text/css" media="all">{literal}div#center_column{ width: 535px; }{/literal}</style>
{/if}

{capture name=path}{l s='Pagamento via PagSeguro' mod='pagseguro'}{/capture}
{if $version != 6}
	{include file="$tpl_dir./breadcrumb.tpl"}
{/if}

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>Ocorreu um erro, durante a compra.</h3>
<p>
    Desculpe, infelizmente ocorreu um erro durante a finaliza&ccedil;&atilde;o da compra.
    Por favor entre em contato com o administrador da loja se o problema persistir.
</p>

<p>
    <a href="{$base_dir|escape}" class="button_small" title="{l s='Voltar' mod='pagseguro'}">&laquo; {l s='Voltar' mod='pagseguro'}</a>
</p>
