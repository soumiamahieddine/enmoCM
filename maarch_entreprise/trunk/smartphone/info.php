<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['coreurl'])) {
    header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
$core = new core_tools();
$core->load_lang();
?>
<div id="info" title="Nous Contacter" class="panel">
    <p id="logo" align="center">
    <img src="<?php
            echo $_SESSION['config']['businessappurl'];
		?>static.php?filename=default_maarch.gif" width="100%" alt="Maarch" />
    </p>
	<fieldset>
		<div class="row">
			<table align="center">
				<tr><td colspan="2" align="center"><b>Maarch</b></td></tr>
				<tr><td colspan="2" align="center"><b>11 bd du Sud-Est</b></td></tr>
				<tr><td colspan="2" align="center"><b>92000 Nanterre</b></td></tr>
				<tr><td align="center"><a href="mailto:info@maarch.org">info@maarch.org</a></td></tr>
				<tr><td colspan="2" align="center">+33 1 47 24 51 59</td></tr>
				<tr><td><br></td></tr>
				<tr><td colspan="2" align="center"><b>Site officiel :</b></td></tr>
				<tr><td colspan="2" align="center"><a href="http://www.maarch.com">http://www.maarch.com</a></td></tr>
				<tr><td colspan="2" align="center"><b>Documentation :</b></td></tr>
				<tr><td colspan="2" align="center"><a href="http://wiki.maarch.org/Accueil">http://wiki.maarch.org/Accueil</a></td></tr>
			</table>
		</div>
	</fieldset>
</div>