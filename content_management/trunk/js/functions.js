//load applet in a modal
function loadApplet(url, value)
{
    if (value != '') {
        //console.log('value : '+value);
        displayModal(url, 'CMApplet', 300, 300);
    }
}

//applet send a message (error) to Maarch
function sendAppletMsg(theMsg)
{
    if (theMsg != '' && theMsg != ' ') {
        if (window.opener.$('divError')) {
            window.opener.$('divError').innerHTML = theMsg;
        } else if ($('divError')) {
            $('divError').innerHTML = theMsg;
        }
    }
}

//destroy the modal of the applet and launch an ajax script
function endOfApplet(objectType, theMsg)
{
    $('divError').innerHTML = theMsg;
    if (objectType == 'template' || objectType == 'templateStyle' || objectType == 'attachmentVersion' || objectType == 'attachmentUpVersion') {
        endTemplate();
    } else if (objectType == 'resource') {
        endResource();
    } else if (objectType == 'attachmentFromTemplate') {
        endAttachmentFromTemplate();
    } else if (objectType == 'attachment') {
        endAttachment();
    } 
    //destroyModal('CMApplet');
}

function endAttachmentFromTemplate()
{
    //window.alert('template ?');
    if(window.opener.$('list_attach')) {
        window.opener.$('list_attach').src = window.opener.$('list_attach').src;
    }
    window.close();
}

function endAttachment()
{
	if (window.opener.$('cur_idAffich')) var num_rep = window.opener.$('cur_idAffich').value;

	if(window.opener.$('viewframevalidRep'+num_rep)) {
		window.opener.$('viewframevalidRep'+num_rep).src = "index.php?display=true&module=visa&page=view_doc&path=last";			
	}
    window.close();
}

function endTemplate()
{
    //window.alert('template ?');
    window.close();
}

//reload the list div and the document if necessary
function endResource()
{
    //window.alert('resource ?');
    showDivEnd(
        'loadVersions',
        'nbVersions',
        'createVersion',
        '../../modules/content_management/list_versions.php'
    );
    if (window.opener.$('viewframe')) {
        window.opener.$('viewframe').src = window.opener.$('viewframe').src;
    } else if (window.opener.$('viewframevalid')) {
        window.opener.$('viewframevalid').src = window.opener.$('viewframevalid').src;
    }
    
    //window.close();
}

function showDivEnd(divName, spanNb, divCreate, path_manage_script)
{
    new Ajax.Request(path_manage_script,
    {
        method:'post',
        parameters: {res_id : 'test'},
            onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if(response.status == 0 || response.status == 1) {
                if(response.status == 0) {
                    window.opener.$(divName).innerHTML = response.list;
                    window.opener.$(spanNb).innerHTML = response.nb;
                    window.opener.$(divCreate).innerHTML = response.create;
                    window.close();
                } else {
                    window.opener.$(divName).innerHTML = 'error = 1 : ' . response.error_txt;
                }
            } else {
                window.opener.$(divName).innerHTML = 'error > 1 : ' . response.error_txt;
                try {
                    //window.opener.$(divName).innerHTML = response.error_txt;
                }
                catch(e){}
            }
        }
    });
}

function showDiv(divName, spanNb, divCreate, path_manage_script)
{
    new Ajax.Request(path_manage_script,
    {
        method:'post',
        parameters: {res_id : 'test'},
            onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if(response.status == 0 || response.status == 1) {
                if(response.status == 0) {
                    if ($(divName)) {
                        $(divName).innerHTML = response.list;
                        $(spanNb).innerHTML = response.nb;
                        $(divCreate).innerHTML = response.create;
                    } else {
                        window.opener.$(divName).innerHTML = response.list;
                        window.opener.$(spanNb).innerHTML = response.nb;
                        window.opener.$(divCreate).innerHTML = response.create;
                    }
                } else {
                    //
                }
            } else {
                try {
                    //$(divName).innerHTML = response.error_txt;
                }
                catch(e){}
            }
        }
    });
}
