/* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL Ether Création
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL Ether Création is strictly forbidden.
* In order to obtain a license, please contact us: contact@ethercreation.com
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe Ether Création
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la SARL Ether Création est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter la SARL SMC a l'adresse: contact@ethercreation.com
* ...........................................................................
* @package ec_ecopresto
* @copyright Copyright (c) 2010-2013 S.A.R.L Ether Création (http://www.ethercreation.com)
* @author Arthur R.
* @license Commercial license
*/

function MAJDereferencement()
{
    
    var ec_token = $('#ec_token').val();    
    fenetreModalshow(1,textDerefEnCours);
    var XHR = new XHRConnection();
    XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=9&ec_token='+ec_token,'GET',resultat_MAJDereferencement);
    return true;
}

resultat_MAJDereferencement = function(obj)
{
    maj = obj.responseText;
    a=maj.split(',');
    if(a[0]==1){
        if(a[1]!=0){
            SetDerefencement(a[1]);
        }  
    }
	else
	{
		fenetreModalhide(1,1,1,'');
	}
}

function SetDerefencement(totS)
{
    
    var ec_token = $('#ec_token').val();    
    var XHR = new XHRConnection();
    XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=11&ec_token='+ec_token,'GET', resultat_Derefencement);
    return true;
}

resultat_Derefencement = function (obj)
{
    loaderEtat2(100);
    fenetreModalhide(1,1,0,textDerefTermine);
}

function catSelSpeAfter(){
     $('.selSpe').each(function(){ 
        $(this).val($(this).attr('catSel')); 
     });
}

function DELDereferencement(){
    
    var tot = $('input:checked.cbDeref').length;
    var i=1;
    var ec_token = $('#ec_token').val(); 
       
	fenetreModalshow(1,textDerefEnCours);
    var XHR = new XHRConnection();
    
    XHR.sendAndLoad('../modules/ecopresto/ajax.php?ref='+$('input:checked.cbDeref:eq(0)').attr('id')+'&actu=0&tot='+tot+'&majsel=12&ec_token='+ec_token,'GET',resultat_DELDereferencement);            
               
}

function DELDereferencementSuite(actu,tot){  
   fenetreModalshow(1,textDerefEnCours);
   var XHR = new XHRConnection();
   var ec_token = $('#ec_token').val(); 
   XHR.sendAndLoad('../modules/ecopresto/ajax.php?ref='+$('input:checked.cbDeref:eq('+actu+')').attr('id')+'&actu='+actu+'&tot='+tot+'&majsel=12&ec_token='+ec_token,'GET',resultat_DELDereferencement);                        
}

resultat_DELDereferencement = function (obj)
{
    maj = obj.responseText;
    a=maj.split(',');
    var actu = parseInt(a[0]);
    var tot = parseInt(a[1]);
    actu = parseInt(actu+1);    
    var charge = (actu*100)/tot;
    loaderEtat2(charge);      
    if(actu==tot){
        fenetreModalhide(0,1,0,'');
    }else{
        DELDereferencementSuite(actu,tot)
    }
}

function GetFilecsv()
{
    
    var ec_token = $('#ec_token').val();    
    fenetreModalshow('1',textImportCatalogueEnCours);
    var XHR = new XHRConnection();
    XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=8&ec_token='+ec_token,'GET',resultat_GetFilecsv);
    return true;
}

resultat_GetFilecsv = function(obj){
    maj = obj.responseText;
    a=maj.split(',');
    if(a[0]=='1'){
        majCat(0,0,0,0,a[2],a[1]);   
    }
    else
    {
        fenetreModalhide(1,0,1,textSynchroTermine);
        $('#importCat').hide();
        $('#noUpdate').show();
    }
}

resultat_majCat = function (obj)
{
    maj = obj.responseText;
	a=maj.split(',');
    var n = parseInt(a[0])+1;
    var ntotal = a[5];
    
    if(a[4]==1){
        var nactu = n;
    }else{
        var nactu = parseInt(n) + parseInt(parseInt(a[3])*3000);
    }
    
    if(isNaN(a[4]) == false && isNaN(a[0]) == false){
        if(nactu<ntotal){       
            var charge = (nactu*100)/ntotal;
            loaderEtat2(charge);
            majCat(n,a[1],a[2],a[3],a[4],a[5]);        
        }else{
            loaderEtat2(100);
            fenetreModalhide('1','1','0',textImportCatalogueTermine);
        }
    }else{
         fenetreModalhide('2','1','0',textImportCatalogueErreur);
    }
}

