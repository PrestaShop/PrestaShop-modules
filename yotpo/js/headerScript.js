function inIframe () {
    try {
    	return window.self !== window.top;
    } catch (e) {
    	return true;
    }
}
var inIframe = inIframe();
if (inIframe) {
	window['yotpo_testimonials_active'] = true;
}
(function(){function e(){var e=document.createElement('script');e.type='text/javascript',e.async=!0,e.src='//staticwww.yotpo.com/js/yQuery.js';var t=document.getElementsByTagName('script')[0];t.parentNode.insertBefore(e,t)}window.attachEvent?window.attachEvent('onload',e):window.addEventListener('load',e,!1)})();
