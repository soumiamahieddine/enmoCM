function change_diff_type_box(difftype_id, path_manage_script, diff_list_id, origin_keyword)
{
    var div_id = diff_list_id || 'diff_type_div';
    var origin_arg = origin_keyword || '';
  
    //~ if($('destination_mandatory'))
    //~ {
        //~ var isMandatory = $('destination_mandatory').style.display;
    //~ }
    //~ else
    //~ {
        //~ var isMandatory = "none";
    //~ }
    
    
    if(difftype_id != null)
    {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { id_type : difftype_id,
                          origin : origin_arg,
                    },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                //alert(answer.responseText);
                if(response.status == 0 )
                {
                    var diff_list_div = $(div_id);
					
                    if(diff_list_div != null)
                    {
                        diff_list_div.innerHTML = response.div_content;
                    }
                }
                else
                {
					var diff_list_div = $(div_id);
                    if(diff_list_div != null)
                    {
                        diff_list_div.innerHTML = '';
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


/**
 * Launch the Ajax autocomplete object to activate autocompletion on users 
 *
 * @param path_script String Path to the Ajax script
 * @param mode String Mode : user_id
 **/
function launch_autocompleter_users(path_script, user)
{
	alert('ojo');
	var div  =  'users';
	var input = 'user_form_id';
	// If the object already exists, we delete it to avoid conflict
	//~ try
	//~ {
		//~ if(mode == 'market')
		//~ {
			//~ delete market_autocompleter;
		//~ }
		//~ else if(mode == 'project')
		//~ {
			//~ delete project_autocompleter;
		//~ }
	//~ }
	//~ catch(e){ }
//~ 
	if( path_script)
	{
		user_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
		 method:'get',
		 paramName:'Input',
		 parameters: 'user='+user,
		 minChars: 2
		 });
	
	}
	else
	{
		if(console)
		{
			console.log('error parameters launch_autocompleter_folder function');
		}
	}
}