function majCat(etp,ref,tentative,fichierActu,nbFichier,lignesTot)
{
    
    var ec_token = $('#ec_token').val();    
    var XHR = new XHRConnection();
    XHR.sendAndLoad('../modules/ecopresto/ajax.php?etp='+etp+'&ref='+ref+'&majsel=3&ec_token='+ec_token+'&tentative='+tentative+'&fichierActu='+fichierActu+'&nbFichier='+nbFichier+'&lignesTot='+lignesTot,'GET',resultat_majCat);
    return true;
}

function getSecuMajPS(tot)
{
    var temOK = 0;
    $('.secuMaj').each(function(){ 
        if($(this).val()<$('#etat0_0').val()){           
            var ec_token = $('#ec_token').val();            
            var XHR = new XHRConnection();
            XHR.sendAndLoad('../modules/ecopresto/import.php?etp='+$(this).val()+'&ec_token='+ec_token+'&total='+tot+'&typ='+$(this).attr('rel'),'GET',resultat_CatalogPS);
            temOK = 1;
        }
    });
    return temOK;
}

resultat_CatalogPS = function (obj)
{
    maj = obj.responseText;
	a=maj.split(',');
    var n = parseInt(a[0]);
    if(isNaN(a[1]) == false && isNaN(a[0]) == false && isNaN(a[2]) == false){
        if(a[2]!=0)
            $('#etat'+a[2]).val(a[0]-($('#etat0_0').val()*(a[2]-1)));
        else
            $('#etatSup').val(a[0]);
            
        var totActu = 0;
                
        $('.cpmMaj').each(function(){            
            totActu = parseInt($(this).val())+parseInt(totActu);
        });
        
        loaderEtat(totActu);
        
        if($('#etat0').val()>totActu){
            if(a[1]>n){       
                var charge = parseFloat(parseInt(totActu)*100/parseInt($('#etat0').val()));
                var pourc = charge.toFixed(2);
                $('#txcharg2').html(''+pourc+'');
                if(a[2]==0)
                    supCatalogPS(n,a[1],a[2]);
                else
                    insertCatalogPS(n,a[1],a[2]);
            }                
        }else{
            var temOK = getSecuMajPS(a[1]);
            
            if(temOK==0){
                
                var ec_token = $('#ec_token').val();                
                var XHR = new XHRConnection();
                XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=13&ec_token='+ec_token,'GET');
                
                fenetreModalhide(1,1,0,textSynchroTermine);
                $('.SPECOMPTEUR').remove();
            }
        }
    }else{
        fenetreModalhide(2,1,0,textSynchroErreur);
    }
}

function loaderEtat2(totActu)
{
        $(".bar").css("width", totActu.toFixed(0) + "%");
        $("#pourcentage").text(totActu.toFixed(0) + "%");    
}

function loaderEtat1(tot,nbre)
{
    var n = parseInt(nbre)/parseInt(tot) * 100.0;
        $(".bar").css("width", n.toFixed(0) + "%");
        $("#pourcentage").text(n.toFixed(0) + "%");    
}

function loaderEtat(totActu)
{
    var n = parseInt(totActu)/parseInt($('#etat0').val()) * 100.0;
        $(".bar").css("width", n.toFixed(0) + "%");
        $("#pourcentage").text(n.toFixed(0) + "%");    
}

function insertCatalogPS(etp,tot,typ)
{
    if(isNaN($('#txcharg2').html()) == false)
    {        
        var ec_token = $('#ec_token').val();        
        var XHR = new XHRConnection();
        XHR.sendAndLoad('../modules/ecopresto/import.php?etp='+etp+'&total='+tot+'&typ='+typ+'&ec_token='+ec_token,'GET',resultat_CatalogPS);
        return true;
    }
}

function supCatalogPS(etp,tot,typ)
{
    if(isNaN($('#txcharg2').html()) == false)
    {
        var XHR = new XHRConnection();        
        var ec_token = $('#ec_token').val();        
        XHR.sendAndLoad('../modules/ecopresto/import.php?etp='+etp+'&total='+tot+'&typ='+typ+'ec_token='+ec_token,'GET',resultat_CatalogPS);
        return true;
    }
}

resultat_recupInfoMajPS = function (obj)
{
    fenetreModalshow(1,textSynchroEnCours);
    maj = obj.responseText;
	a=maj.split(',');
    insertCatalogPS2(a[0],a[1],a[2]);
}

