var docLockInterval;
function addVisaUser(users) {
    if (!users) {
        nb_visa = $j(".droptarget").length;
        next_visa = nb_visa + 1;
        if(nb_visa == 0){
            $j("#emptyVisa").hide();      
        }
        $j("#visa_content").append('<div class="droptarget" id="visa_' + next_visa + '" draggable="true">'
            +'<span class="visaUserStatus">'
                +'<i class="fa fa-hourglass" aria-hidden="true"></i>'
            +'</span>'
            +'<span class="visaUserInfo">'
                +'<i class="fa fa-user fa-2x" aria-hidden="true"></i> '+ $j("select#visaUserList option:selected").text() +' <sup class="nbRes">'+$j("select#visaUserList option:selected").parent().get( 0 ).label+'</sup>'
                +'<input class="userId" type="hidden" value="' + $j("select#visaUserList option:selected").val() + '"/><input class="visaDate" type="hidden" value=""/>'
            +'</span>'
            +'<span class="visaUserConsigne">'
                +'<input type="text" class="consigne" value=""/>'
            +'</span>'
            +'<span class="visaUserAction">'
                +'<i class="fa fa-trash" aria-hidden="true" onclick="delVisaUser(this.parentElement.parentElement);"></i>'
            +'</span>'
            +'<span id="dropZone">'
                +'<i class="fa fa-exchange fa-2x fa-rotate-90" aria-hidden="true"></i>'
            +'</span>'
        +'</div>');
        
        //prototype
        document.getElementById("visaUserList").selectedIndex = 0;
        Event.fire($("visaUserList"), "chosen:updated");
    } else {
        nb_visa = $j(".droptarget").length;
        next_visa = nb_visa + 1;
        if(nb_visa == 0){
            $j("#emptyVisa").hide();      
        }
        $j("#visa_content").append('<div class="droptarget" id="visa_' + next_visa + '" draggable="true">'
            +'<span class="visaUserStatus">'
                +'<i class="fa fa-hourglass" aria-hidden="true"></i>'
            +'</span>'
            +'<span class="visaUserInfo">'
                +'<i class="fa fa-user fa-2x" aria-hidden="true"></i> ' + users.lastname + ' ' + users.firstname + ' <sup class="nbRes">'+users.entity_id+'</sup>'
                +'<input class="userId" type="hidden" value="' + users.user_id + '"/><input class="visaDate" type="hidden" value=""/>'
            +'</span>'
            +'<span class="visaUserConsigne">'
                +'<input type="text" class="consigne" value="' + users.process_comment + '"/>'
            +'</span>'
            +'<span class="visaUserAction">'
                +'<i class="fa fa-trash" aria-hidden="true" onclick="delVisaUser(this.parentElement.parentElement);"></i>'
            +'</span>'
            +'<span id="dropZone">'
                +'<i class="fa fa-exchange fa-2x fa-rotate-90" aria-hidden="true"></i>'
            +'</span>'
        +'</div>');
        
    }
}
function delVisaUser (target) {
    console.log(target);
  var id = '#'+target.id;
    
  if($j(".droptarget").length == 1){
      $j("#emptyVisa").show(); 
  }
  $j(id).remove();
  
  resetPosVisa();
  
}
function resetPosVisa () {
    $i = 1;
    $j(".droptarget").each(function() {
        this.id = 'visa_' + $i;
        $i++;
    });
}
function updateVisaWorkflow(resId) {
    var $i = 0;
    var userList = [];
    if ($j(".droptarget").length) {
        $j(".droptarget").each(function () {
            //console.log('viseur : '+$j("#"+this.id+" .userdId").val());
            userId = $j("#" + this.id).find(".userId").val();
            userConsigne = $j("#" + this.id).find(".consigne").val();
            userVisaDate = $j("#" + this.id).find(".visaDate").val();
            userPos = $i;
            userList.push({userId: userId, userPos: userPos, userConsigne: userConsigne, userVisaDate: userVisaDate});
            $i++;
        });
    }
    $j.ajax({
       url : 'index.php?display=true&module=visa&page=updateVisaWF',
       type : 'POST',
       dataType : 'JSON',
       data: {
           resId: resId,
            userList: JSON.stringify(userList)
       },
       success : function(response){
            if (response.status == 0) {
                $('divInfoVisa').innerHTML = 'Mise à jour du circuit effectuée';
                $('divInfoVisa').style.display = 'table-cell';
                Element.hide.delay(5, 'divInfoVisa');
                eval(response.exec_js);
            } else if (response.status != 1) {
                alert(response.error_txt)
            }
       },
       error : function(error){
           alert(error);
       }

    });
}
function saveVisaWorkflowAsModel () {
    var $i = 0;
    var userList = [];
    var title = $j("#titleModel").val();
    
    if($j(".droptarget").length){
        $j(".droptarget").each(function() {
            //console.log('viseur : '+$j("#"+this.id+" .userdId").val());
            userId = $j("#"+this.id).find(".userId").val();
            userConsigne = $j("#"+this.id).find(".consigne").val();
            userVisaDate = $j("#"+this.id).find(".visaDate").val();
            userPos = $i;
            userList.push({userId:userId, userPos:userPos, userConsigne:userConsigne, userVisaDate:userVisaDate});        
            $i++;
        });
        $j.ajax({
            url : 'index.php?display=true&module=visa&page=saveVisaModel',
            type : 'POST',
            dataType : 'JSON',
            data: {
                title: title,
                userList: JSON.stringify(userList)
            },
            success : function(response){
                if (response.status == 0) {
                    $('divInfoVisa').innerHTML = 'Modèle enregistré';
                    $('divInfoVisa').style.display = 'table-cell';
                    Element.hide.delay(5, 'divInfoVisa');
                    $j('#modalSaveVisaModel').hide();
                    eval(response.exec_js);
                } else {
                    alert(response.error_txt)
                }
            },
            error : function(error){
                alert(error);
            }

         });
   
    }else{
        alert('Aucun utilisateur dans le circuit !');
    }
    
}
function loadVisaModelUsers() {
    
    var objectId = $j("select#modelList option:selected").val();
    var objectType = 'VISA_CIRCUIT';
    $j.ajax({
            url : 'index.php?display=true&module=visa&page=load_listmodel_visa_users',
            type : 'POST',
            dataType : 'JSON',
            data: {
                objectType: objectType,
                objectId: objectId
            },
            success : function(response){
                if (response.status == 0) {
                    
                    var userList = response.result;
                    if(userList){
                        userList.each(function(user, key) {
                            addVisaUser(user);
                         });  
                    }
                    

                } else {
                    alert(response.error_txt);
                }
            },
            error : function(error){
                alert(error);
            }

         });
         
    //prototype
    document.getElementById("modelList").selectedIndex = 0;
    Event.fire($("modelList"), "chosen:updated");
}

