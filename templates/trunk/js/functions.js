function show_templates(show)
{
	var div = $('templates_div');
	if(div != null)
	{
		if(show == true)
		{
			div.style.display = 'block';
		}
		else
		{
			div.style.display = 'none';
			var list = $('templates');
			list.selectedIndex = 0;
		}
	}
}
