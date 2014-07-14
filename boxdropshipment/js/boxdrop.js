/*
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
 */

var bShipment = {

  canvas:          null,
  parcel_count:    0,
  parcel_list:     null,
  parcel_wrapper:  null,
  product_form:    null,
  product_wrapper: null,
  order_id:        0,

  /**
   * inital attribute instantiation
   *
   * @author sweber
   */
  init: function() {

    this.canvas = boxdrop.modalBox.box;
  },


  /**
   * Draws the full parcel form with all handlers
   *
   * @author sweber
   */
  initParcelForm: function() {

    this.resetParcel();
    this.initCanvas();
    this.initProductList();
    this.initParcelHandlers();
    this.addParcel();
    this.updateParcelInfo();
    boxdrop.modalBox.align();
  },


  /**
   * Fills the canvas with the parcel form
   *
   * @author sweber
   */
  initCanvas: function() {

    html = '<form id="bshp-consignment-form">'+
             '<div class="bshp-fl bshp-parcels-wrapper">'+
               '<div class="bshp-add-parcel bshp-fl bshp-pointer"></div>'+
               '<div class="bshp-parcels"></div>'+
               '<div class="bshp-clr"></div>'+
               '<div class="bshp-remove-parcel bshp-fl bshp-pointer"></div>'+
             '</div>'+
             '<div class="bshp-fl">'+
               '<div class="bshp-products"></div>'+
               '<div class="bshp-clr"></div>'+
               '<input type="button" class="button bshp-fr btn btn-default" name="submit" value="'+bTranslation.btnCreateShipment+'" />'+
             '</div>'+
             '<div class="clear"></div>'+
           '</form>';

    this.canvas.html(html);
    this.canvas.find('input').first().unbind('click').bind('click', function() { boxdrop.shipment.createShipment(); });
    this.product_form    = this.canvas.find('#bshp-consignment-form');
    this.parcel_wrapper  = this.canvas.find('.bshp-parcels-wrapper').first();
    this.parcel_list     = this.parcel_wrapper.find('.bshp-parcels').first();
    this.product_wrapper = this.canvas.find('.bshp-products').first();
  },


  /**
   * Injects a product list into our canvas
   *
   * @author sweber
   */
  initProductList: function() {

    lines = '<tr>'+
              '<th class="bshp-pparcel">'+bTranslation.thParcel+'</th>'+
              '<th class="bshp-pimage">'+bTranslation.thImage+'</th>'+
              '<th class="bshp-pimage">'+bTranslation.thAmount+'</th>'+
              '<th class="bshp-partno">'+bTranslation.thArtNo+'</th>'+
              '<th class="bshp-pname">'+bTranslation.thArtName+'</th>'+
            '</tr>';

    for (idx in boxdrop.orderAdminDetail.products) {

      product  = boxdrop.orderAdminDetail.products[idx];
      lines   += '<tr class="bshp-product-line">'+
                   '<td class="bshp-pparcel">'+
                     '<select class="bshp-product-parcel" name="product-parcel['+product.id+']" data-weight="'+product.weight+'" data-volumical="'+product.volumical+'" data-amount="'+product.amount+'">'+
                     '</select>'+
                   '</td>'+
                   '<td class="bshp-pimage">'+product.image+'</td>'+
                   '<td class="bshp-pimage">'+product.amount+'</td>'+
                   '<td class="bshp-partno">'+product.artno+'</td>'+
                   '<td class="bshp-pname">'+product.name+'</td>'+
                 '</tr>';
    }

    this.product_wrapper.html('<table class="bshp-product-table">'+lines+'</table>');
    this.product_wrapper.find('select.bshp-product-parcel').bind('change', function() { boxdrop.shipment.updateParcelInfo(); });
  },


  /**
   * Adds a parcel to all existing parcel dropdowns
   *
   * @author sweber
   */
  addParcel: function() {

    this.parcel_count++;
    this.canvas.find('select.bshp-product-parcel').append('<option value="'+this.parcel_count+'">Parcel '+this.parcel_count+'</option>');
    this.updateParcelInfo();
    boxdrop.modalBox.align();
  },


  /**
   * Removes the last parcel from all existing parcel dropdowns
   *
   * @author sweber
   */
  removeParcel: function() {

    if (this.parcel_count > 1) {

      this.canvas.find('select.bshp-product-parcel').find('option[value='+this.parcel_count+']').remove();
      this.parcel_count--;
      this.updateParcelInfo();
      boxdrop.modalBox.align();
    }
  },


  /**
   * Calculates the total weight and product count of all parcels, based on the products inserted
   *
   * @author sweber
   */
  updateParcelInfo: function() {

    parcel_data = this.createParcelObjects();
    parcel_info = '';

    this.canvas.find('.bshp-product-parcel').each(function() {

      element       = $(this);
      parcel_number = element.val();

      if (parcel_number != '') {

        parcel_data[parcel_number].products  += parseInt(element.data('amount'), 10);
        parcel_data[parcel_number].volumical += parseFloat(element.data('volumical'));
        parcel_data[parcel_number].weight    += parseFloat(element.data('weight'));
      }
    });

    for (parcel_number in parcel_data) {

      parcel = parcel_data[parcel_number];

      if (parcel.products == 0) {

        parcel_description = '<b>'+bTranslation.txtEmptyParcel+'</b><br />';
      } else {

        amount_desc        = (parcel.products == 1) ? bTranslation.txtProduct : bTranslation.txtProducts;
        parcel_description = '<b>'+parcel.products+'</b> '+amount_desc+'<br />'+
                             '<b>'+parcel.weight.toFixed(2)+' kg</b>';
      }

      parcel_info += '<div class="bshp-fl bshp-parcel">'+
                       '<div class="bshp-fl bshp-parcel-img">'+
                         '<img src="'+boxdrop.base_dir+'img/icons/parcel.png" alt="" />'+
                         '<span>'+parcel_number+'</span>'+
                       '</div>'+
                       '<div class="bshp-fl bshp-parcel-desc">'+
                         parcel_description+
                       '</div>'+
                     '</div>';
    }

    this.parcel_list.html(parcel_info);
    this.updateParcelWrapperHeight();
  },


  /**
   * Creates an array of objects for displaying the parcel list on the left hand side
   *
   * @author sweber
   */
  createParcelObjects: function() {

    parcel_data   = {};
    parcel_number = 1;

    for (parcel_number; parcel_number <= this.parcel_count; parcel_number++) {

      parcel_data[parcel_number] = {products:  0,
                                    volumical: 0,
                                    weight:    0};
    }

    return parcel_data;
  },


  /**
   * Resets the parcel count
   *
   * @author sweber
   */
  resetParcel: function() {

    this.parcel_count = 0;
  },


  /**
   * Updates the height of the parcel list, to fit to the product list
   *
   * @author sweber
   */
  updateParcelWrapperHeight: function() {

    min_height = Math.max(this.parcel_wrapper.height(), this.product_wrapper.parent().height());

    boxdrop.modalBox.box.height(min_height);
    boxdrop.modalBox.align();
  },


  /**
   * Add click handlers to the parcel add / remove links
   *
   * @author sweber
   */
  initParcelHandlers: function() {

    this.parcel_add_wrapper    = this.canvas.find('.bshp-add-parcel').first();
    this.parcel_remove_wrapper = this.canvas.find('.bshp-remove-parcel').first();

    this.parcel_add_wrapper.html(   '<img src="'+boxdrop.base_dir+'img/icons/add.png" class="bshp-consignment-parcel-add bshp-form-icon" /> '+bTranslation.txtAddParcel);
    this.parcel_remove_wrapper.html('<img src="'+boxdrop.base_dir+'img/icons/delete.png" class="bshp-consignment-parcel-remove bshp-form-icon" /> '+bTranslation.txtRemoveParcel);

    this.parcel_add_wrapper.unbind('click').
                            bind('click', function() { boxdrop.shipment.addParcel(); });

    this.parcel_remove_wrapper.unbind('click').
                               bind('click', function() { boxdrop.shipment.removeParcel(); });
  },


  /**
   * Reloads the shipping table and its contents
   *
   * @author sweber
   */
  reload: function() {

    if (!boxdrop.getAjaxState()) {

      boxdrop.toggleAjaxState();
      $.ajax(boxdrop.base_dir+'ajax/ajax.php', {async:    true,
                                                cache:    false,
                                                complete: function() { boxdrop.toggleAjaxState(); },
                                                data:     {action:   'admReloadShipmentTable',
                                                           order_id: boxdrop.orderAdminDetail.order_id},
                                                dataType: 'script',
                                                method:   'POST'});
    }
  },


  /**
   * Creates a real shipment order, based on the parcel forms contents.
   *
   * @author sweber
   */
  createShipment: function() {

    if (!boxdrop.getAjaxState()) {

      boxdrop.toggleAjaxState();
      $.ajax(boxdrop.base_dir+'ajax/ajax.php', {async:    true,
                                                cache:    false,
                                                complete: function() { boxdrop.toggleAjaxState(); },
                                                data:     {action:     'admCreateShipment',
                                                           order_id:   boxdrop.orderAdminDetail.order_id,
                                                           parceldata: jQuery.param(this.product_form.serializeArray())},
                                                dataType: 'script',
                                                method:   'POST'});
    }
  }
};

