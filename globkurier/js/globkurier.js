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
// Validate order form
$(function() {
				// Register - Account Type
				if ($('input[name="gk_type"]:checked').val() == 1) {
								$('.gk-box-company').show('fast');
				} else {
								$('.gk-box-company').hide('fast');
				}
				$('input[name="gk_type"]').click(function() {
								if ($('input[name="gk_type"]:checked').val() == 1) {
												$('.gk-box-company').show('fast');
								} else {
												$('.gk-box-company').hide('fast');
								}
				});

				function myOnComplete() {
								//alert("The form validates! (normally, it would submit the form here).");
								return true;
				}
				$(document).ready(function() {
								$("#order_form").RSV({
												onCompleteHandler: myOnComplete,
												rules: [
																"required,sender_name,Uzupelnij nazwę nadawcy.",
																"required,sender_email,Uzupełnij email nadawcy.",
																"valid_email,sender_email,Wprowadź poprawny email nadawcy.",
																"required,sender_address1,Uzupełnij adres.",
																"required,sender_address2,Uzupełnij adres cd.",
																"required,sender_city,Uzupełnij miasto nadawcy.",
																"required,sender_zipcode,Wprowadź poprawny kod pocztowy dla nadawcy.",
																"required,sender_contact_person,Uzupełnij osobę kontaktową dla nadawcy.",
																"required,sender_phone,Uzupełnij telefon nadawcy.",
																"required,recipient_name,Uzupełnij nazwę odbiorcy.",
																"required,recipient_email,Uzupełnij email odbiorcy.",
																"valid_email,recipient_email,Wprowadź poprawny email odbiorcy.",
																"required,recipient_address1,Uzupełnij adres.",
																"required,recipient_address2,Uzupełnij adres cd.",
																"required,recipient_city,Uzupełnij miasto.",
																"required,recipient_zipcode,Uzupełnij kod pocztowy.",
																"required,recipient_contact_person,Uzupełnij osobę kontaktową.",
																"required,recipient_phone,Uzupełnij telefon odbiorcy",
																"required,parcel_weight,Uzupełnij wagę przesyłki.",
																"required,parcel_lenght,Uzupełnij długość przesyłki.",
																"required,parcel_width,Uzupełnij szerokośc przesyłki.",
																"required,parcel_height,Uzupełnij wysokość przesyłki.",
																"required,parcel_count,Wprowadź liczbę przesyłek.",
																"required,parcel_content,Uzupełnij zawartość przesyłki.",
																"required,base_service,Nie wybrano przesyłki. Kliknij 'wyceń'",
																"required,pickup_date,Wprowadź datę odbioru przesyłki.",
																"required,pickup_time_from,Uzupełnij  godzine odbioru 'od' ",
																"required,pickup_time_to,Uzupełnij  godzine odbioru 'do' "
												]
								});
				});

});
