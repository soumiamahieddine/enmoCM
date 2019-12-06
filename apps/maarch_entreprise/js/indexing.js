
var $j = jQuery.noConflict();

/**
 * Compute process limit date from the admission date
 *
 **/
function updateProcessDate(path_manage_script)
{
    var admissionDate;
    
    if ($('admission_date')) {
        admissionDate = $('admission_date').value;
    }
    var typeId;
    if ($('type_id')) {
        typeId = $('type_id').value;
    }
    var priorityId;
    if ($('priority')) {
        priorityId = $('priority').value;
    }
    if (admissionDate != null && admissionDate != '' && admissionDate != NaN) {
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: {
                    type_id: typeId,
                    admission_date : admissionDate,
                    priority_id : priorityId
                },
                onSuccess: function(answer){
                eval("response = "+answer.responseText);
                //alert(answer.responseText);
                if (response.status == 0  || response.status == 1) {
                    //console.log(response.process_date);
                    var process_date = $('process_limit_date');
                    if (response.process_date != null) {
                        process_date.value = response.process_date;
                    }
                }
            }
        });
    }
}

/** Declaration of the autocompleter object used for the contacts*/
var contact_autocompleter;

/**
 * Launch the Ajax autocomplete object to activate autocompletion on contacts
 *
 * @param path_script String Path to the Ajax script
 **/
function launch_autocompleter_contacts(path_script, id_text, id_div, cat_id)
{
    var input  = id_text || 'contact';
    var div    = id_div  || 'show_contacts';
    
    var params = get_contacts_params();
    
    if (contact_autocompleter && contact_autocompleter.element == $$('#' + input)[0])
        contact_autocompleter.options.defaultParams = params;
    else if(path_script)
        contact_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
            method:'get',
            paramName:'Input',
            parameters: params,
            minChars: 2
        });
    else return false;
}

/**
 * Launch the Ajax autocomplete object to activate autocompletion on contacts en put address_id and contact_id in an hidden input
 *
 * @param path_script String Path to the Ajax script
 **/
function launch_autocompleter_contacts_v2(path_script, id_text, id_div, cat_id, contact_id, address_id, path_script2)
{
    var input  = id_text || 'contact';
    var div    = id_div  || 'show_contacts';
    
    var params = get_contacts_params();

    if (contact_autocompleter && contact_autocompleter.element == $j('#' + input)[0]) {
        contact_autocompleter.options.defaultParams = params;
    } else if(path_script) {
        contact_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
            method:'get',
            paramName:'Input',
            parameters: params,
            minChars: 2,
            //loading
            frequency: 0.5, // NOTICE THIS
             indicator: 'searching_autocomplete', // AND THIS
             onShow : function(element, update) {
                Effect.Appear(update,{duration:0});
            },
            afterUpdateElement: function (text, li){
                var all_li = li.id;
                var res = all_li.split(",");
                parent.$(contact_id).value = res[0];
                if (typeof (parent.$(contact_id).onchange) == 'function')
                    parent.$(contact_id).onchange();
                parent.$(address_id).value = res[1];
                if (path_script2 && res[1]) {
                    getDepartment(path_script2, res[1]);
                }
                $j("#" + input).css('background-color', li.getStyle('background-color'));
            }
        });
    } else {
        return false;
    }
}

function launch_autocompleter_contacts_search(path_script, id_text, id_div, cat_id, contact_id, address_id)
{
    var input  = id_text || 'contact';
    var div    = id_div  || 'show_contacts';
    
    var params = 'table=contacts';

    if (contact_autocompleter && contact_autocompleter.element == $j('#' + input)[0]) {
        contact_autocompleter.options.defaultParams = params;
    } else if(path_script) {
        contact_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
            method:'get',
            paramName:'Input',
            parameters: params,
            minChars: 2,
            //loading
            frequency: 0.5, // NOTICE THIS
             indicator: 'searching_autocomplete', // AND THIS
             onShow : function(element, update) {
                Effect.Appear(update,{duration:0});
            },
            afterUpdateElement: function (text, li){
                var all_li = li.id;
                var res = all_li.split(",");
                $(contact_id).value = res[0];
                if (typeof ($(contact_id).onchange) == 'function')
                    $(contact_id).onchange();
                    $(address_id).value = res[1];
            }
        });
    } else {
        return false;
    }
}

