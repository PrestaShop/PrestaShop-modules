var tntRCcodePostal;
var tntRClisteRelais;

$("#form").submit(function()
	{
		if ($("#tntRCSelectedCode").val() == "")
		{
			alert("Vous n\'avez pas choisi de relais colis");
			return false;
		}
	});

function postMobile(token)
{
	var id = $("#id_cart").val();
	var ph = $("#mobileTnt").val();;
	
	$.get(baseDir+"/modules/tntcarrier/relaisColis/postMobileData.php?id_cart="+id+"&phone="+ph+"&token="+token);
}
	
function resetMap() {
	
	if (map) {
		
		map.getStreetView().setVisible(false);
		
		for (var i = 0; i < relaisMarkers.length; i++) { 
			relaisMarkers[i].setMap(null);
			relaisMarkers[i] = null;
		}
		relaisMarkers = new Array();
		if (infowindow) infowindow.close();
		map.setZoom(defaultZoom);
		map.setCenter(defaultCenter);
	}	
}

function tntRCgetRelaisColis(commune)
{
	if (!commune) {
		// La commune du code postal correspond à la sélection du radio bouton tntRCchoixComm
		tntRCCommune = $("input[type=radio][name=tntRCchoixComm][checked]").val();
	}
	else {
		// Utilisation de la valeur fournie en paramètre
		tntRCCommune = commune;
	}
	// Affichage message "chargement en cours"
	//tntRCsetChargementEnCours();
	
	var ajaxUrl;
	var ajaxData;

	ajaxUrl = "http://www.tnt.fr/public/b2c/relaisColis/loadJson.do?cp=" + tntRCcodePostal + "&commune=" + tntRCCommune;
	ajaxData = "";
	
	// Chargement de la liste de relais colis
	$.ajax({
	   type: "GET",
	   url: ajaxUrl,
	   data: ajaxData,
	   dataType: "script"
	});
}
		
function		tntRCgetCommunes()
{
	tntRCcodePostal = $('#tntRCInputCP').val();

	// Code postal non renseigné, on ne fait rien 
	if (tntRCcodePostal=="") return;

	if (mapDetected) resetMap();
	// On ne fait rien si le code postal n'est pas un nombre de 5 chiffres
	if (isNaN(parseInt(tntRCcodePostal)) || tntRCcodePostal.length != 5) {
		$("#relaisColisResponse").html("Veuillez saisir un code postal sur 5 chiffres");
		return;
	}
	var ajaxUrl;
	var ajaxData;
	ajaxUrl = "http://www.tnt.fr/public/b2c/relaisColis/rechercheJson.do?code=" + tntRCcodePostal;
	ajaxData = "";
	$.ajax({type: "GET", url: ajaxUrl, data: ajaxData, dataType: "script", error:function(msg){$("#relaisColisResponse").html("Error !: " + msg );}});
}

