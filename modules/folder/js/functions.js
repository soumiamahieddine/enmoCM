function valid_viewfolder(url)
{
	var type_choice = '';
	var user_id = '';
	var datestart = '';
	var datefin = '';
	var error = '';
	var foldertype_item = $('foldertype');
	var usergroup_item = $('usergroup');
	var user_item = $('user');
	var period_item = $('period');
	if( foldertype_item && foldertype_item.checked)
	{
		type_choice = 'foldertype';
	}
	else if( usergroup_item && usergroup_item.checked)
	{
		type_choice = 'usergroup';
	}
	else if(user_item && user_item.checked)
	{
		type_choice = 'user';
		var user_id_item = $('user_id');
		if(user_id_item)
		{
			user_id = user_id_item.value;
		}
		if(user_id == '')
		{
			error = 'empty_user_id';
		}
	}
	else if(period_item && period_item.checked)
	{
		type_choice = 'period';
		var datestart_item = $('datestart');
		if(datestart_item)
		{
			datestart = datestart_item.value;
		}
		var datefin_item = $('datefin');
		if(datefin_item)
		{
			datefin = datefin_item.value;
		}
	}
	else
	{
		error = 'empty_type_choice';
	}
	if(type_choice  != '' && url && error == '' )
	{
		new Ajax.Request(url,
		{
		    method:'post',
		    parameters: {
				type_report : type_choice,
				user : user_id,
				date_start : datestart,
				date_fin : datefin
						},
		        onSuccess: function(answer){
				var div_to_fill = $('result_folderviewstat');
				div_to_fill.innerHTML = answer.responseText;
			}
		});
	}
}

/**
 * Gets the indexes for a given folder type and fills a div with it
 *
 * @param url String Url to the Ajax script
 * @param foldertype String Folder type identifier
 **/
function get_folder_index(url, foldertype, id_div)
{
	if(url && foldertype)
	{
		new Ajax.Request(url,
		{
			method:'post',
			parameters: {
				foldertype_id : foldertype
			},
			onSuccess: function(answer){
				var div_to_fill = $(id_div);

				if(div_to_fill)
				{
					div_to_fill.innerHTML = answer.responseText;
				}
			}
		});
	}
}

function search_change_coll(url, id_coll)
{
	if(url && id_coll)
	{
		var search_div = $('folder_search_div');
		if(search_div)
		{
			search_div.style.display = 'block';
		}

		var indexes_div = $('opt_indexes');
		if(indexes_div);
		{
			indexes_div.innerHTML = '';
		}
		new Ajax.Request(url,
		{
			method:'post',
			parameters: {
				coll_id : id_coll
					},
					onSuccess: function(answer){
						var select_item = $('foldertype_id');
						if(select_item)
						{
							select_item.update(answer.responseText);
						}
					}
		});
	}
}

function get_ft_opt_index(url)
{
	if(url) {
        new Ajax.Request(url,
        {
            method:'post',
            parameters: {},
			onSuccess: function(answer){
				var div_to_fill = $('opt_index');
				if(div_to_fill)
				{
					div_to_fill.innerHTML = answer.responseText;
				}
			}
        });
    }
}

function checkSubFolder(folderId)
{
	if (folderId == "")
		$('folder_dest_div').style.display = "";
	else
		$('folder_dest_div').style.display = "none";
}