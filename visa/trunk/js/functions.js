function refreshIcones(id_tableau){
	
	var tableau = document.getElementById(id_tableau);
	
	var arrayLignes = tableau.rows; //l'array est stocké dans une variable
	var longueur = arrayLignes.length;//on peut donc appliquer la propriété length
	var i=1; //on définit un incrémenteur qui représentera la clé
	
	while(i<longueur)
	{
		var disabledLine = false;
		//Maj de la couleur de ligne
		if(i % 2 == 0)
		{
			arrayLignes[i].className = "";
		}
		else
		{
			arrayLignes[i].className = "col";
		}
		
		var num = i-1;
		
		if (arrayLignes[i].cells[0].childNodes[0].disabled == true) disabledLine = true;
		//MAJ id et name
		//arrayLignes[i].cells[0].innerHTML = i;
		arrayLignes[i].cells[0].childNodes[0].name = "conseiller_"+num;	arrayLignes[i].cells[0].childNodes[0].id="conseiller_"+num;
		arrayLignes[i].cells[1].childNodes[0].name = "down_"+num;	arrayLignes[i].cells[1].childNodes[0].id="down_"+num;		
		document.getElementById("down_"+num).setAttribute('onclick','deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2, \''+id_tableau+'\');');
				
		arrayLignes[i].cells[2].childNodes[0].name = "up_"+num;	arrayLignes[i].cells[2].childNodes[0].id="up_"+num;
		document.getElementById("up_"+num).setAttribute('onclick','deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1, \''+id_tableau+'\');');
		
		arrayLignes[i].cells[3].childNodes[0].name = "suppr_"+num;	arrayLignes[i].cells[3].childNodes[0].id="suppr_"+num;
		arrayLignes[i].cells[4].childNodes[0].name = "add_"+num;	arrayLignes[i].cells[4].childNodes[0].id="add_"+num;
		arrayLignes[i].cells[5].childNodes[0].name = "consigne_"+num;	arrayLignes[i].cells[5].childNodes[0].id="consigne_"+num;
		arrayLignes[i].cells[6].childNodes[0].name = "date_"+num;	arrayLignes[i].cells[6].childNodes[0].id="date_"+num;
		arrayLignes[i].cells[7].childNodes[0].name = "isSign_"+num;	arrayLignes[i].cells[7].childNodes[0].id="isSign_"+num;
		
		if (longueur > 2) document.getElementById("suppr_"+num).style.visibility="visible";
		else document.getElementById("suppr_"+num).style.visibility="hidden";
		
		if (i > 1){
			document.getElementById("up_"+num).style.visibility="visible";
		}
		else document.getElementById("up_"+num).style.visibility="hidden";
		
		if (i != longueur-1){
			document.getElementById("add_"+num).style.visibility="hidden";
			document.getElementById("isSign_"+num).style.visibility="hidden";
			
			document.getElementById("isSign_"+num).checked=false;
			
			document.getElementById("down_"+num).style.visibility="visible";
		}
		else {
			document.getElementById("add_"+num).style.visibility="visible";
			
			document.getElementById("isSign_"+num).style.visibility="hidden";
			document.getElementById("isSign_"+num).checked=true;
			
			document.getElementById("down_"+num).style.visibility="hidden";
		}
		
		/* Ajout des conditions pour les lignes disabled */
		if (disabledLine){
			document.getElementById("suppr_"+num).style.visibility="hidden";
			document.getElementById("down_"+num).style.visibility="hidden";
			document.getElementById("up_"+num).style.visibility="hidden";
			document.getElementById("isSign_"+num).style.visibility="hidden";
		}
		if (num > 0) {
			if (arrayLignes[i-1].cells[0].childNodes[0].disabled == true)
				document.getElementById("up_"+num).style.visibility="hidden";
		}
		/*************************************************/
		
		i++;
	}
}


