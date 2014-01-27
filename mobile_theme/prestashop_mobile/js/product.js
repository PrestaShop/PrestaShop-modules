/*
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
*/


//global variables
var combinations = new Array();
var selectedCombination = new Array();
var globalQuantity = new Number;
var colors = new Array();

//check if a function exists
function function_exists(function_name)
{
	if (typeof function_name == 'string')
		return (typeof window[function_name] == 'function');
	return (function_name instanceof Function);
}

//execute oosHook js code
function oosHookJsCode()
{
	for (var i = 0; i < oosHookJsCodeFunctions.length; i++)
	{
		if (function_exists(oosHookJsCodeFunctions[i]))
			setTimeout(oosHookJsCodeFunctions[i]+'()', 0);
	}
}

//add a combination of attributes in the global JS sytem
function addCombination(idCombination, arrayOfIdAttributes, quantity, price, ecotax, id_image, reference, unit_price, minimal_quantity)
{
	globalQuantity += quantity;

	var combination = new Array();
	combination['idCombination'] = idCombination;
	combination['quantity'] = quantity;
	combination['idsAttributes'] = arrayOfIdAttributes;
	combination['price'] = price;
	combination['ecotax'] = ecotax;
	combination['image'] = id_image;
	combination['reference'] = reference;
	combination['unit_price'] = unit_price;
	combination['minimal_quantity'] = minimal_quantity;
	combinations.push(combination);

}

// search the combinations' case of attributes and update displaying of availability, prices, ecotax, and image
function findCombination(firstTime)
{
	$('#minimal_quantity_wanted_p').fadeOut();
	$('#quantity_wanted').val(1);
	//create a temporary 'choice' array containing the choices of the customer
	var choice = new Array();
	$('div#attributes select').each(function(){
		choice.push($(this).val());
	});

	//testing every combination to find the conbination's attributes' case of the user
	for (var combination = 0; combination < combinations.length; ++combination)
	{
		//verify if this combinaison is the same that the user's choice
		var combinationMatchForm = true;
		$.each(combinations[combination]['idsAttributes'], function(key, value)
		{
			if (!in_array(value, choice))
			{
				combinationMatchForm = false;
			}
		})

		if (combinationMatchForm)
		{
			if (combinations[combination]['minimal_quantity'] > 1)
			{
				$('#minimal_quantity_label').html(combinations[combination]['minimal_quantity']);
				$('#minimal_quantity_wanted_p').fadeIn();
				$('#quantity_wanted').val(combinations[combination]['minimal_quantity']);
				$('#quantity_wanted').bind('keyup', function() {checkMinimalQuantity(combinations[combination]['minimal_quantity'])});
			}
			//combination of the user has been found in our specifications of combinations (created in back office)
			selectedCombination['unavailable'] = false;
			selectedCombination['reference'] = combinations[combination]['reference'];
			$('#idCombination').val(combinations[combination]['idCombination']);

			$('#ipa_customization').val(combinations[combination]['idCombination']);
			//get the data of product with these attributes
			quantityAvailable = combinations[combination]['quantity'];
			selectedCombination['price'] = combinations[combination]['price'];
			selectedCombination['unit_price'] = combinations[combination]['unit_price'];
			if (combinations[combination]['ecotax'])
				selectedCombination['ecotax'] = combinations[combination]['ecotax'];
			else
				selectedCombination['ecotax'] = default_eco_tax;

			//update the display
			updateDisplay();

			if(typeof(firstTime) != 'undefined' && firstTime && ipa_default != selectedCombination['idCombination'])
				refreshProductImages(0);
			else
				refreshProductImages(combinations[combination]['idCombination']);
			//leave the function because combination has been found
			return;
		}
	}
	//this combination doesn't exist (not created in back office)
	selectedCombination['unavailable'] = true;
	updateDisplay();
}