function initDragNDropVisa() {
    document.getElementById("visa_content").addEventListener("dragstart", function(event) {
        $j(".droptarget").css("border","dashed 2px #93D1E4");
        // The dataTransfer.setData() method sets the data type and the value of the dragged data
        event.dataTransfer.setData("Text", event.target.id);

        // Output some text when starting to drag the p element
        //document.getElementById("demo").innerHTML = "Started to drag the p element.";

        // Change the opacity of the draggable element
        event.target.style.opacity = "0.4";
    });

    // While dragging the p element, change the color of the output text
    document.getElementById("visa_content").addEventListener("drag", function(event) {
        //document.getElementById("demo").style.color = "red";
    });

    // Output some text when finished dragging the p element and reset the opacity
    document.getElementById("visa_content").addEventListener("dragend", function(event) {
        //document.getElementById("demo").innerHTML = "Finished dragging the p element.";
        $j(".droptarget").css("border","dashed 2px #93D1E4");
        event.target.style.opacity = "1";
    });


    /* Events fired on the drop target */

    // When the draggable p element enters the droptarget, change the DIVS's border style
    document.getElementById("visa_content").addEventListener("dragenter", function(event) {
        if ( event.target.className == "droptarget") {
            event.target.style.border = "dashed 2px green";
        }
    });

    // By default, data/elements cannot be dropped in other elements. To allow a drop, we must prevent the default handling of the element
    document.getElementById("visa_content").addEventListener("dragover", function(event) {
        event.preventDefault();
    });

    // When the draggable p element leaves the droptarget, reset the DIVS's border style
    document.getElementById("visa_content").addEventListener("dragleave", function(event) {
        if ( event.target.className == "droptarget" ) {
            event.target.style.border = "dashed 2px #ccc";
        }
    });

    /* On drop - Prevent the browser default handling of the data (default is open as link on drop)
       Reset the color of the output text and DIV's border color
       Get the dragged data with the dataTransfer.getData() method
       The dragged data is the id of the dragged element ("drag1")
       Append the dragged element into the drop element
    */
    document.getElementById("visa_content").addEventListener("drop", function(event) {
        event.preventDefault();
        if ( event.target.className == "droptarget" ) {
            /*event.target.style.border = "";
            var data = event.dataTransfer.getData("Text");
            var oldContent = event.target.innerHTML;
            var draggedConsigne = $j('#'+data+' .consigne').val();
            var replaceConsigne = $j('#'+event.target.id+' .consigne').val();
            event.target.innerHTML = document.getElementById(data).innerHTML;
            $j('#'+event.target.id+' .consigne').val(draggedConsigne);
            document.getElementById(data).innerHTML = oldContent;
            $j('#'+data+' .consigne').val(replaceConsigne);*/
            var data = event.dataTransfer.getData("Text");
            var target =event.target.id;
            posData = data.split("_");
            posTarget = target.split("_");
            if(posData[1] > posTarget[1]){
                $j('#'+target).before($j('#'+data));
            }else{
                $j('#'+target).after($j('#'+data));
            }
            resetPosVisa();
            

        }
    });
}