var bShipmentOrderAdminDetail = {

  canvas:             {},
  consignment_number: '',
  order_id:           0,
  products:           {},
  shipments:          {},
  shipment_lines:     '',

  /**
   * prepares all we need
   *
   * @author sweber
   */
  init: function(products, shipments, order_id) {

    this.order_id  = order_id;
    this.products  = products;
    this.shipments = shipments;

    this.initCanvas();
    boxdrop.modalBox.init();
    boxdrop.shipment.init();
  },


  /**
   * Inits the canvas. Replaces the whole default shipment table here, as its not possible to overload it directly.
   *
   * @author sweber
   */
  initCanvas: function() {

    this.canvas = $('#shipping_table').parent();
    $('#shipping_table').remove();
    this.initShipmentTable();
  },


  /**
   * Creates a beautiful shipment table replacement based on the shipments made with our carriers
   *
   * @author sweber
   */
  initShipmentTable: function() {

    this.generateShipmentLines();
    create_button = '<input type="button" class="bshp-create-consignment button btn btn-default" value="'+bTranslation.btnCreateShipment+'" />';

    if (this.products.length == 0) {

      create_button = bTranslation.txtAllSent;
    }

    html = '<table class="table" width="100%" cellspacing="0" cellpadding="0" id="shipping_table_boxdrop">'+
             '<colgroup><col width="20%"/><col width="15%"/><col width="10%"/><col width="5%"/><col width="50%"/></colgroup>'+
             '<thead>'+
               '<tr>'+
                 '<th>'+bTranslation.thDate+'</th>'+
                 '<th>'+bTranslation.thCarrier+'</th>'+
                 '<th>'+bTranslation.thWeight+'</th>'+
                 '<th>'+bTranslation.thParcels+'</th>'+
                 '<th>'+bTranslation.thTrackingNumber+'</th>'+
               '</tr>'+
             '</thead>'+
             '<tfoot>'+
               '<tr>'+
                 '<td colspan="5" align="right">'+create_button+'</td>'+
               '</tr>'+
             '</tfoot>'+
             '<tbody>'+
               this.shipment_lines+
             '</tbody>'+
           '</table>';

    this.canvas.append(html);
    this.canvas = $('#shipping_table_boxdrop').parent();
    this.initCreateConsignmentHandler();
    this.initAddProductHandler();
  },


  /**
   * Listens for a click on "add product", to add refresh our shipping table, as we are
   * not using the original shipping table here.
   * As we have to wait for the remote AJAX call and sadly cannot hook into it (again...) we'll wait a few moments
   * before firing our own.
   *
   * @author sweber
   */
  initAddProductHandler: function() {

    $('#submitAddProduct').bind('click', function() { setTimeout('boxdrop.shipment.reload()', 1250) });
  },


  /**
   * there is a consignment number, well give th euser the AWB, the current status and a cancel button
   *
   * @author sweber
   */
  generateShipmentLines: function() {

    this.shipment_lines = '';

    for (index in this.shipments) {

      shipment = this.shipments[index];

      this.shipment_lines += '<tr>'+
                               '<td>'+shipment.created_at+'</td>'+
                               '<td>boxdrop DHL</td>'+
                               '<td>'+shipment.shipping_weight+'</td>'+
                               '<td>'+shipment.parcel_count+'</td>'+
                               '<td>'+
                                 '<a href="http://www.dhl.it/content/it/it/express/ricerca.shtml?brand=DHL&AWB'+shipment.airwaybill+'%0D%0A" target="_blank">'+shipment.airwaybill+'</a>'+
                                 ' <a href="'+boxdrop.base_dir+'data/'+shipment.label+'" target="_blank" id="bshp-awb-link"><img src="'+boxdrop.base_dir+'img/icons/print.png" class="bshp-form-icon" alt="" /></a>'+
                               '</td>'+
                             '</tr>';
    }
  },


  /**
   * inits the click handler for creating a new consignment
   *
   * @author sweber
   */
  initCreateConsignmentHandler: function() {

    this.canvas.find('.bshp-create-consignment').
                unbind('click').
                bind('click', function() { boxdrop.orderAdminDetail.showCreateConsignmentModal(); });
  },


  /**
   * opens up a modal box which allows the user to enter all the details for a shipments contents
   *
   * @author sweber
   */
  showCreateConsignmentModal: function () {

    boxdrop.shipment.initParcelForm();
    boxdrop.modalBox.show();
    boxdrop.modalBox.align();
    boxdrop.shipment.updateParcelWrapperHeight();
  }
};


