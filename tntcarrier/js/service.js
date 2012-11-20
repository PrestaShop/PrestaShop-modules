function changeActive(img, id, token)
{
	var deleted;
	if (img.src.indexOf('/img/admin/disabled.gif') >= 0)
	{
		img.src = '../img/admin/enabled.gif';
		deleted = '0';
	}
	else
	{
		img.src = '../img/admin/disabled.gif';
		deleted = '1'
	}
	$.get('../modules/tntcarrier/changeActiveService.php?id='+id+'&deleted='+deleted+'&token='+token);
}