function updateColorSelect(id_attribute)
{
	if (id_attribute == 0)
	{
		refreshProductImages(0);
		return ;
	}
	// Visual effect
	$('#color_'+id_attribute).fadeTo('fast', 1, function(){	$(this).fadeTo('slow', 0, function(){ $(this).fadeTo('slow', 1, function(){}); }); });
	// Attribute selection
	$('#group_'+id_color_default+' option[value='+id_attribute+']').attr('selected', 'selected');
	$('#group_'+id_color_default+' option[value!='+id_attribute+']').removeAttr('selected');
	findCombination();
}

//update display of the availability of the product AND the prices of the product
function updateDisplay()
{
	if (!selectedCombination['unavailable'] && quantityAvailable > 0 && productAvailableForOrder == 1)
	{
		//show the choice of quantities
		$('#quantity_wanted_p:hidden').show('slow');

		//show the "add to cart" button ONLY if it was hidden
		$('#add_to_cart:hidden').fadeIn(600);

		//hide the hook out of stock
		$('#oosHook').hide();

		//availability value management
		if (availableNowValue != '')
		{
			//update the availability statut of the product
			$('#availability_value').removeClass('warning_inline');
			$('#availability_value').text(availableNowValue);
			$('#availability_statut:hidden').show();
		}
		else
		{
			//hide the availability value
			$('#availability_statut:visible').hide();
		}

		//'last quantities' message management
		if (!allowBuyWhenOutOfStock)
		{
			if (quantityAvailable <= maxQuantityToAllowDisplayOfLastQuantityMessage)
				$('#last_quantities').show('slow');
			else
				$('#last_quantities').hide('slow');
		}

		if (quantitiesDisplayAllowed)
		{
			$('#pQuantityAvailable:hidden').show('slow');
			$('#quantityAvailable').text(quantityAvailable);

			if (quantityAvailable < 2) // we have 1 or less product in stock and need to show "item" instead of "items"
			{
				$('#quantityAvailableTxt').show();
				$('#quantityAvailableTxtMultiple').hide();
			}
			else
			{
				$('#quantityAvailableTxt').hide();
				$('#quantityAvailableTxtMultiple').show();
			}
		}
	}
	else
	{
		//show the hook out of stock
		if (productAvailableForOrder == 1)
		{
			$('#oosHook').show();
			if ($('#oosHook').length > 0 && function_exists('oosHookJsCode'))
				oosHookJsCode();
		}

		//hide 'last quantities' message if it was previously visible
		$('#last_quantities:visible').hide('slow');

		//hide the quantity of pieces if it was previously visible
		$('#pQuantityAvailable:visible').hide('slow');

		//hide the choice of quantities
		if (!allowBuyWhenOutOfStock)
			$('#quantity_wanted_p:visible').hide('slow');

		//display that the product is unavailable with theses attributes
		if (!selectedCombination['unavailable'])
			$('#availability_value').text(doesntExistNoMore + (globalQuantity > 0 ? ' ' + doesntExistNoMoreBut : '')).addClass('warning_inline');
		else
		{
			$('#availability_value').text(doesntExist).addClass('warning_inline');
			$('#oosHook').hide();
		}
		$('#availability_statut:hidden').show();


		//show the 'add to cart' button ONLY IF it's possible to buy when out of stock AND if it was previously invisible
		if (allowBuyWhenOutOfStock && !selectedCombination['unavailable'] && productAvailableForOrder == 1)
		{
			$('#add_to_cart:hidden').fadeIn(600);

			if (availableLaterValue != '')
			{
				$('#availability_value').text(availableLaterValue);
				$('p#availability_statut:hidden').show('slow');
			}
			else
				$('p#availability_statut:visible').hide('slow');
		}
		else
		{
			$('#add_to_cart:visible').fadeOut(600);
			$('p#availability_statut:hidden').show('slow');
		}

		if (productAvailableForOrder == 0)
			$('p#availability_statut:visible').hide();
	}

	if (selectedCombination['reference'] || productReference)
	{
		if (selectedCombination['reference'])
			$('#product_reference span').text(selectedCombination['reference']);
		else if (productReference)
			$('#product_reference span').text(productReference);
		$('#product_reference:hidden').show('slow');
	}
	else
		$('#product_reference:visible').hide('slow');

	//update display of the the prices in relation to tax, discount, ecotax, and currency criteria
	if (!selectedCombination['unavailable'] && productShowPrice == 1)
	{
		// retrieve price without group_reduction in order to compute the group reduction after
		// the specific price discount (done in the JS in order to keep backward compatibility)
		if (!displayPrice && !noTaxForThisProduct)
		{
			var priceTaxExclWithoutGroupReduction = ps_round(productPriceTaxExcluded, 6) * (1 / group_reduction);
		} else {
			var priceTaxExclWithoutGroupReduction = ps_round(productPriceTaxExcluded, 6) * (1 / group_reduction);
		}
		var combination_add_price = selectedCombination['price'] * group_reduction;

		var tax = (taxRate / 100) + 1;
		var taxExclPrice = (specific_price ? (specific_currency ? specific_price : specific_price * currencyRate) : priceTaxExclWithoutGroupReduction) + selectedCombination['price'] * currencyRate;


		if (specific_price)
			var productPriceWithoutReduction = priceTaxExclWithoutGroupReduction + selectedCombination['price'] * currencyRate;

		if (!displayPrice && !noTaxForThisProduct)
		{
			var productPrice = taxExclPrice * tax;
			if (specific_price)
				productPriceWithoutReduction = ps_round(productPriceWithoutReduction * tax, 2);
		}
		else
		{
			var productPrice = ps_round(taxExclPrice, 2);
			if (specific_price)
				productPriceWithoutReduction = ps_round(productPriceWithoutReduction, 2);
		}

		var reduction = 0;
		if (reduction_price || reduction_percent)
		{
		    reduction = productPrice * (parseFloat(reduction_percent) / 100) + reduction_price;
		    if (reduction_price && (displayPrice || noTaxForThisProduct))
			reduction = ps_round(reduction / tax, 6);
		}

		if (!specific_price)
			productPriceWithoutReduction = productPrice * group_reduction;


		productPrice -= reduction;
		var tmp = productPrice * group_reduction;
		productPrice = ps_round(productPrice * group_reduction, 2);

		var ecotaxAmount = !displayPrice ? ps_round(selectedCombination['ecotax'] * (1 + ecotaxTax_rate / 100), 2) : selectedCombination['ecotax'];
		productPrice += ecotaxAmount;
		productPriceWithoutReduction += ecotaxAmount;

		if (productPrice > 0)
			$('#our_price_display').text(formatCurrency(productPrice, currencyFormat, currencySign, currencyBlank));
		else
			$('#our_price_display').text(formatCurrency(0, currencyFormat, currencySign, currencyBlank));

		$('#old_price_display').text(formatCurrency(productPriceWithoutReduction, currencyFormat, currencySign, currencyBlank));

		/* Special feature: "Display product price tax excluded on product page" */
		if (!noTaxForThisProduct)
			var productPricePretaxed = productPrice / tax;
		else
			var productPricePretaxed = productPrice;
		$('#pretaxe_price_display').text(formatCurrency(productPricePretaxed, currencyFormat, currencySign, currencyBlank));
		/* Unit price */
		productUnitPriceRatio = parseFloat(productUnitPriceRatio);
		if (productUnitPriceRatio > 0 )
		{
        	newUnitPrice = (productPrice / parseFloat(productUnitPriceRatio)) + selectedCombination['unit_price'];
			$('#unit_price_display').text(formatCurrency(newUnitPrice, currencyFormat, currencySign, currencyBlank));
		}

		/* Ecotax */
		var ecotaxAmount = !displayPrice ? ps_round(selectedCombination['ecotax'] * (1 + ecotaxTax_rate / 100), 2) : selectedCombination['ecotax'];
		$('#ecotax_price_display').text(formatCurrency(ecotaxAmount, currencyFormat, currencySign, currencyBlank));
	}
}

