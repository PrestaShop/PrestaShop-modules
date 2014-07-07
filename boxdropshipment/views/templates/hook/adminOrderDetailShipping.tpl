{*
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
 * @author     boxdrop Group AG
 * @copyright  boxdrop Group AG
 * @license    http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of boxdrop Group AG
 *}

<script>
  {include file="$tpl_path/jsTranslations.tpl"}
</script>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}js/boxdrop.js"></script>
<script>
  $(document).ready(function() {

    var bshp_products  = boxdrop.convertEscapedToJSON('{$products|escape:"url"}');
    var bshp_shipments = boxdrop.convertEscapedToJSON('{$shipments|escape:"url"}');

    boxdrop.init('{$module_dir|escape:'htmlall':'UTF-8'}', '{$token|escape:'htmlall':'UTF-8'}');
    boxdrop.orderAdminDetail.init(bshp_products, bshp_shipments, {$order_id|escape:'htmlall':'UTF-8'});
  });
</script>