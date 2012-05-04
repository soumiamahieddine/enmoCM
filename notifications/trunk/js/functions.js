function change_properties_box(difftype_id, path_manage_script, diff_list_id, origin_keyword)
{
    var div_id = diff_list_id;
    var origin_arg = origin_keyword || '';
  
    //~ if($('destination_mandatory'))
    //~ {
        //~ var isMandatory = $('destination_mandatory').style.display;
    //~ }
    //~ else
    //~ {
        //~ var isMandatory = "none";
    //~ }
    document.getElementById(div_id).style = "height:0px; width:600px; border:0px;";
    if(difftype_id != null)
    {	
		document.getElementById(div_id).style = "height:200px; width:600px; border:1px solid;";
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

function loadDiffusionProperties(difftype_id, path_manage_script)
{
    var div_id = 'diff_type_div'; 
    if(difftype_id != null)
    {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { id_type : difftype_id,
                    },
                onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if(response.status == 0 )
                {
					var diff_list_div = $(div_id);
					var selected_list = response.div_content.split(',');
					var complete_list = $("frmevent").elements["diffusion_values[]"];
					var diffusion_properties = $("frmevent").elements["diffusion_properties[]"];
					for (i=0;i<complete_list.length;i++)
					{
						complete_list[i].selected = false;
					}
					for (i=0;i<complete_list.length;i++)
					{
						for(j=0;j<selected_list.length;j++)
						{
							if(complete_list[i].value == selected_list[j]) 
							{
								complete_list[i].selected = true;
							}
						}
					}
					Move(complete_list,diffusion_properties);
                    /*if(diff_list_div != null)
                    {
                        diff_list_div.innerHTML = response.div_content;
                    }*/
					
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

function loadAttachforProperties(difftype_id, path_manage_script, attachfor_id)
{
    var div_id = attachfor_id
	if(difftype_id != null)
    {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { id_type : difftype_id,
                    },
                onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if(response.status == 0 )
                {
					var diff_list_div = $(div_id);
					var selected_list = response.div_content.split(',');
					var complete_list = $("frmevent").elements["attachfor_values[]"];
					var attachfor_properties = $("frmevent").elements["attachfor_properties[]"];
					for (i=0;i<complete_list.length;i++)
					{
						complete_list[i].selected = false;
					}
					for (i=0;i<complete_list.length;i++)
					{
						for(j=0;j<selected_list.length;j++)
						{
							if(complete_list[i].value == selected_list[j]) 
							{
								complete_list[i].selected = true;
							}
						}
					}
					Move(complete_list,attachfor_properties);
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
