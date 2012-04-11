//load applet in a modal
function loadApplet(url)
{
    displayModal(url, 'CMApplet', 300, 300);
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
function endOfApplet(objectType)
{
    if (objectType == 'template' || objectType == 'templateStyle') {
        endTemplate();
    } else if (objectType == 'resource') {
        endResource();
    }
    destroyModal('CMApplet');
}

function endTemplate()
{
    window.alert('template ?');
}

function endResource()
{
    //window.alert('resource ?');
    showDiv(
        'loadVersions', 
        'nbVersions', 
        'createVersion', 
        '../../modules/content_management/list_versions.php'
    );
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
