function clearAll()
{
	$("#daysDelayed").hide();
	$("#validationDelayed").hide();
}

function displayDays(id)
{
	clearAll()
	$("#"+id).show();
}

function displaySeveral(id)
{
	if (document.getElementById(id).style.display == '')
		document.getElementById(id).style.display = 'none';
	else
		document.getElementById(id).style.display = '';
}