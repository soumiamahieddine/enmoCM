<?php
include_once '../../../core/init.php';
if (
    !isset($_SESSION['config']['corepath'])
    || !isset($_SESSION['config']['databasename'])
) {
    header('location: ../../../');
}

require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
$core = new core_tools();
$core->load_lang();
?>
<html>
    <?php
        $core->loadSmartphoneHeader();
    ?>
    <body>
        <div class="toolbar">
            <h1 id="pageTitle"></h1>
            <!-- <a id="maarchLogo" class="maarchLogo" href="#">&nbsp;&nbsp;&nbsp;&nbsp;</a> -->
        </div>
        <form selected="true" title="BIENVENUE" class="panel" method="post" action="login.php" target="_self">
            <fieldset>
                <div class="row">
                    <p id="maarchLogo" align="center">
						<br><br><br>
						<img src="<?php
                        functions::xecho($_SESSION['config']['businessappurl'])
                        ?>static.php?filename=default_maarch.gif" alt="Maarch" usemap="#maarch">
						<map name="maarch">
							<area shape="rect" coords="0,0,240,80" href="login.php">
						</map>
						<br><br><br><br>
					</p>
                </div>
            </fieldset>
            <input class="whiteButton" type="submit" value="Continuer" width="50px">
        </form>
	</body>
</html>
