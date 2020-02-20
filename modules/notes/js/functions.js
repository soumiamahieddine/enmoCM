function showNotesForm(path, width, height) {
    
    if(typeof(width)==='undefined'){
        var width = '800';
    }   


    if(typeof(height)==='undefined'){
        height = '480';
    }  

    $j.ajax({
        url: path,
        type: 'POST',
        success: function (answer) {
           eval("response = "+answer);
            if(response.status == 0){
                var modal_content = convertToTextVisibleNewLine(response.content);
                createModal(modal_content, 'form_notes', height, width); 
            } else {
                window.top.$j('main_error').html( response.error);
            }
        },
        error: function (error) {
            alert(error);
        }

    });
}

function validNotesForm(path, form_id) {

    new Ajax.Request(path,
    {
        asynchronous:false,
        method:'post',
        parameters: Form.serialize(form_id),
        encoding: 'UTF-8',                       
        onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if(response.status == 0){
                if (typeof window.parent['angularSignatureBookComponent'] != "undefined") {
                    // window.parent.angularSignatureBookComponent.componentAfterNotes();
                }
                destroyModal('form_notes');
                var modInfo = $j('<div class="info" id="main_info" onclick="this.remove();">'+response.msg_result+'</div>');
                modInfo.appendTo('body');
                modInfo.css({'display' : 'table-cell'});
                setTimeout(function() {
                    modInfo.remove();
                }, 5000);

                if(parent.$j('.contentShow iframe').length){
                    parent.$j('.contentShow iframe')[0].contentWindow.location.reload(true);
                } else {
                    eval(response.exec_js);
                }
            } else {
                alert(response.error);
            }
        },
        error: function (error) {
            alert(error);
        }

    });
}

function addTemplateToNote(templateNotes, path)
{
    
    $j.ajax({
        url: path,
        type: 'POST',
        data : {templateId : templateNotes},
        success: function (answer) {
           eval("response = "+answer);
            if (response.status == 0) {
                var strContent = response.content;
                var reg = new RegExp(/\\n/gi);
                var strContentReplace = strContent.replace(reg, '\n');
                $j('#notes').val($j('#notes').val() + ' ' + strContentReplace) ;
            } else {
                window.top.$j('main_error').html(response.error);
            }
        },
        error: function (error) {
            alert(error);
        }

    });
}
