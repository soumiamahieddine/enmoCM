<?php
include_once '../../../core/init.php';
if (!isset($_SESSION['config']['coreurl'])) {
	header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
$core = new core_tools();
$core->load_lang();
?>
<!DOCTYPE html>
<html manifest="iphone.manifest">
    <?php
    $core->loadSmartphoneHeader();
    ?>
    <body onload="init()">
		<div id="loginpage">
			<p id="logo"><img src="<?php
				echo $_SESSION['config']['businessappurl'];
			?>static.php?filename=default_maarch.gif" alt="Maarch" /></p>
		   <!--<h1>welcome page of maarch</h1>-->
		   <?php
		   include($_REQUEST['page'] . '.php');
		   ?>
		   <!--<input class="refresh" name="refreshButton" type="button" value="<?php echo _REFRESH; ?>" onclick="window.location.reload();" />-->
		</div>
    </body>
    <script type="text/javascript">
    // variable du cache géré par le fichier MANIFEST
    var webappCache = window.applicationCache;    
    function init()
    {
		// METTRE A JOUR LE CACHE SI UNE MAJ EST DISPO.
		webappCache.addEventListener("updateready", updateCache, false);
		// FORCER LA VISITE MEME SI LE CACHE N'A PAS PU ETRE MIS A JOUR
		webappCache.addEventListener("error", initVisite, false);
		webappCache.addEventListener("idle", initVisite, false);
    }
    function updateCache() 
    {
		// METTRE A JOUR LE CACHE
		webappCache.swapCache();
		// DEMANDER A L'UTILISATEUR DE RELANCER L'APP POUR VOIR LA NOUVELLE VERSION
		alert("Une nouvelle version est disponible.\nVeuillez rafraîchir la page pour mettre à jour.");
    }

    function initVisite()
    {
		// CODE OU COMMENCER LA VISITE ET CHARGER LE PREMIER PANO
    }
</script>
</html>

