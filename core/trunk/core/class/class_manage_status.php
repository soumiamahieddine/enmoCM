<?php
/**
* Core class for status
*
*  Contains all the functions to manage status
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
*
*/

class manage_status extends dbquery
{
	function __construct()
	{
		parent::__construct();
	}

	public function get_searchable_status()
	{
		$status = array();
		$this->connect();
		$this->query("select id, label_status from ".$_SESSION['tablename']['status']." where can_be_searched = 'Y'");
		while($res = $this->fetch_object())
		{
			array_push($status, array('ID' => $res->id, 'LABEL' => $res->label_status));
		}
		return $status;
	}

	public function get_not_searchable_status()
	{
		$status = array();
		$this->connect();
		$this->query("select id, label_status from ".$_SESSION['tablename']['status']." where can_be_searched = 'N'");
		while($res = $this->fetch_object())
		{
			array_push($status, array('ID' => $res->id, 'LABEL' => $res->label_status));
		}
		return $status;
	}

	public function get_status_data($id_status,$extension = '')
	{
		$this->connect();
		$this->query("select label_status, maarch_module, img_filename from ".$_SESSION['tablename']['status']." where id = '".$id_status."'");
		$res = $this->fetch_object();
		$status_txt = $this->show_string($res->label_status);
		$maarch_module = $res->maarch_module;
		$img_name = $res->img_filename;
		if(!empty($img_name))
		{
			$temp_explode = explode( ".", $img_name);
			$temp_explode[0] = $temp_explode[0].$extension;
			$img_name = implode(".", $temp_explode);
		}
		

		if($maarch_module == 'apps' && isset($img_name) && !empty($img_name))
		{
			$img_path = $_SESSION['config']['businessappurl'].'img/'.$img_name;
		}
		else if(!empty($maarch_module) && isset($maarch_module)&& isset($img_name) && !empty($img_name))
		{
			$img_path = $_SESSION['urltomodules'].$maarch_module.'img/'.$img_name;
		}
		else
		{
			$img_path = $_SESSION['config']['businessappurl'].'img/default_status'.$extension.'.gif';
		}

		if(empty($status_txt) || !isset($status_txt))
		{
			$status_txt = $id_status;
		}

		return array('ID'=> $id_status, 'LABEL'=> $status_txt, 'IMG_SRC' => $img_path);
	}

	public function can_be_modified($id_status)
	{
		$this->connect();
		$this->query("select can_be_modified from ".$_SESSION['tablename']['status']." where id = '".$id_status."'");
		if($this->nb_result() == 0)
		{
			return false;
		}
		$res = $this->fetch_object();
		if($res->can_be_modified == 'N')
		{
			return false;
		}
		return true;
	}
}