function manageFrame(button) {

	var firstDiv = $("visa_listDoc");
	var secondDiv = $("visa_left");
	var thirdDiv = $("visa_right");

	var right = "fa fa-arrow-circle-o-right fa-2x";
	var left = "fa fa-arrow-circle-o-left fa-2x";

	if (button.id == "firstFrame") {

		if (firstDiv.style.display != "none") {
			firstDiv.style.display = "none";
			button.className = right;
		} else {
			firstDiv.style.display = "";
			button.className = left;
		}
	} else if (button.id == "secondFrame") {

		if (secondDiv.style.display != "none") {
			$("thirdFrame").style.display = "none";
			secondDiv.style.display = "none";
			button.className = right;
		} else {
			$("thirdFrame").style.display = "";
			secondDiv.style.display = "";
			button.className = left;
		}
	} else if (button.id == "thirdFrame") {

		if (thirdDiv.style.display != "none") {
			$("secondFrame").style.display = "none";
			thirdDiv.style.display = "none";
			button.className = left;
		} else {
			$("secondFrame").style.display = "";
			thirdDiv.style.display = "";
			button.className = right;
		}
	}

	if (firstDiv.style.display != "none" && secondDiv.style.display != "none" && thirdDiv.style.display != "none") {
		firstDiv.style.width = "15%";
		$("firstFrame").style.marginLeft = "13.8%";
		secondDiv.style.width = "41%";
		$("secondFrame").style.marginLeft = "40.9%";
		thirdDiv.style.width = "41%";
		$("thirdFrame").style.marginLeft = "0.6%";
	} else if (firstDiv.style.display != "none" && (secondDiv.style.display == "none" || thirdDiv.style.display == "none")) {
		firstDiv.style.width = "15%";
		$("firstFrame").style.marginLeft = "13.8%";
		if (secondDiv.style.display == "none") {
			thirdDiv.style.width = "82%";
			$("secondFrame").style.marginLeft = "0.2%";
		}
		else {
			secondDiv.style.width = "82%";
			$("thirdFrame").style.marginLeft = "83%";
		}
	} else if (firstDiv.style.display == "none" && secondDiv.style.display != "none" && thirdDiv.style.display != "none") {
		$("firstFrame").style.marginLeft = "-0.5%";
		secondDiv.style.width = "48%";
		$("secondFrame").style.marginLeft = "47%";
		thirdDiv.style.width = "48%";
		$("thirdFrame").style.marginLeft = "0.6%"
	} else if (firstDiv.style.display == "none" && (secondDiv.style.display == "none" || thirdDiv.style.display == "none")) {
		$("firstFrame").style.marginLeft = "-0.5%";
		if (secondDiv.style.display == "none") {
			thirdDiv.style.width = "98%";
			$("secondFrame").style.marginLeft = "0.2%";
		}
		else {
			secondDiv.style.width = "98%";
			$("thirdFrame").style.marginLeft = "97.5%";
		}
	}
}

function setTitle(input) {
	input.title = input.value;
}

function	triggerFlashMsg($divName, $msg) {
    var div = $($divName);

    div.innerHTML = $msg;
    div.style.display = 'table-cell';
    Element.hide.delay(5, $divName);
}

/* Fonctions ajoutées par DIS */

//Fonction permettant de lancer les 2 modules Tabricator côte à côte pour les différentes pages d'action (formation circuit de visa, visa ..)
function launchTabri(){
	var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
	var tabricatorLeft = new Tabricator('tabricatorLeft', 'DT');
}