function tntRCSetSelectedInfo(selectedIdx, noMarkerInfo)
{
	if (!selectedIdx && selectedIdx != 0) {
		// RAZ des infos sélectionnées
		$("#tntRCSelectedCode").val("");
		$("#tntRCSelectedNom").val("");
		$("#tntRCSelectedAdresse").val("");
		$("#tntRCSelectedCodePostal").val("");
		$("#tntRCSelectedCommune").val("");
		return
	}
	var oRelais = tntRClisteRelais[selectedIdx];

	$("#tntRCSelectedCode").val(oRelais[0]);
	$("#tntRCSelectedNom").val(oRelais[1]);
	$("#tntRCSelectedAdresse").val(oRelais[4]);
	$("#tntRCSelectedCodePostal").val(oRelais[2]);
	$("#tntRCSelectedCommune").val(oRelais[3]);
	var id_cart = document.getElementById("cartRelaisColis").value;
	$.ajax({
	   type: "POST",
	   url: baseDir+"/modules/tntcarrier/relaisColis/postRelaisData.php",
	   data: "id_cart="+id_cart+"&tntRCSelectedCode="+oRelais[0]+"&tntRCSelectedNom="+oRelais[1]+"&tntRCSelectedAdresse="+oRelais[4]+"&tntRCSelectedCodePostal="+oRelais[2]+"&tntRCSelectedCommune="+oRelais[3]
	});
	
	if (mapDetected && !noMarkerInfo) {
		
		// Les noeuds dans le fichier XML ne sont pas forcément ordonnés pour l'affichage, on va donc d'abord récupérer leur valeur
		var codeRelais = oRelais[0]
		var nomRelais = oRelais[1];
		var adresse = oRelais[4];
		var codePostal = oRelais[2];
		var commune = oRelais[3];
		var heureFermeture = oRelais[21];

		var messages = "";
		var lundi_am = (oRelais[7] == "-")?",":oRelais[7]+",";
		var lundi_pm = oRelais[8];
		var mardi_am = (oRelais[9] == "-")?",":oRelais[9]+",";
		var mardi_pm = oRelais[10];
		var mercredi_am = (oRelais[11] == "-")?",":oRelais[11]+",";
		var mercredi_pm = oRelais[12];
		var jeudi_am = (oRelais[13] == "-")?",":oRelais[13]+",";
		var jeudi_pm = oRelais[14];
		var vendredi_am = (oRelais[15] == "-")?",":oRelais[15]+",";
		var vendredi_pm = oRelais[16];
		var samedi_am = (oRelais[17] == "-")?",":oRelais[17]+",";
		var samedi_pm = oRelais[18];
		var dimanche_am = (oRelais[19] == "-")?",":oRelais[19]+",";
		var dimanche_pm = oRelais[20];
		
		if (lundi_pm != "-") lundi_am = lundi_am + lundi_pm;
		if (mardi_pm != "-") mardi_am = mardi_am + mardi_pm;
		if (mercredi_pm != "-") mercredi_am = mercredi_am + mercredi_pm;
		if (jeudi_pm != "-") jeudi_am = jeudi_am + jeudi_pm;
		if (vendredi_pm != "-") vendredi_am = vendredi_am + vendredi_pm;
		if (samedi_pm != "-") samedi_am = samedi_am + samedi_pm;
		if (dimanche_pm != "-") dimanche_am = dimanche_am + dimanche_pm;
		
		var horaires = new Array();
		horaires['lundi'] = lundi_am + ",1";
		horaires['mardi'] = mardi_am + ",2";
		horaires['mercredi'] = mercredi_am + ",3";
		horaires['jeudi'] = jeudi_am + ",4";
		horaires['vendredi'] = vendredi_am + ",5";
		horaires['samedi'] = samedi_am + ",6";
		horaires['dimanche'] = dimanche_am + ",0";
		
		var messages = "";
		for (j=0; j < oRelais[24].length; j++) {
			var ligne = oRelais[24][j];
			if (ligne != "") messages = messages + ligne + "<br/>";
		}

		setInfoMarker(codeRelais, nomRelais, adresse, codePostal, commune, messages, selectedIdx, horaires, relaisMarkers[selectedIdx]);
	}
}

function afficheDetail(i)
{
	if($('#tntRCDetail'+i).is(':visible'))
		$('#tntRCDetail'+i).hide('slow');
	else
		$('#tntRCDetail'+i).show('slow');
	
}