// Change the current product images regarding the combination selected
function refreshProductImages(id_product_attribute)
{
    if (slider_product)
    {
	if (id_product_attribute == 0)
	    slider_product.slide(0);
	else
	{
	    var id_image = 0;
	    $(combinations).each(function() {
		if (this.idCombination == id_product_attribute)
		    id_image = this.image;
	    });

	    var i = 0;
	    $('#thumbs_list_frame').children().each(function(a, b, c, d) {
		if ('thumbnail_' + id_image == $(this).attr('id'))
		    slider_product.slide(i);
		i++;
	    });
	}
    }
}

//To do after loading HTML
$('div[id^="jqm_page_product"]').live('pageshow', function()
{
	// trigger click on the product attribute combination
//	getCombinationById($('#ipa_customization').val());

	//catch the click on the "more infos" button at the top of the page
	$('div#short_description_block p a.button').click(function(){
		$('#more_info_tab_more_info').click();
		$.scrollTo('#more_info_tabs', 1200 );
	});

	// Hide the customization submit button and display some message
	$('p#customizedDatas input').click(function() {
		$('p#customizedDatas input').hide();
		$('#ajax-loader').fadeIn();
		$('p#customizedDatas').append(uploading_in_progress);
	});

	//init the price in relation of the selected attributes
	if (typeof productHasAttributes != 'undefined' && productHasAttributes)
		findCombination(true);
	else if (typeof productHasAttributes != 'undefined' && !productHasAttributes)
		refreshProductImages(0);

	if (typeof productHasAttributes != 'undefined' && !productHasAttributes && productAvailableForOrder == 1 && !quantityAvailable)
	{
		$('#oosHook').show();
		if ($('#oosHook').length > 0 && function_exists('oosHookJsCode'))
			oosHookJsCode();
	}

	$('a#resetImages').click(function() {
		updateColorSelect(0);
	});
	
	$('#payment_paypal_express_checkout').click(function() { $('#paypal_payment_form').submit(); });
});

