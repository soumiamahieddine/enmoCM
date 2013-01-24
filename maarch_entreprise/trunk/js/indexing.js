
/**
 * When you choose a doctype, you must calculate the process date limit (Ajax) and execute module services
 *
 * @param doctype_id String Document type identifier
 * @param path_manage_script String Path to the php file called in the Ajax object
 * @param error_empty_type String Error label displayed when the document type is empty
 * @param action_id String Action identifier
 **/
function change_doctype(doctype_id, path_manage_script, error_empty_type, action_id, get_js_script,display_value_tr, id_res, id_coll)
{
    var tmp_res_id = id_res || null;
    var tmp_coll_id = id_coll || null;
    if(doctype_id != null && doctype_id != '' && doctype_id != NaN)
    {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { type_id : doctype_id,
                          id_action : action_id,
                          res_id : tmp_res_id,
                          coll_id : tmp_coll_id
                        },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
            //  alert(answer.responseText);
                if(response.status == 0  || response.status == 1)
                {
                    if(response.status == 0)
                    {
                        var limit_use = $('process_limit_date_use_yes');
                        if(limit_use)
                        {
                            limit_use.checked = true;
                        }
                        var process_date_tr = $('process_limit_date_tr');
                        if(process_date_tr != null)
                        {
                            Element.setStyle(process_date_tr, {display : display_value_tr})
                        }
                        var process_date = $('process_limit_date');
                        if(process_date != null)
                        {
                            process_date.value = response.process_date;
                        }
                    }
                    else
                    {

                        var limit_use = $('process_limit_date_use_no');
                        if(limit_use)
                        {
                            limit_use.checked = true;
                        }
                        var process_date_tr = $('process_limit_date_tr');
                        if(process_date_tr != null)
                        {
                            Element.setStyle(process_date_tr, {display : 'none'})
                        }
                    }
                    var indexes = response.opt_indexes;
                    var div_indexes = $('comp_indexes');
                    if(div_indexes )
                    {
                        div_indexes.update(indexes);
                    }
                    var services_to_exec = response.services;
                    var path_scripts = '';
                    var call_func = '';

                    for(var ind=0; ind < services_to_exec.length;ind++)
                    {
                        path_scripts += services_to_exec[ind]['script'] + '$$';
                        call_func += services_to_exec[ind]['function_to_execute']+'('+services_to_exec[ind]['arguments']+');';
                    }
                    if(call_func != '' && path_scripts != '' && get_js_script != '')
                    {
                        new Ajax.Request(get_js_script,
                        {
                            method:'post',
                            parameters:
                            {
                                scripts : path_scripts
                            },
                             onSuccess: function(answer){
                                eval(answer.responseText+call_func);
                            }
                        });
                    }
                }
                else
                {
                    try{
                        $('frm_error').innerHTML = response.error_txt;
                        }
                    catch(e){}
                }
            }
        });
    }
    else
    {
        try{
            $('frm_error_'+action_id).innerHTML = error_empty_type;
            }
        catch(e){}
    }
}

/**
 * Activates / Desactivates the process date limit
 *
 * @param activate Bool Activate mode = true, desactivate mode = false
 **/
function activate_process_date(activate, display_value_tr)
{
    var process_date = $('process_limit_date_tr');
    var tr_display_value = display_value_tr || 'table-row';
    if(process_date != null)
    {
        if(activate == true)
        {
            Element.setStyle(process_date, {display : tr_display_value});
            //process_date.style.display = tr_display_value;
        }
        else
        {
            //process_date.style.display = 'none';
            Element.setStyle(process_date, {display : 'none'});
        }
    }
}

/**
 * Adapts the indexing form with the category : loads the fields to display in a JSON object
 *
 * @param cat_id String Category identifier
 **/
