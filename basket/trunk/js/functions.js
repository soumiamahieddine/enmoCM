<!--
//document.write('<script type="text/javascript" src="js/scrollbox.js"></script>');
 function show_config_action( id_action, inside_scrollbox, show_when_disabled)
 {
	//var div_to_show = $('action_'+id_action);
	var chkbox = $('checkbox_'+id_action)

	if(chkbox && (chkbox.disabled == false || show_when_disabled == true) )
	{
		var main_div = $('config_actions');

		if(main_div != null)
		{
			if(inside_scrollbox == false)
			{
				var childs = main_div.childNodes;
			}
			else
			{
				var childs = main_div.firstChild.childNodes;
			}
			if(chkbox && chkbox.disabled == true && show_when_disabled == true)
			{
				var actions_uses = $(id_action+'_actions_uses');
				actions_uses.style.display = 'none';
			}
			for(i=0; i < childs.length; i++)
			{
				if(childs[i].id=='action_'+id_action)
				{
					childs[i].style.display = 'block';
				}
				else
				{
					childs[i].style.display = 'none';
				}
			}
		}
	}


 }

 function check_this_box( id_box)
 {
	var to_check = $(id_box);

	if(to_check && to_check.disabled == false)
	{
		to_check.checked = 'checked';
	}
 }


 function manage_actions(id, inside_scrollbox)
 {

	var elem = $('allowed_basket_actions').getElementsByTagName('input');
		//var elem = document.getElementsByClassName('group_action');
	for(var i=0; i < elem.length; i++)
	{
		if(elem[i].id == 'checkbox_'+id )
		{
			elem[i].checked = false;
			elem[i].disabled = true;
		}
		else
		{
			elem[i].disabled = false;
		}
	}

	var main_div = $('config_actions');
	if(main_div != null)
	{
		if(inside_scrollbox == false)
		{
			var childs = main_div.childNodes;
		}
		else
		{
			var childs = main_div.firstChild.childNodes;
		}
		for(i=0; i < childs.length; i++)
		{
			childs[i].style.display = 'none';
		}
	}
 }

 function autocomplete(indice_max, path_to_script)
 {
	for (var i=0;i<indice_max;i++)
	{
		new Ajax.Autocompleter("user_"+i, "options_"+i, path_to_script, {
			method:'get',
			paramName:'UserInput',
			parameters: 'baskets_owner='+$('baskets_owner').value, //si il y a besoin d'autres paramÃ¨tres
			minChars: 2,
			indicator: "indicator_"+i
		});
	}
}


function check_form_baskets(id_form)
{
	var form = $(id_form);
	var reg_user = new RegExp("^.+, .+ (.+)$");
	if(typeof(form) != 'undefined')
	{
		var found = false;
		var elems = document.getElementsByTagName('INPUT');
		for(var i=0; i<elems.length;i++)
		{
			if(elems[i].type == "text" && elems[i].id.indexOf('user_') >= 0)
			{
				if(elems[i].value != '')
				{
					if(reg_user.test(elems[i].value))
					{
						//return 1; // Ok
						found = true;
					}
					else
					{
						return 2;	// user field not in the right format
					}
				}
			}
			else if(elems[i].type == "hidden")
			{
				if(elems[i].id == "baskets_owner" && elems[i].value == '')
				{
					return 3;	 // baskets_owner field is empty
				}

			}
		}
		if(found == true)
		{
			return 1;
		}
		else
		{
			return 4; // All user fields are empty
		}
	}
	else
	{
		return 5; // Error with the form id
	}

}
-->
