<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['corepath'])) {
    header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
$core = new core_tools();
$core->load_lang();
?>
<div id="info" title="Nous Contacter" class="panel">
    <div class="dis">
        <p id="logo" align="center">
            <img src="<?php
            functions::xecho($_SESSION['config']['businessappurl'])
            ?>smartphone/img/logo_docimsol.png" alt="Maarch" />
        </p>
        <fieldset>
            <div class="row">
                <table align="center">
                    <tr><td colspan="2" align="left"><b>Document Image Solutions</b></td></tr>
                    <tr><td colspan="2" align="left"><b>2 Terrasses Claude Shannon</b></td></tr>
                    <tr><td colspan="2" align="left"><b>64210 Bidart</b></td></tr>
                    <tr><td align="left"><a href="mailto:contact@docimsol.com">contact@docimsol.com</a></td></tr>
                    <tr><td colspan="2" align="left"><a href="tel: +33 5 59 23 73 21">tel : +33 5 59 23 73 21</a></td></tr>
                    <tr><td><br></td></tr>
                    <tr><td colspan="2" align="center"><b>Site officiel :</b></td></tr>
                    <tr><td colspan="2" align="center"><a href="http://wwww.docimsol.eu/">http://www.docimsol.eu/</a></td></tr>
                </table>
            </div>
        </fieldset>
    </div>
    <div class="edissyum">
        <p id="logo" align="center">
            <img src="<?php
            functions::xecho($_SESSION['config']['businessappurl'])
            ?>smartphone/img/logo_edissyum.gif" alt="Maarch" />
        </p>
        <fieldset>
            <div class="row">
                <table align="center">
                    <tr><td colspan="2" align="left"><b>Edissyum</b></td></tr>
                    <tr><td colspan="2" align="left"><b>129 Boulevard Louis Giraud</b></td></tr>
                    <tr><td colspan="2" align="left"><b>84200 Carpentras</b></td></tr>
                    <tr><td align="left"><a href="mailto:contact@edissyum.com">contact@edissyum.com</a></td></tr>
                    <tr><td colspan="2" align="left"><a href="tel: +33 4 90 40 91 86">tel : +33 4 90 40 91 86</a></td></tr>
                    <tr><td><br></td></tr>
                    <tr><td colspan="2" align="center"><b>Site officiel :</b></td></tr>
                    <tr><td colspan="2" align="center"><a href="http://wwww.edissyum.com/">http://www.edissyum.com/</a></td></tr>
                </table>
            </div>
        </fieldset>
    </div>
    <hr>
    <div class="maarch">
        <p id="logo" align="center">
            <img src="<?php
            functions::xecho($_SESSION['config']['businessappurl'])
            ?>static.php?filename=default_maarch.gif" alt="Maarch" />
        </p>
        <fieldset>
            <div class="row">
                <table align="center">
                    <tr><td colspan="2" align="left"><b>Maarch</b></td></tr>
                    <tr><td colspan="2" align="left"><b>11 bd du Sud-Est</b></td></tr>
                    <tr><td colspan="2" align="left"><b>92000 Nanterre</b></td></tr>
                    <tr><td align="left"><a href="mailto:info@maarch.org">info@maarch.org</a></td></tr>
                    <tr><td colspan="2" align="left"><a href="tel: +33 1 47 24 51 59">tel : +33 1 47 24 51 59</a></td></tr>
                    <tr><td colspan="2" align="left"><b>fax : +33 1 47 24 54 08</b></td></tr>
                    <tr><td><br></td></tr>
                    <tr><td colspan="2" align="center"><b>Site officiel :</b></td></tr>
                    <tr><td colspan="2" align="center"><a href="http://www.maarch.com">http://www.maarch.com</a></td></tr>
                    <tr><td colspan="2" align="center"><b>Communauté :</b></td></tr>
                    <tr><td colspan="2" align="center"><a href="http://www.maarch.org">http://www.maarch.org</a></td></tr>
                    <tr><td colspan="2" align="center"><b>Documentation :</b></td></tr>
                    <tr><td colspan="2" align="center"><a href="http://wiki.maarch.org/Accueil">http://wiki.maarch.org/Accueil</a></td></tr>
                </table>
            </div>
        </fieldset>
    </div>
</div>
