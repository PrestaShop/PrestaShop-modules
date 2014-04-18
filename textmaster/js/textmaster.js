/*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*/
var id_current_category = 0;
var ctype;

$(document).ready(function(){
    if ($(".table.project tbody td a.pointer").length > 0) { //if element exists
        $(".table.project tbody td a.pointer")[0].onclick = null;
    }
    
    $(".table.project tbody td a.pointer").click(function() {
        var id_project = $(this).parent().parent().attr('id');
        id_project = id_project.split('_');
        id_project = id_project[2];
        location.href = location.href + '&duplicateproject&id_project=' + id_project;
    });
    
    $('#create_textmaster_project').click(function(){
        $('#textMaster_error').slideUp();
        $('#ajax_running').show();
        
        createProject(false);
        
        $('#ajax_running').hide();
    });
    
    $('.listForm #toggle_select_all').live('change', function(){
        var $label = $(this).siblings('label');
        var switcharooney = $label.attr('placeholder');
        $label.attr('placeholder', $label.text()).text(switcharooney);
        
        var $checkboxes = $(this).parent().siblings('li').find('input[type="checkbox"]:enabled');
        if ($(this).is(':checked')) {
            $checkboxes.attr('checked', 'checked');
        }
        else
        {
            $checkboxes.removeAttr('checked');
        }
    });
    
    $('#fieldset_translation select[name$="_language_from"]').live('change', function(){
        updateWordCounts();
        updateTotalWordsCount();
        updateProjectPrice();
    });
    
    $('input[name="project_data[]"], #toggle_select_all').live('change', function(){
        updateTotalWordsCount();
        updateProjectPrice();
    });
    
    $('input[name="ctype"]').live('change', function(){
        ctype = $(this).val();
    });
    
    $('input[name="ctype"], select[name$="_language_level"], input[name$="_quality_on"], input[name$="_expertise_on"]').live('change', function(){
        setQualityControllAccessibility();
        updateProjectPrice();
    });
    
    $('.toggle_optional_parameters').live('click', function(){
        var rel_text = $(this).attr('rel');
        var text = $(this).text();
        $(this).attr('rel', text).text(rel_text).parent().siblings('.optional_parameters').slideToggle('fast');
    });
    
    $('.authors_confirm').live('click', function(){
        var $my_authors_container = $(this).siblings('.my_authors_container');
        $('#fieldset_'+ctype).find('input[name="authors[]"]').remove();
        
        $my_authors_container.find('input[type="checkbox"]:checked').each(function(){
            $('#fieldset_'+ctype).prepend($('<input />').attr({'type' : 'hidden', 'name' : 'authors[]'}).val($(this).val()));
        });
        
        updateAuthorsCount();
        $('#'+ctype+'_authors_selection_container').bPopup().close();
    });
    
    $('.authors_cancel').live('click', function(){
        var $my_authors_container = $(this).siblings('.my_authors_container');
        
        $my_authors_container.find('input[type="checkbox"]:checked').removeAttr('checked');
        
        $('#fieldset_'+ctype).find('input[name="authors[]"]').each(function(){
            $my_authors_container.find('input[type=checkbox][value='+$(this).val()+']').attr('checked', 'checked');
        });
        
        updateAuthorsCount();
        $('#'+ctype+'_authors_selection_container').bPopup().close();
    });
    
    if ($('#selected_products_form').length > 0) {
        initiateProductsSelect();
    }
    
    $('input[name=restrict_to_textmasters]').live('change', function(){
        if ($('input[name=restrict_to_textmasters]').is(':checked')) {
            $('.authors_selection_description_container').slideDown('slow');
        }
        else{
            $('.authors_selection_description_container').slideUp('slow');
        }
    });
    
    if ($('.authors_selection_description_container').length > 0 && $('input[name=restrict_to_textmasters]').length > 0) {
        if ($('input[name=restrict_to_textmasters]').is(':checked')) {
            $('.authors_selection_description_container').slideDown('slow');
        }
    }
    
    setQualityControllAccessibility();
});

