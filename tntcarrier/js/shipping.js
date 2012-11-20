function tntRCgetDepot()
{
	$("#tntRCError").hide();
	tntRCcodePostal = $("#tntRCInputCP").val();
	if (tntRCcodePostal=="") return;
	if (isNaN(parseInt(tntRCcodePostal))) {
		tntRCgetRelaisColis("Veuillez saisir un code d&eacute;partemental correct");
		return;
	}
	tntRCsetChargementEnCours();
	$.get(
		"../modules/tntcarrier/tntGetDepot.php?code="+tntRCcodePostal,
		function(response, status, xhr) 
			{
				if (status == "error") 
					$("#tntRCLoading").html(xhr.status + " " + xhr.statusText);
				if (response == "The field 'department' is not a valid french department code.\n")
					$("#tntRCLoading").html("Vous n'avez pas saisi un code correct d&eacute;partemental fran&ccedil;ais\n");
				else if (response.indexOf("The security token could not be authenticated or authorized") >= 0)
					$("#tntRCLoading").html("Vos identifiants sont incorrects\n");
				else
					$("#tntRCLoading").html(response);
			}
	);
}
					
function	depositButtonClick()
{
	$("#googleMapTnt").css("display", "");
}
					
function	collectButtonClick()
{
	$("#googleMapTnt").css("display", "none");
}
					
$(document).ready(function() {
	var transport1 = $("#tnt_carrier_collect_yes");
	var transport2 = $("#tnt_carrier_collect_no");
	transport1.click(function() {
		$("#divPex").css("display", "none");
		$("#divClosing").css("display", "");
		$("#tnt_exp_names").css("display", "");
	});
	transport2.click(function() {
		$("#divPex").css("display", "");
		$("#divClosing").css("display", "none");
		$("#tnt_exp_names").css("display", "none");
	});
});

function callbackSelectionRelais() 
{
	var code = document.getElementById("tntRCSelectedCode").value;
	var lastname = document.getElementById("tntRCSelectedNom").value;
	var address = document.getElementById("tntRCSelectedAdresse").value;
	var address2 = document.getElementById("tntRCSelectedAdresse2").value;
	var zipcode = document.getElementById("tntRCSelectedCodePostal").value;
	var city = document.getElementById("tntRCSelectedCommune").value;
	
	if (!code || code == "")
		alert("Aucune agence d\351pot selectionn\351e");
	else 
	{
		document.getElementById("tnt_carrier_shipping_pex").value = code;
		document.getElementById("tnt_carrier_shipping_company").value = lastname;
		var s = lastname.length - lastname.indexOf(" ");

		document.getElementById("tnt_carrier_shipping_last_name").value = "";
		document.getElementById("tnt_carrier_shipping_first_name").value = "";
		document.getElementById("tnt_carrier_shipping_address1").value = address;
		document.getElementById("tnt_carrier_shipping_address2").value = address2;
		document.getElementById("tnt_carrier_shipping_postal_code").value = zipcode;
		document.getElementById("tnt_carrier_shipping_city").value = city;
	}
}

function changeValueTntRC(code, name, address1, address2, zipcode, city)
{
	document.getElementById("tntRCSelectedCode").value = code;
	document.getElementById("tntRCSelectedNom").value = name;
	document.getElementById("tntRCSelectedAdresse").value = address1;
	document.getElementById("tntRCSelectedAdresse2").value = address2;
	document.getElementById("tntRCSelectedCodePostal").value = zipcode;
	document.getElementById("tntRCSelectedCommune").value = city;
}

function	displayCity(id_shop)
{
	var postal = $("#tnt_carrier_shipping_postal_code").val();
	if (postal.length == 5)
	{
		$("#resultCity").html("");
		$.get(
			"../modules/tntcarrier/tntGetCity.php?code="+postal+"&id_shop="+id_shop,
			function(response, status, xhr) 
				{
					if (status == "error") 
						$("#resultCity").html("Erreur. R&eacute;essayer plus tard.");
					else if (response == "account")
						$("#resultCity").html("Veuillez-vous identifier dans l'onglet Param&egrave;tres de compte");
					else
					{
						$("#tnt_carrier_shipping_city").removeAttr('disabled');
						$("#tnt_carrier_shipping_city").html(response);
					}
						
				}
			);
	}
	else
		$("#resultCity").html("Le code postal doit etre de 5 chiffres");
}

function enableSelect()
{
	$("#tnt_carrier_shipping_city").removeAttr('disabled');
}