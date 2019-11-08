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

/**
* @brief Modify a structure
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
require_once "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_history.php";
$core = new core_tools();
$core->test_admin('admin_contacts', 'apps');
$core->load_lang();

$mode = "";
if (isset($_GET['id']) && ! empty($_GET['id'])) {
	$mode = 'up';
	$_SESSION['m_admin']['mode'] = $mode;
} else if (isset($_SESSION['m_admin']['mode']) && ! empty($_SESSION['m_admin']['mode'])){
	$mode = 'up';
} else {
	$_SESSION['CURRENT_ID_CONTACT_TYPE'] = '';
	$_SESSION['CURRENT_DESC_CONTACT_TYPE'] = '';
	$_SESSION['CURRENT_TARGET_CONTACT_TYPE'] = '';
	$_SESSION['CURRENT_CONTACT_CREATION'] = '';

}

/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") {
    $init = true;
}
$level = "";
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2
    || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4
    || $_REQUEST['level'] == 1)
) {
    $level = $_REQUEST['level'];
}
$pagePath = $_SESSION['config']['businessappurl'] . 'index.php?page=contact_types_up';
if ($mode == "up") {
	$pageLabel = _CONTACT_TYPE_MODIF;
} else {
	$pageLabel = _NEW_CONTACT_TYPE_ADDED;
}
$pageId = "contact_types_up";
$core->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
/***********************************************************/

$db = new Database();

$desc = "";
$id = "";

if (isset($_GET['id']) && ! empty($_GET['id'])) {
	$id = $_GET['id'];
	$stmt = $db->query(
		"SELECT label, contact_target, can_add_contact FROM "
	    . $_SESSION['tablename']['contact_types']
	    . " WHERE id = ?", array($id)
	);

	$res = $stmt->fetchObject();
	$desc = functions::show_string($res->label);
	$_SESSION['CURRENT_ID_CONTACT_TYPE'] = $id;
	$_SESSION['CURRENT_DESC_CONTACT_TYPE'] = $desc;
	$_SESSION['CURRENT_TARGET_CONTACT_TYPE'] = $res->contact_target;
	$_SESSION['CURRENT_CONTACT_CREATION'] = $res->can_add_contact;
}

