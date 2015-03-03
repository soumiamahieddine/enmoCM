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
                createModal(modal_content, 'form_attachments', height, width); 
            } else {
                window.top.$('main_error').innerHTML = response.error;
            }
        }
    });
}

function ValidAttachmentsForm (path, form_id) {

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