function fenetreModalshow(progressbar, text)
{
    $('#closeModal').hide();
    $('#closeModalWithoutReload').hide();
    if(progressbar=='0')
        $('.progress').hide();
    else
        $('.progress').show();
    
    $('#titreModal').show();
    $('#titreModal').html(text);
    
    $("#loading-div-background").show();
}

function fenetreModalhide(erreur,refresh,closing,text)
{
    if(closing=='1')
    {
        $("#loading-div-background").hide();
    }
    else
    {
        if(erreur=='1')
            $('#titreModal').html(text);
            
        else if(erreur=='2')
            $('#titreModal').html(text);
            
        if(refresh=='1')
            $('#closeModal').show();
        else if(refresh=='2')
            $('#closeModalWithoutReload').show();
    }
}

function MAJProduct()
{
    fenetreModalshow('1',textMAJProduitsEnCours);
    var tot = 0;
    var i = 0;
    var tabSelect = new Array;
    $('#table1 input:checked.pdtI').each(function(){
        tabSelect[tot]=$(this).attr('rel');
        tot++;
    });
    
    var ec_token = $('#ec_token').val();
    
    if(tot==0)
    {
        var XHR = new XHRConnection();
        XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=7&ec_token='+ec_token,'GET',resultat_selected_null);
    }
    else
    {
        while(i<tot){
            var XHR = new XHRConnection();
            XHR.sendAndLoad('../modules/ecopresto/ajax.php?ref='+tabSelect[i]+'&actu='+i+'&tot='+tot+'&majsel=5&ec_token='+ec_token,'GET',resultat_selected);
            i++;
        }
    }
    
}

resultat_selected_null = function (obj)
{
    maj = obj.responseText;
	a=maj.split(',');
    var tot = a[1];
    var actu = a[0];
    var actu2 = parseInt(actu);
    var charge = (actu*100)/tot;
    loaderEtat2(charge);
    fenetreModalhide('1','2','0',textMAJProduitsTermine);
}

resultat_selected = function (obj)
{
    maj = obj.responseText;
	a=maj.split(',');
    var tot = a[1];
    var actu = a[0];
    var actu2 = parseInt(actu)+1;
    var charge = (actu*100)/tot;
    loaderEtat2(charge);
    if(tot==actu2)
    {
        getProdDelete(actu2, tot);
    }
}

function getProdDelete(i,tot)
{
    var ec_token = $('#ec_token').val();
    var XHR = new XHRConnection();
    XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=6&actu='+i+'&tot='+tot+'&ec_token='+ec_token,'GET', resultat_prodDelete);
    return true;
}

resultat_prodDelete = function (obj)
{
    maj = obj.responseText;
	a=maj.split(',');
    var tot = a[1];
    var actu = a[0];
    var charge = (parseInt(actu)*100)/parseInt(tot);
    loaderEtat2(charge);
    if(tot==actu){
        fenetreModalhide('1','2','0',textMAJProduitsTermine);
    }
}
  

function recupInfoMajPS(nb)
{
    var ec_token = $('#ec_token').val();
    var XHR = new XHRConnection();        
    XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=4&nb='+nb+'&ec_token='+ec_token,'GET',resultat_recupInfoMajPS);
}

function insertCatalogPS2(nb,tot,sup)
{
    if(tot<nb)
        nb = tot;    
    totI = Math.floor(tot/nb);
    totS = Math.floor(sup/nb);
    totGIS = parseInt(tot)+parseInt(sup);
    totIS = parseInt(totI)+parseInt(totS);
    var ec_token = $('#ec_token').val();
    
    $('body').append('<input class="SPECOMPTEUR" type="hidden" id="etat0" value="'+totGIS+'" />');
    $('body').append('<input class="SPECOMPTEUR" type="hidden" id="etat0_0" value="'+totIS+'" />');
    
    var temp = 0;
    while(temp<nb){
        if(temp==0){
            temp++;
            var totA = 0;
            var totP = totI;
        }else if(temp==nb-1){
            temp++;
            var totA = totI*(temp-1);
            var totP = tot;
        }else{
            temp++;
            var totA = totI*(temp-1);
            var totP = totI*temp;
        }
        
        $('body').append('<input type="hidden" rel="'+temp+'" class="cpmMaj secuMaj SPECOMPTEUR" id="etat'+temp+'" value="'+totA+'" />');
        var XHR = new XHRConnection();        
        XHR.sendAndLoad('../modules/ecopresto/import.php?etp='+totA+'&total='+totP+'&typ='+temp+'&ec_token='+ec_token,'GET',resultat_CatalogPS);
    }    
    
    if(sup>0){        
        $('body').append('<input type="hidden" rel="Sup" class="cpmMaj SPECOMPTEUR" id="etatSup" value="0" />');
        var XHR = new XHRConnection();
        XHR.sendAndLoad('../modules/ecopresto/supp.php?etp=0&total='+sup+'&typ=0&ec_token='+ec_token,'GET',resultat_CatalogPS);
        return true;
    }
}