function listeRelais(tabRelais)
{
	var jData = tabRelais;
	var jMessage = $('#relaisColisResponse');
	var tntRCjTable = $("<table style='width:100%;border:1px solid gray;' cellpadding='0' cellspacing='0'></table>");
	
	jMessage.html("");
	tntRCjTable.append("<tr><td class='tntRCblanc' width=''></td><td class='tntRCgris' colspan='2' width=''>&nbsp;Les diff&eacute;rents Relais Colis&#174;</td><td class='tntRCblanc' width=''></td><td class='tntRCgris' width=''>&nbsp;Mon choix</td><td class='tntRCblanc' width=''></td></tr>");
	var i = 0;
	tntRClisteRelais = jData;
	for(i = 0; i < jData.length; i++) {
			
		var oRelais = jData[i];
		var codeRelais = oRelais[0];
		var nomRelais = oRelais[1];
		var adresse = oRelais[4];
		var codePostal = oRelais[2];
		var commune = oRelais[3];
		var heureFermeture = oRelais[21];
		
		var lundi_am = (oRelais[7] == "-")?"ferm&#233;":oRelais[7];
		var lundi_pm = (oRelais[8] == "-")?"ferm&#233;":oRelais[8];
		var mardi_am = (oRelais[9] == "-")?"ferm&#233;":oRelais[9];
		var mardi_pm = (oRelais[10] == "-")?"ferm&#233;":oRelais[10];
		var mercredi_am = (oRelais[11] == "-")?"ferm&#233;":oRelais[11];
		var mercredi_pm = (oRelais[12] == "-")?"ferm&#233;":oRelais[12];
		var jeudi_am = (oRelais[13] == "-")?"ferm&#233;":oRelais[13];
		var jeudi_pm = (oRelais[14] == "-")?"ferm&#233;":oRelais[14];
		var vendredi_am = (oRelais[15] == "-")?"ferm&#233;":oRelais[15];
		var vendredi_pm = (oRelais[16] == "-")?"ferm&#233;":oRelais[16];
		var samedi_am = (oRelais[17] == "-")?"ferm&#233;":oRelais[17];
		var samedi_pm = (oRelais[18] == "-")?"ferm&#233;":oRelais[18];
		var dimanche_am = (oRelais[19] == "-")?"ferm&#233;":oRelais[19];
		var dimanche_pm = (oRelais[20] == "-")?"ferm&#233;":oRelais[20];
		
		if (lundi_pm != "-") lundi_am = lundi_am + "<br/>" + lundi_pm;
		if (mardi_pm != "-") mardi_am = mardi_am + "<br/>" + mardi_pm;
		if (mercredi_pm != "-") mercredi_am = mercredi_am + "<br/>" + mercredi_pm;
		if (jeudi_pm != "-") jeudi_am = jeudi_am + "<br/>" + jeudi_pm;
		if (vendredi_pm != "-") vendredi_am = vendredi_am + "<br/>" + vendredi_pm;
		if (samedi_pm != "-") samedi_am = samedi_am + "<br/>" + samedi_pm;
		if (dimanche_pm != "-") dimanche_am = dimanche_am + "<br/>" + dimanche_pm;

		var messages="";			
		var logo_point = "";
		if (messages != "") logo_point = "<img src='"+baseDir+"/modules/tntcarrier/img/exception.gif' alt='Informations compl&#233;mentaires' width='16px' height='16px'>";
		
		tntRCjTable.append(
			"<tr>"+
				"<td class='tntRCblanc' width=''></td>"+
				"<td class='tntRCblanc' width=''><img src='"+baseDir+"/modules/tntcarrier/img/logo-tnt-petit.jpg'>&nbsp;" + logo_point + "</td>"+
				"<td style='font-size:10px; padding:0 0 3px' class='tntRCrelaisColis' width=''>" + nomRelais + " - " + adresse + " - " + codePostal + " - " + commune + "<BR>&nbsp;&nbsp;&nbsp;&nbsp;>> Ouvert jusqu'&agrave; " + heureFermeture + "</td>"+
				"<td class='tntRCrelaisColis' width=''>&nbsp;</td>"+
				"<td style='font-size:10px;  padding: 0;' class='tntRCrelaisColis' valign='middle' align='center' width=''>"+
					"<img onclick='afficheDetail(" + i + ");' style='vertical-align:middle;cursor:pointer' src='"+baseDir+"/modules/tntcarrier/img/loupe.gif' class='tntRCBoutonLoupe'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
					"<input type='radio' style='vertical-align: middle;margin-left:0px' name='tntRCchoixRelais' value='" + codeRelais + "'" + ( i==0 ? "checked" : "") + " onclick='tntRCSetSelectedInfo(" + i + ")'/>"+
				"</td>"+
				"<td class='tntRCblanc' width=''></td>"+
			"</tr>"+
			"<td colspan='6'><table style='display:none;' id='tntRCDetail"+i+"'><tr><td>lundi : "+lundi_am+"</td><td>mardi : "+mardi_am+"</td><td>mercredi : "+mercredi_am+"</td><td>jeudi : "+jeudi_am+"</td><td>vendredi : "+vendredi_am+"</td><td>samedi : "+samedi_am+"</td><td>dimanche : "+dimanche_am+"</td></tr></table></td>"
			);
	}
	
	// Mémorisation des infos du relais sélectionné par défaut (c'est le premier)		
	tntRCSetSelectedInfo(0, true);
	// Ajout du lien de retour sur la liste des communes si cette dernière a été mémorisée
	
	jMessage.append(tntRCjTable);
	if (mapDetected) init_marker(tabRelais);
}

