<?php

function getContent()
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	$content .= '<p class="sstit">' . _NOTIFICATIONS_DEST_USER_DIFF_TYPE . '</p>';
	
	
	return $content;
}


function updatePropertiesSet($diffusion_properties){
	return null;	
}


function getExtraProperties(){
	
}