function initiateProductsSelect() {
    // Sorting arrow clicked
    $('table.productsList thead tr.nodrag.nodrop th a').live('click', function(){
        getProducts($(this).attr('href'));
        return false;
    });
    
    // Reset button clicked
    $('input[name="submitResetproductsList"]').live('click', function(){
        $('#product_form td input[class="filter"]').each(function(){
            $(this).val('');
        });
        $('select[name="productsListFilter_active"]').val('');
        getProducts();
        return false;
    });
    
    // Filter button clicked
    $('#submitFilterButtonproductsList').live('click', function(){
        getProducts();
        return false;
    });
    
    // Pressed ENTER in filter input
    $('#product_form td input.filter').live('keypress', function(e) {
        if(e.which == 13) {
            getProducts();
            return false;
        }
    });
    
    // 1) table checkbox (not master) clicked
    $('div#products_list_to_select table.productsList tbody input[name="productsListBox[]"]').live('change', function(){
        var checked = false;
        var value = $(this).val();
        if ($(this).attr('checked')) {
            checked = true;
        }
        var ids = [];
        $('#selected_products_ids_container input[name="selected_products_ids[]"]').each(function(){
            ids.push($(this).val());
        });
        
        if (checked) {
            ids.push(value);
        }
        else {
            ids.splice( $.inArray(value, ids), 1 );
        }
        $('#selected_products_ids_container').html('');
        $.each(ids, function(index, value) {
            $('#selected_products_ids_container').append($('<input />').attr('name', 'selected_products_ids[]').attr('type', 'hidden').val(value));
        });
        getSelectedProducts();
    });
    
    // 2) table checkbox (not master) clicked
    $('div#selected_products_form table.productsList tbody input[name="productsListBox[]"]').live('change', function(){
        var checked = false;
        var value = $(this).val();
        if ($(this).attr('checked')) {
            checked = true;
        }
        var ids = [];
        $('#selected_products_ids_container input[name="selected_products_ids[]"]').each(function(){
            ids.push($(this).val());
        });
        if (!checked) {
            ids.splice( $.inArray(value, ids), 1 );
        }
        $('#selected_products_ids_container').html('');
        $.each(ids, function(index, value) {
            $('#selected_products_ids_container').append($('<input />').attr('name', 'selected_products_ids[]').attr('type', 'hidden').val(value));
        });
        getSelectedProducts();
    });
    
    // 1) table master checkbox clicked
    $('div#products_list_to_select table.productsList thead input[name="checkme"]').live('change', function(){
        var ids = [];
        var checked = false;
        if ($(this).attr('checked')) {
            checked = true;
        }
        $('#selected_products_ids_container input[name="selected_products_ids[]"]').each(function(){
            ids.push($(this).val());
        });
        $('div#products_list_to_select table.productsList tbody input[name="productsListBox[]"]').each(function(){
            var value = $(this).val();
            if (checked) {
                ids.push(value);
            }
            else {
                ids.splice( $.inArray(value, ids), 1 );
            }
            ids = makeUnique(ids);
        });
        ids = makeUnique(ids);
        $('#selected_products_ids_container').html('');
        $.each(ids, function(index, value) {
            $('#selected_products_ids_container').append($('<input />').attr('name', 'selected_products_ids[]').attr('type', 'hidden').val(value));
        });
        getSelectedProducts();
    });
    
    // 2) table master checkbox clicked
    $('div#selected_products_form table.productsList thead input[name="checkme"]').live('change', function(){
        if (!$(this).attr('checked')) {
            $('#selected_products_ids_container').html('');
            $('#selected_products_ids_container').append($('<input />').attr('name', 'selected_products_ids[]').attr('type', 'hidden').val(0));
            getSelectedProducts();
        }
    });
    
    // status icon clicked
    $('div#products_list_to_select table.productsList tbody a').live('click', function(){
        return false;
    });
    
    // 1) table pagination select -> disabled default action
    //$('div#products_list_to_select table[name="list_table"] select[name="pagination"]').prop("onchange", false);
$('div#products_list_to_select table[name="list_table"] select[name="pagination"]').removeAttr("onchange");
    
    // 2) table pagination select -> disabled default action
    //$('div#selected_products_form table[name="list_table"] select[name="pagination"]').prop("onchange", false);
$('div#selected_products_form table[name="list_table"] select[name="pagination"]').removeAttr("onchange");
    
    // 2) table page links clicked
    $('div#selected_products_form table[name="list_table"] tbody tr:eq(0) td span:eq(0) input[type="image"]').live('click', function(){
        var new_page = $(this).attr('onclick').split('.');
        new_page = new_page[new_page.length - 1].split('=');
        new_page = new_page[1];
        getSelectedProducts(new_page);
        return false;
    });
    
    // 1) table page links clicked
    $('div#products_list_to_select table[name="list_table"] tbody tr:eq(0) td span:eq(0) input[type="image"]').live('click', function(){
        var new_page = $(this).attr('onclick').split('.');
        new_page = new_page[new_page.length - 1].split('=');
        new_page = new_page[1];
        getProducts('', new_page);
        return false;
    });
    
    // 2) table pagination select changed
    $('div#selected_products_form table[name="list_table"] select[name="pagination"]').live('change', function(){
        getSelectedProducts();
    });
    
    // 1) table pagination select changed
    $('div#products_list_to_select table[name="list_table"] select[name="pagination"]').live('change', function(){
        getProducts();
    });
    
    // Categories tree radio button clicked
    $('#categories-treeview input[type="radio"]').live('click', function(){
        getProducts();
    });
    
    // Adding products into 1) table when page is loaded
    getProducts();
    getSelectedProducts();
}

