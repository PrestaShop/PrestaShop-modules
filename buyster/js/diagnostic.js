function	getDiagnostic(id)
{
	var ref = document.getElementById(id).value;
	$("#loaderDiagnostic").show();
	$("#resultDiagnostic").load(
	"../modules/buyster/diagnostic.php?ref="+ref,
		function(response, status, xhr) 
		{
			$("#loaderDiagnostic").hide();		
			if (status == "error") 
				$("#tntRCLoading").html(xhr.status + " " + xhr.statusText);
		}
	
	);
}