<?php
class admin_templates extends dbquery
{
	/**
	* Clear the session variables of the template administration
	*
	*/
	private function cleartemplateinfos()
	{
		// clear the session variable for the templates
		$_SESSION['m_admin']['template'] = array();
		$_SESSION['m_admin']['template']['ID'] = "";
		$_SESSION['m_admin']['template']['LABEL'] = "";
		$_SESSION['m_admin']['template']['COMMENT'] = "";
		$_SESSION['m_admin']['template']['DATE'] = "";
		$_SESSION['m_admin']['template']['CONTENT'] = "";
		$_SESSION['m_admin']['template']['ENTITIES'] = array();

	}

	/**
	* Form to add or modify a template
	*
	* @param string  $mode  up or add
	* @param string  $id  identifier of the template to modify
	*/
	public function formtemplate($mode,$id = "")
	{
		$core = new core_tools();
		$func = new functions();

		$state = true;

		if(!isset($_SESSION['m_admin']['template']))
		{
			$this->cleartemplateinfos();
		}

		if( $mode <> "add")
		{
			$this->connect();
			$this->query("select * from ".$_SESSION['tablename']['temp_templates']." where id = ".$id."");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _TEMPLATE.' '._UNKNOWN;
				$state = false;
			}
			else
			{
				$_SESSION['m_admin']['template'] = array();
				$line = $this->fetch_object();

				$_SESSION['m_admin']['template']['ID'] = $line->id;
				$_SESSION['m_admin']['template']['LABEL'] = $this->show_string($line->label);
				$_SESSION['m_admin']['template']['COMMENT'] = $this->show_string($line->template_comment);
				$_SESSION['m_admin']['template']['DATE'] = $line->creation_date;
				$_SESSION['m_admin']['template']['CONTENT'] =$this->show_string($line->content);

				$_SESSION['service_tag'] = 'load_template_session';
				echo $core->execute_modules_services($_SESSION['modules_services'], 'template_up.php', "include");
			}
		}
		$_SESSION['mode_editor'] = true;
		include("modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."load_editor.php");
		?>
		<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_add_b.gif&module=templates" alt="" />
				<?php
				if($mode == "up")
				{
					echo _TEMPLATE_MODIFICATION;
				}
				elseif($mode == "add")
				{
					echo _TEMPLATE_ADDITION;
				}
				?>
		</h1>
		<div id="inner_content" class="clearfix">
			<?php
			if($state == false)
			{
			?>
            	<script language="javascript" type="text/javascript">window.document.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=templates&module=templates';</script>
            <?php
			}
			else
			{
				?>
				<form name="frmtemplate" id="frmtemplate" method="post" action="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=templates&page=template_up_db" class="forms">
					<input type="hidden" name="display"  value="true" />
					<input type="hidden" name="module"  value="templates" />
					<input type="hidden" name="page"  value="template_up_db" />
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<input type="hidden" name="mode" id="mode" value="<?php  echo $mode;?>" />
						<?php  if($mode == "up")
						{
						?>
							<input  type="hidden" name="template_id" id="template_id" value="<?php  echo $_SESSION['m_admin']['template']['ID']; ?>"/>
						<?php
						}?>
                        <p>
                        	<label><?php  echo _TEMPLATE_NAME;?> : </label>
                            <input type="text" name="template_name" id="template_name" value="<?php  echo $_SESSION['m_admin']['template']['LABEL'];?>"/>
                        </p>
                        <p>
                        	<label><?php  echo _COMMENTS;?> : </label>
                             <textarea name="template_comment" id="template_comment" rows="3" cols="15" ><?php  echo $_SESSION['m_admin']['template']['COMMENT'];?></textarea>
                        </p>
                        <p>
                        <textarea name="template_content" style="width:100%" rows="15" cols="60">
							<?php  echo $_SESSION['m_admin']['template']['CONTENT'];?>
						</textarea>
                        </p>
                     <?php
					 	$_SESSION['service_tag'] = 'admin_templates';
						echo $core->execute_modules_services($_SESSION['modules_services'], 'admin_templates', "include");?>
						<p class="buttons">
                       		<input type="submit" class="button"  name="Submit" value="<?php  echo _VALIDATE; ?>" />
                       		<input type="button" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=templates&module=templates';" class="button"  name="cancel" value="<?php  echo _CANCEL; ?>" />
						</p>
					</form >
			<?php
			}
			?>
		</div>
	<?php
	}

	/**
	* Verify the template informations
	*
	*/
	private function templateinfo()
	{
		$func = new functions();

		if(!isset($_REQUEST['mode']))
		{
			$_SESSION['error'] = _UNKNOWN_PARAM."<br />";
		}
		$allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
 		$allowedTags.='<li><ol><ul><span><div><br><ins><del><blockquote><font><strike><table><tr><td><th><tbody><thead><tfooter><caption>';

  		if($_REQUEST['template_content']!='')
		{
  		  	//$_SESSION['m_admin']['template']['CONTENT'] = strip_tags(addslashes($_REQUEST['template_content']),$allowedTags);
  		  	$_SESSION['m_admin']['template']['CONTENT'] = strip_tags($this->protect_string_db($_REQUEST['template_content']),$allowedTags);
  		}
		else
		{
			$_SESSION['error'] .= _TEMPLATE_EMPTY.".<br/>";
		}
		$_SESSION['m_admin']['template']['LABEL'] = $func->wash($_REQUEST['template_name'], "no", _THE_WORDING);

		$_SESSION['m_admin']['template']['COMMENT'] = "";
		if(!empty($_REQUEST['template_comment']) && isset($_REQUEST['template_comment']))
		{
			$_SESSION['m_admin']['template']['COMMENT'] = $func->wash($_REQUEST['template_comment'], "no", _THE_TEMPLATE);
		}
		if($_REQUEST['mode'] == "up")
		{
			$_SESSION['m_admin']['template']['ID'] = $func->wash($_REQUEST['template_id'], "no", _ID);
		}
		$_SESSION['m_admin']['template']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['template']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['template']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['template']['start'] = $_REQUEST['start'];
		$_SESSION['service_tag'] = 'template_info';
		$core = new core_tools();
		echo $core->execute_modules_services($_SESSION['modules_services'], 'template_info', "include");
	}

