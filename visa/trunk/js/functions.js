function refreshIcones(id_tableau){
	
	var tableau = document.getElementById(id_tableau);
	
	var arrayLignes = tableau.rows; //l'array est stocké dans une variable
	var longueur = arrayLignes.length;//on peut donc appliquer la propriété length
	var i=1; //on définit un incrémenteur qui représentera la clé
	
	while(i<longueur)
	{
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
		
		if (longueur > 2) document.getElementById("suppr_"+num).style.visibility="visible";
		else document.getElementById("suppr_"+num).style.visibility="hidden";
		
		if (i > 1){
			document.getElementById("up_"+num).style.visibility="visible";
		}
		else document.getElementById("up_"+num).style.visibility="hidden";
		
		if (i != longueur-1){
			document.getElementById("add_"+num).style.visibility="hidden";
			document.getElementById("down_"+num).style.visibility="visible";
		}
		else {
			document.getElementById("add_"+num).style.visibility="visible";
			document.getElementById("down_"+num).style.visibility="hidden";
		}
		
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
	colonne3.innerHTML += "<img src=\"static.php?filename=DownUser.png&module=visa\" id=\"down_"+position+"\" name=\"down_"+position+"\" onclick=\"deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2, '"+id_tableau+"')\" style=\"visibility:hidden;\"/>";

	var colonne4 = ligne.insertCell(2);
	colonne4.innerHTML += "<img src=\"static.php?filename=UpUser.png&module=visa\" id=\"up_"+position+"\" name=\"up_"+position+"\" onclick=\"deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1, '"+id_tableau+"')\" style=\"visibility:visible;\"/>";

	var colonne5 = ligne.insertCell(3);
	colonne5.innerHTML += "<img src=\"static.php?filename=SupprUser.png&module=visa\" id=\"suppr_"+position+"\" name=\"suppr_"+position+"\" onclick=\"delRow(this.parentNode.parentNode.rowIndex, '"+id_tableau+"')\" style=\"visibility:visible;\"/>";
	
	var colonne6 = ligne.insertCell(4);
	colonne6.innerHTML += "<img src=\"static.php?filename=AjoutUser.png&module=visa\" id=\"add_"+position+"\" name=\"add_"+position+"\" onclick=\"addRow('"+id_tableau+"')\"style=\"visibility:visible;\"/>";
	
	var colonne7 = ligne.insertCell(5);
	colonne7.innerHTML += "<input type=\"text\" id=\"consigne_"+position+"\" name=\"consigne_"+position+"\" style=\"width:100%;\"/>";
	
	refreshIcones(id_tableau);
}

function delRow(num, id_tableau){
	console.log("Suppression de la ligne "+num);
	
	document.getElementById(id_tableau).deleteRow(num);
	
	refreshIcones(id_tableau);
}

function deplacerLigne(source, cible, id_tableau)
{
	console.log("Déplacement de la ligne "+source+" vers la ligne "+cible);

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
	
	while(i<longueur)
	{
		
		var num = i-1;
		conseillers += document.getElementById("conseiller_"+num).value + "#";
		consignes += document.getElementById("consigne_"+num).value + "#";
		
		i++;
	}
	
	new Ajax.Request("index.php?display=true&module=visa&page=saveVisaWF",
	{
		
			method:'post',
			parameters: { 
				res_id : res_id,
				coll_id : coll_id,
				conseillers : conseillers,
				consignes : consignes
			},
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if (response.status == 1){
					console.log("path = "+response.path);
					console.log("code = "+response.code);
					$('divErrorVisa').innerHTML = 'Mise à jour du circuit effectuée';
				}
			}
	});
}


/* Fonctions ajoutées par DIS */

//Fonction permettant de lancer les 2 modules Tabricator côte à côte pour les différentes pages d'action (formation circuit de visa, visa ..)
function launchTabri(){
    var tabricator2 = new Tabricator('tabricator2', 'DT');
    var tabricator1 = new Tabricator('tabricator1', 'DT');
	var tabricator0 = new Tabricator('tabricator0', 'DT');
}

function previousDoc(){
	var prevId = $('prevDoc').value;
	var actionId = $('action_id').value;
	destroyModal('modal_'+actionId);
	validForm( 'page', prevId, actionId);
}

function nextDoc(){
	var nextId = $('nextDoc').value;
	var actionId = $('action_id').value;
	destroyModal('modal_'+actionId);
	validForm( 'page', nextId, actionId);
}

function updateFunctionModifRep(idReponse, num_rep){
	document.getElementById("uni_update_rep_link").setAttribute('onclick','window.open(\'index.php?display=true&module=attachments&page=update_attachments&mode=up&collId=letterbox_coll&id='+idReponse+'\',\'\',\'height=301, width=301,scrollbars=yes,resizable=yes\');');	
	document.getElementById("split_update_rep_link").setAttribute('onclick','window.open(\'index.php?display=true&module=attachments&page=update_attachments&mode=up&collId=letterbox_coll&id='+idReponse+'\',\'\',\'height=301, width=301,scrollbars=yes,resizable=yes\');');	
	document.getElementById("cur_idAffich").setAttribute('value',num_rep);
}

function generateBordereau(resId)
{
	console.log("Génération du bordereau");
	new Ajax.Request("index.php?display=true&module=visa&page=remplir_bordereau",
	{
		
			method:'post',
			parameters: { res_id : resId
						},
			asynchronous: false,
			onSuccess: function(answer){
				eval("response = "+answer.responseText);
				if (response.status == 1){
					console.log("path = "+response.path);
					console.log("code = "+response.code);
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
									console.log("path = "+response2.path);
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