function listeCommunes(tabCommunes) 
{
	// RAZ des infos sélectionnées
	tntRCSetSelectedInfo();
	if (mapDetected) resetMap();

	var tntRCjTable = $("<table style='border:1px solid gray;' cellpadding='0' cellspacing='0' width='100%'></table>");
	tntRCjTable.append("<tr><td class='tntRCblanc' width=''></td><td class='tntRCgris' colspan='2' width=''>&nbsp;Les diff&eacute;rents Relais Colis&#174;</td><td class='tntRCblanc' width=''></td><td class='tntRCgris' width=''>&nbsp;Mon choix</td><td class='tntRCblanc' width=''></td></tr>");

	var jData = tabCommunes;
	var blocCodePostal = $('#relaisColisResponse');
	
	var i = 1;
	//var jCommunes = jData.find("VILLE");
	for (var iIdx = 0; iIdx < jData.length; iIdx++) {
		
		var commune = jData[iIdx];
		
		//var jCommune = $(this);
		var nomVille = commune[1]; // IE vs FF

		tntRCjTable.append(
			"<tr>"+
				"<td class='tntRCblanc' width=''></td>"+
				"<td class='tntRCblanc' width=''><img src='"+baseDir+"/modules/tntcarrier/img/logo-tnt-petit.jpg'></td>" +
				"<td class='tntRCrelaisColis' width=''> " + nomVille + " (" + tntRCcodePostal + ") </td>" +
				"<td class='tntRCrelaisColis' width=''>&nbsp;</td>"+
				"<td class='tntRCrelaisColis' align='center' width=''>"+
					"<input type='radio' name='tntRCchoixComm' value='" + nomVille + "' " + ( i ==1 ? "checked" : "") + ">"+
				"</td>"+
				"<td class='tntRCblanc' width=''></td>"+
			"</tr>");
		i = 2;
	}
	
	tntRCjTable.append(
		"<tr>"+	
			"<td class='tntRCblanc' width=''></td>"+
			"<td class='tntRCblanc' colspan='2' width=''></td>"+
			"<td class='tntRCblanc' width=''></td>"+
			"<td class='tntRCblanc' align='center' width=''>"+
				"<a href='javascript:tntRCgetRelaisColis();'><img class='tntRCButton' src='"+baseDir+"/modules/tntcarrier/img/bt-Continuer-2.jpg' onmouseover='this.src=\"/modules/tntcarrier/img/bt-Continuer-1.jpg\"' onmouseout='this.src=\"/modules/tntcarrier/img/bt-Continuer-2.jpg\"'></a>" +
			"</td>"+
			"<td class='tntRCblanc' width='></td>"+
		"</tr>");
	
	blocCodePostal.html(tntRCjTable);	
	
	// Bloc de saisie d'un nouveau code postal			
    //blocCodePostal.append(tntRCchangerCodePostal());
}

function erreurListeCommunes() {
	$("#relaisColisResponse").html("Erreur sur le code postal");
}

function erreurListeRelais() {
$("#relaisColisResponse").html("Erreur");
}

/************************************************************************************************
 * 							Partie Google Map
 ***********************************************************************************************/


var map;
var adresse_pointclic;
var zone_chalandise;
var zoomZoneChalandiseDefault;
var centerZoneChalandiseDefault;
var init_streetview = false;

var contentTo = [
                 '<br/><div>',
                     'Itin&#233;raire : <b>Vers ce lieu</b> - <a href="javascript:fromhere(0)">A partir de ce lieu</a><br/>',
                     'Lieu de d&#233;part<br/>',
                     '<input type="text" id="saisie" name="saisie" value="" maxlength="500" size="30">',
                     '<input type="hidden" id="mode" name="mode" value="toPoint">',
                     '<input type="hidden" id="point_choisi" name="point_choisi" value="">',
                     '<input type="submit" onclick="return popup_roadmap();" value="Ok">',
                     '<br/>Ex: 58 avenue Leclerc 69007 Lyon',
                 '</div>'].join('');
     
var contentFrom = [
                  '<br/><div>',
                      'Itin&#233;raire : <a href="javascript:tohere(0)">Vers ce lieu</a> - <b>A partir de ce lieu</b><br/>',
                      'Lieu d\'arriv&#233;e<br/>',
                      '<input type="text" id="saisie" name="saisie" value="" maxlength="500" size="30">',
                      '<input type="hidden" id="mode" name="mode" value="fromPoint">',
                      '<input type="hidden" id="point_choisi" name="point_choisi" value="">',
                      '<input type="button" onclick="return popup_roadmap();" value="Ok">',
                      '<br/>Ex: 58 avenue Leclerc 69007 Lyon',
                  '</div>'].join('');

