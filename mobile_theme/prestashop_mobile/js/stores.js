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

function searchLocations()
{
	$('#stores_loader').show();
	var address = document.getElementById('addressInput').value;
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode({address: address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK)
			searchLocationsNear(results[0].geometry.location);
		else
			alert(address+' '+translation_6);
		$('#stores_loader').hide();
	});
}

function clearLocations(n)
{
	infoWindow.close();
	for (var i = 0; i < markers.length; i++)
		markers[i].setMap(null);

	markers.length = 0;

	locationSelect.innerHTML = '';
	var option = document.createElement('option');
	option.value = 'none';
	if (!n)
		option.innerHTML = translation_1;
	else
	{
		if (n == 1)
			option.innerHTML = '1'+' '+translation_2;
		else
			option.innerHTML = n+' '+translation_3;
	}
	locationSelect.appendChild(option);
	$('#stores-table tr.node').remove();
}

function searchLocationsNear(center)
{
	var radius = document.getElementById('radiusSelect').value;

	var localSearchUrl = searchUrl + '?ajax=1&latitude=' + center.lat() + '&longitude=' + center.lng() + '&radius=' + radius;
}

function createOption(name, distance, num)
{
	var option = document.createElement('option');
	option.value = num;
	option.innerHTML = name+' ('+distance.toFixed(1)+' '+distance_unit+')';
	locationSelect.appendChild(option);
}

$('#jqm_page_stores').live('pageshow', function()
{
	locationSelect = document.getElementById('locationSelect');
		locationSelect.onchange = function() {
		var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
		if (markerNum != 'none')
		google.maps.event.trigger(markers[markerNum], 'click');
	};

	$('#addressInput').keypress(function(e) {
		code = e.keyCode ? e.keyCode : e.which;
		if(code.toString() == 13)
			searchLocations();
	});
});