function createProject(quatation_only)
{
    var id_shop = $('#id_shop').val();
    if (typeof textmaster_ajax_uri == 'undefined') {
        return;
    }
    
    $("#create_textmaster_project").attr('disabled', 'disabled');
    
    var params = '&ctype=' + ctype + (quatation_only ? '&quote_project=true' : '&add_project=true') + '&id_shop=' + id_shop;
    
    $('input[name="project_data[]"]:checked').each(function(){
        params = params + '&project_data[]=' + $(this).val();
    });
    
    if($('#fieldset_'+ctype+' input[name=restrict_to_textmasters]').is(':checked'))
    {
        $('#'+ctype+'_authors_selection_container .my_authors_container input[name="authors[]"]').each(function(){
            params = params + '&textmasters[]=' + $(this).val();
        });
    }
    
    if($('#project_name').length > 0)
    {
       params = params + '&project_name=' + encodeURIComponent($('#project_name').val()); 
    }
    
    if (ctype == 'translation')
    {
        params = params +
                '&translation_language_from=' + encodeURIComponent($('#translation_language_from').val()) +
                '&translation_language_to=' + encodeURIComponent($('#translation_language_to').val()) +
                '&translation_category=' + encodeURIComponent($('#translation_category').val()) +
                '&translation_project_briefing=' + encodeURIComponent($('#translation_project_briefing').val()) +
                '&translation_language_level=' + encodeURIComponent($('#translation_language_level').val()) +
                '&translation_quality=' + ($('#translation_quality_on').is(':checked') ? 1 : 0) +
                '&translation_expertise=' + ($('#translation_expertise_on').is(':checked') ? 1 : 0) +
                '&translation_vocabulary_type=' + encodeURIComponent($('#translation_vocabulary_type').val()) +
                '&translation_target_reader_groups=' + encodeURIComponent($('#translation_target_reader_groups').val()) +
                '&translation_grammatical_person=' + encodeURIComponent($('#translation_grammatical_person').val());
    }
    else if (ctype == 'proofreading')
    {
        params = params +
                '&proofreading_language_from=' + encodeURIComponent($('#proofreading_language_from').val()) +
                '&proofreading_category=' + encodeURIComponent($('#proofreading_category').val()) +
                '&proofreading_project_briefing=' + encodeURIComponent($('#proofreading_project_briefing').val()) +                                                                           
                '&proofreading_language_level=' + encodeURIComponent($('#proofreading_language_level').val()) +
                '&proofreading_quality=' + ($('#proofreading_quality_on').is(':checked') ? 1 : 0) +
                '&proofreading_expertise=' + ($('#proofreading_expertise_on').is(':checked') ? 1 : 0) +
                '&proofreading_target_reader_groups=' + encodeURIComponent($('#proofreading_target_reader_groups').val());
    }
    else if (ctype == 'copywriting')
    {
        params = params +
                '&copywriting_language_from=' + encodeURIComponent($('#copywriting_language_from').val()) +
                '&copywriting_category=' + encodeURIComponent($('#copywriting_category').val()) +
                '&copywriting_project_briefing=' + encodeURIComponent($('#copywriting_project_briefing').val()) +
                '&copywriting_language_level=' + encodeURIComponent($('#copywriting_language_level').val()) +
                '&copywriting_quality=' + ($('#copywriting_quality_on').is(':checked') ? 1 : 0) +
                '&copywriting_expertise=' + ($('#copywriting_expertise_on').is(':checked') ? 1 : 0) +
                '&copywriting_target_reader_groups=' + encodeURIComponent($('#copywriting_target_reader_groups').val());
    }
   
    $.ajax({
        type: "POST",
        async: false,
        dataType: 'json',
        url: textmaster_ajax_uri,
        data: 'ajax=true&token=' + encodeURIComponent(textmaster_token) + ((page_reference == 'product') ? '&id_product=' + encodeURIComponent(id_product) : id_product) + params,
        success: function(resp)
        {
            if (!quatation_only && 'error' in resp)
            {
                $('#textMaster_error div').text(resp.error);
                $.scrollTo('#textMaster_error', 1200, {offset: -100});
                $('#textMaster_error').slideDown();
                $("#create_textmaster_project").removeAttr('disabled');
            }
            else if ('success' in resp)
            {
                if (quatation_only && resp.success) // project quotation
                {
                    if(!resp.project_price) resp.project_price = '';
                    
                    $('.total_project_price .price_value').text(resp.project_price);
                    $("#create_textmaster_project").removeAttr('disabled');
                }
                else
                {
                    if(page_reference == 'product')
                        window.location=textmaster_module_url+'&configure=textmaster&menu='+ctype;
                    else
                        window.location=textmaster_module_url+'&configure=textmaster&menu='+ctype;
                }
            }
        }
    });
}