function saveCustomization()
{
	$('#quantityBackup').val($('#quantity_wanted').val());
	customAction = $('#customizationForm').attr('action');
	$('body select[id^="group_"]').each(function() {
		customAction = customAction.replace(new RegExp(this.id + '=\\d+'), this.id +'='+this.value);
	});
	$('#customizationForm').attr('action', customAction);
	$('#customizationForm').submit();
}

function submitPublishProduct(url, redirect)
{
	var id_product = $('#admin-action-product-id').val();

	$.ajaxSetup({async: false});
	$.post(url+'/ajax.php', { submitPublishProduct: '1', id_product: id_product, status: 1, redirect: redirect },
		function(data)
		{
			if (data.indexOf('error') === -1)
				document.location.href = data;
		}
	);

	return true;
}

function checkMinimalQuantity(minimal_quantity)
{
	if ($('#quantity_wanted').val() < minimal_quantity)
	{
		$('#quantity_wanted').css('border', '1px solid red');
		$('#minimal_quantity_wanted_p').css('color', 'red');
	}
	else
	{
		$('#quantity_wanted').css('border', '1px solid #BDC2C9');
		$('#minimal_quantity_wanted_p').css('color', '#374853');
	}
}

function getCombinationById(id_combination)
{
    console.log(id_combination);
	passed = false;
	$.each(combinations, function(key, value)
	{
		if (value['idCombination'] == id_combination)
		{
			passed = true;
			selectedCombination = value;
			$.each(value['idsAttributes'], function(key, value){
				$('#attributes select option[value='+value+']').attr('selected','selected');
			})
		}
	})
	if (passed)
	{
		refreshProductImages(id_combination);
		$('#wrapResetImages').show('slow');
	}
}
