function hide_index(mode_hide, display_val)
{
	var tr_link = $('attach_link_tr');
	var tr_title = $('attach_title_tr');
	var indexes = $('indexing_fields');
	if(mode_hide == true)
	{
		if(tr_link && display_val)
		{
			Element.setStyle(tr_link, {display : display_val});
			Element.setStyle(tr_title, {display : display_val});
		}
		if(indexes)
		{
			Element.setStyle(indexes, {display : 'none'});
		}
		//show link and hide index
	}
	else
	{
		if(tr_link && display_val)
		{
			Element.setStyle(tr_link, {display : 'none'});
			Element.setStyle(tr_title, {display : 'none'});
		}
		if(indexes)
		{
			Element.setStyle(indexes, {display : display_val});

		}
		//hide link and show index
	}
}