function loadNewId(path_update, newId, collId, idToDisplay) {
    new Ajax.Request(path_update,
            {
                method: 'post',
                parameters: {
                    res_id: newId,
                    coll_id: collId,
                    action: parent.$('action_id').value
                },
                asynchronous: false,
                onSuccess: function (answer) {
                    eval("response = " + answer.responseText);
                    //console.log(response);
                    if (response.status == 0) { //document verouillé
                        alert(response.error);
                    } else {
                        /* Modification dans la liste de gauche */
                        var zone_old = 'list_doc_' + parent.$('cur_resId').value;
                        var zone_new = 'list_doc_' + newId;
                        //console.log(zone_new);
                        clearInterval(docLockInterval);
                        //unlock old doc
                        new Ajax.Request('index.php?display=true&dir=actions&page=docLocker', {method: 'post', parameters: {'AJAX_CALL': true, 'unlock': true, 'res_id': parent.$('cur_resId').value}, onSuccess: function (answer) {/*var cur_url=window.location.href;*/
                                if (cur_url.indexOf('&directLinkToAction') != -1)
                                    cur_url = cur_url.replace('&directLinkToAction', '');
                                window.location.href = cur_url;
                            }});
                        //lock the new doc
                        docLockInterval = setInterval("new Ajax.Request('index.php?display=true&dir=actions&page=docLocker',{ method:'post', parameters: {'AJAX_CALL': true, 'lock': true, 'res_id': " + newId + " } });", 50000);
                        if ($(zone_new)) {
                            $(zone_new).className = 'selectedId';
                        }
                        if ($(zone_old)) {
                            $(zone_old).className = 'unselectedId';
                        }

                        parent.$('cur_resId').value = newId;

                        if (idToDisplay == 'chrono_number') {
                            parent.$('numIdDocPage').innerHTML = parent.$('chrn_id_' + newId).innerHTML;
                        } else {
                            parent.$('numIdDocPage').innerHTML = newId;
                        }
                        //console.log('display: '+idToDisplay);
                    }
                    if (response.status == 1) { //page de visa
                        parent.$('tabricatorLeft').innerHTML = response.left_html;
                        parent.$('tabricatorRight').innerHTML = response.right_html;
                        parent.$("send_action").setAttribute('onclick', response.valid_button);
                        
                        updateFunctionModifRep(response.id_rep, 1, response.is_vers_rep);

                        //console.log("Initialisation onglets de la partie droite");
                        var tabricatorLeft = new Tabricator('tabricatorLeft', 'DT');
                        var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
                        
                        eval(response.exec_js);

                    }

                    if (response.status == 2) { //page préparation circuit
                        parent.$('tabricatorRight').innerHTML = response.right_html;
                        parent.$("send_action").setAttribute('onclick', response.valid_button);
                        var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
                        //console.log("MAJ OK");
                    }

                    if (response.status == 3) { //page envoi mail
                        parent.$('tabricatorRight').innerHTML = response.right_html;
                        parent.$("send_action").setAttribute('onclick', response.valid_button);
                        var tabricatorRight = new Tabricator('tabricatorRight', 'DT');

                        showEmailForm('index.php?display=true&module=sendmail&page=sendmail_ajax_content&mode=add&identifier=' + newId + '&origin=document&coll_id=' + collId + '&size=medium', '820px', '545px', 'sendmail_iframe');
                    }

                    if (response.status == 4) { //page impression dossier
                        parent.$('tabricatorRight').innerHTML = response.right_html;
                        parent.$("send_action").setAttribute('onclick', response.valid_button);
                        var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
                        //console.log("MAJ OK");
                    }
                },
                onFailure: function () {
                    //console.log("Probleme de Mise à jour !");
                }
            });

}

function loadNewId2(path_update, newId, collId, idToDisplay) {
    new Ajax.Request(path_update,
            {
                method: 'post',
                parameters: {
                    res_id: newId,
                    coll_id: collId,
                    action: $('action_id').value
                },
                asynchronous: false,
                onSuccess: function (answer) {
                    eval("response = " + answer.responseText);
                    //console.log(response);
                    if (response.status == 0) { //document verouillé
                        alert(response.error);
                    } else {
                  
                        /* Modification dans la liste de gauche */
                        var zone_old = 'list_doc_' + $('cur_resId').value;
                        var zone_new = 'list_doc_' + newId;
                        //console.log(zone_new);
                        clearInterval(docLockInterval);
                        //unlock old doc
                        new Ajax.Request('index.php?display=true&dir=actions&page=docLocker', {method: 'post', parameters: {'AJAX_CALL': true, 'unlock': true, 'res_id': $('cur_resId').value}, onSuccess: function (answer) {/*var cur_url=window.location.href;*/
                                if (cur_url.indexOf('&directLinkToAction') != -1)
                                    cur_url = cur_url.replace('&directLinkToAction', '');
                                window.location.href = cur_url;
                            }});
                        //lock the new doc
                        docLockInterval = setInterval("new Ajax.Request('index.php?display=true&dir=actions&page=docLocker',{ method:'post', parameters: {'AJAX_CALL': true, 'lock': true, 'res_id': " + newId + " } });", 50000);

                        if($(zone_new)){
                            $(zone_new).className = 'selectedId';
                        }
                        if($(zone_old)){
                            $(zone_old).className = 'unselectedId';
                        }
                        $('cur_resId').value = newId;

                        if (idToDisplay == 'chrono_number') {
                            $('numIdDocPage').innerHTML = $('chrn_id_' + newId).innerHTML;
                        } else {
                            $('numIdDocPage').innerHTML = newId;
                        }
                        //console.log('display: '+idToDisplay);
                    }
                    if (response.status == 1) { //page de visa
                        $('tabricatorLeft').innerHTML = response.left_html;
                        $('tabricatorRight').innerHTML = response.right_html;
                        $("send_action").setAttribute('onclick', response.valid_button);

                        updateFunctionModifRep(response.id_rep, 1, response.is_vers_rep);

                        console.log("Initialisation onglets de la partie droite");
                        var tabricatorLeft = new Tabricator('tabricatorLeft', 'DT');
                        var tabricatorRight = new Tabricator('tabricatorRight', 'DT');

                    }

                    if (response.status == 2) { //page préparation circuit
                        $('tabricatorRight').innerHTML = response.right_html;
                        $("send_action").setAttribute('onclick', response.valid_button);
                        var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
                        //console.log("MAJ OK");
                    }

                    if (response.status == 3) { //page envoi mail
                        $('tabricatorRight').innerHTML = response.right_html;
                        $("send_action").setAttribute('onclick', response.valid_button);
                        var tabricatorRight = new Tabricator('tabricatorRight', 'DT');

                        showEmailForm('index.php?display=true&module=sendmail&page=sendmail_ajax_content&mode=add&identifier=' + newId + '&origin=document&coll_id=' + collId + '&size=medium', '820px', '545px', 'sendmail_iframe');
                    }

                    if (response.status == 4) { //page impression dossier
                        $('tabricatorRight').innerHTML = response.right_html;
                        $("send_action").setAttribute('onclick', response.valid_button);
                        var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
                        //console.log("MAJ OK");
                    }
                },
                onFailure: function () {
                    //console.log("Probleme de Mise à jour !");
                }
            });

}