function launch_autocompleter2_contacts_v2(path_script, id_text, id_div, cat_id, contact_id, address_id, path_script2)
{
    var input  = id_text || 'contact';
    var div    = id_div  || 'show_contacts';
    
    if (contact_autocompleter && contact_autocompleter.element == $j('#' + input)[0])
        contact_autocompleter.options.defaultParams = params;
    else if(path_script)
        contact_autocompleter = new Ajax.Autocompleter2(input, div, path_script, {
            method:'get',
            paramName:'Input',
            parameters: 'table=contacts&contact_type=letter',
            minChars: 2,
            afterUpdateElement: function (text, li){
                var all_li = li.id;
                var res = all_li.split(",");
                if (parent.$(contact_id) == null) {
                    top.$(contact_id).value = res[0];
                    top.$(address_id).value = res[1];
                } else {
                    parent.$(contact_id).value = res[0];
                    parent.$(address_id).value = res[1];
                }
                
                
            }
        });
    else {
        return false;
    }
}

function getDepartment(path_script, address_id) {
    new Ajax.Request(path_script,
    {
        method:'post',
        parameters: { address_id : address_id
                    },
            onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if(response.status == 0 ) {
                parent.$("department_number").value = response.departement_name;
                parent.$("department_number_id").value = response.departement_id;
            }
        }
    });
}

function launch_autocompleter_choose_contact(path_script, id_text, id_div, cat_id, contact_id){
    var input  = id_text || 'contact';
    var div    = id_div  || 'show_contacts';
    
    // var params = get_contacts_params();

    contact_autocompleter = new Ajax.Autocompleter(input, div, path_script, {
        method:'get',
        paramName:'what',
        // parameters: params,
        minChars: 2,
        afterUpdateElement: function (text, li){
            $(contact_id).value = li.id;
        }
    });

}

function putInSessionContact(path_script){
    var contactSelected = $('contactSelect').options[$('contactSelect').selectedIndex].value;
    if (contactSelected == "") {
        alert("Choisissez un contact");
    } else {
        new Ajax.Request(path_script,
        {
            method:'post',
            parameters: {
                contactid : contactSelected
            },
            onSuccess: function(answer){
                    document.location = 'index.php?display=false&dir=my_contacts&page=create_address_iframe';
            }
        });
    }
}

