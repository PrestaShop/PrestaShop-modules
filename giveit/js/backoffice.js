/*
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
*/

$(document).ready(function(){
    getProducts(false, false, false);
    
    $('#categories-treeview input[type="radio"]').live('click', function(){
        getProducts();
    });
    
    $('#product_settings #associated-categories-tree input[type="radio"]').live('click', function(){
        getProducts();
    });
    
    $('select[name="combination_select"]').live('change', function(){
        checkConfigurationSelections();
    });
    
    $('.update_product_setting').live('click', function(){
        updateProductSetting($(this));
    });
    
    $('.table.productsList tr.filter input.filter').live('keypress', function(e) {
        if(e.which == 13) {
            getProducts();
            return false;
        }
    });
    
    $('#submitFilterButtonproductsList').live('click', function(){
        getProducts();
        return false;
    });
    
    $('input[name="submitResetproductsList"]').live('click', function(){
        $('input.filter').each(function(){
            $(this).val('');
        });
        getProducts();
        return false;
    });
    
    /* ps 1.6 */
    $('button[name="submitResetproductsList"]').live('click', function(){
        $('input.filter').each(function(){
            $(this).val('');
        });
        getProducts();
        return false;
    });
    
    /* ps 1.6 */
    $('.btn.btn-default.dropdown-toggle').live('click', function(){
        $('span.pagination ul.dropdown-menu').toggle();
    });
    
    $('select[name="pagination"]').live('change', function(){
        getProducts();
    });
    
    /* ps 1.6 */
    $('ul.dropdown-menu li a').live('click', function(){
        $('#pagination-items-page').val($(this).attr('data-items'));
        getProducts();
    });
    
    $('ul.pagination.pull-right li[class!="disabled"] a').live('click', function(){
        getProductsByPage($(this).attr('data-page'));
    });
});

function getProducts(order, page, id_category) {
    if (!order) {
        order = '';
    }
    
    if (!page) {
        page = 1;
    }
    
    var pagination = 50;
    
    if ($('select[name="pagination"]').length > 0) {
        pagination = $('select[name="pagination"]').val();
    }
    
    if ($('#pagination-items-page').length > 0) {
        pagination = $('#pagination-items-page').val();
    }
    
    var filter = '';
    $('input.filter').each(function(){
        if ($(this).val()) {
            filter = filter + '&filtering['+ $(this).attr('name') +']=' + $(this).val();
        }
    });
    
    if (typeof id_category === 'undefined')
    {
        if ($('#product_settings #categories-treeview').length > 0){
            var id_category = Number($('#product_settings #categories-treeview input[type="radio"]:checked').val());
        } else {
            var id_category = Number($('#product_settings #associated-categories-tree input[type="radio"]:checked').val());
        }   
    }
    
    var params = "token=" + encodeURIComponent(give_it_token) + "&id_shop=" + id_shop + "&getProductList=true&id_category=" + id_category + '&id_lang=' + id_lang + '&order_url=' + encodeURIComponent(order) + '&pagination=' + pagination + '&current_page=' + page + filter;
    
    $.ajax({
        type: "POST",
        async: false,
        url: give_it_ajax_url,
        data: params,
        success: function(response)
        {
            $("#configuration_products_table_container").html(response);
            checkConfigurationSelections();
        }
    });
}

function checkConfigurationSelections() {
    $('select[name="combination_select"]').each(function(){
        var id_combination = $(this).val();
        var $select_obj = $(this);
        var id_product = $(this).parent().parent().find('td:eq(0) input').val();
        var params = "token=" + encodeURIComponent(give_it_token) + "&id_shop=" + id_shop + "&id_product=" + id_product + "&getCombinationSetting=true&id_combination=" + id_combination + '&id_lang=' + id_lang;
        
        $.ajax({
            type: "POST",
            async: false,
            url: give_it_ajax_url,
            data: params,
            success: function(response)
            {
                $select_obj.parent().parent().find('td:eq(5) select[name="combination_setting"] option[value="'+response+'"]').attr('selected', 'selected');
                $select_obj.parent().parent().find('td:eq(4) select[name="combination_setting"] option[value="'+response+'"]').attr('selected', 'selected'); //ps 1.6
            }
        });
    });
}

function updateProductSetting($button) {
    $('#ajax_running').slideDown(function(){
        var id_product = $button.parent().parent().find('td:eq(0) input').val();
        
        if (typeof id_product == 'undefined')
            id_product = $button.parent().parent().parent().find('td:eq(0) input').val(); //ps 1.6
        
        var setting_value = $button.parent().parent().find('td:eq(5) select[name="combination_setting"]').val();
        
        if (typeof setting_value == 'undefined')
            setting_value = $button.parent().parent().parent().find('td:eq(4) select[name="combination_setting"]').val(); //ps 1.6
        
        var id_combination = $button.parent().parent().find('td:eq(4) select[name="combination_select"]').val();
        
        if (typeof id_combination == 'undefined')
            id_combination = $button.parent().parent().parent().find('td:eq(3) select[name="combination_select"]').val(); //ps 1.6
        
        var params = "token=" + encodeURIComponent(give_it_token) + "&id_shop=" + id_shop + "&id_product=" + id_product + "&setCombinationSetting=true&id_combination=" + id_combination + '&id_lang=' + id_lang + "&setting_value=" + setting_value;
        
        $.ajax({
            type: "POST",
            async: false,
            url: give_it_ajax_url,
            data: params,
            success: function(response)
            {
                if (response == 1) {
                    alert(success_message);
                }
                else
                    alert(error_message);
            }
        });
    });
    
    $('#ajax_running').slideUp();
}

function orderTable(orderBy, orderWay) {
    getProducts(orderBy+'/'+orderWay)
}

function getProductsByPage(page)
{
    getProducts('', page)
}