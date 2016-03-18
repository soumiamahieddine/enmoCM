function saveContactToSession(size, prePath) {
  var contactId = $("transmissionContactidAttach" + size).value;

  if (contactId) {
    new Ajax.Request(prePath + "index.php?display=true&module=attachments&page=saveTransmissionContact",
      {
        method:'post',
        parameters: {
          size      : size,
          contactId : contactId
        }
      });
  }
}

function disableTransmissionButton(currentValue) {
  var button = $('newTransmissionButton');
  if (currentValue == "") {
    button.style.opacity = 0.5;
  } else {
    button.style.opacity = 1;
  }
}

function showTransmissionEditButton(currentValue, editParagraph) {
  if (currentValue == "") {
    $(editParagraph).style.display = "none";
  } else {
    $(editParagraph).style.display = "";
  }
}

function hideEditAndAddButton(editParagraph) {
  $(editParagraph).style.display = "none";
  $("add").style.display = "none";
}

function delLastTransmission() {

  var size = $('transmission').childElementCount;

  if (size >= 1) {
    $('transmission').lastElementChild.remove();
  }
  if (size == 1) {
    $('delTransmissionButton').style.display = "none";
  }
}

function addNewTransmission(prePath, docId, langString) {
  if ($('newTransmissionButton').style.opacity == 1) {

    if ($('delTransmissionButton').style.display == "none") {
      $('delTransmissionButton').style.display = "";
    }


    var div = document.createElement('div');
    $('transmission').appendChild(div);
    var size = $('transmission').childElementCount;
    var lang = langString.split("#");

    div.className = "transmissionDiv";
    div.innerHTML = "<hr style='width:85%; height: 4px; margin-left:0px; margin-bottom:10px; margin-top: 10px'>" +
                    "<p>" +
                      "<label>" + lang[0] + "</label>" +
                      "<select name='transmissionType" + size + "' id='transmissionType" + size + "' />" +
                        "<option value='transmission'>Transmission</option>" +
                      "</select>" +
                      "&nbsp;<span class='red_asterisk'><i class='fa fa-star'></i></span>" +
                    "</p>" +
                    "<p>" +
                      "<label>" + lang[1] + "</label>" +
                      "<input type='text' name='DisTransmissionChrono" + size + "' id='DisTransmissionChrono" + size + "' disabled class='readonly' />" +
                      "<input type='hidden' name='transmissionChrono" + size + "' id='transmissionChrono" + size + "' />" +
                    "</p>" +
                    "<p>" +
                      "<label>" + lang[2] + "</label>" +
                      "<select name='transmissionTemplate" + size + "' id='transmissionTemplate" + size + "' style='display:inline-block;' onchange='showTransmissionEditButton(this.options[this.selectedIndex].value, paraEdit" + size + ")'>" +
                      "</select>" +
                      "&nbsp;<span class='red_asterisk'><i class='fa fa-star'></i></span>" +
                    "</p>" +
                    "<p style='display: none' id='paraEdit" + size + "'>" +
                      "<label>&nbsp;</label>" +
                      "<input type='button' value='" + lang[6] + "' name='transmissionEdit" + size + "' id='transmissionEdit" + size + "' class='button' " +
                        "onclick='window.open(\"" + prePath + "index.php?display=true&module=content_management&page=applet_popup_launcher&transmissionNumber=" + size + "&objectType=transmission&objectId=\"+$(\"transmissionTemplate" + size + "\").value+\"&attachType=response_project&objectTable=res_letterbox&contactId=\"+$(\"transmissionContactidAttach" + size + "\").value+\"&addressId=\"+$(\"transmissionAddressidAttach" + size + "\").value+\"&chronoAttachment=\"+$(\"transmissionChrono" + size + "\").value+\"&titleAttachment=\"+$(\"transmissionTitle" + size + "\").value+\"&back_date=\"+$(\"transmissionBackDate" + size + "\").value+\"&resMaster=" + docId + "\", \"\", \"height=200, width=250,scrollbars=no,resizable=no,directories=no,toolbar=no\");" +
                                "hideEditAndAddButton(paraEdit" + size + ")' />" +
                    "</p>" +
                    "<p>" +
                      "<label>" + lang[3] + "</label>" +
                      "<input type='text' name='transmissionTitle" + size + "' id='transmissionTitle" + size + "' value='' />" +
                      "&nbsp;<span class='red_asterisk'><i class='fa fa-star'></i></span>" +
                    "</p>" +
                    "<p>" +
                      "<label>" + lang[4] + "</label>" +
                      "<input type='text' name='transmissionBackDate" + size + "' id='transmissionBackDate" + size + "' onClick='showCalender(this);' value='' style='width: 75px' />" +
                      "<select name='transmissionExpectedDate" + size + "' id='transmissionExpectedDate" + size + "' style='margin-left: 25px;width: 100px' />" +
                        "<option value='Y'>Attente retour</option>" +
                        "<option value='N'>Pas de retour</option>" +
                      "</select>" +
                    "</p>" +
                    "<p>" +
                      "<label>" + lang[5] + " " +
                        "<a href='#' id='create_multi_contact' title='" + lang[7] + "' " +
                        "onclick='new Effect.toggle(\"create_contact_div_attach\", \"blind\", {delay:0.2});return false;' style='display:inline;' >" +
                          "<i class='fa fa-pencil fa-lg' title='" + lang[7] + "'>" +
                          "</i>" +
                        "</a>" +
                      "</label>" +
                      "<input type='text' name='transmissionContact_attach" + size + "' id='transmissionContact_attach" + size + "' value='' " +
                        "onblur='display_contact_card(\"visible\", \"transmissionContactCard" + size + "\");' " +
                        "onkeyup='erase_contact_external_id(\"transmissionContact_attach" + size + "\", \"transmissionContactidAttach" + size + "\");erase_contact_external_id(\"transmissionContact_attach" + size + "\", \"transmissionAddressidAttach" + size + "\");' />" +
                      "<a href='#' id='transmissionContactCard" + size + "' title='Fiche contact' onclick='document.getElementById(\"info_contact_iframe_attach\").src=\"" + prePath + "index.php?display=false&dir=my_contacts&page=info_contact_iframe&contactid=\"+document.getElementById(\"transmissionContactidAttach" + size + "\").value+\"&addressid=\"+document.getElementById(\"transmissionAddressidAttach" + size + "\").value+\"&fromAttachmentContact=Y\";new Effect.toggle(\"info_contact_div_attach\", \"blind\", {delay:0.2});return false;' style='visibility:hidden;'> " +
                        "<i class='fa fa-book fa-lg'></i>" +
                      "</a>" +
                      "<div id='transmission_show_contacts_attach" + size + "' class='autocomplete autocompleteIndex' style='display: none'></div>" +
                    "</p>" +
                    "<input type='hidden' id='transmissionContactidAttach" + size + "' name='transmissionContactidAttach" + size + "' value='' onchange='saveContactToSession(\"" + size + "\", \"" + prePath + "\")' />" +
                    "<input type='hidden' id='transmissionAddressidAttach" + size + "' name='transmissionAddressidAttach" + size + "' value='' />";

    $('transmissionChrono' + size).value = $('chrono').value + "." + String.fromCharCode(64 + size);
    $('DisTransmissionChrono' + size).value = $('chrono').value + "." + String.fromCharCode(64 + size);
    $('transmissionTitle' + size).value = $('title').value;
    getTemplatesForSelect((prePath + "index.php?display=true&module=templates&page=select_templates"), "transmission", "transmissionTemplate" + size);
    launch_autocompleter_contacts_v2(prePath + "index.php?display=true&dir=indexing_searching&page=autocomplete_contacts", "transmissionContact_attach" + size, "transmission_show_contacts_attach" + size, "", "transmissionContactidAttach" + size, "transmissionAddressidAttach" + size)

  }
}

