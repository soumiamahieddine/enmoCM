function change_entity(entity_id, path_manage_script, diff_list_id, origin_keyword, display_value_tr)
{
	var div_id = diff_list_id || 'diff_list_div';
	var tr_display_val = display_value_tr || 'table-row';
	var origin_arg = origin_keyword || '';
	var isMandatory = $('destination_mandatory').style.display;
	if(entity_id != null)
	{
		new Ajax.Request(path_manage_script,
		{
		    method:'post',
		    parameters: { id_entity : entity_id,
						  load_from_model : 'true',
						  origin : origin_arg,
						  mandatory : isMandatory
					},
		        onSuccess: function(answer){
				eval("response = "+answer.responseText);
				//alert(answer.responseText);
				if(response.status == 0 )
				{
					var diff_list_tr = $('diff_list_tr');
					var diff_list_div = $(div_id);
					if(diff_list_div != null)
					{
						diff_list_div.innerHTML = response.div_content;
					}
					if(diff_list_tr)
					{
						diff_list_tr.style.display = tr_display_val;
					}
					else
					{
						diff_list_div.style.display = 'block';
					}
				}
				else
				{
					var diff_list_tr = $('diff_list_tr');
					var diff_list_div = $(div_id);
					if(diff_list_div != null)
					{
						diff_list_div.innerHTML = '';
					}
					if(diff_list_tr)
					{
						diff_list_tr.style.display = tr_display_val;
					}
					else
					{
						diff_list_div.style.display = 'none';
					}
					try{
						$('frm_error').innerHTML = response.error_txt;
						}
					catch(e){}
				}
			}
		});
	}
}

function change_diff_list(path_manage_script, display_value_tr, difflist_div, difflist_tr)
{
	var list_div = difflist_div || 'diff_list_div';
	var list_tr = difflist_tr || 'diff_list_tr';
	var tr_display_val = display_value_tr || 'table-row';
	//alert(path_manage_script);
	new Ajax.Request(path_manage_script,
		{
		    method:'post',
		    parameters: {
							load_from_model : 'false'
						},
		        onSuccess: function(answer){
				eval("response = "+answer.responseText);
				//alert(answer.responseText);
				if(response.status == 0 )
				{
					//alert(window.opener.document);
					var diff_list_tr = window.opener.$(list_tr);
					var diff_list_div = window.opener.$(list_div );
					if(diff_list_div != null)
					{
						diff_list_div.innerHTML = response.div_content;
					}
					if(diff_list_tr != null)
					{
						diff_list_tr.style.display = tr_display_val;
					}
					window.close();
				}
				else
				{
					//alert(response.error_txt);
					try{
						$('frm_error').innerHTML = response.error_txt;
						}
					catch(e){}
				}
			}
		});
}
