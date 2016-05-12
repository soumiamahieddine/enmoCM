function launch_thesaurus_tooltips(trigger, target,thesaurus_name) {
	var path_to_script = "<?php echo $_SESSION['config']['businessappurl']; ?>index.php?display=true&page=get_thesaurus_info&module=thesaurus";
	var content='';
	new Ajax.Request(path_to_script,
	{
		method:'post',
		parameters:
		{
			thesaurus_name : thesaurus_name
		},
		 onSuccess: function(answer){
		 	var json = JSON.parse(answer.responseText);
			//eval("response = "+answer.responseText);
			if (json.info.thesaurus_parent_id) {
				content += '<h2 title="terme parent">'+json.info.thesaurus_parent_id+'</h2>';
			}
			content += '<ul style="padding-left:20px;">';
			content += '<li style="list-style-type:initial;">';
			content += '<fieldset style="border: solid 1px;width:400px;"><legend><b>'+json.info.thesaurus_name+'</b></legend>';
			if(json.info.thesaurus_description != null){
				content += '<p style="padding:5px;" title="description">'+json.info.thesaurus_description+'</p>';

			}else{
				content += '<p style="text-align:center;font-style:italic;padding:5px;color:grey;" title="description">aucune description</p>';		
			}
			content += '<hr/>';
			if(json.info.thesaurus_name_associate != null){
				content += '<p style="text-align:center;padding:5px;" title="terme(s) associé(s)">'+json.info.thesaurus_name_associate+'</p>';

			}else{
				content += '<p style="text-align:center;font-style:italic;padding:5px;color:grey;" title="terme(s) associé(s)">aucun terme associé</p>';		
			}
			content += '<hr/>';
			content += '<ul title="terme(s) spécique(s) à :  « '+json.info.thesaurus_name+' »" style="margin-top:5px;padding-left:20px;max-height: 200px;overflow: hidden;overflow-y: auto;float:rightwidth:49%;">';
			if(json.info_children){
				for (var i = json.info_children.length - 1; i >= 0; i--) {
					if(json.info.thesaurus_name != json.info_children[i]){
						//console.log(document.getElementById('thesaurus_'+json.info_annexe[i].thesaurus_id));
						if(document.getElementById('thesaurus_'+json.info_children[i].thesaurus_id+'').selected == true){
							content += '<li style="list-style-type:initial;cursor:text;color:grey;font-style:italic;text-decoration: line-through;">';

						}else{
							content += '<li style="list-style-type:initial;cursor:pointer;" >';
						}
						content += '<a onclick="document.getElementById(\'thesaurus_'+json.info_children[i].thesaurus_id+'\').selected = true;Event.fire($(\'thesaurus\'), \'chosen:updated\');">'+json.info_children[i].thesaurus_name+'</a>';				
						content += '</li>';
					}
				} 
			}else{
				content += '<p style="text-align:center;font-style:italic;padding:5px;color:grey;" title="terme(s) spécique(s) à :  « '+json.info.thesaurus_name+' »">aucun terme spécifique</p>';		
			}
			
			content += '</ul>';
			content += '</fieldset>';
			content += '</li>';
			content += '</ul>';
			content += '<ul title="autre(s) terme(s) lié(s) à :  « '+json.info.thesaurus_parent_id+' »" style="margin-top:5px;padding-left:20px;max-height: 200px;overflow: hidden;overflow-y: auto;">';
			if(json.info_annexe){
				for (var i = json.info_annexe.length - 1; i >= 0; i--) {
					if(json.info.thesaurus_name != json.info_annexe[i]){
						//console.log(document.getElementById('thesaurus_'+json.info_annexe[i].thesaurus_id));
						if(document.getElementById('thesaurus_'+json.info_annexe[i].thesaurus_id+'').selected == true){
							content += '<li style="list-style-type:initial;cursor:text;color:grey;font-style:italic;text-decoration: line-through;">';

						}else{
							content += '<li style="list-style-type:initial;cursor:pointer;">';
						}
						content += '<a onclick="document.getElementById(\'thesaurus_'+json.info_annexe[i].thesaurus_id+'\').selected = true;Event.fire($(\'thesaurus\'), \'chosen:updated\');">'+json.info_annexe[i].thesaurus_name+'</a>';				
						content += '</li>';
					}
				} 
			}
			
			content += '</ul>';
			document.getElementById("thesaurus_chosen_"+json.info.thesaurus_id).onclick=function(e){toolTipThes(e, content)}; 
			//console.log(document.getElementById("thesaurus_chosen_"+json.info.thesaurus_id));
			//console.log(document.getElementById("thesaurus_chosen_"+json.info.thesaurus_id));
			/*new Opentip(trigger, content, '', {
			    target: target,
			    showOn: 'click',
			    hideTrigger: 'closeButton',
			    tipJoint: 'left top',
			    fixed: true });Opentip.lastZIndex = 1500;*/
			}
	});

}