$erreur = "";
if (isset($_REQUEST['valid'])) {
	if (isset($_REQUEST['desc_contact_types'])
	    && ! empty($_REQUEST['desc_contact_types'])
	) {
		$desc = $_REQUEST['desc_contact_types'];
		$contact_target = $_REQUEST['contact_target'];
		$contact_creation = $_REQUEST['contact_creation'];
        $desc=str_replace(';', ' ', $desc);
        $desc=str_replace('--', '-', $desc);
	    $desc = $core->wash(
	        $desc, 'no', _CONTACT_TYPE, 'yes', 0, 255
	    );
	    if($_SESSION['error'] <> ''){
	    	$_SESSION['error'] = '';
	    	$erreur .= _CONTACT_TYPE .' '. MUST_BE_LESS_THAN." 255 "._CHARACTERS;
	    }	
	} else {
		$erreur .= _CONTACT_TYPE_MISSING . ".<br/>";
	}

	if (empty($erreur)) {
		if(utf8_encode(utf8_decode($desc)) != $desc) {
			$desc = utf8_encode($desc);
		}
		$stmt = $db->query(
			"SELECT * FROM ".$_SESSION['tablename']['contact_types']
		    . " WHERE lower(label) = lower(?)",
		    array($desc)
		);

		if ($stmt->rowCount() > 0 && $mode <> 'up') {
			$erreur .= _THIS_CONTACT_TYPE . ' ' . _ALREADY_EXISTS ;
		} else {
			if ($mode == "up") {
				$stmt = $db->query(
					"SELECT * FROM ".$_SESSION['tablename']['contact_types']
				    . " WHERE lower(label) = lower(?) and id <> ?",
				    array($desc, $_REQUEST['ID_contact_types'])
				);	
				if($stmt->rowCount() > 0){
					$erreur .= _THIS_CONTACT_TYPE . ' ' . _ALREADY_EXISTS ;
				} else {			
					if (isset($_REQUEST['ID_contact_types'])
					    && ! empty($_REQUEST['ID_contact_types'])
					) {
						$id = $_REQUEST['ID_contact_types'];
						$db->query(
							"UPDATE " . $_SESSION['tablename']['contact_types']
						    . " SET label = ?, contact_target = ?, can_add_contact = ? WHERE id = ?",
						    array($desc, $contact_target, $contact_creation, $id)
						);

						if ($_SESSION['history']['contact_types_up'] == "true") {
							$hist = new history();
							$hist->add(
							    $_SESSION['tablename']['contact_types'], $id,
							    "UP", 'contact_types_up', _CONTACT_TYPE_MODIF . " " . strtolower(_NUM)
							    . $id,
							    $_SESSION['config']['databasetype']
							);
						}
						$_SESSION['info'] .= _CONTACT_TYPE_MODIF . " : " . $id;
					} else {
						$erreur .= _ID_CONTACT_TYPE_PB . ".";
					}
				}
			} else {
				$desc = $_REQUEST['desc_contact_types'];
				if(utf8_encode(utf8_decode($desc)) != $desc) {
					$desc = utf8_encode($desc);
				}
	            $desc=str_replace(';', ' ', $desc);
	            $desc=str_replace('--', '-', $desc);				
				$db->query(
					"INSERT INTO "
				    . $_SESSION['tablename']['contact_types']
				    . " ( label, contact_target, can_add_contact) VALUES (?, ?, ?)",
					array($desc, $contact_target, $contact_creation)
				);
				$stmt = $db->query(
					"SELECT id FROM "
				    . $_SESSION['tablename']['contact_types']
				    . " WHERE label = ?",
				    array($desc)
				);
				$res = $stmt->fetchObject();
				$id = $res->id;

				if ($_SESSION['history']['contact_types_add'] == "true") {
					$hist = new history();
					$hist->add(
					    $_SESSION['tablename']['contact_types'], $id,
					    "ADD", 'contact_types_add', _NEW_CONTACT_TYPE_ADDED . " (" . $desc . ")",
					    $_SESSION['config']['databasetype']
					);

				}
				$_SESSION['info'] .= _NEW_CONTACT_TYPE . " : " . $desc;
			}
		}
	}
	if (empty($erreur)) {
		unset($_SESSION['m_admin']);
		if (isset($_SESSION['fromContactTree']) && $_SESSION['fromContactTree'] == 'yes') {
			$_SESSION['error'] = "";
			?><script type="text/javascript">window.location.href="<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=view_tree_contacts";</script><?php
		} else {
		?>
	   		<script type="text/javascript">window.location.href="<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=contact_types";</script>
		<?php
		}
		exit();
	} else {
		?>
		<div id="header">
        <div id="nav">
            <div><p id="ariane"><?php
            ?></p></div>
            <p id="gauchemenu"></p>
            <a href="index.php"><p id="logo"></p></a>
       </div>
		</div>
		<div id="container">
		    <div id="content">
		<?php
		/****************Management of the location bar  ************/
		$core->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
		/***********************************************************/
	}
}

$core->load_html();

if ($mode == "up") {
	$title = _CONTACT_TYPE_MODIF;
} else {
	$title = _NEW_CONTACT_TYPE_ADDED;
}
$core->load_header($title, true, false);
$time = $core->get_session_time_expire();
?>
<!-- <body onload="setTimeout(window.close, <?php echo $time;?>*60*1000);window.resizeTo(700,700);"> -->
<br/>

<div class="error">
<?php
functions::xecho($erreur);
$erreur = "";
?>
</div>
<h1 class="tit">
<?php
if ($mode == "up") {
	?><i class = "fa fa-edit fa-2x"></i><?php
    echo _CONTACT_TYPE_MODIF;
} else {
	?><i class = "fa fa-plus fa-2x"></i>&nbsp;<?php
    echo _NEW_CONTACT_TYPE_ADDED;
}
?></h1>
<div class="block">
<br/>

