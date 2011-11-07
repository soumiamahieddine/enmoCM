<?php

function getContent()
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	$content .= '<label for="label">' . _NOTIFICATIONS_MAIL_DIFF_TYPE . ':</label>';
	$content .= '<input name="diffusion_properties" type="text"  id="diffusion_properties" value'.
					functions::show_str($_SESSION['m_admin']['event']['diffusion_properties']).'>';
	
	
	return $content;
}

function updatePropertiesSet($diffusion_properties){
	$string = $diffusion_properties;
	return $string;	
}


function getExtraProperties(){
	$result = $_SESSION['m_admin']['event']['diffusion_properties'];
	
	?>
	
	<script language="javascript">
	alert('');
	var element = $("diffusion_properties");
		element.value = '<?php echo $result; ?>';
	</script>
	<?php
}
