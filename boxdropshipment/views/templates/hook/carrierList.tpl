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

<div class="bshp-interaction"></div>

<script type="text/javascript">
  delete boxdrop;
</script>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}js/boxdrop.js"></script>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}js/XDMessage.js"></script>
<script type="text/javascript">
  $(document).ready(function() {

    boxdrop.init('{$module_dir|escape:'htmlall':'UTF-8'}');
    boxdrop.modalBox.init();
    boxdrop.carrierList.init('{$map_url|escape:'htmlall':'UTF-8'}', {$carriers});
  });
</script>
