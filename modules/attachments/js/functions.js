
function showAttachmentsForm(path, width, height) {
    if(typeof(width)==='undefined'){
        width = '800';
    }

    if(typeof(height)==='undefined'){
        height = '480';
    }
    new Ajax.Request(path,
    {
        method:'post',
        parameters: {
            url : path
        },
        onSuccess: function(answer) {
            eval("response = "+answer.responseText);

            if(response.status == 0){
                var modal_content = convertToTextVisibleNewLine(response.content);
                createModalinAttachmentList(modal_content, 'form_attachments', height, width, 'fullscreen');
                eval(response.exec_js);
            } else {
                window.top.$('main_error').innerHTML = response.error;
            }
        }
    });
}

function modifyAttachmentsForm(path, width, height) {
    if(typeof(width)==='undefined'){
        width = '800';
    }

    if(typeof(height)==='undefined'){
        height = '480';
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
                createModalinAttachmentList(modal_content, 'form_attachments', height, width, 'fullscreen'); 
                eval(response.exec_js);
            } else {
                alert(response.error);
            }
        }
    });
}

function createModalinAttachmentList(txt, id_mod, height, width, mode_frm){
    // FIX IE 11
    if($j('#leftPanelShowDocumentIframe')){
       $j('#leftPanelShowDocumentIframe').hide(); 
    }
    if($j('#rightPanelShowDocumentIframe')){
       $j('#rightPanelShowDocumentIframe').hide(); 
    }
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
    
    var layer_height = document.body.clientHeight;
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
            //fenetre.style.width = (window.top.window.document.getElementsByTagName('html')[0].offsetWidth - 30)+"px";
            fenetre.style.width = "98%";
        }
        //fenetre.style.height = (window.top.window.document.getElementsByTagName('body')[0].offsetHeight - 20)+"px";
        fenetre.style.height = "95%";
    }

    Element.update(fenetre,txt);
    
    
    Event.observe(layer, 'mousewheel', function(event){Event.stop(event);}.bindAsEventListener(), true);
    Event.observe(layer, 'DOMMouseScroll', function(event){Event.stop(event);}.bindAsEventListener(), false);
    
    $j('html',window.top.window.document).scrollTop(0);
    $j('body',window.top.window.document).scrollTop(0);
    
    $j('body',window.top.window.document).css("overflow","hidden");
    window.top.window.$(id_mod).focus();
}

function cleanTitle(str) {
    //permet de supprimer les # dans le titre qui bloque l'ouverture de l'applet java
    var res = str.replace(/#/g, " ");
    return(res);
}

// Function to go back in case of error while selecting a document
function historyBack(errorMessage){
    if(confirm(errorMessage)){
        history.back();
    }else history.back();
}

// Function to display the necessary informations after selecting a document
function displayInfos(chrono_number, title, contactid, addressid, placeholder, listchrononumber, hiddendata, res_id){
    var chrono_res = window.opener.$('chrono_number');
    var chrono_tr = window.opener.$('chrono_number_tr');
    var attachment_type_tr = window.opener.$('attachment_type_tr'); // NCH01 new modifs

    var list_chrono = window.opener.$('list_chrono_number');
    var list_chrono_tr = window.opener.$('list_chrono_number_tr');
    // Empty field, in case the users fails the first time and want to retry
    chrono_res.value = '';
    chrono_tr.style.display = 'table-row';
    chrono_res.removeAttribute('readonly');
    chrono_res.removeAttribute('class', 'readonly');

    list_chrono_tr.style.display = 'none';
    attachment_type_tr.style.display = 'table-row'; // NCH01 new modifs

    // Enable the chrono number generator (For Orange customer the generate chrono number button is always enabled)
    window.opener.$('chrono_number_generate').style.display = 'table-row';
    if (!chrono_number) {
        // Add placeholder
        window.opener.$('chrono_number').setAttribute('placeholder', placeholder);
    }else {
        // Fill the chrono_number input with the project response identifier and set it read_only
        chrono_res.value = chrono_number;
        chrono_res.setAttribute('readonly', 'readonly');
        chrono_res.setAttribute('class', 'readonly');
    }

    if (contactid && addressid){
        // Fill the hidden input, to get the contact info after the form validation
        window.opener.$('contactid').value = contactid;
        window.opener.$('addressid').value = addressid;
    }
    // Fill the object input with the current title
    if(title)
        window.opener.$('title').value = title;

    // Add the select with all the chrono number (in case there is more than one response project)
    if(listchrononumber){
        list_chrono_tr.style.display = 'table-row';
        list_chrono.innerHTML = listchrononumber;
        chrono_tr.style.display = 'none';
        window.opener.$('hiddenChronoNumber').value = hiddendata;
        window.opener.$('contact_id_tr').style.display = 'none';
    }

    // Display some usefull infos, fill res_id input and close the modal window
    window.opener.$('title_tr').style.display = 'table-row';
    window.opener.$('chrono_number').style.display = 'table-row';
    if(window.opener.$('close_incoming')){
        window.opener.$('close_incoming').style.display = 'table-row';
    }
    window.opener.$('res_id').value = res_id;

    self.close();
}

function fillHiddenInput(chrono_number){ // Function used to fill the chrono number and contact input when there is more than on response project
    document.getElementById('chrono_number').value = chrono_number;
    var data = document.getElementById('hiddenChronoNumber').value;
    var infos = data.split(',');
    var allInfos = [];
    for(var i = 0; i < infos.length; i++){  // Create the tab with chrono number and contact informations
        allInfos[i] = infos[i].split('#');
        if(allInfos[i][0] === chrono_number){   // Fill the hidden contact input
            if(allInfos[i][1] === '' && allInfos[i][2] === ''){
                document.getElementById('title').value = allInfos[i][3];
            }else{
                document.getElementById('contactid').value = allInfos[i][1];
                document.getElementById('addressid').value = allInfos[i][2];
                document.getElementById('title').value = allInfos[i][3];
                document.getElementById('contact_id_tr').style.display = 'none';
            }
        }
    }
}

function activePjTab(target) {

    $j('[id^=PjDocument_],#MainDocument').css('background-color', 'rgb(197, 197, 197)');
    $j('[id^=PjDocument_],#MainDocument').css('height', '21px');
    $j('[id^=iframePjDocument_],#iframeMainDocument').hide();

    $j('#'+target.id).css('background-color', 'white');
    $j('#'+target.id).css('height', '23px');
    $j('#iframe'+target.id).show();
}
