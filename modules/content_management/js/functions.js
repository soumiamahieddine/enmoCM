var editing;
function editingDoc(elem,user){

    editing = setInterval(function() {checkEditingDoc(elem,'user')}, 500);

}
//load applet in a modal
function loadApplet(url, value)
{
    if (value != '') {
        //console.log('value : '+value);
        displayModal(url, 'CMApplet', 300, 300);
    }
}

function endAttachment()
{
    window.close();
}

function checkEditingDoc(elem, userId) {

    if ($j('#'+elem.id).parent().parent().find('[name=attachNum\\[\\]]').length) {
        var attachNum = $j('#'+elem.id).parent().parent().find('[name=attachNum\\[\\]]').val();
    } else {
        var attachNum = '0';
    }
    
    if ($j('#add').length) {
        var target = $j('#add');
    } else {
        var target = $j('#edit');
    }
    //LOCK VALIDATE BUTTON
    target.prop('disabled', true);
    target.css({"opacity":"0.5"});
    target.val('Edition en cours ...');

    //LOCK EDIT BUTTON (IF MULTI ATTACHMENT)
    $j("[name=templateOffice_edit\\[\\]]").css({"opacity":"0.5"});
    $j("[name=templateOffice_edit\\[\\]]").prop('disabled', true);

    $j.ajax({
       url : 'index.php?display=true&page=checkEditingDoc&module=content_management',
       type : 'POST',
       dataType : 'JSON',
       data: {attachNum : attachNum},
       success : function(response){
            if (response.status == 0) {
                console.log('no lck found!');

                //UNLOCK VALIDATE BUTTON
                target.prop('disabled', false);
                target.css({"opacity":"1"});
                target.val('Valider');
                if ($j("#mailingInfo").is(":visible")) {
                    $j("#addMailing").show();
                    $j("#editMailing").show();
                }

                //UNLOCK EDIT BUTTON (IF MULTI ATTACHMENT)
                $j("[name=templateOffice_edit\\[\\]], #edit").css({"opacity":"1"});
                $j("[name=templateOffice_edit\\[\\]], #edit").prop('disabled', false);

                if($j('#cancelpj').length){
                    $j('#cancelpj').prop('disabled', false);
                    $j('#cancelpj').css({'opacity':'1'});
                }

                //END OF CHECKING APPLET
                console.log('clearInterval');
                clearInterval(editing);

                //CONSTRUCT TAB (IF PDF)
                if ($j("#ongletAttachement li",window.parent.document).eq(attachNum).length) {
                    $j("#ongletAttachement li",window.parent.document).eq(attachNum).after($j("#MainDocument",window.parent.document).clone())
                } else {
                    $j("#MainDocument",window.parent.document).after($j("#MainDocument",window.parent.document).clone());
                }
                
                //$j("#MainDocument",window.parent.document).after($j("#MainDocument",window.parent.document).clone());
                $j("#iframeMainDocument",window.parent.document).after($j("#iframeMainDocument",window.parent.document).clone());
                if ($j("#ongletAttachement #PjDocument_"+attachNum,window.parent.document).length) {
                    $j("#ongletAttachement #PjDocument_"+attachNum,window.parent.document).remove();
                    $j("div #iframePjDocument_"+attachNum,window.parent.document).remove();
                }
                $j("div #iframeMainDocument",window.parent.document).eq(1).attr("id","iframePjDocument_"+attachNum);
                $j("div #iframePjDocument_"+attachNum,window.parent.document).attr("src","index.php?display=true&dir=indexing_searching&page=file_iframe&num="+attachNum+"&#navpanes=0"+response.pdf_version);              

                $j("#ongletAttachement #MainDocument",window.parent.document).eq(1).attr("id","PjDocument_"+attachNum);
                $j("#ongletAttachement #PjDocument_"+attachNum,window.parent.document).html("<span>PJ n°"+(parseInt(attachNum)+1)+"</span>");
                $j("#ongletAttachement #PjDocument_"+attachNum,window.parent.document).click();
                $j("#ongletAttachement [id^=PjDocument_]",window.parent.document).each(function( index ) {
                    $j("#"+this.id,window.parent.document).attr("onclick","activePjTab(this);");
                });
                
            } else {
                console.log('lck found! Editing in progress !');

                //LOCK VALIDATE BUTTON
                target.prop('disabled', true);
                target.css({"opacity":"0.5"});
                target.val('Edition en cours ...');

                //LOCK EDIT BUTTON (IF MULTI ATTACHMENT)
                $j("[name=templateOffice_edit\\[\\]]").css({"opacity":"0.5"});
                $j("[name=templateOffice_edit\\[\\]]").prop('disabled', true);

                if($j('#cancelpj').length){
                    $j('#cancelpj').prop('disabled', true);
                    $j('#cancelpj').css({'opacity':'0.5'});
                }

            }
       },
       error : function(error){
           console.log(error);
           //alert(error);
       }

    });

}