function updateWordCounts() {
    $('input[name="project_data[]"]').each(function(){
        var words = 0;
        var element = $(this).attr('value');
        var lang = $('#fieldset_translation select[name$="_language_from"]').val();
        if (element in word_counts && lang in word_counts[element])
        {
            words = word_counts[element][lang];
        }
        if (words > 0) {
            $(this).removeAttr('disabled');
        }
        $(this).siblings('.word_count').find('.word_count_value').text(words); 
    });
}

function updateTotalWordsCount()
{
    var total_words = 0;
    $('input[name="project_data[]"]:checked').each(function(){
        var words = $(this).siblings('.word_count').find('.word_count_value').text();
        total_words+=Number(words);
    });
    $('#total_words').text(total_words);
}

function setQualityControllAccessibility()
{
    if ($('#proofreading_language_level').length > 0 && $ ('#proofreading_language_level').val() == 'regular') {
        $('#proofreading_quality_on').removeAttr('checked');
        $('#proofreading_quality_on').attr('disabled', 'disabled');
    }
    else {
        $('#proofreading_quality_on').removeAttr('disabled');
    }
    
    if ($('#translation_language_level').length > 0 && $ ('#translation_language_level').val() == 'regular') {
        $('#translation_quality_on').removeAttr('checked');
        $('#translation_quality_on').attr('disabled', 'disabled');
    }
    else {
        $('#translation_quality_on').removeAttr('disabled');
    }
    
    if ($('#copywriting_language_level').length > 0 && $ ('#copywriting_language_level').val() == 'regular') {
        $('#copywriting_quality_on').removeAttr('checked');
        $('#copywriting_quality_on').attr('disabled', 'disabled');
    }
    else {
        $('#copywriting_quality_on').removeAttr('disabled');
    }
    
    /*$input = $('#fieldset_'+ctype+' select[name$="_language_level"] option').filter(":selected");
    
    if($input.val() == 'regular')
    {
        $('input[name$="_quality_on"]').attr('disabled', 'true').removeAttr('checked');
    }
    else
    {
        $('input[name$="_quality_on"]').removeAttr('disabled');
    }*/
}

function updateProjectPrice()
{
   $('#ajax_running').show();
   
    createProject(true);
    
    var $price_container = $('.total_project_price');
    if ($('input[name="project_data[]"]:checked').length > 0)
    {
        $price_container.siblings('.preference_description').hide();
        $price_container.find('.price_empty').hide();
    }
    else
    {
        $price_container.siblings('.preference_description').show();
        $price_container.find('.price_empty').show();
    }
    
    $('#ajax_running').hide();
}

function displayAuthorsSelection(button)
{
    $('#'+ctype+'_authors_selection_container').bPopup({
        fadeSpeed:"slow", 
        modalColor:"DimGray", 
        scrollBar:true
    });   
}

function updateAuthorsCount()
{
    var selected_authors_count = $('#fieldset_'+ctype).find('input[name="authors[]"]').length;
    $('#fieldset_'+ctype+' .selected_authors_value').text(selected_authors_count);
}

// Removes not unique values from array
function makeUnique(array){
    return $.grep(array,function(el,index){
        return index == $.inArray(el,array);
    });
}