function getContacts(path_script, id, mode){
    new Ajax.Request(path_script,
    {
        method:'post',
        parameters: {
            type_id: id,
            mode: mode
        },
        onSuccess: function(answer){
            if(mode=="view"){
                if (id != "") {
                    $('contacts_created_tr').setStyle({display : 'table-row'});
                    $('contacts_created').innerHTML = answer.responseText;
                } else {
                    $('contacts_created_tr').setStyle({display : 'none'});
                }
            } else if(mode="set"){
                //$('contactSelect').innerHTML = answer.responseText;
            }
        }
    });
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
    var cat = '';
    
    if (parent.document.getElementById('category_id').value != undefined) {
        cat = parent.document.getElementById('category_id').value;
    }else if (parent.parent.document.getElementById('category_id').value != undefined) {
        cat = parent.parent.document.getElementById('category_id').value;
    }else{
        cat = document.getElementById('category_id').value;
    }
    if (arr.length == 0) {
        var contact_type = 'letter';
        params = 'table=contacts&contact_type=' + contact_type;
    } else {
        if (arr[0] == 'internal') {
            params = 'table=users';
        } else if(arr[0] == 'external') {
            params = 'table=contacts';
        } else {
            params = 'table=contacts';
        }
    }
    
    return params;
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

function display_contact_card(mode, id)
{
	if(id){
		var contact_card = $(id);

	}else{
		var contact_card = $('contact_card');
	}

    if ($('contactidAttach')) {
        var contactid = $('contactidAttach').value;
    } else {
        if ($('contactid')) {
            var contactid = $('contactid').value;
        }        
    }
    

    if(contact_card && (mode == 'hidden' || mode == 'visible') && contactid != '')
    {
        Element.setStyle(contact_card, {visibility : mode});
    } else if (contactid == '') {
        Element.setStyle(contact_card, {visibility : 'hidden'});
    }
}

function changeCycle(path_manage_script)
{
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

var addMultiContacts = function (idField, idList, theUrlToListScript, paramNameSrv, minCharsSrv, contact_id, address_id) {
     new Ajax.Autocompleter(
         idField,
         idList,
         theUrlToListScript,
         {
             paramName : paramNameSrv,
             minChars : minCharsSrv,
             tokens : ',',
             //afterUpdateElement : extractEmailAdress,
			parameters : 'table=contacts',
            //loading
            frequency: 0.5, // NOTICE THIS
             indicator: 'searching_autocomplete_multi', // AND THIS
             onShow : function(element, update) {
                Effect.Appear(update,{duration:0});
            },
            afterUpdateElement: function (text, li){
                var all_li = li.id;
                var res = all_li.split(",");
                parent.$(contact_id).value = res[0];
                if(res[1]==' ') {
                    res[1] = 0;
                }
                parent.$(address_id).value = res[1];
            }
         });
 };
 
function updateMultiContacts(path, action, contact, target, array_index, addressid, contactid) {

	new Ajax.Request(path,
	{
		method:'post',
		parameters: { url : path,
					  'for': action,
					  contact: contact,
					  field: target,
					  index: array_index,
                      addressid: addressid,
                      contactid: contactid
					},
		onLoading: function(answer) {
			$('loading_' + target).style.display='inline';
		},
		onSuccess: function(answer) {
			eval("response = "+answer.responseText);
			if(response.status == 0){
				$(target).innerHTML = response.content;
				if (action == 'add') {
                    $j('#contactid').val('');
                    $j('#is_multicontacts').val('');
                    $j('#addressid').val('');
                    $j('#email').val('');
                }
			} else {
				alert(response.error);
				eval(response.exec_js);
			}
			// $('loading_' + target).style.display='none';
		}
	});
}

function set_new_contact_address(path_manage_script, id_div, close,transmission){
    if (close == "true") {
        new Effect.toggle(parent.document.getElementById(id_div), 'blind', {delay:0.2});  
    }
    
    if(transmission != '' & transmission != '0' && transmission != undefined){
        new Ajax.Request(path_manage_script,
    {
        method:'post',
        parameters: {},
        onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if (parent.$('transmissionContact_attach'+transmission)) {
                parent.$('transmissionContact_attach'+transmission).value = response.contactName;
            } else if (parent.$('contact')) {
                parent.$('contact').value = response.contactName;
            }
            if (parent.$('transmissionContactidAttach'+transmission)) {
                parent.$('transmissionContactidAttach'+transmission).value = response.contactId;
            } else if (parent.$('contactid')){
                parent.$('contactid').value = response.contactId;
            }
            if (parent.$('addressidAttach'+transmission)) {
                parent.$('addressidAttach'+transmission).value = response.addressId;
            } else if (parent.$('transmissionAddressidAttach'+transmission)){
                parent.$('transmissionAddressidAttach'+transmission).value = response.addressId;
            }
            getDepartment('index.php?display=true&page=getDepartment', response.addressId);
        }       
    });
    }else{
        new Ajax.Request(path_manage_script,
        {
        method:'post',
        parameters: {},
        onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if (parent.$('contact_attach')) {
                parent.$('contact_attach').value = response.contactName;
            } else if (parent.parent.$('contact_attach')) {
                parent.parent.$('contact_attach').value = response.contactName;
            } else if (parent.$('contact') && parent.$('add_multi_contact_tr').style.display == 'none') {
                parent.$('contact').value = response.contactName;
            } else if (parent.parent.$('contact')  && parent.parent.$('add_multi_contact_tr').style.display == 'none') {
                parent.parent.$('contact').value = response.contactName;
            }
            if (response.rateColor != "") {
                parent.$j('#contact').css('background-color', response.rateColor);
            }
            if (parent.$('email') && parent.$('add_multi_contact_tr').style.display != 'none') {
                parent.$('email').value = response.contactName;
            } else if (parent.parent.$('email') && parent.parent.$('add_multi_contact_tr').style.display != 'none') {
                parent.parent.$('email').value = response.contactName;
            }
            if (parent.$('contactidAttach')) {
                parent.$('contactidAttach').value = response.contactId;
            } else if (parent.parent.$('contactidAttach')){
                parent.parent.$('contactidAttach').value = response.contactId;
            } else if (parent.$('contactid')){
                parent.$('contactid').value = response.contactId;
            } else if (parent.parent.$('contactid')){
                parent.parent.$('contactid').value = response.contactId;
            }
            if (parent.$('addressidAttach')) {
                parent.$('addressidAttach').value = response.addressId;
            } else if (parent.parent.$('addressidAttach')){
                parent.parent.$('addressidAttach').value = response.addressId;
            } else if (parent.$('addressid')){
                parent.$('addressid').value = response.addressId;
            } else if (parent.parent.$('addressid')){
                parent.parent.$('addressid').value = response.addressId;
            }
            getDepartment('index.php?display=true&page=getDepartment', response.addressId);
        }       
    });
    }
}