$(document).ready(function()
{   

    $('.NoSendCom').live('click',function(){    
        $('#orderMan'+$(this).attr('rel')).hide();
        var XHR = new XHRConnection();
        var ec_token = $('#ec_token').val();
        XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=10&ec_token='+ec_token+'&typ=0&idc='+$(this).attr('rel'),'GET');
        return true;
    });
    
    $('.sendCom').live('click',function(){
        $('#orderMan'+$(this).attr('rel')).hide();
        var ec_token = $('#ec_token').val();
        var XHR = new XHRConnection();
        XHR.sendAndLoad('../modules/ecopresto/ajax.php?majsel=10&typ=1&ec_token='+ec_token+'&idc='+$(this).attr('rel'),'GET');
        return true;
    });
    
    $('#titreModal').hide();
    $('#titreModalFin').hide();
    $('#titreModalErreur').hide();
    $("#loading-div-background").css({ opacity: 0.8 });
    $('#closeModalButton').live('click',function(){
          $("#loading-div-background").hide();
          location.reload();
    });
    $('#closeModalWithoutReloadButton').live('click',function(){
          $("#loading-div-background").hide();
    });
    $('#closeModal').hide();
    $('#closeModalWithoutReload').hide();

    $(".menuTabButton").click(function () {
	  $(".menuTabButton.selected").removeClass("selected");
	  $(this).addClass("selected");
	  $(".tabItem.selected").removeClass("selected");
	  $("#" + this.id + "Sheet").addClass("selected");
	}); 

    if($('#table1').length>0)
    {
        var props2 = {  
            highlight_keywords: true,  
            on_keyup: true,  
            on_keyup_delay: 1500,  
            refresh_filters: true,  
            sort: false,
            themes_path: '../modules/ecopresto/img/TF_Themes/',
            modules_path: '../modules/ecopresto/js/TF_Modules/',
            col_0: 'none', col_1: 'select', col_2: 'select', col_3: 'none', col_6: 'select',
            btn_reset: true,
            display_all_text: '< Tous >',
            alternate_rows: false,    
            rows_counter: false,  
            enable_default_theme: false,    
            loader: true,  
            loader_html: '<h4 style="color:red;">Loading, please wait...</h4>',
    extensions: {     
                    name:['ColsVisibility'],   
                    src:['../modules/ecopresto/js/TFExt_ColsVisibility/TFExt_ColsVisibility.js'],   
                    description:['Columns visibility manager'],   
                    initialize:[function(o){o.SetColsVisibility();}]   
                },  
                  
    showHide_cols_tick_to_hide: false,
    showHide_enable_tick_all: true,
    btn_showHide_cols_target_id: 'spnColMng',  
    showHide_cols_container_target_id: 'colsMng',  
    showHide_cols_at_start: [3,6,9],  
    btn_showHide_cols_html: '<button>INFO</button>',  
    btn_showHide_cols_close_html: '<button>Close</button>',  
    showHide_cols_text: '',  
    showHide_cols_cont_css_class: 'colsMngContainer',  
    showHide_cols_enable_hover: false
}  
setFilterGrid("table1",props2);  
    }
    $('.cbDerefAll').live('click', function(){
        if($('.cbDerefAll').attr('checked')=='checked')
            $('.cbDeref').attr('checked',true);
        else
            $('.cbDeref').attr('checked',false);          
    });
    
    $('.cbImporterAll').live('click', function(){
        if($('.cbImporterAll').attr('checked')=='checked')
            $('.checBB').attr('checked',true);
        else
            $('.checBB').attr('checked',false);          
    });
    
    var id_shop = $('#idshop').val();
    $('input:checked.pdtI').each(function(){
        var catID = $(this).parent().parent().attr('id');
        var cat = catID.split('___');
        $('#check'+cat[0]+'___'+cat[1]).attr('checked',true);
        $('#check'+cat[0]).attr('checked',true); 
    });
    
    $('.spancat').live('click',function()
    {
        $(this).first().next('select').show();
        $(this).hide();
    });
    
    $('.selSpe').live('change',function(){
        var ec_token = $('#ec_token').val();        
        var XHR = new XHRConnection();
        var ec_token = $('#ec_token').val();
        XHR.sendAndLoad('../modules/ecopresto/ajax.php?ec_token='+ec_token+'&rel='+$(this).attr('rel')+'&cat='+$(this).val()+'&majsel=2&ec_token='+ec_token,'GET',resultat_majInsert);
        return true;
    });
    
    $('.cat').live('click',function(){
        var lstclass = $(this).attr('class');
        var lstclass2 = lstclass.split(' ');   
         
        if($('.ss'+lstclass2[1]).parent().css('display')=='none')
            $('.ss'+lstclass2[1]).parent().show();
        else{
            $('.ss'+lstclass2[1]).parent().hide();
            $('.pdt'+lstclass2[1]).parent().hide();
        }
    });
    
    $('.sscat').live('click',function(){
        var lstclass = $(this).attr('class');
        var lstclass2 = lstclass.split(' ');        
        if($('.pdt'+lstclass2[2]+lstclass2[1]).parent().css('display')=='none')
            $('.pdt'+lstclass2[2]+lstclass2[1]).parent().show();
        else
            $('.pdt'+lstclass2[2]+lstclass2[1]).parent().hide();
    });    
    

    
    $('.checBB').live('click',function(){
        var valCheck = $(this).val();
        var valSplit = valCheck.split('___');
        var etp = 0;        
        
        if(typeof(valSplit[2])!='undefined'){
            if(this.checked){
                $('#check'+valSplit[0]).attr('checked',true);            
                $('#check'+valSplit[0]+'___'+valSplit[1]).attr('checked',true);
                etp = 0;
            }
            else
            {                
                majInsert(valSplit[0]+'___'+valSplit[1]+'___'+valSplit[2],1);
                if($('input:checked.checBB'+valSplit[0]+'___'+valSplit[1]).length==0){
                    $('#check'+valSplit[0]+'___'+valSplit[1]).attr('checked',false);
                }
                if($('input:checked.checBB'+valSplit[0]).length==0){
                    $('#check'+valSplit[0]).attr('checked',false);            
                }
                etp = 1;
            }
        }else if(typeof(valSplit[1])!='undefined'){
            if(this.checked){
                $('#check'+valSplit[0]).attr('checked',true);
                $('.checBB'+valSplit[0]+'___'+valSplit[1]).attr('checked',true);
                etp = 0;
            }
            else
            {                
                majInsert(valSplit[0]+'___'+valSplit[1]+'___'+valSplit[2],1);
                $('.checBB'+valSplit[0]+'___'+valSplit[1]).attr('checked',false);              
                if($('input:checked.checBB'+valSplit[0]).length==0){
                    $('#check'+valSplit[0]).attr('checked',false);            
                }
                etp = 1;
            }
        }else{
            if(this.checked){
                $('.checBB'+valSplit[0]).attr('checked',true);
                etp = 0;
            }else{                
                majInsert(valSplit[0]+'___'+valSplit[1]+'___'+valSplit[2],1);
                $('.checBB'+valSplit[0]).attr('checked',false);  
                etp = 1; 
            }
        }
        if(etp==0){            
            majInsert(valSplit[0]+'___'+valSplit[1]+'___'+valSplit[2],0);
        }
    });
    
    resultat_majInsert = function (obj){
        maj = obj.responseText;
        var majSplit = maj.split(',');
        loaderEtat1(majSplit[0],majSplit[1]);
        if(majSplit[0]==majSplit[1])
            fenetreModalhide('1','2','0',textMAJProduitsTermine);
    }
    function majInsert(idS,etp){
        var valSplit = idS.split('___');
        if(valSplit[2] != 'undefined'){
            return true;
        }else if(valSplit[1]!='undefined'){
            var tot = $('input:checked.checBB'+valSplit[0]+'___'+valSplit[1]).length;
            var tem = 0;
            $('input:checked.checBB'+valSplit[0]+'___'+valSplit[1]).each(function() {
                var lstT = $(this).val();
                tem++;
                lstTT = lstT.split('___');
                if(typeof(lstTT[2])!='undefined'){
                    return true;
                }
            });
        }else{
            var tot = $('input:checked.checBB'+valSplit[0]).length;
            var tem = 0;
            $('input:checked.checBB'+valSplit[0]).each(function() {
                var lstT = $(this).val();
                tem++;
                lstTT = lstT.split('___');
                if(typeof(lstTT[2])!='undefined'){
                    return true;
                }
            });
        }         
    } 
});
