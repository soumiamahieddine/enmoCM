<?php
/**
* Reopen Mail Class
*
* Contains all the specific functions to reopen mail
*
* @package  Maarch LetterBox 2.0
* @version 2.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class ReopenMail : Contains all the specific functions to reopen a mail
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package Maarch LetterBox 2.0
* @version 2.0
*/

class ReopenMail extends dbquery
{

	/**
	* Redefinition of the LetterBox object constructor
	*/
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Checks the res_id
	*
	* @param string $mode add or up
	*/
	public function reopen_mail_check()
	{
		if(empty($_REQUEST['id']))
		{
			$_SESSION['error'] = _ID.' '._IS_EMPTY;
		}
		else
		{
			$_SESSION['m_admin']['reopen_mail']['ID'] = $this->wash($_REQUEST['id'], "num", _ID." ");
		}
	}

	/**
	* Update databse
	*
	*/
	public function update_db()
	{
		// add ou modify users in the database
		$this->reopen_mail_check();
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=reopen_mail&id=".$_SESSION['m_admin']['reopen_mail']['ID']."&admin=reopen_mail");
			exit();
		}
		else
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
			$sec = new security();
			$ind_coll = $sec->get_ind_collection('letterbox_coll');
			$table = $_SESSION['collections'][$ind_coll]['table'];
			$this->connect();

			$this->query("select res_id from ".$table." where res_id = ".$_SESSION['m_admin']['reopen_mail']['ID']);
			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _NUM_GED." "._UNKNOWN;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=reopen_mail&id=".$_SESSION['m_admin']['reopen_mail']['ID']."&admin=reopen_mail");
				exit();
			}
			$this->query("update ".$table." set status = 'COU'
					where res_id = ".$_SESSION['m_admin']['reopen_mail']['ID']."");

			if($_SESSION['history']['resup'] == true )
			{
				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
				$hist = new history();
				$hist->add($table, $_SESSION['m_admin']['reopen_mail']['ID'],"UP",_REOPEN_THIS_MAIL." : ".$_SESSION['m_admin']['reopen_mail']['ID'], $_SESSION['config']['databasetype'], 'apps');
			}

			$_SESSION['error'] = _REOPEN_THIS_MAIL." : ".$_SESSION['m_admin']['reopen_mail']['ID'];
			unset($_SESSION['m_admin']);
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=admin");
			exit();
		}
	}


	/**
	* Form to reopen a mail
	*
	*/
	public function formreopenmail()
	{
		?>
		<h1><img src="<? echo $_SESSION['config']['businessappurl'];?>static.php?filename=default_status_big.gif" alt="" border="0" /> <? echo _REOPEN_MAIL;?></h1>

		<div id="inner_content" class="clearfix" align="center">
		<br /><br />
		<p ><? echo _MAIL_SENTENCE2._MAIL_SENTENCE3;?> </p>
		  <br/>
		  <p ><img src="<? echo $_SESSION['config']['businessappurl'];?>static.php?filename=separateur_1.jpg" width="90%" height="1" alt="" /></p>
		  <form name="form1" method="post" action="<? echo $_SESSION['config']['businessappurl']."index.php?display=true&admin=reopen_mail&page=reopen_mail_db";?>" >
		  <p>
			<label for="id"><? echo _ENTER_DOC_ID;?> : </h2>
				<input type="text" name="id" id="id" value="<?php echo $_SESSION['m_admin']['reopen_mail']['ID'];?>" />
		  </p >
			 <br/>

		   <p >(<? echo _TO_KNOW_ID;?>) </p>

			<br/>
			<p class="buttons">
					<input type="submit" name="Submit" value="<? echo _MODIFY_STATUS;?>" class="button"/>
					<input type="button" name="close" value="<? echo _CANCEL;?>" onclick="javascript:window.location.href='<? echo $_SESSION['config']['businessappurl'];?>index.php?page=admin';" class="button"/>
				</p>

		  </form>
		</div>
	<?php
	}
}
?>
