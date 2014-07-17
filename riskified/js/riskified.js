/*
 *  Riskified payments security module for Prestashop. Riskified reviews, approves & guarantees transactions you would otherwise decline.
 *
 *  @author    riskified.com <support@riskified.com>
 *  @copyright 2013-Now riskified.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Riskified 
 */
 var ALERT_TITLE = "Riskified Message";
 var ALERT_BUTTON_TEXT = "Ok";

 function riskifiedAlert(txt) {
	d = document;
	if(d.getElementById("modalContainer")) return;
	mObj = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
	mObj.id = "modalContainer";
	mObj.style.height = d.documentElement.scrollHeight + "px";

	alertObj = mObj.appendChild(d.createElement("div"));
	alertObj.id = "alertBox";

	if(d.all && !window.opera) alertObj.style.top = document.documentElement.scrollTop + "px";
	alertObj.style.left = (d.documentElement.scrollWidth - alertObj.offsetWidth)/2 + "px";
	alertObj.style.visiblity="visible";

	h1 = alertObj.appendChild(d.createElement("h1"));
	h1.appendChild(d.createTextNode(ALERT_TITLE));

	msg = alertObj.appendChild(d.createElement("p"));
  //msg.appendChild(d.createTextNode(txt));
  
  	msg.innerHTML = txt;

  btn = alertObj.appendChild(d.createElement("a"));
  btn.id = "closeBtn";
  btn.appendChild(d.createTextNode(ALERT_BUTTON_TEXT));
  btn.href = "#";
  btn.focus();
  btn.onclick = function() { removeCustomAlert();return false; }
  alertObj.style.display = "block";
}

function removeCustomAlert() {
	document.getElementsByTagName("body")[0].removeChild(document.getElementById("modalContainer"));
}

function sendToRiskified() {
	var confi = confirm("Are you sure that you want to submit an order to Riskified?");  

	if(confi==true) {
		$.ajax({  type: 'POST',     
			url: riskifiedGetPostUrl() + 'modules/riskified/riskajax.php?id_order='+riskifiedGetOrderId()+'&token='+riskifiedGetToken(),     
			data: '',     
			success: function(data)     
			{       
				if(data=='captured'){
					riskifiedAlert('Order was captured successfully');
				}
				else if(data=='submitted')    
				{
					riskifiedAlert('Order was submitted!');
				}
				else if(data)   
				{
					riskifiedAlert(data);
				}
			}   
		}
		);
	}
}