/**
 * Simple modalbox
 */
var bShipmentModalbox = {

  box:         null,
  close:       null,
  initial:     true,
  initialized: false,
  layer:       null,

  /**
   * Injects modal box context, stores handles and inits click handlers
   *
   * @author sweber
   * @param  string base_dir
   */
  init: function() {

    /*
     * we'll append our modal box to the end of the DOM, to prevent cascaded zindex issues
     */
    if (!this.initialized) {

      $('body').append('<div class="bshp-layer" style="display: none;"></div>'+
                       '<div class="bshp-modal bootstrap" style="display: none;"></div>'+
                       '<div class="bshp-close" style="display: none;"><img src="'+boxdrop.base_dir+'/img/icons/close.png" /></div>');
      this.box   = $('.bshp-modal').first();
      this.close = $('.bshp-close').first();
      this.layer = $('.bshp-layer').first();
      this.close.unbind('click')
                        .bind('click', function() { boxdrop.modalBox.hide(); });

      this.initialized = true;
    }
  },


  /**
   * Shows all modalbox elements
   *
   * @author sweber
   */
  show: function() {

    this.box.show();
    this.close.show();
    this.layer.show();
    this.initial = true;
  },


  /**
   * Hides all modalbox elements
   *
   * @author sweber
   */
  hide: function() {

    this.box.hide();
    this.close.hide();
    this.layer.hide();
  },


  /**
   * aligns the modal box's size to its content
   *
   * @author sweber
   */
  align: function() {

    height_offset = 0;
    width_offset  = 0;

    if (this.initial) {

      height_offset = 40;
      width_offset  = 40;
      this.initial  = false;
    }

    height = parseInt(this.box.height(), 10);
    width  = parseInt(this.box.width(), 10) + width_offset;
    height = (height > $(window).height()) ? $(window).height() - 60 : height + height_offset;

    this.box.css({'margin-left': ($(window).width() - width) / 2,
                  'width':       width,
                  'left':        0,
                  'height':      height,
                  'top':         ($(window).height() - height) / 2});

    this.close.css({'right': ($(window).width() - width) / 2 - 40,
                    'top':   ($(window).height() - height) / 2 - 16});
  }
};