function addRow(id_tableau)
{
	var tableau = document.getElementById(id_tableau);	
	var ligne = tableau.insertRow(-1);//on a ajouté une ligne
	var position = ligne.rowIndex;
	
	if (position%2 == 1) ligne.className = "col";

	/*var colonne1 = ligne.insertCell(0);//on a une ajouté une cellule
	colonne1.innerHTML += position;//on y met la position
	*/
	var id_Cons = "conseiller_0";
	//alert(id_Cons);
	var listeDeroulante = document.getElementById(id_Cons);
	var colonne2 = ligne.insertCell(0);//on ajoute la seconde cellule
	var listOptions = "";
	for (var j=0; j<listeDeroulante.options.length; j++){
		listOptions += "<option value='"+listeDeroulante.options[j].value+"'>"+listeDeroulante.options[j].innerHTML+"</option>";
	}
	colonne2.innerHTML += "<select>"+listOptions+"</select>";
	//colonne2.innerHTML += "</select>";

	var colonne3 = ligne.insertCell(1);
	//colonne3.innerHTML += "<img src=\"static.php?filename=DownUser.png&module=visa\" id=\"down_"+position+"\" name=\"down_"+position+"\" onclick=\"deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2, '"+id_tableau+"')\" style=\"visibility:hidden;\"/>";
	colonne3.innerHTML += "<a href=\"javascript://\"  id=\"down_"+position+"\" name=\"down_"+position+"\" onclick=\"deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2, '"+id_tableau+"')\" style=\"visibility:hidden;\" ><i class=\"fa fa-arrow-down fa-2x\"></i></a>";

	var colonne4 = ligne.insertCell(2);
	//colonne4.innerHTML += "<img src=\"static.php?filename=UpUser.png&module=visa\" id=\"up_"+position+"\" name=\"up_"+position+"\" onclick=\"deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1, '"+id_tableau+"')\" style=\"visibility:visible;\"/>";
	colonne4.innerHTML += "<a href=\"javascript://\" id=\"up_"+position+"\" name=\"up_"+position+"\" onclick=\"deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1, '"+id_tableau+"')\" style=\"visibility:visible;\" ><i class=\"fa fa-arrow-up fa-2x\"></i></a>";

	var colonne5 = ligne.insertCell(3);
	//colonne5.innerHTML += "<img src=\"static.php?filename=SupprUser.png&module=visa\" id=\"suppr_"+position+"\" name=\"suppr_"+position+"\" onclick=\"delRow(this.parentNode.parentNode.rowIndex, '"+id_tableau+"')\" style=\"visibility:visible;\"/>";
	colonne5.innerHTML += "<a href=\"javascript://\" onclick=\"delRow(this.parentNode.parentNode.rowIndex, '"+id_tableau+"')\" id=\"suppr_"+position+"\" name=\"suppr_"+position+"\" style=\"visibility:visible;\" ><i class=\"fa fa-user-times fa-2x\"></i></a>";
	
	var colonne6 = ligne.insertCell(4);
	//colonne6.innerHTML += "<img src=\"static.php?filename=AjoutUser.png&module=visa\" id=\"add_"+position+"\" name=\"add_"+position+"\" onclick=\"addRow('"+id_tableau+"')\"style=\"visibility:visible;\"/>";
	colonne6.innerHTML += "<a href=\"javascript://\" id=\"add_"+position+"\" name=\"add_"+position+"\" onclick=\"addRow('"+id_tableau+"')\"style=\"visibility:visible;\" ><i class=\"fa fa-user-plus fa-2x\"></i></a>";
	
	var colonne7 = ligne.insertCell(5);
	colonne7.innerHTML += "<input type=\"text\" id=\"consigne_"+position+"\" name=\"consigne_"+position+"\" style=\"width:100%;\"/>";
	
	var colonne8 = ligne.insertCell(6);
	colonne8.innerHTML += "<input type=\"hidden\" id=\"date_"+position+"\" name=\"date_"+position+"\"/>";
	
	var colonne9 = ligne.insertCell(7);
	colonne9.innerHTML += "<input type=\"checkbox\" id=\"isSign_"+position+"\" name=\"isSign_"+position+"\"/>";
	
	refreshIcones(id_tableau);
}

function delRow(num, id_tableau){
	//console.log("Suppression de la ligne "+num);
	
	document.getElementById(id_tableau).deleteRow(num);
	
	refreshIcones(id_tableau);
}