function affiche_chrono(){
    
    var type_id = document.getElementById('attachment_types').options[document.getElementById('attachment_types').selectedIndex];

    if (type_id.getAttribute('with_chrono') == 'true') { 
        $('get_chrono_display').setStyle({display: 'none'});     
        $('chrono_label').setStyle({display: 'inline'});
        $('chrono_display').setStyle({display: 'inline'});
            new Ajax.Request('index.php?display=true&module=attachments&page=get_chrono_attachment',
                {
                    method:'post',
                    parameters:
                    {
                        type_id : type_id
                    },
                     onSuccess: function(answer){
                        eval("response = "+answer.responseText);
                        $('chrono_display').value=response.chronoNB;
                        $('chrono').value=response.chronoNB;
                    }
                });
    } else if (type_id.getAttribute('get_chrono') != '') {
        $('chrono_display').setStyle({display: 'none'});
        $('chrono_display').value='';     
        $('chrono_label').setStyle({display: 'inline'});
        $('get_chrono_display').setStyle({display: 'inline'});
        $('chrono').value='';
            new Ajax.Request('index.php?display=true&module=attachments&page=get_other_chrono_attachment',
                {
                    method:'post',
                    parameters:
                    {
                        type_id : type_id.value
                    },
                     onSuccess: function(answer){
                        eval("response = "+answer.responseText);
                        $('get_chrono_display').innerHTML=response.chronoList;
                    }
                });
    } else {
        $('chrono_label').setStyle({display: 'none'});
        $('get_chrono_display').setStyle({display: 'none'});
        $('chrono_display').setStyle({display: 'none'});
        $('chrono_display').value='';
        $('chrono').value='';
    }
}

function showEditButton(){

    var modele_id = document.getElementById('templateOffice').options[document.getElementById('templateOffice').selectedIndex];

    if (modele_id.value != '') {
        $('edit').setStyle({display: 'inline'});
        if ($('not_enabled')) {
            $('not_enabled').setStyle({display: 'inline'});
		}
        if ($('choose_file')){
            $('choose_file').setStyle({display: 'none'});
		}
        if ($('file_loaded')) {
            $('file_loaded').setStyle({display: 'none'});
        }
    } else {
        $('edit').setStyle({display: 'none'});
        if ($('not_enabled')) {
            $('not_enabled').setStyle({display: 'none'});
        }
        if ($('file_loaded')) {
            $('file_loaded').setStyle({display: 'none'});
        }
    }
}

function loadInfoContact(){
    var reg = /^\d+$/;
    var pathScript = '';

    if(!reg.test(document.getElementById('contactid').value)){
        pathScript = 'index.php?display=false&page=user_info&id='+document.getElementById('contactid').value;
    }else{
        pathScript = 'index.php?display=false&dir=my_contacts&page=info_contact_iframe&seeAllAddresses&contactid='+document.getElementById('contactid').value+'&addressid='+document.getElementById('addressid').value;
    }
    
    return pathScript;
}

function initSenderRecipientAutocomplete(inputId, mode, alternateVersion, cardId) {
    var route = '../../rest/autocomplete/all';

    $j("#" + inputId).typeahead({
        // order: "asc",
        display: "idToDisplay",
        templateValue: "{{otherInfo}}",
        emptyTemplate: "Aucune donnée pour <b>{{query}}</b>",
        minLength: 3,
        dynamic: true,
        filter: false,
        source: {
            ajax: function (query) {
                return {
                    type: "GET",
                    url: route,
                    data: {
                        search              : query,
                        noContactsGroups    : true,
                        color               : !alternateVersion
                    }
                }
            }
        },
        callback: {
            onClickAfter: function (node, li, item) {
                if (item.type == "entity") {
                    $j("#" + inputId + "_id").val(item.serialId);
                } else {
                    $j("#" + inputId + "_id").val(item.id);
                }
                $j("#" + inputId + "_type").val(item.type);

                if (!alternateVersion) {
                    if (li[0].getStyle('background-color') == 'rgba(0, 0, 0, 0)') {
                        $j("#" + inputId).css('background-color', 'white');
                    } else {
                        $j("#" + inputId).css('background-color', li[0].getStyle('background-color'));
                    }
                    
                }
                if(typeof cardId != 'undefined'){
                    $j("#" + cardId).css('visibility', 'visible');
                }
            },
            onCancel: function () {
                $j("#" + inputId + "_id").val('');
                $j("#" + inputId + "_type").val('');
                $j("#" + inputId).css('background-color', "");
                if(typeof cardId != 'undefined'){
                    $j("#" + cardId).css('visibility', 'hidden');
                }
            },
            onLayoutBuiltBefore: function (node, query, result, resultHtmlList) {
                if (typeof resultHtmlList != "undefined" && result.length > 0) {
                    $j.each(resultHtmlList.find('li'), function (i, target) {
                        if (result[i]['type'] == "contact" && result[i]["rateColor"] != "") {
                            $j(target).css({"background-color" : result[i]["rateColor"]});
                        }
                        if (result[i]['type'] == "contact") {
                            $j(target).find('span').before("<i class='fa fa-building fa-1x'></i>&nbsp;&nbsp;");
                        } else if (result[i]['type'] == "user") {
                            $j(target).find('span').before("<i class='fa fa-user fa-1x'></i>&nbsp;&nbsp;");
                        } else if (result[i]['type'] == "onlyContact") {
                            $j(target).find('span').before("<i class='fa fa-address-card fa-1x'></i>&nbsp;&nbsp;");
                        }
                    });
                }
                return resultHtmlList;
            }
        }
    });
}