var bShipmentCarrierList = {

  base_dir:         null,
  carriers:         {},
  delivery_options: new Array(),
  display_area:     null,
  map_url:          null,
  selected_carrier: null,

  /**
   * prepares all we need
   *
   * @author sweber
   * @param  map_url  base URL from where we can load other JS files might needed
   * @param  carriers
   */
  init: function(map_url, carriers) {

    this.carriers     = carriers;
    this.display_area = $('.bshp-interaction');
    this.map_url      = map_url;

    this.checkSelectedCarrier();
  },


  /**
   * handles a shop selection in the shop map
   *
   * @author sweber
   */
  handleShopSelection: function(shop_id) {

    boxdrop.modalBox.hide();

    if (!boxdrop.getAjaxState()) {

      boxdrop.toggleAjaxState();
      $.ajax(boxdrop.base_dir+'ajax/ajax.php', {async:   true,
                                                cache:   false,
                                                complete: function() { boxdrop.toggleAjaxState(); },
                                                data:    {action: 'getShopDetails',
                                                          shop_id: shop_id},
                                                method:  'POST',
                                                success: function(data) {

                                                  boxdrop.carrierList.display_area.html(data);
                                                  boxdrop.carrierList.hideCarrierList();
                                               }});
    }
  },


  /**
   * Hides the original carrier list after selecting a shop.
   * This is to make the process clearer for the customer.
   * Our interaction area is offering a link to re-display the carrier list if the client wants to change it.
   * We'll set up the handler here.
   *
   * @author sweber
   */
  hideCarrierList: function() {

    $('.delivery_options').hide();
    $('.bshp-change-dropoff-point').unbind('click').
                                    bind('click', function() { boxdrop.carrierList.checkSelectedCarrier(); });
    $('.bshp-change-carrier').unbind('click').
                              bind('click', function() { boxdrop.carrierList.showCarrierList(); });
  },


  /**
   * Shows the carrier list and resets our interaction area.
   *
   * @author sweber
   */
  showCarrierList: function() {

    $('.delivery_options').show();
    this.display_area.html('');
  },


  /**
   * Checks if the selected carrier is one of our dropOff carriers.
   * If so, it will open the selection dialogue
   *
   * @author sweber
   */
  checkSelectedCarrier: function() {

    this.selected_carrier = $('input[name^="delivery_option"]:checked');
    this.selected_carrier = this.selected_carrier.val().split(',')[0];

    if ($.inArray(this.selected_carrier, this.carriers.dropoff) != -1) {

      boxdrop.modalBox.box.html('<iframe src="'+this.map_url+'" width="100%" height="100%" frameborder="0" framescroll="0" />');
      boxdrop.modalBox.show();

      this.modalboxResizeHandler();
      $(window).bind('resize', function() { boxdrop.carrierList.modalboxResizeHandler() });

      XDMessage.receiveMessage(function(e){ boxdrop.carrierList.handleShopSelection(e.data); });
    }
  },


  /**
   * Should be called upon window.resize event to fit the modalbox to the screen.
   * The map itself will listen on her own!
   *
   *  @author sweber
   */
  modalboxResizeHandler: function() {

    boxdrop.modalBox.box.css({height: ($(window).height() - 50),
                              width:  ($(window).width() - 50)});
  }
};