function deplacerLigne(source, cible, id_tableau)
{

	var tableau = document.getElementById(id_tableau);
	//on initialise nos variables
	var ligne = tableau.rows[source];//on copie la ligne
	var nouvelle = tableau.insertRow(cible);//on insère la nouvelle ligne
	var cellules = ligne.cells;

	//on boucle pour pouvoir agir sur chaque cellule
	for(var i=0; i<cellules.length; i++)
	{
		nouvelle.insertCell(-1).innerHTML += cellules[i].innerHTML;//on copie chaque cellule de l'ancienne à la nouvelle ligne
		if (i == 5 && cellules[i].childNodes[0].value != ""){
			nouvelle.cells[5].childNodes[0].value = cellules[i].childNodes[0].value;
		}
		if (i == 0){
			nouvelle.cells[0].childNodes[0].selectedIndex = cellules[i].childNodes[0].selectedIndex;
		}
	}

	//on supprimer l'ancienne ligne
	tableau.deleteRow(ligne.rowIndex);//on met ligne.rowIndex et non pas source car le numéro d'index a pu changer
	refreshIcones(id_tableau);
}

function saveVisaWorkflow(res_id, coll_id, id_tableau){
	var tableau = document.getElementById(id_tableau);
	
	var arrayLignes = tableau.rows; //l'array est stocké dans une variable
	var longueur = arrayLignes.length;//on peut donc appliquer la propriété length
	var i=1; //on définit un incrémenteur qui représentera la clé
	
	var conseillers = "";
	var consignes = "";
	var dates = "";
	var isSign = "";
	
	var cons_empty = false;
	while(i<longueur)
	{
		
		var num = i-1;
		if (document.getElementById("conseiller_"+num).value == "" ) cons_empty = true;
		conseillers += document.getElementById("conseiller_"+num).value + "#";
		consignes += document.getElementById("consigne_"+num).value + "#";
		dates += document.getElementById("date_"+num).value + "#";
		if (document.getElementById("isSign_"+num).checked == true) isSign += "1#";
		else isSign += "0#";
		
		
		i++;
	}

	if (cons_empty){
		$('divErrorVisa').innerHTML = 'Au moins un conseiller est vide';
	}
	else
	new Ajax.Request("index.php?display=true&module=visa&page=saveVisaWF",
	{
		
			method:'post',
			parameters: { 
				res_id : res_id,
				coll_id : coll_id,
				conseillers : conseillers,
				consignes : consignes,
				dates : dates,
				list_sign : isSign
			},
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if (response.status == 1){
					$('divErrorVisa').innerHTML = 'Mise à jour du circuit effectuée';
				}
			}
	});
}

function load_listmodel_visa(
	selectedOption,
	objectType,
	diff_list_id,
	save_auto
) {
	if (save_auto == undefined || save_auto == '') {
		save_auto = false;
	}
    var div_id = diff_list_id || 'tab_visaSetWorkflow';
	
	var objectId = selectedOption.value || selectedOption;
	
	var diff_list_div = $(div_id);
	new Ajax.Request(
		"index.php?display=true&module=visa&page=load_listmodel_visa",
        {
            method:'post',
            parameters: { 
				objectType : objectType,
				objectId : objectId
			},
            onSuccess: function(answer){
                eval("response = "+answer.responseText);
                //alert(answer.responseText);
                if(response.status == 0 ) {
					//console.log("Recup liste");
					diff_list_div.innerHTML = response.div_content;
					if (save_auto){
						var res_id = $('values').value;
						var coll_id = $('coll_id').value;
						saveVisaWorkflow(res_id, coll_id, diff_list_id);
					}
                }
                else if (response.status != 1 ){
					diff_list_div.innerHTML = '';
                    try{
                        $('frm_error').innerHTML = response.error_txt;
                    } catch(e){}
                }
            }
        }
	);
}

