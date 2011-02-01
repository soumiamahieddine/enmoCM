<?php
/**
* File : maarch_credits.php
*
* Show all contributors for Maarch.
* Thanks a lot for your help!!
*
* @package  Maarch FrameWork 3.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Loic Vinet <dev@maarch.org>
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$core_tools = new core_tools();

/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=boxes&module=maarch_credits';
$page_label = _MAARCH_CREDITS;
$page_id = "maarch_credits";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
?>

<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_menu_help.gif" alt="" /> <?php  echo _MAARCH_CREDITS; ?></h1>
<div id="inner_content" class="clearfix">
    <div class="maarch_credits_left_box" style="height:420px;">
        <h3><?php  echo _MAARCH_CREDITS; ?></h3>
    <hr/>
    <p><em>Copyright &copy; 2008, 2009 Maarch SAS.</em></p>
    <p>Maarch Entreprise est diffusé sous les termes de la <a href="http://www.gnu.org/licenses/gpl-3.0-standalone.html">licence GNU GPLv3</a></p>
    <div>
        <ul>
            <li>Site officiel : <a href="http://www.maarch.fr">http://www.maarch.fr</a></li>
            <li>Communtauté : <a href="http://www.maarch.org">http://www.maarch.org</a></li>
            <li>Documentation : <a href="http://www.maarch.org/maarch_wiki">http://www.maarch.org/maarch_wiki</a></li>
        </ul>
    </div>
    <p>&nbsp;</p>
    <h3>Composants externes</h3>
    <hr/>
    <em>Maarch Entreprise s'appuie sur quelques composants externes. Merci à leurs développeurs !</em>
    <p>&nbsp;</p>
    <ul>
        <li><a href="http://www.fpdf.org/">Fpdf</a></li>
        <li><a href="http://www.setasign.de/products/pdf-php-solutions/fpdi/">fpdi</a></li>
        <li><a href="http://chir.ag/tech/download/pdfb">Pdfb</a></li>
        <li><a href="http://www.foolabs.com/xpdf/">Pdftotext</a></li>
        <li><a href="http://www.prototypejs.org/">Prototype</a></li>
        <li><a href="http://script.aculo.us/">Script.aculo.us</a></li>
        <li><a href="http://www.cyber-sandbox.com/">Tabricator</a></li>
        <li><a href="http://tafel.developpez.com">Tafel Tree</a></li>
        <li><a href="http://framework.zend.com/">Zend Lucene Search</a></li>
    </ul>
    </div>

    <div class="credits_list block" style="height:420px;">


    <h3>Credits</h3>
    <p>&nbsp;</p>
    <ul>
        <li>Bruno CARLIN</li>
        <li>Driss DEMIRAY</li>
        <li>Mathieu DONZEL</li>
        <li>Jean-Louis ERCOLANI</li>
        <li>Claire FIGUERAS</li>
        <li>Laurent GIOVANNONI</li>
        <li>Yves-Christian KPAKPO</li>
        <li>Fod&eacute; NDIAYE</li>
        <li>C&eacute;dric NDOUMBA</li>
        <li>Serge THIERRY-MIEG</li>
        <li>Loic VINET</li>
        <li><em>Et toute la communauté Maarch</em></li>
    </ul>
    <p>&nbsp;</p>


    <div class="img_credits_maarch_box"><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=maarch_box.png" /></div>
    </div>
</div>
<p style="clear:both"></p>