var bShipmentConfigPage = {

  btn_default_prices: null,
  btn_free_price:     null,
  inp_default_price:  null,
  inp_order_total:    null,

  /**
   * inits the configuration page
   *
   * @author sweber
   */
  init: function () {

    this.initFields();
    this.initButtonHandlers();
    boxdrop.modalBox.init();
  },


  /**
   * Inits all objects and aligns the shipment cost preset boxes
   *
   * @author sweber
   */
  initFields: function () {

    this.btn_default_prices = $('.bshp-default-preset').first();
    this.btn_free_price     = $('.bshp-free-price-preset').first();
    this.inp_default_price  = $('.bshp-freeprice-defaultprice').first();
    this.inp_order_total    = $('.bshp-freeprice-minordertotal').first();

    boxdrop.alignHeight($('.bshp-align-height-panelinner'));
    boxdrop.alignHeight($('.bshp-align-height-warnings'));
  },


  /**
   * Inits the button handlers
   *
   * @author sweber
   */
  initButtonHandlers: function () {

    this.btn_free_price.bind(    'click', function() { boxdrop.configPage.presetHandlerFree(); });
    this.btn_default_prices.bind('click', function() { boxdrop.configPage.presetHandlerDefault(); });
  },


  /**
   * Handler for setting up the free price shipping preset
   *
   * @author sweber
   */
  presetHandlerFree: function() {

    this.inp_order_total.removeClass(  'bshp-error-border');
    this.inp_default_price.removeClass('bshp-error-border');

    if (this.inp_order_total.val() == '') {

      this.inp_order_total.addClass('bshp-error-border');
    }

    if (this.inp_default_price.val() == '') {

      this.inp_default_price.addClass('bshp-error-border');
    }

    if (this.inp_order_total.val()   != '' &&
        this.inp_default_price.val() != '') {

      if (!boxdrop.getAjaxState()) {

        boxdrop.toggleAjaxState();
        $.ajax(boxdrop.base_dir+'ajax/ajax.php', {async:    true,
                                                  cache:    false,
                                                  complete: function() { boxdrop.toggleAjaxState(); },
                                                  data:     {action:    'admSetupPresetFree',
                                                             min_total: this.inp_order_total.val(),
                                                             shp_price: this.inp_default_price.val()},
                                                  dataType: 'script',
                                                  method:   'POST'});
      }
    }
  },


  /**
   * Handler for setting up the default price shipping preset
   *
   * @author sweber
   */
  presetHandlerDefault: function() {

    if (!boxdrop.getAjaxState()) {

      boxdrop.toggleAjaxState();
      $.ajax(boxdrop.base_dir+'ajax/ajax.php', {async:    true,
                                                cache:    false,
                                                complete: function() { boxdrop.toggleAjaxState(); },
                                                data:     {action: 'admSetupPresetDefault'},
                                                dataType: 'script',
                                                method:   'POST'});
    }
  }
};