// Removes item from array
function removeFromArray(array, removeItem) {
    array = jQuery.grep(array, function(value) {
        return value != removeItem;
    }); 
}

// Adds products into 1) table
function getProducts(order, new_page)
{
    if (!order) {
        order = '';
    }
    if (!new_page) {
        new_page = 1;
    }
    var id_category = Number($('#categories-treeview input[type="radio"]:checked').val());
    var id_shop = $('#id_shop').val();

    var filter = '';
    $('#product_form td input[class="filter"]').each(function(){
        if ($(this).val()) {
            filter = filter + '&filtering['+ $(this).attr('name').replace('productsListFilter_', '') +']=' + $(this).val();
        }
    });
    if ($('select[name="productsListFilter_active"]').val() == '0' || $('select[name="productsListFilter_active"]').val() == '1') {
        filter = filter + '&filtering[active]=' + $('select[name="productsListFilter_active"]').val();
    }
    
    //$('#ajax_running').slideDown('fast', function(){
        id_category = Number(id_category);
        id_current_category = id_category;
        var pagination = $('div#products_list_to_select table[name="list_table"] select[name="pagination"]').val();
        if (!pagination) {
            pagination = 20;
        }
        var params = "token=" + encodeURIComponent(textmaster_token) + "&id_shop=" + id_shop + "&id_lang="+id_lang+"&getProductList=true&id_category=" + id_category + '&pagination=' + pagination +
            '&current_page=' + new_page + '&order_url=' + encodeURIComponent(order) + filter;
        
        $.ajax({
            type: "POST",
            async: false,
            url: textmaster_ajax_uri,
            data: params,
            success: function(response)
            {
                $("#products_list_to_select").html(response);
                checkCheckboxes();
            }
        });
        $('div#products_list_to_select table[name="list_table"] select[name="pagination"]').removeAttr("onchange");
        $('#ajax_running').slideUp('fast'); 
    //});
}

// Adds products into 2) table
function getSelectedProducts(new_page)
{
    var id_shop = $('#id_shop').val();
    
    if (!new_page) {
        new_page = 1;
    }
    //$('#ajax_running').slideDown('fast', function(){
        var ids = [];
        $('#selected_products_ids_container input[name="selected_products_ids[]"]').each(function(){
            ids.push($(this).val());
        });
        
        var pagination = 20;
        if ($('div#selected_products_form table[name="list_table"] select[name="pagination"]').length > 0) {
            pagination = $('div#selected_products_form table[name="list_table"] select[name="pagination"]').val();
        }
        else {
            pagination = $('div#selected_products_form table[name="list_table"] select[name="productsList_pagination"]').val();
        }
        
        var params = "token=" + encodeURIComponent(textmaster_token) + "&id_shop="+id_shop+"&id_lang="+id_lang+"&getSelectedProducts=true&selected_ids=" + encodeURIComponent(ids) + '&pagination=' + pagination +
            '&current_page=' + new_page;
        
        $.ajax({
            type: "POST",
            async: false,
            url: textmaster_ajax_uri,
            data: params,
            success: function(response)
            {
                $('div#selected_products_form').html(response);
                checkCheckboxes();
            }
        });
        $('div#selected_products_form table[name="list_table"] select[name="pagination"]').attr("onchange" , "");
        $('#ajax_running').slideUp('fast');
    //});
}

// Checks / Unchecks checkboxes in both tables
function checkCheckboxes() {
    var ids = [];
    $('#selected_products_ids_container input[name="selected_products_ids[]"]').each(function(){
        ids.push($(this).val());
    });
    if (ids.length == 0) {
        $('div#selected_products_form table.productsList thead input[name="checkme"]').removeAttr("checked");
        $('div#products_list_to_select table.productsList thead input[name="checkme"]').removeAttr("checked");
    }
    else {
        $('div#selected_products_form table.productsList thead input[name="checkme"]').attr('checked', 'checked');
    }
    
    $('input[name="productsListBox[]"]').each(function(){
        if ($.inArray($(this).val(), ids) !== -1) {
            $(this).attr('checked', 'checked');
        }
        else {
            $(this).removeAttr('checked');
        }
    });
}

function setProjectProperties()
{
    $('#textMaster_error').slideUp();
    $('#ajax_running').show();
    
    createProject(false);
    
    $('#ajax_running').hide();
    return false;
}