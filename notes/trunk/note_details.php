<?php 
/**
* File : notes_details.php
*
* Popup to show the notes
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/ 
include('core/init.php'); 
 

require_once("core/class/class_functions.php");
require("core/class/class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();

require_once("core/class/class_db.php");

$func = new functions();
$db = new dbquery();
$db->connect();
$table ='';
$coll_id = "";
$user = '';
$text = "";
$user_id = '';
$date = "";
if(empty($_SESSION['collection_id_choice']))
{
	$_SESSION['collection_id_choice']= $_SESSION['user']['collections'][0];
}
$error = '';
if(isset($_REQUEST['modify']) )
{
	$id = $_REQUEST['id'];
	$table = $_REQUEST['table'];
	$coll_id = $_REQUEST['coll_id'];
	
	if(empty($_REQUEST['notes']))
	{
		$error = _NOTES.' '._EMPTY;
	}
	elseif(empty($error))
	{
		$text = $func->protect_string_db($_REQUEST['notes']);
		$db->query("UPDATE ".$_SESSION['tablename']['not_notes']." SET 
		note_text = '".$text."', date = '".date("Y")."-".date("m")."-".date("d")." ".date("H:i:s")."' 
		WHERE id = ".$id);
				
		if($_SESSION['history']['noteup'])
		{
			require_once("core/class/class_history.php");
			$hist = new history();								
			$hist->add($_SESSION['tablename']['not_notes'], $id ,"UP", _NOTE_UPDATED, $_SESSION['config']['databasetype'], 'notes');
		}
		$_SESSION['error'] = _NOTES_MODIFIED;
		?>
       <script language="javascript">window.opener.location.reload();self.close();</script>
        <?php 
		exit();
	}
	
}
if(isset($_REQUEST['delete']) )
{
	$id = $_REQUEST['id'];
	
	$db->query("delete from ".$_SESSION['tablename']['not_notes']." where id = ".$id);
				
	if($_SESSION['history']['notedel'])
	{
		require_once("core/class/class_history.php");
		$hist = new history();								
		$hist->add($_SESSION['tablename']['not_notes'], $id ,"DEL", _NOTES_DELETED, $_SESSION['config']['databasetype'], 'notes');
	}
	$_SESSION['error'] = _NOTES_DELETED;
	?>
      <script language="javascript">window.opener.location.reload();self.close();</script>
       <?php 
	exit();
}

if(isset($_REQUEST['id']))
{
	$s_id = $_REQUEST['id'];
}
else
{
	$s_id = "";
}
if(isset($_REQUEST['table']) && empty($table))
{
	$table = $_REQUEST['table'];
}

if(isset($_REQUEST['coll_id'])&& empty($coll_id))
{
	$coll_id = $_REQUEST['coll_id'];
}

$core_tools->load_html();
//here we building the header
$core_tools->load_header(_NOTE);	
?>
<body id="pop_up">
<?php 
if(empty($table) && empty($coll_id))
{
	$error =  _PB_TABLE_COLL;
}
else
{
	if(!empty($coll_id))
	{
		$where = " and coll_id = '".$coll_id."'";
	}
	else
	{
		$where = " and tablename = '".$table."'";
	}
	$db->query("select  n.date, n.user_id, n.note_text, u.lastname, u.firstname 
	from ".$_SESSION['tablename']['not_notes']." n
	inner join ".$_SESSION['tablename']['users']." u on n.user_id  = u.user_id 
	where n.id = ".$s_id." ".$where);
	
	$line = $db->fetch_object();
	$user = $func->show_string($line->lastname." ".$line->firstname);
	$text = $func->show_string($line->note_text);
	$user_id = $line->user_id;
	$date = $line->date;
}	

$can_modify = false;
if(trim($user_id) == $_SESSION['user']['UserId'])
{
	$can_modify = true;	
}
?>
<div class="error"><?php  echo $error; $error = '';?></div>
<h2 class="tit" style="padding:10px;"><?php  echo _NOTES;?> </h2>
	<h2 class="sstit" style="padding:10px;"><?php  echo _NOTES." "._OF." ".$user." (".$date.") ";?></h2>

	<div class="block" style="padding:10px">
      <form name="form1" method="post" class="forms" action="<?php  echo $_SESSION['urltomodules']."notes/note_details.php"; ?>">
		<textarea  <?php  if(!$can_modify){?>readonly="readonly" class="readonly" <?php  } ?>style="width:380px" cols="70" rows="10"  name="notes"  id="notes"><?php  echo $text; ?></textarea>
	  
      	<input type="hidden" name="id" id="id" value="<?php  echo $s_id; ?>"/>
        <input type="hidden" name="table" id="table" value="<?php  echo $table; ?>"/>
        <input type="hidden" name="coll_id" id="coll_id" value="<?php  echo $coll_id; ?>"/>
      
	   <br/>
        <p class="buttons">
             
                <?php  if($can_modify)
				{?>
                	<input type="submit" name="modify" id="modify" value="<?php  echo _MODIFY;?>"  class="button"/>
                    <input type="submit" name="delete" id="delete" value="<?php  echo _DELETE;?>"  class="button"/>
       		 <?php  }?>
             <input type="button" name="close_button" value="<?php  echo _CLOSE_WINDOW;?>" onClick="javascript:self.close();" class="button"/>
            </p>
         	
      </form>
	</div>
	<div class="block_end">&nbsp;</div>
</body>
</html>