function saveVisaModel(id_tableau){
	var tableau = document.getElementById(id_tableau);
	
	var arrayLignes = tableau.rows; //l'array est stocké dans une variable
	var longueur = arrayLignes.length;//on peut donc appliquer la propriété length
	var i=1; //on définit un incrémenteur qui représentera la clé
	
	var conseillers = "";
	var consignes = "";
	var isSign = "";
	
	var cons_empty = false;
	while(i<longueur)
	{
		
		var num = i-1;
		if (document.getElementById("conseiller_"+num).value == "" ) cons_empty = true;
		conseillers += document.getElementById("conseiller_"+num).value + "#";
		consignes += document.getElementById("consigne_"+num).value + "#";
		
		if (document.getElementById("isSign_"+num).checked == true) isSign += "1#";
		else isSign += "0#";
		
		i++;
	}
	
	if (cons_empty){
		$('divErrorVisa').innerHTML = 'Au moins un conseiller est vide';
	}
	else
	new Ajax.Request("index.php?display=true&module=visa&page=saveVisaModel",
	{
		
			method:'post',
			parameters: { 
				id_list : $('objectId_input').value,
				title : $('titleModel').value,
				conseillers : conseillers,
				consignes : consignes,
				list_sign : isSign
			},
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if (response.status == 1){
					$('divErrorVisa').innerHTML = 'Modèle sauvegardé';
					$('modalSaveVisaModel').style.display = 'none';
				}
			}
	});
}
/* Fonctions ajoutées par DIS */

//Fonction permettant de lancer les 2 modules Tabricator côte à côte pour les différentes pages d'action (formation circuit de visa, visa ..)
function launchTabri(){
    var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
	var tabricatorLeft = new Tabricator('tabricatorLeft', 'DT');
}



function loadNewId(path_update,newId, collId){
	/* Modification dans la liste de gauche */
	var zone_old = 'list_doc_'+$('cur_resId').value;
	var zone_new = 'list_doc_'+newId;
	//console.log(zone_new);
	
	$(zone_old).className = 'unselectedId';
	$(zone_new).className = 'selectedId';
	
	$('cur_resId').value=newId;
	$('numIdDocPage').innerHTML=newId;
	//console.log($("send"));
	
	/****************************************/
	
	
	
	//Modification de l'affichage du document
	//$('viewframevalidDoc').setAttribute('src','index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='+newId+'&coll_id='+collId);
	
	//Modification des autres zones
	new Ajax.Request(path_update,
	{
			method:'post',
			parameters: { 
				res_id : newId,
				coll_id : collId,
				action : $('action_id').value
						},
			asynchronous: false,
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				//console.log(response);
				if (response.status == 1){ //page de visa
					$('tabricatorLeft').innerHTML = response.left_html;
					$('tabricatorRight').innerHTML = response.right_html;
					//console.log("Modification bouton action");
					$("send_action").setAttribute('onclick',response.valid_button);
					
					updateFunctionModifRep(response.id_rep, 1, response.is_vers_rep);
					
					//console.log("Initialisation onglets de la partie droite");
					var tabricatorLeft = new Tabricator('tabricatorLeft', 'DT');
					var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
				}
				
				if (response.status == 2){ //page préparation circuit
					/*$('page_avancement').innerHTML = response.avancement;
					$('onglet_notes').innerHTML = response.notes_dt;
					$('page_notes').innerHTML = response.notes_dd;*/
					$('tabricatorRight').innerHTML = response.right_html;
					//console.log("'"+response.valid_button+"'");
					$("send_action").setAttribute('onclick',response.valid_button);
					var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
					//console.log("MAJ OK");
				}
				
				if (response.status == 3){ //page envoi mail
					/*$('page_avancement').innerHTML = response.avancement;
					$('onglet_notes').innerHTML = response.notes_dt;
					$('page_notes').innerHTML = response.notes_dd;
					$('onglet_pj').innerHTML = response.pj_dt;
					$('page_pj').innerHTML = response.pj_dd;*/
					$('tabricatorRight').innerHTML = response.right_html;
					$("send_action").setAttribute('onclick',response.valid_button);
					var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
					
					showEmailForm('index.php?display=true&module=sendmail&page=sendmail_ajax_content&mode=add&identifier='+newId+'&origin=document&coll_id='+collId+'&size=medium', '820px', '545px', 'sendmail_iframe');
				}
				
				if (response.status == 4){ //page impression dossier
					/*$('page_avancement').innerHTML = response.avancement;
					$('onglet_notes').innerHTML = response.notes_dt;
					$('page_notes').innerHTML = response.notes_dd;
					$('onglet_pj').innerHTML = response.pj_dt;
					$('page_pj').innerHTML = response.pj_dd;*/
					//console.log(response.right_html);
					$('tabricatorRight').innerHTML = response.right_html;
					//console.log("'"+response.valid_button+"'");
					$("send_action").setAttribute('onclick',response.valid_button);
					var tabricatorRight = new Tabricator('tabricatorRight', 'DT');
					//console.log("MAJ OK");
				}
			},
			 onFailure: function(){ 
				//console.log("Probleme de Mise à jour !");
			 }
	});
	
}