function getTemplatesForSelect(path_to_script, attachment_type, selectToChange)
{
  new Ajax.Request(path_to_script,
    {
      method:'post',
      parameters: {attachment_type: attachment_type},
      onSuccess: function(answer){
        $(selectToChange).innerHTML = answer.responseText;
      }
    });
}

function hide_index(mode_hide, display_val)
{
	var tr_link = $('attach_link_tr');
	var tr_title = $('attach_title_tr');
	var indexes = $('indexing_fields');
	var comp_index = $('comp_indexes');
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
		if(comp_index)
		{
			Element.setStyle(comp_index, {display : 'none'});
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
		if(comp_index)
		{
			Element.setStyle(comp_index, {display : 'block'});
		}
		//hide link and show index
	}
}

function showAttachmentsForm(path, width, height) {
    
    if(typeof(width)==='undefined'){
        var width = '800';
    }
    
    if(typeof(height)==='undefined'){
        var height = '480';
    }  
    
    new Ajax.Request(path,
    {
        method:'post',
        parameters: { url : path
                    },  
        onSuccess: function(answer) {
            eval("response = "+answer.responseText);
           
            if(response.status == 0){
                var modal_content = convertToTextVisibleNewLine(response.content);
                createModal(modal_content, 'form_attachments', height, width, 'fullscreen'); 
            } else {
                window.top.$('main_error').innerHTML = response.error;
            }
        }
    });
}

