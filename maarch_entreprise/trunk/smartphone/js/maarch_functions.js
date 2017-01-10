function addNotes (
    fieldNotes,
    coll_id,
    id
)
{

    var empty = false;
    if (fieldNotes.length < 1 || coll_id.length < 1 || id.length < 1) {
        empty = true;
    }
    
    if (empty) {
        alert('Vous devez entrer une note');
        return;
    }
    
    var path_manage_script = 'ajax_add_notes.php';
    console.log(fieldNotes);
    console.log(coll_id);
    console.log(id);
    new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { id : id,
                          coll_id : coll_id,
                          fieldNotes : fieldNotes
                        },
            onSuccess: function(answer){
                eval("response = "+answer.responseText);
                if (response.status == 1) {
                    $('newNote').style.display = 'block';
                    $('newNote').replace(response.newNote);
                    $('fieldNotes').setValue('');
                    $('noNotes').replace('');
                } else {
                    alert(response.msg);
                }
            }
        }
    );
}

function delNotes (
    id
)
{
    var test = confirm('Supprimer ?');
    if (!test) {
        return;
    }
    
    var delDiv = ''+id+'';
    
    var path_manage_script = 'ajax_del_notes.php';

    new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { id : id },
            onSuccess: function(answer){
                eval("response = "+answer.responseText);
                if (response.status == 1) {
                    console.log($(delDiv));
                    $(delDiv).replace('');
                } else {
                    alert(response.msg);
                }
            }
        }
    );
}

function cacher(person, lastname, firstname)
{
    if(document.getElementById(person).style.display == 'none'){
        document.getElementById(person).style.display='block';

    }
    else{
        document.getElementById(person).style.display='none';
    }
    document.getElementById('lastname').value = "";
    document.getElementById('firstname').value = "";
}

function searchContacts(searchValue)
{
    var path_manage_script = 'ajax_contact_list.php';
    new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { searchValue : searchValue },
            onSuccess: function(answer){
                eval("response = "+answer.responseText);
                if (response.status == 1) {
                    $('allContacts').update(response.rechercheContact);
                    $('allContacts').innerHtml;
                    $('noContacts').replace('');
                } else {
                    alert(response.msg);
                }
            }
        }
    );
}