var boxdrop = {

  base_dir:         '',
  configPage:       {},
  carrierList:      {},
  inAjaxRequest:    false,
  modalBox:         {},
  orderAdminDetail: {},
  shipment:         {},

  init: function(base_dir) {

    $('head').append('<link rel="stylesheet" type="text/css" href="'+base_dir+'/css/boxdrop.css" />');

    this.base_dir         = base_dir;
    this.carrierList      = bShipmentCarrierList;
    this.configPage       = bShipmentConfigPage;
    this.modalBox         = bShipmentModalbox;
    this.orderAdminDetail = bShipmentOrderAdminDetail;
    this.shipment         = bShipment;
  },


  /**
   * As we have to use the escaping fpr all variables passed to templates, this is a fancy workaround
   * to convert URL-escaped JSON PHP strings into JSON objects.
   *
   * @author sweber
   */
  convertEscapedToJSON: function(string) {

    return jQuery.parseJSON(decodeURIComponent(string));
  },


  /**
   * Aligns the height of the given elements
   *
   * @author sweber
   */
  alignHeight: function(group) {

    var max_height = 0;

    group.each(function() {

      if ($(this).height() > max_height) {

        max_height = $(this).height();
      }
    });

    group.height(max_height);
  },


  /**
   * toggles the inAjaxRequest state
   *
   * @author sweber
   */
  toggleAjaxState: function() {

    this.inAjaxRequest = !this.inAjaxRequest;
  },


  /**
   * returns wheter we are in an AJAX request or not
   *
   * @author sweber
   */
  getAjaxState: function() {

    return this.inAjaxRequest;
  }
};
