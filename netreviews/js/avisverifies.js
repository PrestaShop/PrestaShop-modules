/**
 * @author    NetReviews (www.avis-verifies.com) - Contact: contact@avis-verifies.com
 * @category  js
 * @copyright NetReviews
 * @license   NetReviews 
 * @date 09/04/2014
 */   

if(typeof jQuery == 'undefined') {	

			function loadScript(url, callback) {
					var script = document.createElement("script")
					script.type = "text/javascript";
					if (script.readyState) { //IE
						script.onreadystatechange = function () {
							if (script.readyState == "loaded" || script.readyState == "complete") {
								script.onreadystatechange = null;
								callback();
							}
						};
					} else { //Others
						script.onload = function () {
							callback();
						};
					}
					script.src = url;
					document.getElementsByTagName("head")[0].appendChild(script);
				}

			var jQueryIsLoaded=false;
			try {
				if(typeof jQuery === 'undefined')
					jQueryIsLoaded=false;
				else
					jQueryIsLoaded=true;
			} catch(err) {
				jQueryIsLoaded=false;
			}

			if(!jQueryIsLoaded){
				//https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js
				loadScript("http://www.avis-verifies.com/review/js/jquery-1.8.2.min.js");
						
			}
			
	
}


jQuery(document).ready(function($) {
					
		$('div#av_product_award #bottom #AV_button').live( "click" ,function(){
			$('#tab_avisverifies').click();

			console.log($('#idTabavisverifies'));
				
			$('html,body').animate({scrollTop: $("#av_more_info_tabs").offset().top}, 'slow');

		});

});

function switchCommentsVisibility(review_number){
	
	comment = $('div[review_number='+review_number+']');
	console.log(review_number);
	comment.toggle();

	//Swich entre "afficher les échanges" et "masquer les échanges"
	$('a#display'+review_number+'[review_number='+review_number+']').toggle();		 			
	$('a#hide'+review_number+'[review_number='+review_number+']').toggle();	
}



