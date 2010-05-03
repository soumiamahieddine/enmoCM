<?php
/**
* Admin status Class
*
* Contains all the specific functions of status admin
*
* @package  Maarch LetterBox 2.0
* @version 2.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class AdminStatus : Contains all the specific functions of status admin
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package Maarch LetterBox 2.0
* @version 2.0
*/

class AdminStatus extends dbquery
{
	/**
	* Redefinition of the LetterBox object constructor
	*/
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Return the status data in sessions vars
	*
	* @param string $mode add or up
	*/
	public function statusinfo($mode)
	{

		// return the user information in sessions vars
		$func = new functions();
		$_SESSION['m_admin']['status']['ID'] = $func->wash($_REQUEST['id'], "no", _ID." ");

		$_SESSION['m_admin']['status']['LABEL'] = $func->wash($_REQUEST['label'], "no", _DESC." ", 'yes', 0, 50);

		$_SESSION['m_admin']['status']['IS_SYSTEM'] = $func->wash($_REQUEST['is_system'], "no", _IS_SYSTEM." ");

		$_SESSION['m_admin']['status']['IMG_FILENAME'] = '';
		$_SESSION['m_admin']['status']['MODULE'] = 'apps';

		$_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] = $func->wash($_REQUEST['can_be_searched'], "no", CAN_BE_SEARCHED." ");
		$_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] = $func->wash($_REQUEST['can_be_modified'], "no", _CAN_BE_MODIFIED." ");

		$_SESSION['m_admin']['status']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['status']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['status']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['status']['start'] = $_REQUEST['start'];
	}

	/**
	* Add ou modify status in the database
	*
	* @param string $mode up or add
	*/
	public function addupstatus($mode)
	{
		// add ou modify users in the database
		$this->statusinfo($mode);
		$order = $_SESSION['m_admin']['status']['order'];
		$order_field = $_SESSION['m_admin']['status']['order_field'];
		$what = $_SESSION['m_admin']['status']['what'];
		$start = $_SESSION['m_admin']['status']['start'];
		if(!empty($_SESSION['error']))
		{
			if($mode == "up")
			{
				if(!empty($_SESSION['m_admin']['status']['ID']))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status_up&id=".$_SESSION['m_admin']['status']['ID']."&admin=status");
					exit;
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit();
				}
			}
			if($mode == "add")
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status_add&admin=status");
				exit();
			}
		}
		else
		{
			$this->connect();

			if($mode == "add")
			{
				$this->query("INSERT INTO ".$_SESSION['tablename']['status']." ( id, label_status, img_filename, is_system, maarch_module, can_be_searched, can_be_modified)
				VALUES (  '".$this->protect_string_db($_SESSION['m_admin']['status']['ID'])."', '".$this->protect_string_db($_SESSION['m_admin']['status']['LABEL'])."',
				'".$this->protect_string_db($_SESSION['m_admin']['status']['IMG_FILENAME'])."','".$this->protect_string_db($_SESSION['m_admin']['status']['IS_SYSTEM'])."',
				'".$this->protect_string_db($_SESSION['m_admin']['status']['MODULE'])."', '".$this->protect_string_db($_SESSION['m_admin']['status']['CAN_BE_SEARCHED'])."', '".$this->protect_string_db($_SESSION['m_admin']['status']['CAN_BE_MODIFIED'])."' )");

				if($_SESSION['history']['statusadd'])
				{
					require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['status'], $this->protect_string_db($_SESSION['m_admin']['status']['ID']),"ADD",_STATUS_ADDED.' : '.$this->protect_string_db($_SESSION['m_admin']['status']['LABEL']), $_SESSION['config']['databasetype']);
				}
				$_SESSION['error'] = _STATUS_ADDED.' '.$_SESSION['m_admin']['status']['LABEL'];
				$this->clearstatusinfos();

				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();

			}
			elseif($mode == "up")
			{
				$this->query("update ".$_SESSION['tablename']['status']." set label_status = '".$this->protect_string_db($_SESSION['m_admin']['status']['LABEL'])."', img_filename = '".$this->protect_string_db($_SESSION['m_admin']['status']['IMG_FILENAME'])."',maarch_module = '".$this->protect_string_db($_SESSION['m_admin']['status']['MODULE'])."', can_be_searched = '".$this->protect_string_db($_SESSION['m_admin']['status']['CAN_BE_SEARCHED'])."' , can_be_modified = '".$this->protect_string_db($_SESSION['m_admin']['status']['CAN_BE_MODIFIED'])."' where id = '".$_SESSION['m_admin']['status']['ID']."'");

				if($_SESSION['history']['statusup'])
				{
					require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['status'], $this->protect_string_db($_SESSION['m_admin']['status']['ID']),"UP",_STATUS_MODIFIED.' : '.$this->protect_string_db($_SESSION['m_admin']['status']['LABEL']), $_SESSION['config']['databasetype']);
				}
				$_SESSION['error'] = _STATUS_MODIFIED.' : '.$_SESSION['m_admin']['status']['LABEL'];
				$this->clearstatusinfos();

				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
		}
	}


	/**
	* Form to modify a status
	*
	* @param  $string $mode up or add
	* @param int  $id  $id of the status to change
	*/
	public function formstatus($mode,$id = "")
	{
		$func = new functions();

		$state = true;

		if(!isset($_SESSION['m_admin']['status']))
		{
			$this->clearstatusinfos();
		}

		if( $mode <> "add")
		{
			$this->connect();
			$this->query("select * from ".$_SESSION['tablename']['status']." where id = '".$id."'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _THE_STATUS.' '._ALREADY_EXISTS;
				$state = false;
			}
			else
			{
				$_SESSION['m_admin']['status'] = array();
				$line = $this->fetch_object();

				$_SESSION['m_admin']['status']['ID'] = $line->id;
				$_SESSION['m_admin']['status']['LABEL'] = $this->show_string($line->label_status);
				$_SESSION['m_admin']['status']['IS_SYSTEM'] = $this->show_string($line->is_system);
				$_SESSION['m_admin']['status']['IMG_FILENAME'] = $this->show_string($line->img_filename);
				$_SESSION['m_admin']['status']['MODULE'] = $this->show_string($line->maarch_module);
				$_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] = $this->show_string($line->can_be_searched);
				$_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] = $this->show_string($line->can_be_modified);
			}
		}
		else if($mode == 'add')
		{
			$_SESSION['m_admin']['status']['IS_SYSTEM'] = 'N';
			$_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] = 'Y';
			$_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] = 'Y';
		}
		?>
		<h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_status_b.gif" alt="" />
				<?php
				if($mode == "up")
				{
					echo _MODIFY_STATUS;
				}
				elseif($mode == "add")
				{
					echo _ADD_STATUS;
				}
				?>
				</h1>


		<div id="inner_content" class="clearfix" align="center">
		<br /><br />

			<?php
			if($state == false)
			{
				echo "<br /><br />"._THE_STATUS." "._UNKOWN."<br /><br /><br /><br />";
			}
			else
			{
				?>
				<form name="frmstatus" id="frmstatus" method="post" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&admin=status&page=status_up_db";?>" class="forms addforms">
					<input type="hidden" name="display" value="true" />
					<input type="hidden" name="admin" value="status" />
					<input type="hidden" name="page" value="status_up_db" />
					<input type="hidden" name="is_system" id="is_system" value="<?php echo $_SESSION['m_admin']['status']['IS_SYSTEM'];?>" />
					<input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
					<p>
					 	<label for="id"><?php echo _ID; ?> : </label>
						<input name="id" type="text"  id="id" value="<?php echo $func->show($_SESSION['m_admin']['status']['ID']); ?>" <?php if($mode == "up"){ echo 'readonly="readonly" class="readonly"';}?>/>
					</p>
					<p>
					 	<label for="label"><?php echo _DESC; ?> : </label>
						<input name="label" type="text"  id="label" value="<?php echo $func->show($_SESSION['m_admin']['status']['LABEL']); ?>"/>
					</p>
					<p>
                        <label ><?php echo _CAN_BE_SEARCHED; ?> : </label>
                        <input type="radio"  class="check" name="can_be_searched" value="Y" <?php if($_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] == 'Y'){?> checked="checked"<?php } ?> /><?php echo _YES;?>
                        <input type="radio" name="can_be_searched" class="check"  value="N" <?php if($_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] == 'N'){?> checked="checked"<?php } ?> /><?php echo _NO;?>
                    </p>
					<p>
                        <label ><?php echo _CAN_BE_MODIFIED; ?> : </label>
                        <input type="radio"  class="check" name="can_be_modified" value="Y" <?php if($_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] == 'Y'){?> checked="checked"<?php } ?> /><?php echo _YES;?>
                        <input type="radio" name="can_be_modified" class="check"  value="N" <?php if($_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] == 'N'){?> checked="checked"<?php } ?> /><?php echo _NO;?>
                    </p>

					 <p class="buttons">
						<?php

					if($mode == "up")
						{
						?>
							<input class="button" type="submit" name="Submit" value="<?php echo _MODIFY_STATUS; ?>" />
						<?php
						}

						elseif($mode == "add")
						{
						?>
							<input type="submit" class="button"  name="Submit" value="<?php echo _ADD_STATUS; ?>" />
						<?php
						}
						?>
                       <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=status&amp;admin=status';"/>
					</p>
				</form >

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
	private function clearstatusinfos()
	{
		// clear the session variable
		$_SESSION['m_admin']['status'] = array();
		$_SESSION['m_admin']['status']['ID'] = '';
		$_SESSION['m_admin']['status']['LABEL'] = '';
		$_SESSION['m_admin']['status']['IS_SYTEM'] = '';
		$_SESSION['m_admin']['status']['IMG_FILENAME'] = '';
		$_SESSION['m_admin']['status']['MODULE'] = '';
		$_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] = '';
		$_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] = '';
	}

	/**
	* delete a status in the database
	*
	* @param string $id model identifier
	*/
	public function delstatus($id)
	{
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
			exit;
		}
		else
		{
			$this->connect();

			$this->query("select id from ".$_SESSION['tablename']['status']." where id = '".$id."'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _THE_STATUS.' '._UNKNOWN;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
			else
			{
				$res = $this->fetch_object();
				$label = $res->LABEL;
				$this->query("delete from ".$_SESSION['tablename']['status']." where id = '".$id."'");

				if($_SESSION['history']['statusdel'])
				{
					require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['status'], $this->protect_string_db($id),"DEL",_STATUS_DELETED.' : '.$this->protect_string_db($id), $_SESSION['config']['databasetype']);
				}
				$_SESSION['error'] = _STATUS_DELETED." ".$id;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=status&admin=status&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
		}
	}
}
?>