function previousDoc(path_update,collId){
	var current = parent.$('cur_resId').value;
	$('list_doc_'+current).previousSibling.click();
}

function nextDoc(path_update,collId){
	var current = parent.$('cur_resId').value;
	$('list_doc_'+current).nextSibling.click();
        
}

function updateFunctionModifRep(idReponse, num_rep, is_version){
	if(idReponse == 0){
		if (parent.document.getElementById("update_rep_link")) {
			parent.document.getElementById("update_rep_link").style.display = 'none';
		}
		if (parent.document.getElementById("sign_link")) {
			parent.document.getElementById("sign_link").style.display = 'none';
		}

	}else{
		new Ajax.Request("index.php?display=true&page=checkSignFile&module=visa&res_id="+idReponse,
		{
			method:'post',
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if (response.status == 1){
					if (parent.document.getElementById("sign_link")){
						parent.document.getElementById("sign_link").style.display = '';
						parent.document.getElementById("sign_link").setAttribute('onclick','document.getElementById("list_attach").src="index.php?display=true&module=attachments&page=del_attachment&relation=1&id='+idReponse+'&fromDetail="');	
						parent.document.getElementById("sign_link").style.color = 'green';
						parent.document.getElementById("sign_link_img").src = 'static.php?filename=sign_valid.png';
						parent.document.getElementById("sign_link_img").title= 'Enlever la signature';
						parent.document.getElementById("sign_link_img").style.cursor = 'not-allowed';
						parent.document.getElementById("sign_link").setAttribute('disabled','disabled');

					}
					if (parent.document.getElementById("sign_link_certif")){
						parent.document.getElementById("sign_link_certif").setAttribute('onclick','');	
						parent.document.getElementById("sign_link_certif").style.color = 'green';
					}
					
					if (parent.document.getElementById("update_rep_link")) {
						parent.document.getElementById("update_rep_link").style.display = 'none';
					}
				}
				else if (response.status == 0){
					if (parent.document.getElementById("sign_link")){
						parent.document.getElementById("sign_link").style.display = '';
						parent.document.getElementById("sign_link").setAttribute('onclick','signFile('+idReponse+','+is_version+',2);');	
						parent.document.getElementById("sign_link").style.color = '';
						parent.document.getElementById("sign_link_img").src = 'static.php?filename=sign.png';
						parent.document.getElementById("sign_link_img").title= 'Signer ces projets de réponse (sans certificat)';
						parent.document.getElementById("sign_link_img").style.cursor = 'pointer';
						parent.document.getElementById("sign_link").removeAttribute('disabled');
					}
					if (parent.document.getElementById("sign_link_certif")){
						parent.document.getElementById("sign_link_certif").setAttribute('onclick','signFile('+idReponse+','+is_version+',0);');	
						parent.document.getElementById("sendPIN").setAttribute('onclick','signFile('+idReponse+','+is_version+',\'\', $(\'valuePIN\').value);');	
						parent.document.getElementById("valuePIN").setAttribute('onKeyPress','if (event.keyCode == 13) signFile('+idReponse+','+is_version+',\'\', $(\'valuePIN\').value);');	
						parent.document.getElementById("sign_link_certif").style.color = '';
						parent.document.getElementById("sign_link_img").src = 'static.php?filename=sign.png';
					}
					if (parent.document.getElementById("update_rep_link")) {
						parent.document.getElementById("update_rep_link").style.display = '';
						console.log("is_version = "+is_version);
						/*if (is_version == 2) document.getElementById("update_rep_link").style.display = 'none';
						else */if (is_version != 1) parent.document.getElementById("update_rep_link").setAttribute('onclick','modifyAttachmentsForm(\'index.php?display=true&module=attachments&page=attachments_content&id='+idReponse+'&relation=1&fromDetail=\',\'98%\',\'auto\');');	
						else parent.document.getElementById("update_rep_link").setAttribute('onclick','modifyAttachmentsForm(\'index.php?display=true&module=attachments&page=attachments_content&id='+idReponse+'&relation=2&fromDetail=\',\'98%\',\'auto\');');	
						
					}
				}
			}
		});
		parent.document.getElementById("cur_idAffich").setAttribute('value',num_rep);
		parent.document.getElementById("cur_rep").setAttribute('value',idReponse);
	}
}