var infowindow;

var relaisMarkers = [];
var iconRelais = new google.maps.MarkerImage(
		baseDir+"/modules/tntcarrier/img/google/relaisColis.png", 
		new google.maps.Size(40, 30), 
		new google.maps.Point(0, 0), 
		new google.maps.Point(20, 30))

//Limites de la France
var allowedBounds = new google.maps.LatLngBounds(
		new google.maps.LatLng(39.56533418570851, -7.41426946590909), 
		new google.maps.LatLng(52.88994181429149, 11.84176746590909));

var defaultCenter = new google.maps.LatLng(46.2276380, 2.2137490); // the center ???
var defaultZoom = 5; 						// default zoom level
var aberration = 0.2; 						// this value is a good choice for france (?!)
var minMapScale = 5;
//var maxMapScale = 20;

var mapDetected = false;
var callbackLinkMarker = "";

// fonction appellé après saisie du code postal de recherche
function init_marker(json) {
	
	zone_chalandise = new google.maps.LatLngBounds();
	
	for (var i = 0; i < relaisMarkers.length; i++) { 
		relaisMarkers[i].setMap(null);
		relaisMarkers[i] = null;
	}
	relaisMarkers = new Array();
	
	if (infowindow) infowindow.close();
	
	var markers = json;
	
	for (var i = 0; i < markers.length; i++) {
		createMarker(markers[i], i);
	}
	
	zoomZoneChalandiseDefault = zone_chalandise.getCenter();
	centerZoneChalandiseDefault = zone_chalandise;
	
	retourZoomChalandise();
}

function setInfoMarker(codeRelais, nomRelais, adresse, codePostal, commune, messages, indice, horaires, marker) {
	
	var htmlInfo = [
		"<div>",
			"<div class='rc'>",
				"<b>RELAIS COLIS N\260 ", codeRelais, "</b><br/>",
				"<b>", nomRelais, "</b><br/>", 
				adresse, "<br/>", 
				codePostal, " ", commune,
			"</div>",
			"<div><br/>", messages, "</div>",
			callbackLinkMarker,
		"</div>",
		"<div id='trajet'>" + contentTo + "</div>"
	].join('');

	// Création du contenu de l'onglet horaire
	var htmlHoraires = "<table class='horairesRCPopup'>";
	var jourSemaine = (new Date()).getDay();
	for (jour in horaires) {
		var heures = (horaires[jour]).split(",");
		if (heures[0] == '' && heures[1] == '') heures[0] = "ferm&#233;";
		htmlHoraires = htmlHoraires  + "<tr" + (jourSemaine == parseInt(heures[2]) ? " class='selected'" : "") + "><td class='horairesRCJourPopup'>&nbsp;" + jour + "</td><td class='horaireRCPopup'>" + heures[0] + " " + heures[1] + "</td></tr>";
	}
	htmlHoraires = htmlHoraires + "</table>";
	
	adresse_pointclic = [adresse, "|", codePostal, " ", commune].join('');
	
	var contentString = [
         '<div id="tabs" style="width:340px;">',
         '<ul>',
           '<li><a href="#tabInfos"><span>Infos</span></a></li>',
           '<li><a href="#tabHoraires"><span>Horaires</span></a></li>',
         '</ul>',
         '<div id="tabInfos">',
           htmlInfo,
         '</div>',
         '<div id="tabHoraires">',
           htmlHoraires,
         '</div>',
         '</div>'
       ].join('');

    if (infowindow) infowindow.close();
    infowindow = new google.maps.InfoWindow({content: contentString});

	google.maps.event.addListener(infowindow, "domready", function() {  
		$("#point_choisi").attr("value", adresse_pointclic);
		$("#tabs").parent().removeAttr("style");
	});

	infowindow.open(map, marker);
}

function createMarker(markerData, indice) {
	
	var marker = new google.maps.Marker({
		icon: iconRelais,
		position: new google.maps.LatLng(markerData[5], markerData[6]),
		map: map,
		title:markerData[1]
	});
	
	google.maps.event.addListener(marker, "click", function() {
		// Sélectionne le relais correspondant dans la liste
		$("input[type=radio][name=tntRCchoixRelais]:eq("+ indice + ")").attr("checked", true);
		tntRCSetSelectedInfo(indice);
	});

	relaisMarkers.push(marker);
	zone_chalandise.extend(marker.getPosition());
}


