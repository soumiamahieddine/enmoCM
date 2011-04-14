<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
* @brief  Access to the baskets
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
if (isset($_SESSION['search']['plain_text'])) {

    $_SESSION['search']['plain_text'] = "";

}
$_SESSION['FILE'] = array();
if (isset($_REQUEST['extension'])) {
    $_SESSION['origin'] = "scan";
    $_SESSION['FILE']['extension'] = $_REQUEST['extension'];
    $_SESSION['upfile']['size'] = $_REQUEST['taille_fichier'];
    $_SESSION['upfile']['mime'] = "application/pdf";
    $_SESSION['upfile']['local_path'] = "tmp/tmp_file_".$_REQUEST['md5'].".pdf";
    $_SESSION['upfile']['name'] = "tmp_file_".$_REQUEST['md5'].".pdf";
    $_SESSION['upfile']['md5'] = $_REQUEST['md5'];
    $_SESSION['upfile']['format'] = 'pdf';
} else {
    $_SESSION['origin'] = "";
    $_SESSION['upfile'] = array();
}
//file size
if (isset($_REQUEST['taille_fichier'])) {
    $_SESSION['FILE']['taille_fichier'] = $_REQUEST['taille_fichier'];
    $_SESSION['upfile']['size'] = $_REQUEST['taille_fichier'];
}
//file temporary path
if (isset($_REQUEST['Ftp_File'])) {
    $_SESSION['FILE']['Ftp_File'] = $_REQUEST['Ftp_File'];
}
//fingerprint of the file
if (isset($_REQUEST['md5'])) {
    $_SESSION['FILE']['md5'] = $_REQUEST['md5'];
}
//scan user
if (isset($_REQUEST['scan_user'])) {
    $_SESSION['FILE']['scan_user'] = $_REQUEST['scan_user'];
}
//scan workstation
if (isset($_REQUEST['scan_wkstation'])) {
    $_SESSION['FILE']['scan_wkstation'] = $_REQUEST['scan_wkstation'];
}
if (isset($_REQUEST['tmp_file'])) {
    $_SESSION['FILE']['tmp_file'] = $_REQUEST['tmp_file'];
}
//print_r($_SESSION['FILE']);
//print_r($_SESSION['upfile']);exit;

require_once "core/class/class_request.php";
$core = new core_tools();
$core->test_user();
$core->load_lang();
$core->test_service('view_baskets', "basket");
if (! isset($_REQUEST['noinit'])) {
    $_SESSION['current_basket'] = array();
}
require_once "modules/basket/class/class_modules_tools.php";
/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") {
    $init = true;
}
$level = "";
if (isset($_REQUEST['level'])
    && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3
        || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
) {
    $level = $_REQUEST['level'];
}
$pagePath = $_SESSION['config']['businessappurl'].'index.php?page=view_baskets&module=basket';
$pageLabel = _MY_BASKETS;
$pageId = "my_baskets";
$core->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
/***********************************************************/
$bask = new basket();
//$bask->load_basket();
if (isset($_REQUEST['baskets']) && ! empty($_REQUEST['baskets'])) {
    //$_SESSION['tmpbasket']['service'] = $_SESSION['user']['services'][0]['ID'];
    $_SESSION['tmpbasket']['status'] = "all";
    $bask->load_current_basket(trim($_REQUEST['baskets']), 'frame');
}
?><h1> <?php
if (count($_SESSION['user']['baskets']) > 0) {
    ?><div style="">
        <form name="select_basket" method="get"  id="select_basket"  action="<?php echo $_SESSION['config']['businessappurl'];?>index.php">
        <img src="<?php echo $_SESSION['config']['businessappurl']."static.php?filename=picto_basket_b.gif&module=basket";?>" alt="" /> <?php echo _VIEW_BASKETS_TITLE; ?> :
            <input type="hidden" name="page" id="page" value="view_baskets" />
            <input type="hidden" name="module" id="module" value="basket" />

            <select name="baskets"id="baskets" onchange="this.form.submit();" class="listext_big" >
                <option value=""><?php echo _CHOOSE_BASKET;?></option>
                <?php
    for ($i = 0; $i < count($_SESSION['user']['baskets']); $i ++) {
        ?>
        <option value="<?php
        if (isset($_SESSION['user']['baskets'][$i]['id'])) {
            echo $_SESSION['user']['baskets'][$i]['id'];
        }
        ?>" <?php
        if (isset($_SESSION['current_basket']['id'])
            && $_SESSION['current_basket']['id'] == $_SESSION['user']['baskets'][$i]['id']
        ) {
            echo 'selected="selected"';
        }
        ?>>
                        <?php echo $_SESSION['user']['baskets'][$i]['name'];?>
                      </option>
                    <?php
                }
                ?>
            </select>
        </form>
    </div>
    <?php
}
else
{?>
     <img src="<?php echo $_SESSION['config']['businessappurl']."static.php?filename=picto_basket_b.gif&module=basket";?>" alt="" /> <?php echo _VIEW_BASKETS_TITLE;
}?>
</h1>
<div id="inner_content">
<?php
if(count($_SESSION['user']['baskets'])== 0)
{
    ?><div align="center"><?php echo _NO_BASKET_DEFINED_FOR_YOU;?></div><?php

}

if(isset($_SESSION['current_basket']['page_include']) && !empty($_SESSION['current_basket']['page_include']))
{
    //$bask->show_array($_SESSION['current_basket']);
    include($_SESSION['current_basket']['page_include']);
}
else
{

    if(count($_SESSION['user']['baskets'])> 0)
    {
        $core->execute_modules_services($_SESSION['modules_services'], 'view_basket', "include");
        echo '<p style="border:0px solid;padding-left:250px;"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=arrow_up.gif" /></p>';
        ?><div align="left"  style="width:500px;"><?php

         echo "<p align = 'justify'>
         <table width='100%'>
             <tr>
                 <td width='80px'><img src='".$_SESSION['config']['businessappurl']."static.php?filename=lunch_guide.gif'> </td>
                 <td> <div class='block' align='center'>"._BASKET_WELCOME_TXT1."<br/>"._BASKET_WELCOME_TXT2.".<div class='block_end'>&nbsp;</div></div></td>
             </tr>
         </table>
         </p>";
         ?></div><?php

    }
}

?></div>
