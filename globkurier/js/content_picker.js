/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http:// opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http:// www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license http:// opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

(function($) {
	$.fn.contentPicker = function(content_list) {
		var content_list = $.extend({}, $.fn.contentPicker.defaults, content_list);
		return this.each(function() {
			$.contentPicker(this, content_list);
		});
	};
	$.contentPicker = function(elm, content_list) {
		var e = $(elm)[0];
		return e.contentPicker || (e.contentPicker = new jQuery._contentPicker(e, content_list));
	};

	$.contentPicker.version = '0.1';
	$.contentPicker.author = 'Tomasz Solik | info@tomaszsolik.pl | www.tomaszsolik.pl';

	$._contentPicker = function(elm, content_list) {
		var cpOver = false;
		var keyDown = false;
		var selectedClass = "selected";
		var selectedSelector = "li." + selectedClass;

		// Disable browser autocomplete
		$(elm).attr('autocomplete', 'OFF');
		var content = Array();
		$.each(content_list, function(i, v) {
			content.push(v);
		});

		// Content list
		var $cpDiv = $('<div class="content-picker"></div>');
		var $cpList = $('<ul></ul>');

		// Build the list
		for (var i = 0; i < content.length; i++) {
			$cpList.append("<li>" + content[i] + "</li>");
		}
		$cpDiv.append($cpList);
		// Append the contentPicker to the body and position it.
		$cpDiv.appendTo('body').hide();

		// Store the mouse state, used by the blur event. Use mouseover instead of
		// mousedown since Opera fires blur before mousedown.
		$cpDiv.mouseover(function() {
			cpOver = true;
		}).mouseout(function() {
			cpOver = false;
		});

		$("li", $cpList).mouseover(function() {
			if (!keyDown) {
				$(selectedSelector, $cpDiv).removeClass(selectedClass);
				$(this).addClass(selectedClass);
			}
		}).mousedown(function() {
			cpOver = true;
		}).click(function() {
			setContentValue(elm, this, $cpDiv);
			tpOver = false;
		});

		var showPicker = function() {
			if ($cpDiv.is(":visible")) {
				return false;
			}
			$("li", $cpDiv).removeClass(selectedClass);
			// Position
			var elmOffset = $(elm).offset();
			$cpDiv.css({
				'top' : elmOffset.top + elm.offsetHeight,
				'left' : elmOffset.left
			});

			// Show picker. This has to be done before scrollTop is set since that
			// can't be done on hidden elements.
			$cpDiv.show();
		};
		// Attach to click as well as focus so timePicker can be shown again when
		// clicking on the input when it already has focus.
		$(elm).focus(showPicker).click(showPicker);
		// Hide timepicker on blur
		$(elm).blur(function() {
			if (!cpOver) {
				$cpDiv.hide();
			}
		});
	};
	// End function

	function setContentValue(elm, sel, $tpDiv) {
		// Update input field
		elm.value = $(sel).text();
		// Trigger element's change events.
		$(elm).change();
		// Keep focus for all but IE (which doesn't like it)
		if (!$.browser.msie) {
			elm.focus();
		}
		// Hide picker
		$tpDiv.hide();
	}


	$.fn.contentPicker.defaults = ['Akcesoria telefoniczne', 'Artykuły medyczne (nie leki)', 'Artykuły i narzędzia budowlane', 'Artykuły i urządzenia sportowe', 'Ceramika', 'Części samochodowe', 'Dokumenty', 'Fotografie', 'Książki i czasopisma', 'Materiały firmowe', 'Meble', 'Odzież', 'Sprzęt AGD i RTV', 'Sprzęt komputerowy', 'Wózki', 'Zabawki i modele'];

})(jQuery);