function searchColleagues(searchValue)
{
    var path_manage_script = 'ajax_colleagues_list.php';
    new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { searchValue : searchValue },
            onSuccess: function(answer){
                eval("response = "+answer.responseText);
                if (response.status == 1) {
                    $('allColleagues').update(response.rechercheColleague);
                    $('allColleagues').innerHtml;
                    $('noContacts').replace('');
                } else {
                    alert(response.msg);
                }
            }
        }
    );
}


    function toggle_visibility_suivant(valeur) { 
        
        
        var nombreligneAffiche = document.getElementById('start').value;
        var nblinetoshow = document.getElementById('sendNbLineToShow').value;
        //alert('nblinetoshow :'+nblinetoshow );
        var nombreligneSuivante = document.getElementById('start');
        var hiddenSuivant = parseFloat(nombreligneAffiche) + parseFloat(nblinetoshow);

        if(nombreligneAffiche <= valeur ){
            nombreligneAffiche = parseFloat(nombreligneAffiche) + parseFloat(nblinetoshow);
            nombreligneSuivante.value= parseFloat(nombreligneAffiche);

            id = 'boutonSuivant'; 
            var f = document.getElementById(id);
            if(nombreligneAffiche >=valeur & f.style.display == 'block'){
                document.getElementById(id).setAttribute("disabled","disabled"); 
                   //document.getElementById(id).style.display = 'none';
               }

               id = 'boutonPrecedent'; 
               var f = document.getElementById(id);
               if(f.style.display == 'block'){

           //document.getElementById(id).style.display = 'block';
           document.getElementById(id).removeAttribute("disabled","disabled");
       }
   }


       if(hiddenSuivant >= valeur){
        var nombreligneAffiche2 = nombreligneAffiche -nblinetoshow;
        var iter = nombreligneAffiche2 - nblinetoshow;
        for( iter; iter < nombreligneAffiche2; iter++){
         id = 'res_'+iter;
         var h = document.getElementById(id);
               //alert('iter :'+id);
               if(iter <= nombreligneAffiche2 & h.style.display == 'block'){
                  document.getElementById(id).style.display = 'none';
              }
              
          }   
      }

          var iter = nombreligneAffiche - nblinetoshow;
          for( iter; iter < nombreligneAffiche; iter++){
           id = 'res_'+iter;
           var e = document.getElementById(id);
           if(iter <= nombreligneAffiche & e.style.display == 'none'){
              document.getElementById(id).style.display = 'block';
          }

        }

        var doubleNbLineToShow = nblinetoshow * 2;
        var iter = nombreligneAffiche - doubleNbLineToShow;
        nombreligneAffiche = nombreligneAffiche -nblinetoshow;
        for( iter; iter < nombreligneAffiche; iter++){
           id = 'res_'+iter;
           var e = document.getElementById(id);
           if(iter <= nombreligneAffiche & e.style.display == 'block'){
              document.getElementById(id).style.display = 'none';
          }
          
      }


 }


    function toggle_visibility_precedent(valeur) { 
        var nombreligneAffiche = document.getElementById('start').value;
        var nombreligneSuivante = document.getElementById('start');
        var nblinetoshow = document.getElementById('sendNbLineToShow').value;
        //alert('nblinetoshow :'+nblinetoshow );
        if(nombreligneAffiche > nblinetoshow){
                nombreligneAffiche = parseFloat(nombreligneAffiche) - parseFloat(nblinetoshow);
                nombreligneSuivante.value= parseFloat(nombreligneAffiche);
                id = 'boutonSuivant'; 
                var f = document.getElementById(id);
                if(f.disabled == 'disabled'){
                   document.getElementById(id).removeAttribute("disabled");
                   //document.getElementById(id).style.display = 'block';
                    }

                }

        if(nombreligneAffiche <=nblinetoshow){
                        id = 'boutonPrecedent'; 
                var f = document.getElementById(id);
                if(f.style.display == 'block'){
                   document.getElementById(id).setAttribute("disabled","disabled"); 
                   //document.getElementById(id).style.display = 'none';
                    }
                }

          id = 'boutonSuivant'; 
          var f = document.getElementById(id);
          if(f.style.display == 'block'){

           //document.getElementById(id).style.display = 'block';
           document.getElementById(id).removeAttribute("disabled","disabled");
       }

    var iter = nombreligneAffiche - nblinetoshow;


        for( iter; iter <= nombreligneAffiche; iter++){
         id = 'res_'+iter;
         var e = document.getElementById(id);
         if(iter <= nombreligneAffiche & e.style.display == 'none'){
          document.getElementById(id).style.display = 'block';
        }
       
        }


            iter = nombreligneAffiche + parseFloat(nblinetoshow);
    //alert('iter '+iter);        
    var nombreLigneInferieur = parseFloat(nombreligneAffiche) /*- parseFloat(5)*/;
   for( nombreLigneInferieur; nombreLigneInferieur <= iter; nombreLigneInferieur++){
       id = 'res_'+nombreLigneInferieur;
       var e = document.getElementById(id);
       if(e.style.display == 'block'){
          document.getElementById(id).style.display = 'none';
            }
        }


}

function clean(){
    if(document.getElementById('notes')){
        document.getElementById('notes').parentNode.removeChild(document.getElementById('notes'));
    }else if(document.getElementById('details')){
        document.getElementById('details').parentNode.removeChild(document.getElementById('details'));
                var k = document.getElementById(about);
       if(k.style.display == '-webkit-transform: translateX(-100%)'){
          document.getElementById(about).style.display = '-webkit-transform: translateX(0%)';
            }
        } else if(document.getElementById('about')){
        if(document.getElementById('list')){
          //alert('ok LIST');
            document.getElementById('list').parentNode.removeChild(document.getElementById('list'));
        }
        if(document.getElementById('about')){
          //alert('ok about');
            document.getElementById('about').parentNode.removeChild(document.getElementById('about'));
        }
        if(document.getElementById('search').style.webkitTransform == 'translateX(-100%)'){
         // alert('-webkit-transform: translateX(-100%)');
          document.getElementById('search').style.webkitTransform = 'translateX(0%)';
          var h1 = document.getElementById('search');
          var att = document.createAttribute("selected");
          att.value = "true";
          h1.setAttributeNode(att);
            }
    }else if(document.getElementById('list')){ 
           document.getElementById('list').parentNode.removeChild(document.getElementById('list'));
           if(document.getElementById('__1__').style.webkitTransform == 'translateX(-100%)'){
          //alert('-webkit-transform: translateX(-100%)');
          document.getElementById('__1__').style.webkitTransform = 'translateX(0%)';
          var h1 = document.getElementById('__1__');
          var att = document.createAttribute("selected");
          att.value = "true";
          h1.setAttributeNode(att);
            }
    } else if(document.getElementById('search')){
         //alert('ok search');
            document.getElementById('search').parentNode.removeChild(document.getElementById('search'));
            window.location.reload();
    }else if(document.getElementById('__1__')){
            window.location.reload();
    }
}
