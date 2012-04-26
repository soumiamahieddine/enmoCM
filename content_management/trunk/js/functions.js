//load applet in a modal
function loadApplet(url, value)
{
    if (value != '') {
        console.log('value : '+value);
        displayModal(url, 'CMApplet', 300, 300);
    }
}

//applet send a message (error) to Maarch
function sendAppletMsg(theMsg)
{
    if (theMsg != '' && theMsg != ' ') {
        $('maarchcm').innerHTML = '<h2>' + theMsg + '</h2>';
        $('divError').innerHTML = theMsg;
    }
}

//destroy the modal of the applet and launch an ajax script
function endOfApplet(objectType, theMsg)
{
    //window.alert('endOfApplet');
    $('divError').innerHTML = theMsg;
    if (objectType == 'template' || objectType == 'templateStyle') {
        endTemplate();
    } else if (objectType == 'resource') {
        endResource();
    } else if (objectType == 'attachmentFromTemplate') {
        endAttachmentFromTemplate();
    } else if (objectType == 'attachment') {
        endAttachment();
    }
    destroyModal('CMApplet');
}

function endAttachmentFromTemplate()
{
    //window.alert('template ?');
    if($('list_attach')) {
        $('list_attach').src = $('list_attach').src;
    }
}

function endAttachment()
{
    window.close();
}

function endTemplate()
{
    //window.alert('template ?');
}

//reload the list div and the document if necessary
function endResource()
{
    //window.alert('resource ?');
    showDiv(
        'loadVersions',
        'nbVersions',
        'createVersion',
        '../../modules/content_management/list_versions.php'
    );
    if($('viewframe')) {
        $('viewframe').src = $('viewframe').src;
    }
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
                    $(divName).innerHTML = response.list;
                    $(spanNb).innerHTML = response.nb;
                    $(divCreate).innerHTML = response.create;
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