function get_num_rep(res_id){
	trig_elements = document.getElementsByClassName('trig');
	for (i=0; i<trig_elements.length; i++){
		var id = trig_elements[i].id;
		var splitted_id = id.split("_");
		if (splitted_id.length == 3 && splitted_id[0] == 'ans' && splitted_id[2] == res_id) return splitted_id[1];
	}
	return 0;
}
function ValidAttachmentsForm (path, form_id) {

  console.log(Form.serialize(form_id));
    new Ajax.Request(path,
    {
        asynchronous:false,
        method:'post',
        parameters: Form.serialize(form_id),
        encoding: 'UTF-8',                       
        onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if(response.status == 0){
                destroyModal('form_attachments');

            if ($('viewframe') != undefined) {
                var srcViewFrame = $('viewframe').src;
                $('viewframe').src = srcViewFrame;
            }
				if ($('cur_idAffich')) var num_rep = $('cur_idAffich').value;
				if ($('cur_resId')) var res_id_master = $('cur_resId').value;
				if (response.cur_id) var rep_id = response.cur_id;
				if (num_rep == 0) num_rep = get_num_rep(rep_id);
				
				if($('viewframevalidRep'+num_rep+'_'+rep_id)) {
					if (response.majFrameId > 0){
						$('viewframevalidRep'+num_rep+'_'+rep_id).src = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master="+res_id_master+"&id="+response.majFrameId;	
						if ($('cur_rep')) $('cur_rep').value = response.majFrameId;
						$('viewframevalidRep'+num_rep+'_'+rep_id).id = 'viewframevalidRep'+num_rep+'_'+response.majFrameId;
					}
					else
						$('viewframevalidRep'+num_rep+'_'+rep_id).src = $('viewframevalidRep'+num_rep+'_'+rep_id).src;
				}

				if($('ans_'+num_rep+'_'+rep_id)) {
					$('ans_'+num_rep+'_'+rep_id).innerHTML = response.title;
					if (response.isVersion){
						$('ans_'+num_rep+'_'+rep_id).setAttribute('onclick','updateFunctionModifRep(\''+response.majFrameId+'\', '+num_rep+', '+response.isVersion+');');			
						$('ans_'+num_rep+'_'+rep_id).id = 'ans_'+num_rep+'_'+response.majFrameId;
					}
				}
				
				if ($('cur_idAffich')){
					console.log('test refresh');
					loadNewId('index.php?display=true&module=visa&page=update_visaPage',res_id_master, $('coll_id').value);
				}
                eval(response.exec_js);
            } else {
                alert(response.error);
            }
        }
    });
	
}