function previousDoc(path_update,collId){
	var list = $('list_docs').value;
	var tab_docs = list.split("#");
	var current = $('cur_resId').value;
	//console.log(tab_docs);
	
	for (var i=0; i < tab_docs.length-1 ; i++){
		if (tab_docs[i] == current && i != 0) loadNewId(path_update,tab_docs[i-1], collId);
	}
}

function nextDoc(path_update,collId){
	var list = $('list_docs').value;
	var tab_docs = list.split("#");
	var current = $('cur_resId').value;
	//console.log(tab_docs);
	
	for (var i=0; i < tab_docs.length-1 ; i++){
		if (tab_docs[i] == current && i != tab_docs.length-2 ) loadNewId(path_update,tab_docs[i+1], collId);
	}
}

function updateFunctionModifRep(idReponse, num_rep, is_version){
	new Ajax.Request("index.php?display=true&page=checkSignFile&module=visa&res_id="+idReponse,
	{
		method:'post',
		onSuccess: function(answer){
			eval("response = "+answer.responseText);
			if (response.status == 1){
				if (document.getElementById("sign_link")){
					document.getElementById("sign_link").setAttribute('onclick','');	
					document.getElementById("sign_link_certif").setAttribute('onclick','');	
					document.getElementById("sign_link").style.color = 'green';
					document.getElementById("sign_link_certif").style.color = 'green';
				}
				
				if (document.getElementById("update_rep_link")) {
					document.getElementById("update_rep_link").style.display = 'none';
				}
			}
			else if (response.status == 0){
				if (document.getElementById("sign_link")){
					document.getElementById("sign_link").setAttribute('onclick','signFile('+idReponse+','+is_version+',2);');	
					document.getElementById("sign_link_certif").setAttribute('onclick','signFile('+idReponse+','+is_version+',0);');	
					
					document.getElementById("sendPIN").setAttribute('onclick','signFile('+idReponse+','+is_version+',\'\', $(\'valuePIN\').value);');	
					
					document.getElementById("valuePIN").setAttribute('onKeyPress','if (event.keyCode == 13) signFile('+idReponse+','+is_version+',\'\', $(\'valuePIN\').value);');	
					
					document.getElementById("sign_link").style.color = '';
					document.getElementById("sign_link_certif").style.color = '';
				}
				if (document.getElementById("update_rep_link")) {
					document.getElementById("update_rep_link").style.display = '';
					console.log("is_version = "+is_version);
					/*if (is_version == 2) document.getElementById("update_rep_link").style.display = 'none';
					else */if (is_version != 1) document.getElementById("update_rep_link").setAttribute('onclick','modifyAttachmentsForm(\'index.php?display=true&module=attachments&page=attachments_content&id='+idReponse+'&relation=1&fromDetail=\',\'98%\',\'auto\');');	
					else document.getElementById("update_rep_link").setAttribute('onclick','modifyAttachmentsForm(\'index.php?display=true&module=attachments&page=attachments_content&id='+idReponse+'&relation=2&fromDetail=\',\'98%\',\'auto\');');	
					
				}
			}
		}
	});
	
	
	document.getElementById("cur_idAffich").setAttribute('value',num_rep);
	document.getElementById("cur_rep").setAttribute('value',idReponse);
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
		console.log("Signature serveur simple");
		var path = '';
		if (isVersion == 0) path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&id='+res_id;
		else if (isVersion == 1) path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isVersion&id='+res_id;
		else if (isVersion == 2) path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isOutgoing&id='+res_id;
		new Ajax.Request(path,
		{
			method:'post',
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				console.log(response.status);
				if (response.status == 0){
					if ($('cur_idAffich')) var num_rep = window.opener.$('cur_idAffich').value;
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
						$('sign_link').style.color = 'green';
						$('sign_link_certif').style.color = 'green';
						$('sign_link').setAttribute('onclick','');	
						$('sign_link_certif').setAttribute('onclick','');	
					}
					
					if($('viewframevalidRep'+num_rep)) {
						$('viewframevalidRep'+num_rep).src = "index.php?display=true&module=attachments&page=view_attachment&res_id_master="+num_idMaster+"&id="+newId;			
					}
					
					if($('ans_'+num_rep)) {
						$('ans_'+num_rep).setAttribute('onclick','updateFunctionModifRep(\''+newId+'\', '+num_rep+', 0);');			
					}
					
					var zone_id = 'signedDoc_'+$('cur_resId').value;
					if (hasAllAnsSigned($('cur_resId').value) == 1){
						$(zone_id).style.visibility = 'visible';
					}
					else{
						$(zone_id).style.visibility = 'hidden';
					}
				}
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
		window.opener.$('sign_link_certif').style.color = 'green';
		window.opener.$('sign_link').setAttribute('onclick','');	
		window.opener.$('sign_link_certif').setAttribute('onclick','');	
	}
	if(window.opener.$('viewframevalidRep'+num_rep)) {
		window.opener.$('viewframevalidRep'+num_rep).src = "index.php?display=true&module=attachments&page=view_attachment&res_id_master="+num_idMaster+"&id="+newId;			
	}
	
	if(window.opener.$('ans_'+num_rep)) {
		window.opener.$('ans_'+num_rep).setAttribute('onclick','updateFunctionModifRep(\''+newId+'\', '+num_rep+', 0);');			
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
		
			method:'post',
			parameters: { res_id : resId
						},
			asynchronous: false,
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if (response.status == 1){
					//console.log("path = "+response.path);
					//console.log("code = "+response.code);
					new Ajax.Request("index.php?display=true&module=visa&page=put_barcode",
					{
						
							method:'post',
							parameters: { path : response.path,
										  res_id : resId,
										  code : response.code
										},
							asynchronous: false,
							onSuccess: function(answer){
								eval("response2 = "+answer.responseText);
								if (response2.status == 1){
									//console.log("path = "+response2.path);
								}
							}
					});
				}
			}
	});
	
}

