<?php
/**
* File : alarm2.php
*
* launch the notification script
*
* @package  Maarch Notifications 1.0
* @version 1.0
* @since 06/2006
* @license GPL
* @author  Loic Vinet 	<dev@maarch.org>
*/ 
session_name('entreprise');
session_start();
date_default_timezone_set('Europe/Paris');
$_ENV['log'] = '';
if($argc != 2 )
{
	$_ENV['log'] .= date('d m Y').' '.date('H:i:s')." ERROR 1 : You must specify the configuration file! \r\n";
	$log = $_ENV['log'];
	//Function_log($log, '.');
	echo $log;
	exit();
}
else
{
		$conf = $argv[1];
		if(!file_exists($conf))
		{
			$_ENV['log'] .= date('d m Y').' '.date('H:i:s')." ERROR 2 : Error during the configuration file opening! \r\n";
			$log = $_ENV['log'];
			//Function_log($log, '.');
			echo $log;
			exit();
		}

		$_ENV['log'] .= date("d m Y")." ".date("H:i:s")." ERROR 4 : Error on loading file : ".$conf."\r\n";
		$log = $_ENV['log'];
		//Function_log($log, '.');
		
		//Load maarch directory	
		$maarch_directory =  explode(DIRECTORY_SEPARATOR, $argv[0]);
		array_pop($maarch_directory);
		$maarch_directory =  implode(DIRECTORY_SEPARATOR, $maarch_directory).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php";
		require_once($maarch_directory);
		
		//Load notifications module
		$boot_notification = new notifications();
		$boot_notification->build_config($argv[1]);
		
		//Load default variables
		$used_coll_ext = $_SESSION['collection'][0]['extensions'][0];
		$template_path = $_SESSION['config']['MaarchDirectory']."modules".DIRECTORY_SEPARATOR."notifications".DIRECTORY_SEPARATOR.$_SESSION['templates_directory'].DIRECTORY_SEPARATOR.$_SESSION['templates']['alarm2'];
		$template_path_copy = $_SESSION['config']['MaarchDirectory']."modules".DIRECTORY_SEPARATOR."notifications".DIRECTORY_SEPARATOR.$_SESSION['templates_directory'].DIRECTORY_SEPARATOR.$_SESSION['templates']['alarm2_copy'];
		chdir($_SESSION['config']['MaarchDirectory']);
		
		require_once($_SESSION['config']['MaarchDirectory']."core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_functions.php");
		require_once($_SESSION['config']['MaarchDirectory']."core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_core_tools.php");
		
		require_once($_SESSION['config']['MaarchDirectory']."core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_db.php");
		require_once($_SESSION['config']['MaarchDirectory']."core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
		require_once($_SESSION['config']['MaarchDirectory']."modules".DIRECTORY_SEPARATOR."notifications".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_notifications_engine.php");
		require_once($_SESSION['config']['MaarchDirectory']."modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_entities.php");
		
		//Load lang files	
		$lang_file =  explode(DIRECTORY_SEPARATOR, $argv[0]);
		array_pop($lang_file);
		$lang_file =  implode(DIRECTORY_SEPARATOR, $lang_file).'/lang/'.$_SESSION['config']['lang'].'.php';
	
		if(file_exists($lang_file))
		{
			include($lang_file);
		}
		else
		{
			echo "Language file missing...<br/>";
			exit;
		}

		//Load functions objects
		$func = new functions();

		//Load notifications engine
		$notification = new notification_engine();
		
		//Restore array of user
		$users = array();
		$users = $notification->build_users_list();
		$flag_for_notif = array();
		$verif='true';
		


		//For each users, restore his list instance with all docs to be process
		for($ui=0; $ui < count($users); $ui++)
		{
			//First process for first dest user
			$list_trait = array();
			
			$list_trait = $notification->get_ressource_for_alarm2($users[$ui]);
			if ($list_trait <> false)
			{
				
				//Init array with all docs and all data
				$my_docs = array();
				
				
				foreach ($list_trait as $this_list_trait)
				{
					//to flag ressources, we need to save all docs in an array
					array_push($flag_for_notif, $this_list_trait);
					
					//Return all desired custom for this ressource
					array_push ($my_docs, $notification->get_doc_info($this_list_trait));
				}

				//Load template for this script			
				$template = $notification->get_template($template_path);
				
				//explode this template
				$working_template = array();
				$working_template = $notification->decode_template($template);
			
				//Replace developped fiel in template by the good value
				$mail_trait = $notification->execute_engine($working_template,$my_docs,$users[$ui]);				
				$verif = $notification->send_mail($users[$ui]['MAIL'],  _MAIL_TO_PROCESS_LIST." - "._LATE, $mail_trait);
				echo $users[$ui]['MAIL']." send mail\r\n";
			}
			if ($_SESSION['features']['copy_for_alarm2'] == 'true')
			//sending copy for this new ressources only if features copy_for_notif is enabled
			{
				$list_copy = $notification->get_ressource_for_alarm2_copy($users[$ui]);
							
				if ($list_copy <> false)
				{		
					//Init array with all docs and all data
					$copy_docs = array();
	
					foreach ($list_copy as $this_list_copy)
					{
						//Return all desired custom for this ressource
						array_push ($copy_docs, $notification->get_doc_info($this_list_copy));
					}

					//Load template for this script
					$template_copy = $notification->get_template($template_path_copy);
					
					//explode this template
					$working_template_copy = array();
					$working_template_copy = $notification->decode_template($template_copy);
				
					//Replace developped field in template by the good value
					$mail_copy = $notification->execute_engine($working_template_copy,$copy_docs,$users[$ui]);	
					$verif = $notification->send_mail($users[$ui]['MAIL'], _COPIES_MAIL_LIST." - "._LATE, $mail_copy);
					echo $users[$ui]['MAIL']." send mail\r\n";
				}	

			}	
		}
		//Update Flag_notif to yes if mail has been sended
		$notification->add_flag_for_ressources($flag_for_notif, 'flag_alarm2', $verif);
}