function modifyAttachmentsForm(path, width, height) {

    if(typeof(width)==='undefined'){
        var width = '800';
    }
    
    if(typeof(height)==='undefined'){
        var height = '480';
    }  

    new Ajax.Request(path,
    {
        method:'post',
        parameters: { url : path
                    },  
        onSuccess: function(answer) {
            eval("response = "+answer.responseText);
           
            if(response.status == 0){
                var modal_content = convertToTextVisibleNewLine(response.content);
                createModalinAttachmentList(modal_content, 'form_attachments', height, width); 
            } else {
                window.top.$('main_error').innerHTML = response.error;
            }
        }
    });
}

function setFinalVersion(path) {  

var check = $('final').value;

    new Ajax.Request(path,
    {
        asynchronous:false,
        method:'post',
        encoding: 'UTF-8',                       
        onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if(response.status == 0 || response.status == 1){
                eval(response.exec_js);
            } else {
                alert(response.error);
            }
        }
    });
}

function loadSelectedContact() {
    ContactAndAddress = $('selectContactIdRes').value;
    value = ContactAndAddress.split("#");  
    $('contactidAttach').value=value[0];
    $('addressidAttach').value=value[1];
    $('contact_attach').value=value[2];
    $('contact_attach').focus();
}

function createModalinAttachmentList(txt, id_mod, height, width, mode_frm){
    if(height == undefined || height=='') {
        height = '100px';
    }

    if(width == undefined || width=='') {
        width = '400px';
    }

    if( mode_frm == 'fullscreen') {
        width = (screen.availWidth)+'px';
        height = (screen.availHeight)+'px';
    }

    if(id_mod && id_mod!='') {
        id_layer = id_mod+'_layer';
    } else {
        id_mod = 'modal';
        id_layer = 'lb1-layer';
    }

    var tmp_width = width;
    var tmp_height = height;
    var layer_height = window.top.window.$('container').clientHeight;
    var layer_width = window.top.window.document.getElementsByTagName('html')[0].offsetWidth - 5;
    var layer = new Element('div', {'id':id_layer, 'class' : 'lb1-layer', 'style' : "display:block;filter:alpha(opacity=70);opacity:.70;z-index:"+get_z_indexes()['layer']+';width :'+ (layer_width)+"px;height:"+layer_height+'px;'});

    if( mode_frm == 'fullscreen') {
        var fenetre = new Element('div', {'id' :id_mod,'class' : 'modal', 'style' :'top:0px;left:0px;width:'+width+';height:'+height+";z-index:"+get_z_indexes()['modal']+";position:absolute;" });
    } else {
        var fenetre = new Element('div', {'id' :id_mod,'class' : 'modal', 'style' :'top:0px;left:0px;'+'width:'+width+';height:'+height+";z-index:"+get_z_indexes()['modal']+";margin-top:0px;margin-left:0px;position:absolute;" });
    }

    Element.insert(window.top.window.document.body,layer);
    Element.insert(window.top.window.document.body,fenetre);

    if( mode_frm == 'fullscreen') {
        navName = BrowserDetect.browser;
        if (navName == 'Explorer') {
            if (width == '1080px') {
                fenetre.style.width = (window.top.window.document.getElementsByTagName('html')[0].offsetWidth - 55)+"px";
            }
        } else {
            fenetre.style.width = (window.top.window.document.getElementsByTagName('html')[0].offsetWidth - 30)+"px";
        }
        fenetre.style.height = (window.top.window.document.getElementsByTagName('body')[0].offsetHeight - 20)+"px";
    }

    Element.update(fenetre,txt);
    Event.observe(layer, 'mousewheel', function(event){Event.stop(event);}.bindAsEventListener(), true);
    Event.observe(layer, 'DOMMouseScroll', function(event){Event.stop(event);}.bindAsEventListener(), false);
    window.top.window.$(id_mod).focus();
}

function setButtonStyle(radioButton, fileFormat, statusValidateButton) {
    if (radioButton == "yes" && fileFormat != "pdf" && statusValidateButton == "1") {
        $('edit').style.visibility="hidden";
    } else if (radioButton == "no" && fileFormat != "pdf") {
        $('edit').style.visibility="visible";
    }
}