function hasAllAnsSigned(id_doc){
	var retour = 'null';
	new Ajax.Request("index.php?display=true&page=checkAllAnsSigned&module=visa&res_id="+id_doc,
	{
		method:'post',
		asynchronous:false,
		onSuccess: function(answer){
			eval("response = "+answer.responseText);
			retour = response.status;
		}
	});
	return retour;
}

function signFile(res_id,isVersion, mode, pinCode){
	var reg = /^[0-9]{4}$/;
	var func_onclic;
	if(pinCode == undefined || pinCode=='')
    {
        pinCode='';
    }
	else if (!reg.test(pinCode)){
		//alert("Le code PIN doit comporter 4 chiffres");
		$('badPin').style.display = 'block';
		$('badPin').innerHTML = 'Le format est incorrect (4 chiffres)';
		$('valuePIN').value = "";
	}
	else {
		$('modalPIN').style.display = 'none';
		new Ajax.Request("index.php?display=true&module=visa&page=encodePinCode",
		{
			method:'post',
			asynchronous:false,
			parameters: { 
				pinCode : pinCode
			}			
		});
	}
	if (mode == 2){

		var path = '';
		if (isVersion == 0) path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&id='+res_id;
		else if (isVersion == 1) path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isVersion&id='+res_id;
		else if (isVersion == 2) path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isOutgoing&id='+res_id;
		new Ajax.Request(path,
		{
			method:'post',
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if (response.status == 0){
					if ($('cur_idAffich')) var num_rep = $('cur_idAffich').value;
					if ($('cur_rep')){
						var newId = response.new_id;
						var oldRep = $('cur_rep').value;
						$('cur_rep').value = newId;
					}
					if ($('cur_resId')) var num_idMaster = $('cur_resId').value;
					if ($('update_rep_link')){
						$('update_rep_link').style.display = 'none';
					}
					if ($('sign_link')){
						$("sign_link").style.display = '';
						link = 'index.php?display=true&module=attachments&page=del_attachment&relation=1&id='+newId;
						$("sign_link").setAttribute('onclick','document.getElementById(\'list_attach\').src="'+link+'"');	
						$("sign_link").style.color = 'green';
						$("sign_link_img").src = 'static.php?filename=sign_valid.png';
						$("sign_link_img").title= 'Enlever la signature';
						$("sign_link_img").style.cursor = 'not-allowed';
						$("sign_link").setAttribute('disabled','disabled');	
						//console.log($('sign_link').style);
					}
					if ($('sign_link_certif')){
						$('sign_link_certif').style.color = 'green';
						$('sign_link_certif').setAttribute('onclick','');	
					}
					
					if(oldRep == newId-1){
						oldRep = res_id;
					}
					if($('viewframevalidRep'+num_rep+'_'+oldRep)) {
						$('viewframevalidRep'+num_rep+'_'+oldRep).src = "index.php?display=true&module=attachments&page=view_attachment&res_id_master="+num_idMaster+"&id="+newId;			
						$('viewframevalidRep'+num_rep+'_'+oldRep).id = 'viewframevalidRep'+num_rep+'_'+newId;
					}

					
					if($('ans_'+num_rep+'_'+oldRep)) {
						$('ans_'+num_rep+'_'+oldRep).setAttribute('onclick','updateFunctionModifRep(\''+newId+'\', '+num_rep+', 0);');		
						$('ans_'+num_rep+'_'+oldRep).id = 'ans_'+num_rep+'_'+newId;							
					}

					if($('content_'+num_rep+'_'+oldRep)) {
						$('content_'+num_rep+'_'+oldRep).id = 'content_'+num_rep+'_'+newId;							
					}
					var zone_id = 'signedDoc_'+$('cur_resId').value;
                                        
                                        if($(zone_id)){
                                            if (hasAllAnsSigned($('cur_resId').value) == 1){
						$(zone_id).style.visibility = 'visible';
                                            }
                                            else{
                                                    $(zone_id).style.visibility = 'hidden';
                                            }
                                        }
					
					$('ans_'+num_rep+'_'+newId).innerHTML='<sup><i class="fa fa-certificate fa-lg fa-fw" style="color:#fdd16c"></i></sup>Réponse signée';
					$('ans_'+num_rep+'_'+newId).title=$('ans_'+num_rep+'_'+newId).title;
					//$('list_attach').src = 'index.php?display=true&module=attachments&page=frame_list_attachments&template_selected=documents_list_attachments_simple&load&attach_type_exclude=converted_pdf,print_folder';
                                        
                                        loadToolbarBadge('attachments_tab','index.php?display=true&module=attachments&page=load_toolbar_attachments&origin=document&resId='+$('cur_resId').value+'&collId=letterbox_coll');
				}
				else{
					alert(response.error);
					console.log(func_onclic);
					$("sign_link").setAttribute('onclick',func_onclic);
				}	
				//$("sign_link").removeAttribute("onclick");
				//$("sign_link_img").style.display = 'none';
				//$('sign_link').className = 'fa fa-spinner fa-2x fa-spin';
				document.getElementById("sign_link").className = document.getElementById("sign_link").className.replace( /(?:^|\s)fa fa-spinner fa-2x fa-spin(?!\S)/g , "" )
				$('sign_link').title="";
				$("sign_link_img").style.display = '';
			},
			onCreate: function(answer){
				func_onclic = $("sign_link").getAttribute("onclick");
				$("sign_link").removeAttribute("onclick");
				$("sign_link_img").style.display = 'none';
				$('sign_link').className = 'fa fa-spinner fa-2x fa-spin';
				$('sign_link').title="en cours de traitement..."
			}
		});
	}
	else {
		new Ajax.Request("index.php?display=true&module=visa&page=checkPinCode",
		{
			method:'post',
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if (response.status == 1){
					$('badPin').style.display = 'none';
					if (isVersion == 0) window.open('index.php?display=true&module=visa&page=sign_ans&collId=letterbox_coll&id='+res_id+'&modeSign='+mode,'','height=301, width=301,scrollbars=yes,resizable=yes');
					else if (isVersion == 1) window.open('index.php?display=true&module=visa&page=sign_ans&collId=letterbox_coll&isVersion&id='+res_id+'&modeSign='+mode,'','height=301, width=301,scrollbars=yes,resizable=yes');
					else if (isVersion == 2) window.open('index.php?display=true&module=visa&page=sign_ans&collId=letterbox_coll&isOutgoing&id='+res_id+'&modeSign='+mode,'','height=301, width=301,scrollbars=yes,resizable=yes');
				}
				else if (response.status == 0){
					if (mode == 1){
						if (isVersion == 0) window.open('index.php?display=true&module=visa&page=sign_ans&collId=letterbox_coll&id='+res_id+'&modeSign='+mode,'','height=301, width=301,scrollbars=yes,resizable=yes');
						else if (isVersion == 1) window.open('index.php?display=true&module=visa&page=sign_ans&collId=letterbox_coll&isVersion&id='+res_id+'&modeSign='+mode,'','height=301, width=301,scrollbars=yes,resizable=yes');
						else if (isVersion == 2) window.open('index.php?display=true&module=visa&page=sign_ans&collId=letterbox_coll&isOutgoing&id='+res_id+'&modeSign='+mode,'','height=301, width=301,scrollbars=yes,resizable=yes');
					}
					
					else {
							var attr = document.getElementById("sendPIN").getAttribute('onclick').split(',');	
							document.getElementById("sendPIN").setAttribute('onclick',attr[0]+','+attr[1]+','+mode+','+attr[3]);	
							
							var attr2 = document.getElementById("valuePIN").getAttribute('onKeyPress').split(',');	
							document.getElementById("valuePIN").setAttribute('onKeyPress',attr2[0]+','+attr2[1]+','+mode+','+attr2[3]);	
							
							$('modalPIN').style.display = 'block';
							//console.log("Code PIN :"+pinCode);
					}
				}
			}
		});
	
	}
	
}

