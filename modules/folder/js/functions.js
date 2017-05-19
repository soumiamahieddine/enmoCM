/**
 * Fill the Folder field in indexing page (basing on the subfolder field value)
 *
 * @param path_to_script String Path to the Ajax script
 **/
function fill_folder(path_to_script)
{
	var subfolder_value = $('subfolder').value;
	var subfolder_id = subfolder_value.substring(subfolder_value.indexOf('(')+1, subfolder_value.indexOf(')'));
	//console.log(subfolder_id);
	if(path_to_script)
	{
		new Ajax.Request(path_to_script,
		{
			method:'post',
			parameters:
			{
				id_subfolder : subfolder_id
			},
			 onSuccess: function(answer){
				eval("response = "+answer.responseText);
				//alert(answer.responseText);
				if(response.status == 0 )
				{
					$('folder').value = response.value;
				}
				else
				{
					if(console)
					{
						console.log('Erreur Ajax');
					}
				}
			}
		});
	}
	else
	{
		if(console)
		{
			console.log('Error fill_folder ');
		}
	}
}


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
			//	alert(answer.responseText);
				var div_to_fill = $('result_folderviewstat');
				div_to_fill.innerHTML = answer.responseText;
			}
		});
	}
}

function valid_histfolder(url)
{
	var type_choice = '';
	var action_id = '';
	var datestart = '';
	var datefin = '';
	var error = '';
	var input = $('folder_id');
	var period_item = $('period');
	var action_item = $('action');
	if(input)
	{
		id_folder = input.value;
	}

	if(period_item && period_item.checked)
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
	else if(action_item && action_item.checked)
	{
		type_choice = 'action';
		var actions_list = $('action_id');
		//console.log(actions_list);
		if(actions_list)
		{
			action_id =  actions_list.options[actions_list.selectedIndex].value;
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
				date_start : datestart,
				date_fin : datefin,
				id_action : action_id,
				folder_id : id_folder
						},
		        onSuccess: function(answer){
			//	alert(answer.responseText);
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

	if(url && foldertype) {		
		$j.ajax({
			url: url,
			type: 'POST',
			data: { 
				foldertype_id : foldertype
			},
			success: function (answer) {
				var div_to_fill = $j('#'+id_div);
				if(div_to_fill) {						
					div_to_fill.html ( answer);
				}
			},
			error: function (error) {
				alert(error);
			}
  		});

	}
}

function search_change_coll(url, id_coll)
{

    if(url && id_coll)
    {
        var search_div = $j('#folder_search_div');
        if(search_div)
        {
            search_div.css("display","block");
        }

        var indexes_div = $j('#opt_indexes');
        if(indexes_div);
        {
            indexes_div.html('');
        }
    
        $j.ajax({
			url: url,
			type: 'POST',
			data: {
				coll_id : id_coll
			},
			success: function (answer) {
				var select_item = $j('#foldertype_id');
				if(select_item) {
					select_item.html(answer);
				}
			},
			error: function (error) {
				alert(error);
			}

    	});
    }
}

function get_ft_opt_index(url)
{
	if(url) {		 
		$j.ajax({
			url: url,
			type: 'POST',
			data: {},
			success: function (answer) {
				var div_to_fill = $j('#opt_index');
				if(div_to_fill) {
					div_to_fill.html(answer);
				}
			},
			error: function (error) {
				alert(error);
			}

		});
    }
}

function checkSubFolder(folderId)
{
	if (folderId == "") {
		$j('#folder_dest_div').css("display","");
	} else {
		$j('#folder_dest_div').css("display","none");
	}
}