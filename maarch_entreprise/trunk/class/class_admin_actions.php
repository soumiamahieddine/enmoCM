<?php
/**
* Admin action Class
*
* Contains all the specific functions of action admin
*
* @package  Maarch LetterBox 2.0
* @version 2.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class AdminActions : Contains all the specific functions of action admin
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package Maarch LetterBox 2.0
* @version 2.0
*/

class AdminActions extends dbquery
{
	/**
	* Redefinition of the LetterBox object constructor
	*/
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Return the action data in sessions vars
	*
	* @param string $mode add or up
	*/
	public function actioninfo($mode)
	{

		// return the user information in sessions vars
		$func = new functions();
		if($_REQUEST['mode'] == "up")
		{
			$_SESSION['m_admin']['action']['ID'] = $func->wash($_REQUEST['id'], "no", _ID." ");
		}
		$_SESSION['m_admin']['action']['LABEL'] = $func->wash($_REQUEST['label'], "no", _DESC." ", 'yes', 0, 255);
		if(empty($_REQUEST['action_page']))
		{
			$_SESSION['m_admin']['action']['ID_STATUS'] = $func->wash($_REQUEST['status'], "no", _STATUS." ",  'yes', 0, 10);
		}
		else
		{
			$_SESSION['m_admin']['action']['ID_STATUS'] = trim($_REQUEST['status']);
		}
		if(empty($_REQUEST['status']))
		{
			$_SESSION['m_admin']['action']['ACTION_PAGE'] = $func->wash($_REQUEST['action_page'], "no", _ACTION_PAGE." ", 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['action']['ACTION_PAGE'] = trim($_REQUEST['action_page']);
		}
		$_SESSION['m_admin']['action']['HISTORY'] = $func->wash($_REQUEST['history'], "no", _HISTORY." ");

		$_SESSION['m_admin']['action']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['action']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['action']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['action']['start'] = $_REQUEST['start'];
	}

	/**
	* Add ou modify action in the database
	*
	* @param string $mode up or add
	*/
	public function addupaction($mode)
	{
		// add ou modify users in the database
		$this->actioninfo($mode);
		$order = $_SESSION['m_admin']['action']['order'];
		$order_field = $_SESSION['m_admin']['action']['order_field'];
		$what = $_SESSION['m_admin']['action']['what'];
		$start = $_SESSION['m_admin']['action']['start'];

		if(!empty($_SESSION['error']))
		{
			if($mode == "up")
			{
				if(!empty($_SESSION['m_admin']['action']['ID']))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action_up&id=".$_SESSION['m_admin']['action']['ID']."&admin=action");
					exit;
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit;
				}
			}
			if($mode == "add")
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action_add&admin=action");
				exit;
			}
		}
		else
		{
			$this->connect();

			if($mode == "add")
			{
				$this->query("INSERT INTO ".$_SESSION['tablename']['actions']." ( label_action, id_status, action_page, history)
				VALUES (  '".$this->protect_string_db($_SESSION['m_admin']['action']['LABEL'])."',
				'".$this->protect_string_db($_SESSION['m_admin']['action']['ID_STATUS'])."', '".$this->protect_string_db($_SESSION['m_admin']['action']['ACTION_PAGE'])."',
				'".$this->protect_string_db($_SESSION['m_admin']['action']['HISTORY'])."' )");

				if($_SESSION['history']['actionadd'])
				{
					$this->query("select id from ".$_SESSION['tablename']['actions']." where label_action = '".$this->protect_string_db($_SESSION['m_admin']['action']['LABEL'])."' and id_status = '".$this->protect_string_db($_SESSION['m_admin']['action']['ID_STATUS'])."' and action_page = '".$this->protect_string_db($_SESSION['m_admin']['action']['ACTION_PAGE'])."' and history = '".$this->protect_string_db($_SESSION['m_admin']['action']['HISTORY'])."'");
					$res = $this->fetch_object();
					$id = $res->id;
					require_once($_SESSION['pathtocoreclass'].'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['actions'], $id,"ADD",_ACTION_ADDED.' : '.$this->protect_string_db($_SESSION['m_admin']['action']['LABEL']), $_SESSION['config']['databasetype']);
				}
				$_SESSION['error'] = _ACTION_ADDED.' : '.$_SESSION['m_admin']['action']['LABEL'];
				$this->clearactioninfos();
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();

			}
			elseif($mode == "up")
			{
				$this->query("update ".$_SESSION['tablename']['actions']." set label_action = '".$this->protect_string_db($_SESSION['m_admin']['action']['LABEL'])."', id_status = '".$this->protect_string_db($_SESSION['m_admin']['action']['ID_STATUS'])."', action_page = '".$this->protect_string_db($_SESSION['m_admin']['action']['ACTION_PAGE'])."', history = '".$this->protect_string_db($_SESSION['m_admin']['action']['HISTORY'])."' where id = ".$_SESSION['m_admin']['action']['ID']."");

				if($_SESSION['history']['actionup'])
				{
					require_once($_SESSION['pathtocoreclass'].'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['actions'], $_SESSION['m_admin']['action']['ID'],"UP",_ACTION_MODIFIED.' : '.$this->protect_string_db($_SESSION['m_admin']['action']['LABEL']), $_SESSION['config']['databasetype']);
				}

				$_SESSION['error'] = _ACTION_MODIFIED.' : '.$_SESSION['m_admin']['action']['LABEL'];
				$this->clearactioninfos();
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
		}
	}


	/**
	* Form to modify a action
	*
	* @param  $string $mode up or add
	* @param int  $id  $id of the action to change
	*/
	public function formaction($mode,$id = "")
	{
		$func = new functions();

		$state = true;

		if(!isset($_SESSION['m_admin']['action']))
		{
			$this->clearactioninfos();
		}

		if( $mode <> "add")
		{
			$this->connect();
			$this->query("select * from ".$_SESSION['tablename']['actions']." where id = '".$id."'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _THE_ACTION.' '._ALREADY_EXISTS;
				$state = false;
			}
			else
			{
				$_SESSION['m_admin']['action'] = array();
				$line = $this->fetch_object();

				$_SESSION['m_admin']['action']['ID'] = $line->id;
				$_SESSION['m_admin']['action']['LABEL'] = $this->show_string($line->label_action);
				$_SESSION['m_admin']['action']['ID_STATUS'] = $this->show_string($line->id_status);
				$_SESSION['m_admin']['action']['IS_SYSTEM'] = $this->show_string($line->is_system);
				$_SESSION['m_admin']['action']['ACTION_PAGE'] = $this->show_string($line->action_page);
				$_SESSION['m_admin']['action']['HISTORY'] = $this->show_string($line->history);
			}
		}
		else if($mode == 'add')
		{
			$_SESSION['m_admin']['action']['IS_SYSTEM'] = 'N';
			$_SESSION['m_admin']['action']['HISTORY'] = 'Y';
		}

		$this->connect();
		$this->query("select * from ".$_SESSION['tablename']['status']." order by label_status");

		$arr_status = array();

		while($res = $this->fetch_object())
		{
			array_push($arr_status, array('id' => $res->id, 'label' => $res->label_status, 'is_system' => $res->is_system, 'img_filename' => $res->img_filename,
			'module' => $res->module, 'can_be_searched' => $res->can_be_searched, 'can_be_modified' => $res->can_be_modified));
		}
		?>
		<h1><img src="<? echo $_SESSION['config']['img'];?>/manage_actions_b.gif" alt="" />
				<?php
				if($mode == "up")
				{
					echo _MODIFY_ACTION;
				}
				elseif($mode == "add")
				{
					echo _ADD_ACTION;
				}
				?>
				</h1>


		<div id="inner_content" class="clearfix" align="center">
		<br /><br />

			<?php
			if($state == false)
			{
				echo "<br /><br />"._THE_ACTION." "._UNKOWN."<br /><br /><br /><br />";
			}
			else
			{

				?>
				<form name="frmaction" id="frmaction" method="post" action="<? echo $_SESSION['config']['businessappurl']."admin/action/action_up_db.php";?>" class="forms addforms">
					<input type="hidden" name="mode" id="mode" value="<? echo $mode;?>" />
					<input type="hidden" name="id" id="id" value="<? echo $_SESSION['m_admin']['action']['ID'];?>" />
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
					<p>
					 	<label for="label"><?php echo _DESC; ?> : </label>
						<input name="label" type="text"  id="label" value="<?php echo $func->show($_SESSION['m_admin']['action']['LABEL']); ?>"/>
					</p>
					<? if($_SESSION['m_admin']['action']['IS_SYSTEM']  == 'Y')
					{

						echo '<div class="error">'._DO_NOT_MODIFY_UNLESS_EXPERT.'</div><br/>';
					}
					?>
					<p>
                        <label ><?php echo _ASSOCIATED_STATUS; ?> : </label>

                        <select name="status" id="status">
							<option value=""><? echo _CHOOSE_STATUS;?></option>
							<?
								for($i=0; $i<count($arr_status);$i++)
								{
									?><option value="<? echo $arr_status[$i]['id'];?>" <? if($_SESSION['m_admin']['action']['ID_STATUS'] == $arr_status[$i]['id']) { echo 'selected="selected"';}?>><? echo $arr_status[$i]['label'];?></option><?
								}
							?>
						</select>

                    </p>
					<p>
						<label><? echo _ACTION_PAGE;?> : </label>
						<select name="action_page" id="action_page">
							<option value=""><? echo _NO_PAGE;?></option>
						<? for($i=0; $i< count($_SESSION['actions_pages']); $i++)
						{
							?><option value="<? echo $_SESSION['actions_pages'][$i]['ID'];?>" <? if($_SESSION['actions_pages'][$i]['ID'] == $_SESSION['m_admin']['action']['ACTION_PAGE']){ echo 'selected="selected"';}?> ><? echo $_SESSION['actions_pages'][$i]['LABEL'];?></option><?
						}?>
						</select>
					</p>
                     <p>
					 	<label for="history"><?php echo _ACTION_HISTORY; ?> : </label>
						<input type="radio"  class="check" name="history" value="Y" <? if($_SESSION['m_admin']['action']['HISTORY'] == 'Y'){ echo 'checked="checked"';}?> /><? echo _YES;?>
						<input type="radio"  class="check" name="history" value="N" <? if($_SESSION['m_admin']['action']['HISTORY'] == 'N'){ echo 'checked="checked"';}?>/><? echo _NO;?>
					</p>
					 <p class="buttons">
						<?php

					if($mode == "up")
						{
						?>
							<input class="button" type="submit" name="Submit" value="<?php echo _MODIFY_ACTION; ?>" />
						<?php
						}

						elseif($mode == "add")
						{
						?>
							<input type="submit" class="button"  name="Submit" value="<?php echo _ADD_ACTION; ?>" />
						<?
						}
						?>
                       <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<? echo $_SESSION['config']['businessappurl'];?>index.php?page=action&amp;admin=action';"/>
					</p>
				</form >

				<div class="infos"><? echo _INFOS_ACTIONS;?></div>
			<?php
			}
			?>

		</div>
	<?php
	}

	/**
	* Clear the session variables of the edmit 's administration
	*
	*/
	private function clearactioninfos()
	{
		// clear the session variable
		$_SESSION['m_admin']['action'] = array();
		$_SESSION['m_admin']['action']['ID'] = '';
		$_SESSION['m_admin']['action']['LABEL'] = '';
		$_SESSION['m_admin']['action']['ID_STATUS'] = '';
		$_SESSION['m_admin']['action']['ACTION_PAGE'] = '';
		$_SESSION['m_admin']['action']['HISTORY'] = 'Y';
	}

	/**
	* delete an action in the database
	*
	* @param string $id model identifier
	*/
	public function delaction($id)
	{
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
			exit;
		}
		else
		{
			$this->connect();

			$this->query("select id from ".$_SESSION['tablename']['actions']." where id = '".$id."'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _THE_ACTION.' '._UNKNOWN;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
			else
			{
				$res = $this->fetch_object();
				$label = $res->LABEL;
				$this->query("delete from ".$_SESSION['tablename']['actions']." where id = '".$id."'");

				if($_SESSION['history']['actiondel'])
				{
					require_once($_SESSION['pathtocoreclass'].'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['actions'], $id,"DEL",_ACTION_DELETED.' : '.$id, $_SESSION['config']['databasetype']);
				}
				$_SESSION['error'] = _ACTION_DELETED;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=action&admin=action&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
		}
	}
}
?>