//load applet in a modal
function loadAppletSign(url){
    displayModal(url, 'VisaApplet', 300, 300);
}


function translateError(key){
	var message = "";
	switch(key){
		case "300" :
			message = "L'exécutable de signature est introuvable. Veuillez contacter votre administrateur.";break;
		case "99" :
			message = "Erreur lors de la signature numérique";break;
		case "98" :
			message = "Code PIN erroné";break;
		case "97" :
			message = "Trop de tentatives infructueuses pour le code PIN. Veuillez contacter votre administrateur.";break;
		case "12" :
			message = "Le répertoire PDF destination n'a pas de droit d'écriture. Veuillez contacter votre administrateur";break;
		case "16" :
			message = "Le code PIN doit comporter 4 chiffres";break;
		case "51" :
			message = "Anomalie lors de l'incrustation de l'imagette de signature";break;
		default :
			message = "";break;
	}
	return message;
}


//destroy the modal of the applet and launch an ajax script
function endOfAppletSign(objectType, theMsg, newId)
{
    if (objectType == 'ans_project') {
		if (newId != 0){
			endAttachmentSign(newId);
		}
		else{
			if (theMsg != '' && theMsg != ' ') {
				if ($('maarchcm_error')) {
					$('maarchcm_error').innerHTML = translateError(theMsg);
					$('maarchcm_error').style.display = "block";
				}
				if (theMsg == "98"){
					window.opener.$('modalPIN').style.display = 'block';
					window.opener.$('badPin').innerHTML = 'Code PIN incorrect (3 essais maximum)';
					window.opener.$('badPin').style.display = 'block';
					window.close();
				}
			}
		}
    }
    //destroyModal('CMApplet');
}