<br/>
<form method="post" name="frmcontact_types" id="frmcontact_types" class="forms" action="<?php
	echo $_SESSION['config']['businessappurl'];
	?>index.php?display=true&page=contact_types_up">
		<input type="hidden" name="display" value="true" />
	    <input type="hidden" name="page" value="contact_types_up" />
	    <?php 
/*	    if (isset($_SESSION['fromContactTree']) && $_SESSION['fromContactTree']=="yes"){
	    		?><input type="hidden" name="fromContactTree" value="yes" /><?php
	    	}*/
	if ($mode == "up") {
	    ?>
		<p>
	    	<label><?php
	    echo _ID . ' ' . _CONTACT_TYPE;
	    ?> :</label>
			<input type="text" class="readonly" name="ID_contact_types" value="<?php
		functions::xecho($_SESSION['CURRENT_ID_CONTACT_TYPE']);
		?>" readonly="readonly" />
	     </p>
	     <p>&nbsp;</p>
		<?php
	}
	?>

		<p>
	    	<label>
	    		<?php echo _CONTACT_TYPE;?> :
	    	</label>
		   <input 
		   		type="text"  name="desc_contact_types" value="<?php functions::xecho($_SESSION['CURRENT_DESC_CONTACT_TYPE']);?>" 
			/>
	     </p>

		<p>
	    	<label>
	    		<?php echo _CONTACT_TARGET;?>
	    	</label>
		   <select name="contact_target" id="contact_target" >
		   		<option value="both" <?php if($_SESSION['CURRENT_TARGET_CONTACT_TYPE'] == 'both'){?> selected="selected"<?php } ?> ><?php echo _IS_CORPORATE_PERSON . " ". _AND ." " . _INDIVIDUAL;?></option>
		   		<option value="corporate" <?php if($_SESSION['CURRENT_TARGET_CONTACT_TYPE'] == 'corporate'){?> selected="selected"<?php } ?> ><?php echo _IS_CORPORATE_PERSON;?></option>
		   		<option value="no_corporate" <?php if($_SESSION['CURRENT_TARGET_CONTACT_TYPE'] == 'no_corporate'){?> selected="selected"<?php } ?> ><?php echo _INDIVIDUAL;?></option>
			</select>
		</p>
		<p>
			<label>
				<?php echo _CONTACT_TYPE_CREATION;?>
			</label>
			<input name="contact_creation" value="Y" type="radio"
				   <?php if($_SESSION['CURRENT_CONTACT_CREATION'] == 'Y' || $_SESSION['CURRENT_CONTACT_CREATION'] == ''){?>checked=""<?php }?> ><?php echo _YES;?>
			<input name="contact_creation" value="N" type="radio"
				   <?php if($_SESSION['CURRENT_CONTACT_CREATION'] == 'N'){?>checked=""<?php }?>><?php echo _NO;?>
		</p>
	<p class="buttons">
		<input type="submit" name="valid" class="button" value="<?php
	echo _VALIDATE;
	?>" />
		<input type="button" class="button"  name="cancel" value="<?php
	echo _CANCEL;
	?>" 
	    <?php 
	    if (isset($_SESSION['fromContactTree']) && $_SESSION['fromContactTree']=="yes"){
    		?> onclick="window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=view_tree_contacts';" /><?php
    	} else {
    		?> onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=contact_types';" /><?php
    	}?>
	<br/><br/>
	<input type="hidden" name="mode" value="<?php echo $mode;?>"/>

</form>

</div>

<div class="block_end">&nbsp;</div>
<!-- </body> -->
</html>

<?php 
if (isset($_REQUEST['valid'])) {
?>
		    <p id="footer">
		        <?php
		        if (isset($_SESSION['config']['showfooter'])
		            && $_SESSION['config']['showfooter'] == 'true'
		        ) {
		            $core->load_footer();
		        }
		        ?>
		    </p>
		    <?php
		    $_SESSION['error'] = '';
		    $_SESSION['info'] = '';
		    $core->view_debug();
		    ?>
		</div>
	</div>
<?php
}
