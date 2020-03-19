function change_diff_list(
    origin,
    display_value_tr,
    difflist_div,
    difflist_tr,
    category,
    specific_role,
    entity_id_dest
) {
    if(category === undefined){
        category = '';
    }
    if(specific_role === undefined){
        specific_role = '';
    }
    var list_div = difflist_div || 'diff_list_div';
    var list_tr = difflist_tr || 'diff_list_tr';
    var tr_display_val = display_value_tr || 'table-row';

    new Ajax.Request(
		'index.php?display=true&module=entities&page=load_listinstance',
        {
            method:'post',
            parameters: {
			origin : origin,
            category : category,
            specific_role : specific_role
            },
            onSuccess: function(answer){
                eval("response = "+answer.responseText);
                if(response.status == 0 )
                {
                    var diff_list_tr = window.opener.$(list_tr);
                    var diff_list_div = window.opener.$(list_div);

                    if(diff_list_tr != null)
                    {
                        diff_list_tr.style.display = tr_display_val;
                    }

                    window.opener.$j('#destination').val(entity_id_dest);
                    window.opener.$j('#destination').trigger("chosen:updated");

                    if(window.opener.parent.document.getElementById('iframe_tab')){
                        diff_list_div.innerHTML = response.div_content_action;
                    } else if(window.opener.parent.document.getElementById('uniqueDetailsIframe')){
                        diff_list_div.innerHTML = response.div_content;
                        window.opener.$j('#save_list_diff').click();
                    } else {
                        diff_list_div.innerHTML = response.div_content;
                    }
                    window.close();
                }
                else
                {
                    try{
                        $('frm_error').innerHTML = response.error_txt;
                        }
                    catch(e){}
                }
            }
        }
	);
}

function isIdToken(value)
{
    var token = value.match(/[\w_]+/g);
    if(!token)
        return false;
    if(token[0] != value)
        return false;
    else 
        return true;
    
}

function validate_difflist_type() {
  var main_error = $('main_error'); 
  main_error.innerHTML = '';
  
  var difflist_type_id = $('difflist_type_id').value;
  var difflist_type_label = $('difflist_type_label').value;
  
  var allow_entities = 'N';
  
  if($('allow_entities').checked)
	allow_entities = 'Y';
  
  var difflist_type_roles = "";
  
  var selected_roles = $('selected_roles');
  for (var i=0;i<selected_roles.length;i++) {
	difflist_type_roles = difflist_type_roles + selected_roles[i].value + " ";
  }
  
  var idValid = isIdToken(difflist_type_id);
  if(idValid == false) {
      main_error.innerHTML = 'Identifiant invalide (A-Z, a-z, 0-9 et _)';
      return;
  }
  main_error.innerHTML = '';
    
  new Ajax.Request(
    'index.php?module=entities&page=admin_difflist_type_validate&display=true',
    { 
      method: 'post',
      parameters: 
      {
        mode : $('mode').value,
		difflist_type_id : difflist_type_id,
        difflist_type_label : difflist_type_label,
		difflist_type_roles : difflist_type_roles,
		allow_entities : allow_entities
      },
      onSuccess: function(transport) {
          var responseText = transport.responseText.replace(new RegExp("(\r|\n)", "g"), "");
          if(responseText)
            $('difflist_type_messages').innerHTML += responseText;
          else  
            goTo('index.php?module=entities&page=admin_difflist_types');
        }
    }
  );
  
}

function loadToolbarEntities(where)
{
    var path_manage_script = 'index.php?display=true&module=entities&page=load_toolbarEntities';
    $j.ajax({
        url : path_manage_script,
        //dataType: "json",
        type : 'POST',
        data: {
            where : where,
        },
        beforeSend: function() {
            //alert('beforesend');
            $j("#entity_id").html('<option value="">chargement en cours ...</option>');
            $j("#entity_id").trigger("chosen:updated");
            //show loading image in toolbar
        },
        success: function(answer){
            eval("response = " + answer);
            if(response.status == 0 ) {
                $j("#entity_id").html(response.resultContent);
                $j("#entity_id").trigger("chosen:updated");
            }
        },
        error: function(){
            //alert('erreur');
        }
    });
}

function moveToDest(user_id,role_id,origin) {
    var pos = $j('#'+user_id+'_'+role_id)[0].rowIndex;

    $j('tr[id$=_dest]').after($j('#'+user_id+'_'+role_id)[0]);

    console.log($j('#diffListUser_'+role_id+' tr:eq('+pos+')'));
    if ($j('#diffListUser_'+role_id+' tr:eq('+pos+')').length) {
        $j('#diffListUser_'+role_id+' tr:eq('+pos+')')[0].before($j('tr[id$=_dest]')[0]);
    }else{
        $j('#diffListUser_'+role_id)[0].append($j('tr[id$=_dest]')[0]);
    }
    
    var destUserId = $j('tr[id$=_dest]')[0].id.replace("_dest","");

    $j('#'+destUserId+'_dest .movedest').append('<i class="fa fa-arrow-up" style="cursor:pointer;" title="" onclick="moveToDest(\''+destUserId+'\',\''+role_id+'\');"></i>');
    
    $j('tr[id$=_dest]')[0].id = destUserId+'_'+role_id;


    $j('#'+user_id+'_'+role_id).removeClass('col');
    $j('#'+user_id+'_'+role_id+' .movedest i').remove();

    $j('#'+user_id+'_'+role_id).prop('onclick',null).off('click');

    $j('#'+user_id+'_'+role_id)[0].id = user_id+'_dest';

    i=0;
    $j("#diffListUser_"+role_id+' tr').each(function() {
        $j('#'+this.id).removeClass('col');

        if (i%2) {
            $j('#'+this.id).addClass('col');
        }
        i++
    });

    $j.ajax({
        url : 'index.php?display=true&module=entities&page=reloadListDiff',
        type : 'POST',
        dataType : 'JSON',
        data: {
            rank: pos,
            origin: origin,
            role_id: role_id
            
        },
        success : function(response){
            if (response.status == 0) {
                
                var userList = response.result;

            } else {
                alert('ERROR!');
            }
        },
        error : function(error){
            console.log('ERROR!');
        }

     });
}
