<!--
// Fonction pour gérer les changements dynamiques de sous-menu.
// Prend en variable le numéro du sous-menu à afficher.

function ChangeMenu(idsm){
	if(document.getElementById("sm"+idsm).style.visibility!='visible'){ // si le sous-menu est déjà affiché, on ne fait rien. Sinon :
		for(i=1;i<=7;i++){ // boucle pour détécter quel menu est affiché
			if(document.getElementById("sm"+i).style.visibility=='visible'){
				ActiveSM=i
				break;
			}
		}
		document.getElementById("sm"+ActiveSM).style.visibility='hidden' // on cache le sous menu affiché
		document.getElementById("sm"+idsm).style.visibility='visible' // on affiche le nouveau
	}
}

function ChangeH2(objet){
	if(objet.getElementsByTagName('img')[0].src.indexOf("plus")>-1){
		objet.getElementsByTagName('img')[0].src="img/moins.png";
		objet.getElementsByTagName('span')[0].firstChild.nodeValue=" ";
	}else{
		objet.getElementsByTagName('img')[0].src="img/plus.png";
		objet.getElementsByTagName('span')[0].firstChild.nodeValue=" ";
	}
}

function Change2H2(objet){
	if(objet.getElementsByTagName('img')[0].src.indexOf("folderopen")>-1){
		objet.getElementsByTagName('img')[0].src="img/folder.gif";
		objet.getElementsByTagName('span')[0].firstChild.nodeValue=" ";
	}else{
		objet.getElementsByTagName('img')[0].src="img/folderopen.gif";
		objet.getElementsByTagName('span')[0].firstChild.nodeValue=" ";
	}
}

function afficheCalque(calque){
	calque.style.display='block';
}
function masqueCalque(calque){
	calque.style.display='none';
}

function getScrollXY() {
  var scrOfX = 0, scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
  return new Array(scrOfX,scrOfY);
}

function afficheImage(nom){
	var idCalque=document.getElementById('lb1-layer');
	var idIMG=document.getElementById('lb1-img');
	
	idCalque.style.width=document.getElementsByTagName('html')[0].offsetWidth+"px";
	idCalque.style.height=document.getElementsByTagName('body')[0].offsetHeight+"px";
	idIMG.style.width=idCalque.style.width;
	idIMG.style.marginTop=getScrollXY()[1]+15+'px';
	
	
	afficheCalque(idCalque);
	afficheCalque(idIMG);
	idIMG.innerHTML="<span style='color:white;font-weight:bold'>Cliquez sur l'image pour fermer</span><br /><br /><img src=\"img/lb1/"+nom+"\" id=\"lb1-image\" onclick=\"masqueCalque(document.getElementById('lb1-layer'));masqueCalque(document.getElementById('lb1-img'));\" />";
}

function DetailsOn(objet){
	objet.style.visibility="visible";
}
function DetailsOff(objet){
	objet.style.visibility="hidden";
}

var initialized = 0;
var etat = new Array();

function reinit()
{
	initialized = 0;
	etat = new Array();
}

function initialise(){
    for (i=0;i<500;i++){
        etat[i] = new Array();
        etat[i]["h2"] = document.getElementById('h2'+i);
        etat[i]["desc"] = document.getElementById('desc'+i);
        etat[i]["etat"] = 0;
    }
    initialized = 1;
}

function ferme(id){
    Effect.SlideUp(etat[id]["desc"]);
    ChangeH2(etat[id]["h2"]);
    etat[id]["etat"] = 0;
}

function ouvre(id){
    Effect.SlideDown(etat[id]["desc"]);
    ChangeH2(etat[id]["h2"])
    etat[id]["etat"] = 1;
}

function ferme2(id){
    Effect.SlideUp(etat[id]["desc"]);
    Change2H2(etat[id]["h2"]);
    etat[id]["etat"] = 0;
}

function ouvre2(id){
    Effect.SlideDown(etat[id]["desc"]);
    Change2H2(etat[id]["h2"])
    etat[id]["etat"] = 1;
}

function change(id){
    if (!initialized ){
        initialise()
    }
	//alert(etat[id]["etat"]);
    if (etat[id]["etat"]){
        ferme(id);
    }else{
        for (i=0;i<etat.length;i++){
			if (etat[i]["etat"]){
                ferme(i);
            }
        }
        ouvre(id);
    }
}

function change2(id){
    if (!initialized){
        initialise()
    }
    if (etat[id]["etat"]){
        ferme2(id);
    }else{
        for (i=0;i<etat.length;i++){
			if (etat[i]["etat"]){
                //ferme(i);
            }
        }
        ouvre2(id);
    }
}

function affDetails(obj){
    document.getElementById("conteneurDetails").innerHTML = obj.parentNode.parentNode.getElementsByTagName("div")[0].innerHTML;
}

function show_special_form(id, var_visible)
{
	var elem = window.document.getElementById(id);
	if(elem != null)
	{
		elem.style.display = var_visible;
	}
}
// -->