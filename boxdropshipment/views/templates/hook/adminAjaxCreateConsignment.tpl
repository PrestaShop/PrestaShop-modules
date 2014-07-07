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


{if {$status} == 'error'}

  showErrorMessage('{$message|escape:"quotes"}', 5000);
{else}

  boxdrop.orderAdminDetail.products  = boxdrop.convertEscapedToJSON('{$products|escape:"url"}');
  boxdrop.orderAdminDetail.shipments = boxdrop.convertEscapedToJSON('{$shipments|escape:"url"}');
  boxdrop.shipment.parcel_count      = 0;

  $('#shipping_table_boxdrop').remove();
  boxdrop.orderAdminDetail.initShipmentTable();
  boxdrop.modalBox.hide();
  showSuccessMessage('{l s='Shipment successfully created!' mod='boxdropshipment' js=1}');

  {if $auto_download == '1'}

    window.open($('#bshp-awb-link').attr('href'), '', 'width=800,height=600,scrollbars=yes');
  {/if}
{/if}