function tntRCInitMap() 
{
	// Si la carte n'est pas présente, fin de l'initialisation
	if (!document.getElementById("map_canvas")) return;
	mapDetected = true;
	
	// Si une fonction de callback a été définie, un lien est ajouté
 	// dans la popup d'info du marqueur de relais colis
	if (window.callbackSelectionRelais) callbackLinkMarker = "<a onclick='callbackSelectionRelais();' href='#' style='color:#FF6600'>Choisir ce relais</a>";
	
	//Ajout du lien pour retour en zoom zone de chalandise
	var jMapCanvas = $("#map_canvas");
	jMapCanvas.wrap("<div></div>");
	
	var mapClass = jMapCanvas.attr("class"); 
	if (mapClass && mapClass != "") {
		jMapCanvas.attr("class", "");
		jMapCanvas.parent().attr("class", mapClass);
	}

	var myOptions = {
		zoom: defaultZoom,
		center: defaultCenter,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		navigationControl: true,
		scaleControl: true,
		mapTypeControl: true,
		streetViewControl: true
	};
	map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

    // If the map position is out of range, move it back
    function checkBounds() {

		// Perform the check and return if OK
		var currentBounds = map.getBounds();
		var cSpan = currentBounds.toSpan(); // width and height of the bounds
		var offsetX = cSpan.lng() / (2+aberration); // we need a little border
		var offsetY = cSpan.lat() / (2+aberration);
		var C = map.getCenter(); // current center coords
		var X = C.lng();
		var Y = C.lat();
	
		// now check if the current rectangle in the allowed area
		var checkSW = new google.maps.LatLng(C.lat()-offsetY,C.lng()-offsetX);
		var checkNE = new google.maps.LatLng(C.lat()+offsetY,C.lng()+offsetX);
		
		if (allowedBounds.contains(checkSW) &&
			allowedBounds.contains(checkNE)) {
			return; // nothing to do
		}
	
		var AmaxX = allowedBounds.getNorthEast().lng();
		var AmaxY = allowedBounds.getNorthEast().lat();
		var AminX = allowedBounds.getSouthWest().lng();
		var AminY = allowedBounds.getSouthWest().lat();
	
		if (X < (AminX+offsetX)) {X = AminX + offsetX;}
		if (X > (AmaxX-offsetX)) {X = AmaxX - offsetX;}
		if (Y < (AminY+offsetY)) {Y = AminY + offsetY;}
		if (Y > (AmaxY-offsetY)) {Y = AmaxY - offsetY;}
	
		map.setCenter(new google.maps.LatLng(Y,X));
		return;
    }
	google.maps.event.addListener(map, "drag", function() {
		checkBounds();
	});

	google.maps.event.addListener(map, "zoom_changed", function() {
		if (map.getZoom() < minMapScale) {
			map.setZoom(minMapScale);
		}
	});
	google.maps.event.addListener(map.getStreetView(), "visible_changed", function() {
		//premier accès lors du chargement de la page, il ne faut pas cacher les markers
		if (init_streetview == true) {
			if(map.getStreetView().getVisible() == true) {
				for (var k = 0; k < relaisMarkers.length; k++) { 
					relaisMarkers[k].setVisible(false);
				}
			}
			else {
				for (var k = 0; k < relaisMarkers.length; k++) { 
					relaisMarkers[k].setVisible(true);
				}
			}
		}
		else init_streetview = true;
	});
}

function retourZoomChalandise() {
	if(zoomZoneChalandiseDefault){
		map.setCenter(zoomZoneChalandiseDefault);
		map.fitBounds(centerZoneChalandiseDefault);
	}
}

function fromhere() {
	switchFromTo(contentFrom);
}

function tohere() {
	switchFromTo(contentTo);
}

function switchFromTo(htmlContent) {
	var adresse_saisie = $("#saisie").val();
	$("#trajet").html(htmlContent);
	$("#point_choisi").attr('value', adresse_pointclic);
	$("#saisie").val(adresse_saisie);
}

function popup_roadmap() {
	if($("#saisie").val() == "") return false;
	window.open("http://www.tnt.fr/public/geolocalisation/print_roadmap.do?mode="+ $("#mode").val() +"&point_choisi="+ $("#point_choisi").val() +"&saisie="+ $("#saisie").val());
    return false;
}

/*$().ready(tntRCInitMap);*/