function showNotesPage(id_tabricator){
	var tab = $(id_tabricator);
	var tabDT = tab.getElementsByTagName('DT');
	for (var i = 0; i < tabDT.length; i++){
		tabDT[i].setAttribute("class", "trig");
		if (tabDT[i].id == "onglet_notes") tabDT[i].setAttribute("class", "trig open");
	}
	
	var tabDD = tab.getElementsByTagName('DD');
	for (var i = 0; i < tabDD.length; i++){
		tabDD[i].style.display = "none";
		if (tabDD[i].id == "page_notes") tabDD[i].style.display = "block";
	}
}

function printFolder(res_id, coll_id, form_id, path){
	console.log("printFolder");
	new Ajax.Request(path,
    {
        asynchronous:false,
        method:'post',
        parameters: Form.serialize(form_id),   
        encoding: 'UTF-8',                       
        onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if(response.status == 0){
				var id_folder = response.id_folder;
				var winPrint = window.open('index.php?display=true&module=attachments&page=view_attachment&res_id_master='+res_id+'&id='+id_folder,'','height=800, width=700,scrollbars=yes,resizable=yes');
				winPrint.focus();
				winPrint.print();
                /*eval(response.exec_js);
                window.parent.destroyModal('form_email'); */
            } 
        }
    });

}