	/**
	* Update the database with the template data
	*
	*/
	public function uptemplate()
	{
		$this->templateinfo();
		$order = $_SESSION['m_admin']['template']['order'];
		$order_field = $_SESSION['m_admin']['template']['order_field'];
		$what = $_SESSION['m_admin']['template']['what'];
		$start = $_SESSION['m_admin']['template']['start'];
		if(!empty($_SESSION['error']))
		{
			if($_REQUEST['mode'] == "up")
			{
				if(!empty($_SESSION['m_admin']['label']['ID']))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=template_up&module=templates&id=".$_SESSION['m_admin']['label']['ID']);
					exit;
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=templates&module=templates&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit;
				}
			}
			elseif($_REQUEST['mode'] == "add" )
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=template_add&module=templates");
				exit;
			}
		}
		else
		{
			$core = new core_tools();
			$_SESSION['service_tag'] = 'load_template_db';

			$this->connect();
			if( $_REQUEST['mode'] <> "add")
			{
				$this->query("update ".$_SESSION['tablename']['temp_templates']." set label = '".$_SESSION['m_admin']['template']['LABEL']."' , template_comment = '".$_SESSION['m_admin']['template']['COMMENT']."', content = '".$_SESSION['m_admin']['template']['CONTENT']."' where id = '".$_SESSION['m_admin']['template']['ID']."'");

				echo $core->execute_modules_services($_SESSION['modules_services'], 'uptemplate', "include");

				if($_REQUEST['mode'] == "up")
				{
					$_SESSION['error'] = _TEMPLATE_MODIFICATION;
					if($_SESSION['history']['templateup'] == "true")
					{
						require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['temp_templates'], $_SESSION['m_admin']['template']['ID'],"UP",_TEMPLATE_MODIFICATION." : ".$_SESSION['m_admin']['template']['LABEL'], $_SESSION['config']['databasetype'], 'templates');
					}
				}

				$this->cleartemplateinfos();
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=templates&module=templates&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
			else
			{
				$this->query("select label from ".$_SESSION['tablename']['temp_templates']." where label = '".$_SESSION['m_admin']['template']['LABEL']."'");

				if($this->nb_result() > 0)
				{
					$_SESSION['error'] = _THE_TEMPLATE.' '.$_SESSION['m_admin']['template']['LABEL'].' '._ALREADY_EXISTS;
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=template_add&module=templates");
					exit;
				}
				else
				{

					$this->connect();
					require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
					$users = new history();

					 if( $_REQUEST['mode'] == "add")
					 {
						$this->query("INSERT INTO ".$_SESSION['tablename']['temp_templates']." ( label, creation_date, template_comment, content  ) VALUES ( '".$_SESSION['m_admin']['template']['LABEL']."', now(),'".$_SESSION['m_admin']['template']['COMMENT']."', '".$_SESSION['m_admin']['template']['CONTENT']."')");
						$this->query("select id from ".$_SESSION['tablename']['temp_templates']." where label = '".$_SESSION['m_admin']['template']['LABEL']."' and template_comment = '".$_SESSION['m_admin']['template']['COMMENT']."' and content = '".$_SESSION['m_admin']['template']['CONTENT']."'");
						$res = $this->fetch_object();
						$_SESSION['m_admin']['template']['ID'] = $res->id;

						if($_SESSION['history']['templateadd'] == "true")
						{
							$users->add($_SESSION['tablename']['temp_templates'], $_SESSION['m_admin']['template']['ID'],"ADD", _TEMPLATE_ADDED." : ".$_SESSION['m_admin']['template']['LABEL'], $_SESSION['config']['databasetype'], 'templates');
						}
					 }

					echo $core->execute_modules_services($_SESSION['modules_services'], 'uptemplate', "include");
					$_SESSION['error'] = _TEMPLATE_ADDED;
				}

				$_SESSION['error'] = "";
			}

			if ($_REQUEST['mode'] == "add")
			{
				$url = $_SESSION['config']['businessappurl']."index.php?page=templates&module=templates&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
			}

			$this->cleartemplateinfos();
			header("location: ".$url);
			exit();
		}
	}


	/**
	* delete a template in the database
	*
	* @param string $id template identifier
	*/
	public function deltemplate($id)
	{
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=templates&module=templates&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
			exit;
		}
		else
		{

			$this->connect();

			$this->query("select id, label from ".$_SESSION['tablename']['temp_templates']." where id = ".$id);

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _TEMPLATE.' '._UNKNOWN;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=templates&module=templates&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
			else
			{
				$res = $this->fetch_object();
				$label = $res->label;
				$this->query("delete from ".$_SESSION['tablename']['temp_templates']." where id = ".$id);
				$this->query("delete from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ".$id);

				if($_SESSION['history']['templatedel'])
				{
					require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
					$users = new history();
					$users->add($_SESSION['tablename']['temp_templates'], $id,"DEL",_TEMPLATE_DELETION." : ".$label, $_SESSION['config']['databasetype'], 'templates');
				}
					$_SESSION['error'] = _DELETED_TEMPLATE;
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=templates&module=templates&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit();
			}
		}
	}
}
?>