function load_specific_thesaurus(thesaurus_id) {
	var path_to_script = "<?php echo $_SESSION['config']['businessappurl']; ?>index.php?display=true&page=get_thesaurus_childs&module=thesaurus";
	var content='';

	if(document.getElementById('thesaurus_parent_id').selectedOptions[0].value != ""){
		new Ajax.Request(path_to_script,
		{
			method:'post',
			parameters:
			{
				thesaurus_id : thesaurus_id
			},
			 onSuccess: function(answer){
			 	thesaurus_parent_label=document.getElementById('thesaurus_parent_id').selectedOptions[0].innerHTML.replace(/^\s+/g, '').replace(/\s+$/g, '').replace(/&nbsp;/gi,'');

			 	var json = JSON.parse(answer.responseText);
				//eval("response = "+answer.responseText);
				if(json){
					document.getElementById('thesaurus_name_specific').innerHTML = "Terme(s) spécifique(s) pour :<br/>« " + thesaurus_parent_label + " »";
					content += '<ul style="text-align:left;padding-left:20px;">';
					for (var i = json.length - 1; i >= 0; i--) {				
							content += '<li style="list-style-type:initial;padding:5px;" title="Accéder à la page du terme">';
							content += "<a href='<?php echo $_SESSION['config']['businessappurl']; ?>index.php?page=manage_thesaurus_list_controller&mode=up&module=thesaurus&id="+json[i].thesaurus_id+"&start=0&order=asc&order_field=&what='>"+json[i].thesaurus_name+"</a>";				
							content += '</li>';

					} 
					document.getElementById('thesaurus_list_specific_content').innerHTML = content;
					content += '</ul>';
				}else{
					document.getElementById('thesaurus_name_specific').innerHTML = "Terme(s) spécifique(s) pour :<br/>« " + thesaurus_parent_label + " »";
					content = '<i style="color:grey;">Aucun terme spécifique</i>';
					document.getElementById('thesaurus_list_specific_content').innerHTML = content;
				}
				
				}
		});
	}else{
		document.getElementById('thesaurus_name_specific').innerHTML = "Terme(s) spécifique(s) pour :<br/><i>Pas de terme générique</i>";
		content = '<i style="color:grey;">Aucun terme spécifique</i>';
		document.getElementById('thesaurus_list_specific_content').innerHTML = content;
	}
	
}

function toolTipThes(e, content){
    
    var DocRef;
    console.log(e);
    if(e){
        mouseX = e.pageX;
        mouseY = e.pageY;
    }
    else{
        mouseX = event.clientX;
        mouseY = event.clientY;

        if( document.documentElement && document.documentElement.clientWidth) {
            DocRef = document.documentElement;
        } else {
            DocRef = document.body;
        }

        mouseX += DocRef.scrollLeft;
        mouseY += DocRef.scrollTop;
    }
    var topPosition  = mouseY + 10;
    var leftPosition = mouseX - 200;
    
    var writeHTML = content
    $('return_previsualise_thes').update(writeHTML);
    $('return_previsualise_thes').innerHTML;

    var divWidth = $('return_previsualise_thes').getWidth();
    if (divWidth > 0) {
        leftPosition = mouseX - (divWidth + 40);
    }
	if(leftPosition < 0){
		leftPosition = - leftPosition;
	}
    var divHeight = $('return_previsualise_thes').getHeight();
    if (divHeight > 0) {
        topPosition = mouseY - (divHeight - 2);
    }
    
    if (topPosition < 0) {
        topPosition = 10;
    }
    
    //var scrollY = (document.all ? document.scrollTop : window.pageYOffset);
    var scrollY = f_filterResults (
        window.pageYOffset ? window.pageYOffset : 0,
        document.documentElement ? document.documentElement.scrollTop : 0,
        document.body ? document.body.scrollTop : 0
    );
    
    if (topPosition < scrollY) {
        topPosition = scrollY + 10;
    }
    
    $('return_previsualise_thes').style.top=topPosition-10+'px';
    $('return_previsualise_thes').style.left=mouseX+'px';
    
    $('return_previsualise_thes').style.maxWidth='600px';
    $('return_previsualise_thes').style.maxHeight='600px';
    $('return_previsualise_thes').style.display='block';
    
}