function change_category(cat_id, display_value_tr, path_manage_script,get_js_script, params_cat)
{
    //Category = RM
    if(cat_id == 'rm_archive')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'doc_date_label', type:'label', state:'display'},
        {id:'originating_entity_tr', type:'tr', state:'display'},
        {id:'originating_subentity_tr', type:'tr', state:'display'},
        {id:'requesting_entity_tr', type:'tr', state:'display'},
        {id:'appraisal_code_tr', type:'tr', state:'display'},
        {id:'apparaisal_duration_tr', type:'tr', state:'display'},
        {id:'folder_id_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'item_name_mandatory', type:'label', state:'display'},
        {id:'originating_entity_mandatory', type:'label', state:'display'},
        {id:'originating_subentity_mandatory', type:'label', state:'hide'},
        {id:'requesting_entity_mandatory', type:'label', state:'display'},
        {id:'appraisal_code_mandatory', type:'label', state:'display'},
        {id:'apparaisal_duration_mandatory', type:'label', state:'display'},
        {id:'folder_id_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = INCOMING
    else if(cat_id == 'incoming')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'priority_tr', type:'tr', state:'display'},
        {id:'doc_date_label', type:'label', state:'hide'},
        {id:'mail_date_label', type:'label', state:'display'},
        {id:'author_tr', type:'tr', state:'hide'},
        {id:'admission_date_tr', type:'tr', state:'display'},
        {id:'nature_id_tr', type:'tr', state:'display'},
        {id:'label_dep_dest', type:'label', state:'display'},
        {id:'label_dep_exp', type:'label', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'display'},
        {id:'dest_contact_choose_label', type:'label', state:'hide'},
        {id:'exp_contact_choose_label', type:'label', state:'display'},
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'dest_contact', type:'label', state:'hide'},
        {id:'exp_contact', type:'label', state:'display'},
        {id:'type_contact_internal', type:'radiobutton', state:'not_checked'},
        {id:'type_contact_external', type:'radiobutton', state:'checked'},
        {id:'market_tr', type:'tr', state:'display'},
        {id:'project_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'hide'},
        {id:'admission_date_mandatory', type:'label', state:'display'},
        {id:'type_contact_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'nature_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'display'},
        {id:'process_limit_date_mandatory', type:'label', state:'display'},
        {id:'market_mandatory', type:'label', state:'hide'},
        {id:'chrono_number', type:'label', state:'hide'},
        {id:'chrono_number_mandatory', type:'label', state:'hide'},
        {id:'chrono_number_tr', type:'tr', state:'hide'},
        {id:'project_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = OUTGOING
    else if(cat_id == 'outgoing')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'priority_tr', type:'tr', state:'display'},
        {id:'doc_date_label', type:'label', state:'hide'},
        {id:'mail_date_label', type:'label', state:'display'},
        {id:'author_tr', type:'tr', state:'hide'},
        {id:'admission_date_tr', type:'tr', state:'hide'},
        {id:'nature_id_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'label_dep_dest', type:'label', state:'hide'},
        {id:'label_dep_exp', type:'label', state:'display'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'display'},
        {id:'dest_contact_choose_label', type:'label', state:'display'},
        {id:'exp_contact_choose_label', type:'label', state:'hide'},
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'dest_contact', type:'label', state:'display'},
        {id:'exp_contact', type:'label', state:'hide'},
        {id:'type_contact_internal', type:'radiobutton', state:'not_checked'},
        {id:'type_contact_external', type:'radiobutton', state:'checked'},
        {id:'market_tr', type:'tr', state:'display'},
        {id:'project_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'hide'},
        {id:'admission_date_mandatory', type:'label', state:'hide'},
        {id:'type_contact_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'nature_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'display'},
        {id:'process_limit_date_mandatory', type:'label', state:'display'},
        {id:'market_mandatory', type:'label', state:'hide'},
        {id:'chrono_number', type:'label', state:'display'},
        {id:'chrono_number_tr', type:'tr', state:'display'},
        {id:'chrono_number_mandatory', type:'label', state:'display'},
        {id:'project_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = INTERNAL
    else if(cat_id == 'internal')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'priority_tr', type:'tr', state:'display'},
        {id:'doc_date_label', type:'label', state:'hide'},
        {id:'mail_date_label', type:'label', state:'display'},
        {id:'author_tr', type:'tr', state:'hide'},
        {id:'admission_date_tr', type:'tr', state:'hide'},
        {id:'nature_id_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'label_dep_dest', type:'label', state:'display'},
        {id:'label_dep_exp', type:'label', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'display'},
        {id:'dest_contact_choose_label', type:'label', state:'hide'},
        {id:'exp_contact_choose_label', type:'label', state:'display'},
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'dest_contact', type:'label', state:'hide'},
        {id:'exp_contact', type:'label', state:'display'},
        {id:'type_contact_internal', type:'radiobutton', state:'checked'},
        {id:'type_contact_external', type:'radiobutton', state:'not_checked'},
        {id:'market_tr', type:'tr', state:'display'},
        {id:'project_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'hide'},
        {id:'admission_date_mandatory', type:'label', state:'hide'},
        {id:'type_contact_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'nature_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'display'},
        {id:'process_limit_date_mandatory', type:'label', state:'display'},
        {id:'market_mandatory', type:'label', state:'hide'},
        {id:'chrono_number', type:'label', state:'hide'},
        {id:'chrono_number_tr', type:'tr', state:'hide'},
        {id:'chrono_number_mandatory', type:'label', state:'hide'},
        {id:'project_mandatory', type:'label', state:'hide'}
        ];
    }
    //Category = MARKET_DOCUMENT
    else if(cat_id == 'market_document')
    {
        var category = [
        {id:'doctype_mail', type:'label', state:'hide'},
        {id:'doctype_res', type:'label', state:'display'},
        {id:'priority_tr', type:'tr', state:'hide'},
        {id:'doc_date_label', type:'label', state:'display'},
        {id:'mail_date_label', type:'label', state:'hide'},
        {id:'author_tr', type:'tr', state:'display'},
        {id:'admission_date_tr', type:'tr', state:'hide'},
        {id:'nature_id_tr', type:'tr', state:'hide'},
        {id:'department_tr', type:'tr', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'hide'},
        {id:'process_limit_date_tr', type:'tr', state:'hide'},
        {id:'diff_list_tr', type:'tr', state:'hide'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'hide'},
        {id:'contact_id_tr', type:'tr', state:'hide'},
        {id:'market_tr', type:'tr', state:'display'},
        {id:'project_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'hide'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'display'},
        {id:'admission_date_mandatory', type:'label', state:'hide'},
        {id:'type_contact_mandatory', type:'label', state:'hide'},
        {id:'contact_mandatory', type:'label', state:'hide'},
        {id:'nature_id_mandatory', type:'label', state:'hide'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'hide'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_mandatory', type:'label', state:'hide'},
        {id:'market_mandatory', type:'label', state:'hide'},
        {id:'chrono_number', type:'label', state:'hide'},
        {id:'chrono_number_tr', type:'tr', state:'hide'},
        {id:'chrono_number_mandatory', type:'label', state:'hide'},
        {id:'project_mandatory', type:'label', state:'hide'}
        ];
    }//Category = POSTINDEXING DOCUMENT
    else if(cat_id == 'postindexing_document')
    {
        var category = [
        {id:'attachment_tr', type:'tr', state:'hide'},
        {id:'attachment_mandatory', type:'label', state:'hide'},
        {id:'category_tr', type:'tr', state:'hide'},
        {id:'doctype_mail', type:'label', state:'display'},
        {id:'doctype_res', type:'label', state:'hide'},
        {id:'priority_tr', type:'tr', state:'display'},
        {id:'doc_date_label', type:'label', state:'hide'},
        {id:'mail_date_label', type:'label', state:'display'},
        {id:'author_tr', type:'tr', state:'hide'},
        {id:'admission_date_tr', type:'tr', state:'display'},
        {id:'nature_id_tr', type:'tr', state:'display'},
        {id:'label_dep_dest', type:'label', state:'display'},
        {id:'label_dep_exp', type:'label', state:'hide'},
        {id:'process_limit_date_use_tr', type:'tr', state:'display'},
        {id:'process_limit_date_tr', type:'tr', state:'display'},
        {id:'department_tr', type:'tr', state:'display'},
        {id:'box_id_tr', type:'tr', state:'display'},
        {id:'contact_choose_tr', type:'tr', state:'display'},
        {id:'dest_contact_choose_label', type:'label', state:'hide'},
        {id:'exp_contact_choose_label', type:'label', state:'display'},
        {id:'contact_id_tr', type:'tr', state:'display'},
        {id:'dest_contact', type:'label', state:'hide'},
        {id:'exp_contact', type:'label', state:'display'},
        {id:'type_contact_internal', type:'radiobutton', state:'not_checked'},
        {id:'type_contact_external', type:'radiobutton', state:'checked'},
        {id:'market_tr', type:'tr', state:'display'},
        {id:'project_tr', type:'tr', state:'display'},
        {id:'category_id_mandatory', type:'label', state:'display'},
        {id:'type_id_mandatory', type:'label', state:'display'},
        {id:'priority_mandatory', type:'label', state:'display'},
        {id:'doc_date_mandatory', type:'label', state:'display'},
        {id:'author_mandatory', type:'label', state:'hide'},
        {id:'admission_date_mandatory', type:'label', state:'display'},
        {id:'type_contact_mandatory', type:'label', state:'display'},
        {id:'contact_mandatory', type:'label', state:'display'},
        {id:'nature_id_mandatory', type:'label', state:'display'},
        {id:'subject_mandatory', type:'label', state:'display'},
        {id:'destination_mandatory', type:'label', state:'display'},
        {id:'diff_list_tr', type:'tr', state:'display'},
        {id:'arbox_id_mandatory', type:'label', state:'hide'},
        {id:'process_limit_date_use_mandatory', type:'label', state:'display'},
        {id:'process_limit_date_mandatory', type:'label', state:'display'},
        {id:'market_mandatory', type:'label', state:'hide'},
        {id:'chrono_number', type:'label', state:'hide'},
        {id:'chrono_number_mandatory', type:'label', state:'hide'},
        {id:'chrono_number_tr', type:'tr', state:'hide'},
        {id:'project_mandatory', type:'label', state:'hide'}
        ];
    }
    if(params_cat)
    {
        process_category(category, display_value_tr, params_cat);
    }
    else
    {
        process_category(category, display_value_tr);
    }

    if(cat_id != null && cat_id != '' && cat_id != NaN)
    {
        //Read the actual box for this category
        //change_box(cat_id);
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { category_id : cat_id
                        },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                //~ console.log('1 '+answer.responseText);
                //~ console.log('2 '+response);
                if(response.status == 0 )
                {
                    var services_to_exec = response.services;
                    //console.log('3 '+print_r(services_to_exec));
                    var path_scripts = '';
                    var call_func = '';

                    for(var ind=0; ind < services_to_exec.length;ind++)
                    {
                        path_scripts += services_to_exec[ind]['script'] + '$$';
                        call_func += services_to_exec[ind]['function_to_execute']+'('+services_to_exec[ind]['arguments']+');';
                    }
                    //console.log(get_js_script);
                    if(call_func != '' && path_scripts != '' && get_js_script != '')
                    {
                        //console.log('OK');
                        new Ajax.Request(get_js_script,
                        {
                            method:'post',
                            parameters:
                            {
                                scripts : path_scripts
                            },
                             onSuccess: function(answer){
                                 //console.log(answer.responseText+call_func);
                                eval(answer.responseText+call_func);
                            }
                        });
                    }
                }
                else
                {
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
 * Shows all the required fields and labels for the category and hides all the others
 *
 * @param category String JSON Object of the fields to display
 **/
function process_category(category, display_value_tr, params_cat)
{
    var tr_display_val = display_value_tr || 'table-row';
    var no_param = true;
    if(params_cat)
    {
        no_param = false;
    }
    if(category != null && category.length > 0)
    {
        for(var i=0; i < category.length; i++)
        {
            var item = $(category[i]['id']);
            if(item != null)
            {
                if(category[i]['state'] == 'hide' )
                {
                    Element.setStyle(item, {display : 'none'});
                    //item.style.display = 'none';
                }
                else if(category[i]['state'] == 'display')
                {
                    if(category[i]['type'] == 'label')
                    {
                        Element.setStyle(item, {display : 'inline'});
                        //item.style.display = 'inline';
                    }
                    else if(category[i]['type'] == 'tr')
                    {
                        Element.setStyle(item, {display : tr_display_val});
                        //item.style.display = tr_display_val;
                    }
                }
                else if(category[i]['state'] == 'checked')
                {
                    item.checked = true;
                    //~ if(  no_param || typeof(params_cat[category[i]['id']]) == undefined ||  typeof(params_cat[category[i]['id']]['onchange']) == undefined || params_cat[category[i]['id']]['onchange'] == true )
                    //~ {
                        //~ item.onchange();
                    //~ }
                    if(  no_param || typeof(params_cat[category[i]['id']]) == undefined ||  typeof(params_cat[category[i]['id']]['onclick']) == undefined || params_cat[category[i]['id']]['onclick'] == true )
                    {
                        item.onclick();
                    }
                }
                else if(category[i]['state'] == 'not_checked')
                {
                    item.checked = false;
                }
            }
        }
    }
}
/** Declaration of the autocompleter object used for the contacts*/
var contact_autocompleter;

/**
 * Launch the Ajax autocomplete object to activate autocompletion on contacts
 *
 * @param path_script String Path to the Ajax script
 **/
function launch_autocompleter_contacts(path_script, id_text, id_div)
{
    var input = id_text || 'contact';
    var div  = id_div || 'show_contacts';
    // Get the parameters
    var params = get_contacts_params();
    // If the object already exists, we delete it to avoid conflict
    try
    {
        delete contact_autocompleter;
    }
    catch(e){ }

    if( path_script)
    {
        // Ajax autocompleter object creation
        contact_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
         method:'get',
         paramName:'Input',
         parameters: params,
         minChars: 2
         });
    }
    else
    {
        if(console != null)
        {
            console.log('error parameters launch_autocompleter_contacts function');
        }
        else
        {
            alert('error parameters launch_autocompleter_contacts function');
        }
    }
}

/**
 * Gets the parameters for the contacts : the table which must be use in the ajax script
 *
 * @return String parameters
 **/
function get_contacts_params(name_radio)
{
    var check = name_radio || 'type_contact';
    var arr = get_checked_values(check);
    var params = '';
    if(arr.length == 0)
    {
        if(console != null)
        {
            console.log('Erreur get_contacts_params, no items checked');
        }
        else
        {
            alert('Erreur get_contacts_params, no items checked');
        }
    }
    else
    {
        if(arr[0] == 'internal')
        {
            params = 'table=users';
        }
        else if(arr[0] == 'external')
        {
            params = 'table=contacts';
        }
        else
        {
            params = 'table=contacts';
        }
    }
    //window.alert(params);
    return params;
}

/**
 * Update the parameters of the contact_autocompleter object
 *
 **/
function update_contact_autocompleter()
{
    var params = get_contacts_params();
    if( contact_autocompleter  )
    {
        if(contact_autocompleter.options.defaultParams == null)
        {
            contact_autocompleter.options.defaultParams = 'table=contacts';
        }
        var old_param = contact_autocompleter.options.defaultParams;
        var new_param =old_param.substring(0, old_param.indexOf('table=') -1) ;
        if(new_param && new_param !='')
        {
            new_param += '&'+params;
        }
        else
        {
            new_param =  params;
        }
        contact_autocompleter.options.defaultParams = new_param;
    }
}

/**
 * Open in a popup the contact or user card
 *
 * @param path_contact_card String Path to the contact card
 * @param path_user_card String Path to the user card
 **/
function open_contact_card(path_contact_card,path_user_card)
{
    var contact_value = $('contact').value;
    var arr = get_checked_values('type_contact');
    if(arr.length == 0)
    {
        if(console != null)
        {
            console.log('Erreur launch_autocompleter_contacts, no items checked');
        }
        else
        {
            alert('Erreur launch_autocompleter_contacts, no items checked');
        }
    }
    else
    {
        var contact_id = contact_value.substring(contact_value.indexOf('(')+1, contact_value.indexOf(')'));

        if(arr[0] == 'internal')
        {
            window.open(path_user_card+'&id='+contact_id, 'contact_info', 'height=450, width=600,scrollbars=no,resizable=yes');
        }
        else if(arr[0] == 'external')
        {
            window.open(path_contact_card+'&id='+contact_id, 'contact_info', 'height=600, width=600,scrollbars=yes,resizable=yes');
        }
    }
}

function create_contact(path_create, id_action)
{
    $('type_contact_external').checked = true;
    $('type_contact_external').onclick();

    var contact_frm = $('indexingfrmcontact');
    if(contact_frm)
    {
        var corporate = 'Y' ;
        if($('is_corporate_N').checked == true)
        {
            corporate = 'N' ;
        }
        var title_val = $('title').value;
        var society_val = $('society').value;
        var phone_val = $('phone').value;
        var mail_val = $('mail').value;
        var num_val =  $('num').value;
        var street_val = $('street').value;
        var add_comp_val = $('add_comp').value;
        var cp_val = $('cp').value;
        var town_val = $('town').value;
        var country_val = $('country').value;
        var comp_data_val = $('comp_data').value;
        var lastname_val = $('lastname').value;
        var firstname_val = $('firstname').value;
        var func_val = $('function').value;

        new Ajax.Request(path_create,
        {
            method:'post',
            asynchronous:false,
            parameters: {
                is_corporate : corporate,
                title : title_val,
                society : society_val,
                phone : phone_val,
                mail : mail_val,
                num : num_val,
                street : street_val,
                add_comp : add_comp_val,
                cp : cp_val,
                town : town_val,
                country : country_val,
                comp_data : comp_data_val,
                lastname : lastname_val,
                firstname : firstname_val,
                func : func_val
                },
                    onSuccess: function(answer){
                    eval("response = "+answer.responseText);
                //  alert(answer.responseText);
                    if(response.status == 0 )
                    {
                        var contact = $('contact');
                        if(contact)
                        {
                            contact.value = response.value;
                            $('contact_card').style.visibility = 'visible';
                            new Effect.toggle('create_contact_div', 'blind', {delay:0.2});
                            clear_form('indexingfrmcontact');
                        }
                    }
                    else
                    {
                        try{
                            $('frm_error_'+id_action).innerHTML = response.error_txt;
                            }
                        catch(e){}
                    }
                }
        });
    }

}

/**
 * When you change the contact type, the table used for the autocompletion change
 *
 * @param path_autocomplete String Path to the Ajax script
 **/
function change_contact_type(path_autocomplete, empty_contact_div, id_internal, id_external, id_contact)
{
    var contact_id = id_contact || 'contact';
    var external_type = id_external || 'type_contact_external';
    var internal_type = id_internal || 'type_contact_internal';
    var create_contact = $('create_contact');
    var contact_card = $('contact_card');
    if(typeof(empty_contact_div) == 'undefined' || empty_contact_div == null)
    {
        var empty_contact = true ;
    }
    else
    {
        var empty_contact = empty_contact_div ;
    }
    if(create_contact)
    {
        if($(internal_type).checked == true)
        {
            Element.setStyle(create_contact, {display : 'none'});
            //create_contact.style.display = 'none';
        }
        else if($(external_type ).checked == true)
        {
            Element.setStyle(create_contact, {display : 'inline'});
            //create_contact.style.display = 'inline';
        }
    }
    if(empty_contact)
    {
        $(contact_id).value='';
    }
    display_contact_card('hidden');
    update_contact_autocompleter();
}

function init_validation(path_autocomplete_contact, display_value_tr, path_manage_script, get_js_script)
{
    var param_cat = {'type_contact_internal' : {'onclick' : false} , 'type_contact_external' : {'onclick' : false}};
    change_category($('category_id').value, display_value_tr,path_manage_script,get_js_script, param_cat);
    change_contact_type(path_autocomplete_contact, false);
    $('contact').onchange();
    //$('destination').onchange();
    //$('market').onchange();
}

function clear_error(id_error)
{
    //console.log("'"+id_error+"'");
    var error_div = $(id_error);
    //console.log(error_div);
    if(error_div)
    {
        error_div.update();
    }
}

function display_contact_card(mode)
{
    var contact_card = $('contact_card');
    if(contact_card && (mode == 'hidden' || mode == 'visible'))
    {
        Element.setStyle(contact_card, {visibility : mode});
    }
}

function changeCycle(path_manage_script) {
    var policy_id = $('policy_id');
    if(policy_id.value != '') {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { policy_id : policy_id.value
                        },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                if(response.status == 0 || response.status == 1) {
                    if(response.status == 0) {
                        //response.selectClient;
                        $('cycle_div').innerHTML = response.selectCycle;
                    } else {
                        //
                    }
                } else {
                    try {
                        $('frm_error').innerHTML = response.error_txt;
                    }
                    catch(e){}
                }
            }
        });
    } //else {
        //if($('policy_id')) {
            //Element.setStyle($('policy_id'), {display : 'none'})
        //}
    //}
}

function getIframeContent(path_manage_script) {
    var iframe = document.getElementById("file_iframe");
    var iframeContent = iframe.contentDocument;
    var templateContent2 = iframeContent.getElementById("template_content_ifr");
    //window.alert(templateContent2);
    if (templateContent2) {
        var templateContent = templateContent2.contentDocument;
        if (templateContent) {
            var templateContentBody = templateContent.getElementById("tinymce");
            //window.alert(templateContentBody.innerHTML);
            new Ajax.Request(path_manage_script,
            {
                method:'post', asynchronous:false,
                parameters: { template_content : templateContentBody.innerHTML
                },
                onSuccess: function(answer){
                    eval("response = "+answer.responseText);
                    //alert(answer.responseText);
                }
            });
        }
    }
}