function endAttachmentSign(newId)
{
	if (window.opener.$('cur_idAffich')) var num_rep = window.opener.$('cur_idAffich').value;
	if (window.opener.$('cur_rep')){
		var oldRep = window.opener.$('cur_rep').value;
		window.opener.$('cur_rep').value = newId;
	}
	if (window.opener.$('cur_resId')) var num_idMaster = window.opener.$('cur_resId').value;
	
	if (window.opener.$('update_rep_link')){
		window.opener.$('update_rep_link').style.display = 'none';
	}
	if (window.opener.$('sign_link')){
		window.opener.$('sign_link').style.color = 'green';
		window.opener.$('sign_link').setAttribute('onclick','');	
	}
	
	if (window.opener.$('sign_link_certif')){
		window.opener.$('sign_link_certif').style.color = 'green';
		window.opener.$('sign_link_certif').setAttribute('onclick','');
	}
	if(window.opener.$('viewframevalidRep'+num_rep+'_'+oldRep)) {
		window.opener.$('viewframevalidRep'+num_rep+'_'+oldRep).src = "index.php?display=true&module=attachments&page=view_attachment&res_id_master="+num_idMaster+"&id="+newId;	
		window.opener.$('viewframevalidRep'+num_rep+'_'+oldRep).id = 'viewframevalidRep'+num_rep+'_'+newId;
	}
	
	if(window.opener.$('ans_'+num_rep+'_'+oldRep)) {
		window.opener.$('ans_'+num_rep+'_'+oldRep).setAttribute('onclick','updateFunctionModifRep(\''+newId+'\', '+num_rep+', 0);');	
		$('ans_'+num_rep+'_'+oldRep).id = 'ans_'+num_rep+'_'+newId;				
	}
	
	var zone_id = 'signedDoc_'+window.opener.$('cur_resId').value;
	if (hasAllAnsSigned(window.opener.$('cur_resId').value) == 1){
		window.opener.$(zone_id).style.visibility = 'visible';
	}
	else{
		window.opener.$(zone_id).style.visibility = 'hidden';
	}
	
	
    window.close();
}
function generateWaybill(resId)
{
    //console.log("Génération du bordereau");
    new Ajax.Request("index.php?display=true&module=visa&page=visa_waybill",
            {
                method: 'post',
                parameters: {res_id: resId
                },
                asynchronous: false,
                onSuccess: function (answer) {
                    eval("response = " + answer.responseText);
                    if (response.status == 1) {
                        //console.log("path = "+response.path);
                        //console.log("code = "+response.code);
                        new Ajax.Request("index.php?display=true&module=visa&page=put_barcode",
                                {
                                    method: 'post',
                                    parameters: {path: response.path,
                                        res_id: resId,
                                        code: response.code
                                    },
                                    asynchronous: false,
                                    onSuccess: function (answer) {
                                        eval("response2 = " + answer.responseText);
                                        if (response2.status == 1) {
                                            //console.log("path = "+response2.path);
                                        }
                                    }
                                });
                    }
                }
            });

}

function showNotesPage(id_tabricator) {
    var tab = $(id_tabricator);
    var tabDT = tab.getElementsByTagName('DT');
    for (var i = 0; i < tabDT.length; i++) {
        tabDT[i].setAttribute("class", "trig");
        if (tabDT[i].id == "onglet_notes")
            tabDT[i].setAttribute("class", "trig open");
    }

    var tabDD = tab.getElementsByTagName('DD');
    for (var i = 0; i < tabDD.length; i++) {
        tabDD[i].style.display = "none";
        if (tabDD[i].id == "page_notes")
            tabDD[i].style.display = "block";
    }
}

function printFolder(res_id, coll_id, form_id, path) {
    //console.log("printFolder");
    new Ajax.Request(path,
            {
                asynchronous: false,
                method: 'post',
                parameters: Form.serialize(form_id),
                encoding: 'UTF-8',
                onSuccess: function (answer) {
                    eval("response = " + answer.responseText);
                    if (response.status == 0) {
                        var id_folder = response.id_folder;
                        var winPrint = window.open('index.php?display=true&module=attachments&page=view_attachment&res_id_master=' + res_id + '&id=' + id_folder, '', 'height=800, width=700,scrollbars=yes,resizable=yes');
                        /*winPrint.focus();
                         winPrint.print();*/
                    }
                    else if (response.status == 1 || response.status == -1) {
                        $('divErrorPrint').innerHTML = response.error_txt;
                        $('divErrorPrint').style.display = 'table-cell';
                        Element.hide.delay(5, 'divErrorPrint');
                    }
                }
            });

}

function selectAllPrintFolder() {
    console.log($j('#allPrintFolder')[0].checked);
    if($j('#allPrintFolder')[0].checked == true){
        $j('.checkPrintFolder').prop('checked', true);
    }else{
        $j('.checkPrintFolder').prop('checked', false);
    }
}
