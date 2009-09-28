<?php
/*
*
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

/**
* @brief   Frame : displays a diffusion list (used in select_entity.php)
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtomodules']."basket".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$core_tools->test_service('select_entity', 'basket');
?>
<body>
<div align="center">
	<?php
    if((!isset($_SESSION['entity']) || empty($_SESSION['entity']))&& $_SESSION['origin'] <> "details")
    {
        echo "<br/><br/><div align=\"center\">"._CHOOSE_DEPARTMENT_FIRST.". </div>";
    }
    else
    {
		$conn = new dbquery();
        $conn->connect();
        if(!$_SESSION["popup_suite"])
        {
            $_SESSION['diff'] = array();
            $fields = " user_id, sequence ";
            if (($_SESSION['origin'] == 'validation' && $_SESSION['change_model'] == false) || $_SESSION['origin'] == "details")
            {
                $table = $_SESSION['tablename']['bask_listinstance'];
                $where = " res_id =".$_GET['id']." and coll_id = 'coll_1'";
                $msg = _NO_LIST_DEFINED__FOR_THIS_MAIL.".";
            }
            else
            {
                $table = $_SESSION['tablename']['bask_listmodels'];
                $where = " ID='".$_SESSION['entity']."' ";
                $msg = _NO_LIST_DEFINED__FOR_THIS_DEPARTMENT.".";
                if($_SESSION['change_model'] == true)
                {
                    $_SESSION['change_model'] = false;
                }
            }
            $conn->query("select ".$fields." from ".$table." where ".$where." order by sequence asc");
            while($line = $conn->fetch_object())
            {
                $_SESSION['diff'][$line->sequence-1]['UserID'] = $line->user_id;
            }
            for ($i=0;$i<count($_SESSION['diff']);$i++)
            {
                if($_SESSION['diff'][$i]['UserID']<>"")
                {
                    $conn->query("select firstname, lastname, department, mail from ".$_SESSION['tablename']['users']." where user_id='".$_SESSION['diff'][$i]['UserID']."'");
                    $line = $conn->fetch_object();
                    $_SESSION['diff'][$i]['FirstName'] = $line->firstname;
                    $_SESSION['diff'][$i]['LastName'] = $line->lastname;
                    $_SESSION['diff'][$i]['Service_id'] = $line->department;
                    $_SESSION['diff'][$i]['Service'] = $line->department;
                    $_SESSION['diff'][$i]['Mail'] = $line->mail;
                }
            }
            $_SESSION['liste_originale'] = array();
            $_SESSION['liste_originale'] = $_SESSION['diff'];
        }
        if(count($_SESSION['diff']) < 1)
        {
        	?>
            <br/>
            <div align="center"><?php echo $msg;?></div>
        	<?php
        }
        else
        {
			?>
            <h2 class="sstit"><?php echo _PRINCIPAL_RECIPIENT;?></h2>
            <table cellpadding="0" cellspacing="0" border="0" class="listingsmall">
                <thead>
                    <tr>
                        <th><?php echo _LASTNAME;?></th>
                        <th><?php echo _FIRSTNAME;?></th>
                        <th><?php echo _DEPARTMENT;?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $_SESSION['diff'][0]['LastName'];?></td>
                        <td><?php echo $_SESSION['diff'][0]['FirstName'];?></td>
                        <td><?php echo $_SESSION['diff'][0]['Service']; ?></td>
                    </tr>
                </tbody>
            </table>
            <br/>
            <h2 class="sstit"><?php echo _TO_CC;?></h2>
            <?php
            if(count($_SESSION['diff']) > 1)
            {
                ?>
                <table cellpadding="0" cellspacing="0" border="0" class="listingsmall">
                    <thead>
                            <tr>
                                <th><?php echo _LASTNAME;?></th>
                                <th><?php echo _FIRSTNAME;?></th>
                                <th><?php echo _DEPARTMENT;?></th>
                            </tr>
                    </thead>
                    <tbody>
                <?php
                $color = ' class="col"';
                for($i=1;$i<count($_SESSION['diff']);$i++)
                {
                    if($color == ' class="col"')
                    {
                        $color = '';
                    }
                    else
                    {
                        $color = ' class="col"';
                    }
                    ?>
                    <tr <?php echo $color; ?> >
                        <td><?php echo $_SESSION['diff'][$i]['LastName'];?></td>
                        <td><?php echo $_SESSION['diff'][$i]['FirstName'];?></td>
                        <td><?php echo $_SESSION['diff'][$i]['Service'];?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
                </table>
                <?php
            }
            else
            {
                ?>
                <div align="center"><span><i><?php echo _NO_COPY_FOR_RECIPIENT;?></i></span></div>
                <?php
            }
        }
    }
    ?>
</div>
</body>
</html>
