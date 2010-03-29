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
* @brief   Action : Document validation
*
* Open a modal box to displays the validation form, make the form checks and loads the result in database. Used by the core (manage_action.php page).
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/
/**
* $confirm  bool false
*/
$confirm = false;
/**
* $etapes  array Contains only one etap : form
*/
$etapes = array('form');
/**
* $frm_width  Width of the modal (empty)
*/
$frm_width='';
/**
* $frm_height  Height of the modal (empty)
*/
$frm_height = '';
/**
* $mode_form  Mode of the modal : fullscreen
*/
$mode_form = 'fullscreen';

include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');

///////////////////// Pattern to check dates
if($_SESSION['config']['databasetype'] == "SQLSERVER")
{
	$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}
else // MYSQL & POSTGRESQL
{
	$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}

/**
 * Gets the path of the file to displays
 *
 * @param $res_id String Resource identifier
 * @param $coll_id String Collection identifier
 * @return String File path
 **/
function get_file_path($res_id, $coll_id)
{
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
	$sec =new security();
	$view = $sec->retrieve_view_from_coll_id($coll_id);
	if(empty($view))
	{
		$view = $sec->retrieve_table_from_coll($coll_id);
	}
	$db = new dbquery();
	$db->connect();
	$db->query("select docserver_id, path, filename from ".$view." where res_id = ".$res_id);
	$res = $db->fetch_object();
	$path = preg_replace('/#/', DIRECTORY_SEPARATOR, $res->path);
	$docserver_id = $res->docserver_id;
	$filename = $res->filename;
	$db->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$docserver_id."'");
	$res = $db->fetch_object();
	$docserver_path = $res->path_template;

	return $docserver_path.$path.$filename;
}

function check_category($coll_id, $res_id)
{
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
	$sec =new security();
	$view = $sec->retrieve_view_from_coll_id($coll_id);

	$db = new dbquery();
	$db->connect();
	$db->query("select category_id from ".$view." where res_id = ".$res_id);
	$res = $db->fetch_object();

	if(!isset($res->category_id))
	{
		$ind_coll = $sec->get_ind_collection($coll_id);
		$table_ext = $_SESSION['collections'][$ind_coll]['extensions'][0];
		$db->query("insert into ".$table_ext." (res_id, category_id) VALUES (".$res_id.", '".$_SESSION['default_category']."')");
		//$db->show();
	}
}

/**
 * Returns the validation form text
 *
 * @param $values Array Contains the res_id of the document to validate
 * @param $path_manage_action String Path to the PHP file called in Ajax
 * @param $id_action String Action identifier
 * @param $table String Table
 * @param $module String Origin of the action
 * @param $coll_id String Collection identifier
 * @param $mode String Action mode 'mass' or 'page'
 * @return String The form content text
 **/
function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode )
{
	if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"]))
	{
		$browser_ie = true;
		$display_value = 'block';
   	}
	elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $_SERVER["HTTP_USER_AGENT"]) )
	{
		$browser_ie = true;
		$display_value = 'block';
	}
	else
	{
		$browser_ie = false;
		$display_value = 'table-row';
	}
	$_SESSION['req'] = "action";
	$res_id = $values[0];
	$frm_str = '';
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
	require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
	require_once("modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
	require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_types.php");

	$sec =new security();
	$core_tools =new core_tools();
	$b = new basket();
	$type = new types();
	$business = new business_app_tools();

	if($_SESSION['features']['show_types_tree'] == 'true')
	{
		$doctypes = $type-> getArrayStructTypes($coll_id);
	}
	else
	{
		$doctypes = $type->getArrayTypes($coll_id);
	}
	$db = new dbquery();
	$db->connect();
	$hidden_doctypes = array();
	$tmp = $business->get_titles();
	$titles = $tmp['titles'];
	$default_title = $tmp['default_title'];
	if($core_tools->is_module_loaded('templates'))
	{
		$db->query("select type_id from ".$_SESSION['tablename']['temp_templates_doctype_ext']." where is_generated = 'Y'");
		while($res = $db->fetch_object())
		{
			array_push($hidden_doctypes, $res->type_id);
		}
	}
	$today = date('d-m-Y');

	if($core_tools->is_module_loaded('entities'))
	{
		$services = array();
		if(!empty($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities']))
		{
			$db->query("select entity_id, short_label from ".$_SESSION['tablename']['ent_entities']." where entity_id in (".$_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities'].") and enabled= 'Y' order by entity_label");
			while($res = $db->fetch_object())
			{
				array_push($services, array( 'ID' => $res->entity_id, 'LABEL' => $db->show_string($res->short_label)));
			}
		}
	}

	if($core_tools->is_module_loaded('physical_archive'))
	{
 		$boxes = array();
		$db->query("select arbox_id, title from ".$_SESSION['tablename']['ar_boxes']." where status = 'NEW'  order by title");
		while($res = $db->fetch_object())
		{
			array_push($boxes, array( 'ID' => $res->arbox_id, 'LABEL' => $db->show_string($res->title)));
		}
	}
	check_category($coll_id, $res_id);
	$data = get_general_data($coll_id, $res_id, 'minimal');
	//print_r($data);
	$frm_str .= '<div id="validleft">';
	$frm_str .= '<div id="valid_div" style="display:none;";>';
		$frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=file_index_b.gif"  align="middle" alt="" />'._VALIDATE_MAIL.' '._NUM.$res_id;
					$frm_str .= '</h1>';
					$frm_str .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';
					$frm_str .= '<form name="index_file" method="post" id="index_file" action="#" class="forms indexingform" style="text-align:left;">';

					$frm_str .= '<input type="hidden" name="values" id="values" value="'.$res_id.'" />';
					$frm_str .= '<input type="hidden" name="action_id" id="action_id" value="'.$id_action.'" />';
					$frm_str .= '<input type="hidden" name="mode" id="mode" value="'.$mode.'" />';
					$frm_str .= '<input type="hidden" name="table" id="table" value="'.$table.'" />';
					$frm_str .= '<input type="hidden" name="coll_id" id="coll_id" value="'.$coll_id.'" />';
					$frm_str .= '<input type="hidden" name="module" id="module" value="'.$module.'" />';
					$frm_str .= '<input type="hidden" name="req" id="req" value="second_request" />';

			$frm_str .= '<div  style="display:block">';

			if($core_tools->test_service('index_attachment', 'attachments', false))
			{
				$frm_str .= '<table width="100%" align="center" border="0" >';
				  $frm_str .= '<tr id="attachment_tr" style="display:'.$display_value.';">';
				 		$frm_str .='<td><label for="attachment" class="form_title" >'._ATTACH_TO_DOC.' : </label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><input type="radio" name="attachment" id="attach" value="true" onclick="hide_index(true, \''.$display_value.'\');" /> '._YES.' <input type="radio" name="attachment" id="no_attach" value="false" checked="checked" onclick="hide_index(false, \''.$display_value.'\');" /> '._NO.'</td>';
							$frm_str .='<td><span class="red_asterisk" id="attachment_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				    $frm_str .= '<tr id="attach_title_tr" style="display:none;">';
				 		$frm_str .='<td><label for="attach_title" class="form_title" >'._TITLE.' : </label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><input type="text" name="attach_title" id="attach_title" value="" /></td>';
							$frm_str .='<td><span class="red_asterisk" id="res_id_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				   $frm_str .= '<tr id="attach_link_tr" style="display:none;">';
				 		$frm_str .='<td><label for="attach" class="form_title" >'._NUM_GED.' : </label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><input type="text" name="res_id" id="res_id" class="readonly" readonly="readonly" value="" /><br/><a href="javascript://" onclick="window.open(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv&mode=popup&action_form=show_res_id&modulename=attachments&init_search&nodetails\',\'search_doc_for_attachment\', \'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=1020,height=710\');" title="'._SEARCH.'"><em>'._SEARCH_DOC.'</em></a></td>';
							$frm_str .='<td><span class="red_asterisk" id="res_id_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';

				$frm_str .= '</table>';
			}
				  $frm_str .= '<table width="100%" align="center" border="0"  id="indexing_fields" style="display:block;">';

				  /*** Category ***/
				  $frm_str .= '<tr id="category_tr" style="display:'.$display_value.';">';
				  	$frm_str .='<td class="indexing_label"><label for="category_id" class="form_title" >'._CATEGORY.'</label></td>';
					$frm_str .='<td>&nbsp;</td>';
					$frm_str .='<td class="indexing_field"><select name="category_id" id="category_id" onchange="clear_error(\'frm_error_'.$id_action.'\');change_category(this.options[this.selectedIndex].value, \''.$display_value.'\',  \''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=change_category\',  \''.$_SESSION['config']['businessappurl'].'index.php?display=true&page=get_content_js\');">';
								$frm_str .='<option value="">'._CHOOSE_CATEGORY.'</option>';
							foreach(array_keys($_SESSION['mail_categories']) as $cat_id)
							{
								$frm_str .='<option value="'.$cat_id.'"';
								if($_SESSION['default_category'] == $cat_id || $_SESSION['indexing']['category_id'] == $cat_id || (isset($data['category_id']) && $data['category_id'] == $cat_id) )
								{
									$frm_str .='selected="selected"';
								}

								$frm_str .='>'.$_SESSION['mail_categories'][$cat_id].'</option>';

							}
						$frm_str.='</select></td>';
						$frm_str .= '<td><span class="red_asterisk" id="category_id_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				   /*** Doctype ***/
				  $frm_str .= '<tr id="type_id_tr" style="display:'.$display_value.';">';
				  	$frm_str .='<td class="indexing_label"><label for="type_id"><span class="form_title" id="doctype_res" style="display:none;">'._DOCTYPE.'</span><span class="form_title" id="doctype_mail" style="display:inline;" >'._DOCTYPE_MAIL.'</span></label></td>';
					$frm_str .='<td>&nbsp;</td>';
					$frm_str .='<td class="indexing_field"><select name="type_id" id="type_id" onchange="clear_error(\'frm_error_'.$id_action.'\');change_doctype(this.options[this.selectedIndex].value, \''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=change_doctype\', \''._ERROR_DOCTYPE.'\', \''.$id_action.'\', \''.$_SESSION['config']['businessappurl'].'index.php?display=true&page=get_content_js\' , \''.$display_value.'\','.$res_id.', \''.$coll_id.'\');">';
							$frm_str .='<option value="">'._CHOOSE_TYPE.'</option>';
							if($_SESSION['features']['show_types_tree'] == 'true')
							{
								for($i=0; $i<count($doctypes);$i++)
								{
									$frm_str .='<option value="" class="doctype_level1" title="'.$doctypes[$i]['label'].'" label="'.$doctypes[$i]['label'].'">'.$doctypes[$i]['label'].'</option>';
									for($j=0; $j<count($doctypes[$i]['level2']);$j++)
									{
										$frm_str .='<option value="" class="doctype_level2" title="'.$doctypes[$i]['level2'][$j]['label'].'" label="'.$doctypes[$i]['level2'][$j]['label'].'">&nbsp;&nbsp;'.$doctypes[$i]['level2'][$j]['label'].'</option>';
										for($k=0; $k<count($doctypes[$i]['level2'][$j]['types']);$k++)
										{
											if(!in_array($doctypes[$i]['level2'][$j]['types'][$k]['id'],$hidden_doctypes))
											{
												$frm_str .='<option value="'.$doctypes[$i]['level2'][$j]['types'][$k]['id'].'" ';
												if(isset($data['type_id']) && !empty($data['type_id']) && $data['type_id'] == $doctypes[$i]['level2'][$j]['types'][$k]['id'])
												{
													$frm_str .= ' selected="selected" ';
												}
												$frm_str .=' title="'.$doctypes[$i]['level2'][$j]['types'][$k]['label'].'" label="'.$doctypes[$i]['level2'][$j]['types'][$k]['label'].'">&nbsp;&nbsp;&nbsp;&nbsp;'.$doctypes[$i]['level2'][$j]['types'][$k]['label'].'</option>';
											}
										}
									}
								}
							}
							else
							{
								for($i=0; $i<count($doctypes);$i++)
								{
									$frm_str .='<option value="'.$doctypes[$i]['ID'].'" ';
									if(isset($data['type_id']) && !empty($data['type_id']) && $data['type_id'] == $doctypes[$i]['ID'])
									{
										$frm_str .= ' selected="selected" ';
									}
									$frm_str .=' >'.$doctypes[$i]['LABEL'].'</option>';
								}
							}
							$frm_str .='</select>';
							$frm_str .= '<td><span class="red_asterisk" id="type_id_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				/*** Priority ***/
				  $frm_str .= '<tr id="priority_tr" style="display:'.$display_value.';">';
				 		$frm_str .='<td class="indexing_label"><label for="priority" class="form_title" >'._PRIORITY.'</label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><select name="priority" id="priority" onchange="clear_error(\'frm_error_'.$id_action.'\');">';
							$frm_str .='<option value="">'._CHOOSE_PRIORITY.'</option>';
								for($i=0; $i<count($_SESSION['mail_priorities']);$i++)
								{
									$frm_str .='<option value="'.$i.'" ';
									if($_SESSION['default_mail_priority'] == $i || (isset($data['type_id'])&& $data['priority'] == $i))
									{
										$frm_str .='selected="selected"';
									}
									$frm_str .='>'.$_SESSION['mail_priorities'][$i].'</option>';
								}
							$frm_str .='</select></td>';
							$frm_str .= '<td><span class="red_asterisk" id="priority_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				  /*** Doc date ***/
				   $frm_str .= '<tr id="doc_date_tr" style="display:'.$display_value.';">';
				 		$frm_str .='<td class="indexing_label"><label for="doc_date" class="form_title" id="mail_date_label" style="display:inline;" >'._MAIL_DATE.'</label><label for="doc_date" class="form_title" id="doc_date_label" style="display:none;" >'._DOC_DATE.'</label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><input name="doc_date" type="text" id="doc_date" value="';
						if(isset($data['doc_date'])&& !empty($data['doc_date']))
						{
							$frm_str .= $data['doc_date'];
						}
						else
						{
							$frm_str .= $today;
						}
						$frm_str .= '" onclick="clear_error(\'frm_error_'.$id_action.'\');showCalender(this);"/></td>';
						$frm_str .= '<td><span class="red_asterisk" id="doc_date_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr >';
				  /*** Author ***/
				   $frm_str .= '<tr id="author_tr" style="display:'.$display_value.';">';
				 		$frm_str .='<td class="indexing_label"><label for="author" class="form_title" >'._AUTHOR.'</label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><input name="author" type="text" id="author" onchange="clear_error(\'frm_error_'.$id_action.'\');"';
						if(isset($data['author'])&& !empty($data['author']))
						{
							$frm_str .= ' value="'.$data['author'].'" ';
						}
						'/></td>';
						$frm_str .= '<td><span class="red_asterisk" id="author_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				  /*** Admission date ***/
				  $frm_str .= '<tr id="admission_date_tr" style="display:'.$display_value.';">';
				 		$frm_str .='<td class="indexing_label"><label for="admission_date" class="form_title" >'._RECEIVING_DATE.'</label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><input name="admission_date" type="text" id="admission_date" value="';
						if(isset($data['admission_date'])&& !empty($data['admission_date']))
						{
							$frm_str .= $data['admission_date'];
						}
						else
						{
							$frm_str .= $today;
						}
						$frm_str .= '" onclick="clear_error(\'frm_error_'.$id_action.'\');showCalender(this);"/></td>';
						$frm_str .= '<td><span class="red_asterisk" id="admission_date_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				/*** Contact ***/
				  $frm_str .= '<tr id="contact_choose_tr" style="display:'.$display_value.';">';
				   $frm_str .='<td class="indexing_label"><label for="type_contact" class="form_title" ><span id="exp_contact_choose_label">'._SHIPPER_TYPE.'</span><span id="dest_contact_choose_label">'._DEST_TYPE.'</span></label></td>';
				   $frm_str .='<td>&nbsp;</td>';
				   $frm_str .='<td class="indexing_field"><input type="radio" name="type_contact" id="type_contact_internal" value="internal"  class="check" onclick="clear_error(\'frm_error_'.$id_action.'\');change_contact_type(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=autocomplete_contacts\');"';

				   if($data['type_contact'] == 'internal')
				   {
					  $frm_str .= ' checked="checked" ';
					}
				   $frm_str .= ' />'._INTERNAL.'<input type="radio" name="type_contact"   class="check" id="type_contact_external" value="external" onclick="clear_error(\'frm_error_'.$id_action.'\');change_contact_type(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=autocomplete_contacts\');"';
				    if($data['type_contact'] == 'external')
				   {
					  $frm_str .= ' checked="checked" ';
					}
				    $frm_str .= '/>'._EXTERNAL.'</td>';
					$frm_str .= '<td><span class="red_asterisk" id="type_contact_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				     $frm_str .= '</tr>';
					   $frm_str .= '<tr id="contact_id_tr" style="display:'.$display_value.';">';
				   $frm_str .='<td class="indexing_label"><label for="contact" class="form_title" ><span id="exp_contact">'._SHIPPER.'</span><span id="dest_contact">'._DEST.'</span>';
				   if($_SESSION['features']['personal_contact'] == "true"  && $core_tools->test_service('my_contacts','apps', false))
				   {
						$frm_str .=' <a href="#" id="create_contact" title="'._CREATE_CONTACT.'" onclick="new Effect.toggle(\'create_contact_div\', \'blind\', {delay:0.2});return false;" style="display:inline;" ><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=modif_liste.png" alt="'._CREATE_CONTACT.'"/></a>';
					}
					 $frm_str .= '</label></td>';
				   $frm_str .='<td><a href="#" id="contact_card" title="'._CONTACT_CARD.'" onclick="open_contact_card(\''.$_SESSION ['config']['businessappurl'].'index.php?display=true&page=contact_info\', \''.$_SESSION ['config']['businessappurl'].'index.php?display=true&page=user_info\');" style="visibility:hidden;" ><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=my_contacts_off.gif" alt="'._CONTACT_CARD.'" /></a>&nbsp;</td>';
				   $frm_str .='<td class="indexing_field"><input type="text" name="contact" id="contact" onchange="clear_error(\'frm_error_'.$id_action.'\');display_contact_card(\'visible\');"';
				    if(isset($data['contact']) && !empty($data['contact']))
				   {
					  $frm_str .= ' value="'.$data['contact'].'" ';
					}
				   $frm_str .=  ' /><div id="show_contacts" class="autocomplete"></div></td>';
				   $frm_str .= '<td><span class="red_asterisk" id="contact_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				     $frm_str .= '</tr>';
				/*** Nature ***/
				 $frm_str .= '<tr id="nature_id_tr" style="display:'.$display_value.';">';
				 		$frm_str .='<td class="indexing_label"><label for="nature_id" class="form_title" >'._NATURE.'</label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><select name="nature_id" id="nature_id" onchange="clear_error(\'frm_error_'.$id_action.'\');">';
							$frm_str .='<option value="">'. _CHOOSE_NATURE.'</option>';
							foreach(array_keys($_SESSION['mail_natures']) as $nature)
							{
								$frm_str .='<option value="'.$nature.'"';
								if($_SESSION['default_mail_nature'] == $nature || (isset($data['nature_id'])&& $data['nature_id'] == $nature))
								{
									$frm_str .='selected="selected"';
								}
								$frm_str .='>'.$_SESSION['mail_natures'][$nature].'</option>';
							}
						 $frm_str .= '</select></td>';
						 $frm_str .= '<td><span class="red_asterisk" id="nature_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				/*** Subject ***/
				  $frm_str .= '<tr id="subject_tr" style="display:'.$display_value.';">';
				 		$frm_str .='<td class="indexing_label"><label for="subject" class="form_title" >'._SUBJECT.'</label></td>';
						$frm_str .='<td>&nbsp;</td>';
						$frm_str .='<td class="indexing_field"><textarea name="subject" id="subject" rows="4" onchange="clear_error(\'frm_error_'.$id_action.'\');" >';
						  if(isset($data['subject']) && !empty($data['subject']))
						   {
							  $frm_str .= $data['subject'];
							}
						 $frm_str .= '</textarea></td>';
						 $frm_str .= '<td><span class="red_asterisk" id="subject_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				/*** Entities : department + diffusion list ***/
				if($core_tools->is_module_loaded('entities'))
				{
					$_SESSION['validStep'] = "ok";
				  $frm_str .= '<tr id="department_tr" style="display:'.$display_value.';">';
				 		$frm_str .='<td class="indexing_label"><label for="department" class="form_title" id="label_dep_dest" style="display:inline;" >'._DEPARTMENT_DEST.'</label><label for="department" class="form_title" id="label_dep_exp" style="display:none;" >'._DEPARTMENT_EXP.'</label></td>';
						$frm_str .='<td>&nbsp;</td>';
					$frm_str .='<td class="indexing_field"><select name="destination" id="destination" onchange="clear_error(\'frm_error_'.$id_action.'\');change_entity(this.options[this.selectedIndex].value, \''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=load_listinstance'.'\',\'diff_list_div\', \'indexing\', \''.$display_value.'\');">';
						$frm_str .='<option value="">'._CHOOSE_DEPARTMENT.'</option>';
					   for($i=0; $i < count($services); $i++)
					   {
							$frm_str .='<option value="'.$services[$i]['ID'].'" ';
							if(isset($data['destination'])&& $data['destination'] == $services[$i]['ID'])
							{
								$frm_str .='selected="selected"';
							}
							$frm_str .= '>'.$db->show_string($services[$i]['LABEL']).'</option>';
					   }
					$frm_str .='</select></td>';
					$frm_str .= '<td><span class="red_asterisk" id="destination_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				  $frm_str .= '<tr id="diff_list_tr" style="display:none;">';
				  		$frm_str .= '<td colspan="3">';
				  		$frm_str .= '<h2 onclick="new Effect.toggle(\'diff_list_div\', \'blind\', {delay:0.2});return false;"  class="categorie" style="width:90%;">';
								$frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=plus.png" alt="" id="img_diff_list" />&nbsp;<b><small>'._DIFF_LIST_COPY.' :</small></b>';
						$frm_str .= '<span class="lb1-details">&nbsp;</span>';
					$frm_str .= '</h2>';
					$frm_str .= '<div id="diff_list_div"  style="display:none">';
						$frm_str .= '<div>ici</div>';
						$frm_str .= '<div id="diff_list_div" class="scroll_div" style="height:200px;border: 1px solid;"></div>';
						$frm_str .= '</td>';
				  $frm_str .= '</tr>';
				}
				/*** Physical_archive : Arbox ***/
				if($core_tools->is_module_loaded('physical_archive'))
				{
					$frm_str .= '<tr id="box_id_tr" style="display:'.$display_value.';">';
					$frm_str .='<td class="indexing_label"><label for="arbox_id" class="form_title" id="label_box" style="display:inline;" >'._BOX_ID.'</label></td>';
					$frm_str .='<td>&nbsp;</td>';
					$frm_str .='<td class="indexing_field"><select name="arbox_id" id="arbox_id" onchange="clear_error(\'frm_error_'.$id_action.'\');" ';
					
					//if($data['arbox_id'] <> "" && $data['arbox_id'] <> 1 )
					if($data['arbox_id'] <> "" )
					{
						$frm_str .='disabled="disabled">';
						$frm_str .='<option value="'.$data['arbox_id'].'">'.$data['arbox_id'].'</option>';
					}
					else
					{
						$frm_str .='>';
						$frm_str .='<option value="">'._CHOOSE_BOX.'</option>';
					}
					

					for($i=0; $i < count($boxes); $i++)
					{
						$frm_str .='<option value="'.$boxes[$i]['ID'].'"';
						if(isset($data['arbox_id'])&& $data['arbox_id'] == $boxes[$i]['ID'])
						{
							$frm_str .= ' selected="selected" ';
						}
						$frm_str .= ' >'.$db->show_string($boxes[$i]['LABEL']).'</option>';
					}
					$frm_str .='</select></td>';
					$frm_str .= '<td><span class="red_asterisk" id="arbox_id_mandatory" style="display:inline;">*</span>&nbsp;</td>';
				  $frm_str .= '</tr>';
				}
		/*** Process limit date ***/
		$frm_str .= '<tr id="process_limit_date_use_tr" style="display:'.$display_value.';">';
			$frm_str .='<td class="indexing_label"><label for="process_limit_date_use" class="form_title" >'._PROCESS_LIMIT_DATE_USE.'</label></td>';
			$frm_str .='<td>&nbsp;</td>';
			$frm_str .='<td class="indexing_field"><input type="radio"  class="check" name="process_limit_date_use" id="process_limit_date_use_yes" value="yes" ';
			if($data['process_limit_date_use'] == true || !isset($data['process_limit_date_use']))
			{
				$frm_str .=' checked="checked"';
			}
			$frm_str .=' onclick="clear_error(\'frm_error_'.$id_action.'\');activate_process_date(true, \''.$display_value.'\');" />'._YES.'<input type="radio" name="process_limit_date_use"  class="check"  id="process_limit_date_use_no" value="no" onclick="clear_error(\'frm_error_'.$id_action.'\');activate_process_date(false, \''.$display_value.'\');" ';
			if(isset($data['process_limit_date_use']) && $data['process_limit_date_use'] == false)
			{
				$frm_str .=' checked="checked"';
			}
			$frm_str .='/>'._NO.'</td>';
			$frm_str .= '<td><span class="red_asterisk" id="process_limit_date_use_mandatory" style="display:inline;">*</span>&nbsp;</td>';
	    $frm_str .= '</tr>';
		$frm_str .= '<tr id="process_limit_date_tr" style="display:'.$display_value.';">';
			$frm_str .='<td class="indexing_label"><label for="process_limit_date" class="form_title" >'._PROCESS_LIMIT_DATE.'</label></td>';
			$frm_str .='<td>&nbsp;</td>';
			$frm_str .='<td class="indexing_field"><input name="process_limit_date" type="text" id="process_limit_date"  onclick="clear_error(\'frm_error_'.$id_action.'\');showCalender(this);" value="';
			if(isset($data['process_limit_date'])&& !empty($data['process_limit_date']))
			{
				$frm_str .= $data['process_limit_date'];
			}
			$frm_str .='"/></td>';
			$frm_str .= '<td><span class="red_asterisk" id="process_limit_date_mandatory" style="display:inline;">*</span>&nbsp;</td>';
	    $frm_str .= '</tr>';

	    		/*** Chrono number ***/
	/*	$frm_str .= '<tr id="chrono_number_tr" style="display:'.$display_value.';">';
			$frm_str .='<td><label for="chrono_number" class="form_title" >'._CHRONO_NUMBER.'</label></td>';
			$frm_str .='<td>&nbsp;</td>';
			$frm_str .='<td class="indexing_field"><input type="text" name="chrono_number" id="chrono_number" onchange="clear_error(\'frm_error_'.$id_action.'\');"/></td>';
			$frm_str .='<td><span class="red_asterisk" id="chrono_number_mandatory" style="display:inline;">*</span>&nbsp;</td>';
	    $frm_str .= '</tr>';*/

		/*** Folder : Market & Project ***/
		if($core_tools->is_module_loaded('folder'))
		{
			$frm_str .= '<tr id="project_tr" style="display:'.$display_value.';">';
				$frm_str .= '<td class="indexing_label"><label for="project" class="form_title" >'._PROJECT.'</label></td>';
				$frm_str .= '<td>&nbsp;</td>';
				 $frm_str .='<td class="indexing_field"><input type="text" name="project" id="project" value="';
				if(isset($data['project'])&& !empty($data['project']))
				{
					$frm_str .= $data['project'];
				}
				 $frm_str .='" onblur="clear_error(\'frm_error_'.$id_action.'\');return false;"/><div id="show_project" class="autocomplete"></div></td>'; // $(\'market\').value=\'\';
				 $frm_str .= '<td><span class="red_asterisk" id="project_mandatory" style="display:inline;">*</span>&nbsp;</td>';
			$frm_str .= '</tr>';
			$frm_str .= '<tr id="market_tr" style="display:'.$display_value.';">';
				$frm_str .= '<td class="indexing_label"><label for="market" class="form_title" >'._MARKET.'</label></td>';
				$frm_str .= '<td>&nbsp;</td>';
				 $frm_str .='<td class="indexing_field"><input type="text" name="market" id="market" onblur="clear_error(\'frm_error_'.$id_action.'\');fill_project(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=folder&page=ajax_get_project\');" value=\'';
				 if(isset($data['market'])&& !empty($data['market']))
				{
					$frm_str .= $data['market'];
				}
				 $frm_str .= '\' /><div id="show_market" class="autocomplete"></div></td>';
				 $frm_str .= '<td><span class="red_asterisk" id="market_mandatory" style="display:inline;">*</span>&nbsp;</td>';
			$frm_str .= '</tr>';
		}
			$frm_str .= '</table>';
			$frm_str .= '<div id="comp_indexes" style="display:block;">';
		$frm_str .= '</div>';
		$frm_str .= '</div>';
		/*** Actions ***/
		$frm_str .= '<hr width="90%" align="center"/>';
		$frm_str .= '<p align="center">';
			$frm_str .= '<b>'._ACTIONS.' : </b>';

			$actions  = $b->get_actions_from_current_basket($res_id, $coll_id, 'PAGE_USE');
			if(count($actions) > 0)
			{
				$frm_str .='<select name="chosen_action" id="chosen_action">';
					$frm_str .='<option value="">'._CHOOSE_ACTION.'</option>';
					for($ind_act = 0; $ind_act < count($actions);$ind_act++)
					{
						$frm_str .='<option value="'.$actions[$ind_act]['VALUE'].'"';
						if($ind_act==0)
						{
							$frm_str .= 'selected="selected"';
						}
						$frm_str .= '>'.$actions[$ind_act]['LABEL'].'</option>';
					}
				$frm_str .='</select> ';
				$frm_str .= '<input type="button" name="send" id="send" value="'._VALIDATE.'" class="button" onclick="valid_action_form( \'index_file\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$res_id.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
			}
			$frm_str .= '<input name="close" id="close" type="button" value="'._CANCEL.'" class="button" onClick="javascript:$(\'baskets\').style.visibility=\'visible\';destroyModal(\'modal_'.$id_action.'\');reinit();"/>';
		$frm_str .= '</p>';
	$frm_str .= '</form>';
	$frm_str .= '</div>';
	$frm_str .= '</div>';
		$frm_str .= '</div>';

		/*** Frame to display the doc ***/
		$frm_str .= '<div id="validright">';
		$frm_str .= '<div id="create_contact_div" style="display:none">';
			$frm_str .= '<div>';
				$frm_str .= '<fieldset style="border:1px solid;">';
			$frm_str .= '<legend ><b>'._CREATE_CONTACT.'</b></legend>';
	$frm_str .= '<form name="indexingfrmcontact" id="indexingfrmcontact" method="post" action="'.$_SESSION['config']['businessappurl'].'index.php?display=true&mpage=contact_info" >';
			$frm_str .= '<table>';
				$frm_str .= '<tr>';
					$frm_str .= '<td colspan="2">';
						$frm_str .= '<label for="is_corporate">'._IS_CORPORATE_PERSON.' : </label>';
					$frm_str .= '</td>';
					$frm_str .= '<td colspan="2">';
						$frm_str .='<input type="radio" class="check" name="is_corporate" id="is_corporate_Y" value="Y" ';					
							$frm_str .=' checked="checked"';
						$frm_str .= 'onclick="javascript:show_admin_contacts(true, \''.$display_value.'\');">'._YES;
						$frm_str .='<input type="radio" id="is_corporate_N" class="check" name="is_corporate" value="N"';
						$frm_str .=' onclick="javascript:show_admin_contacts( false, \''.$display_value.'\');"/>'._NO;
					$frm_str .= '</td>';
				$frm_str .= '</tr>';
		 		$frm_str .= '<tr id="title_p" style="display:';
					 $frm_str .= $display_value;
				$frm_str .='">';
					$frm_str .= '<td  colspan="2">';
						$frm_str .='<label for="title">'._TITLE2.' : </label>';
					$frm_str .= '</td>';
					$frm_str .= '<td colspan="2" >';
						$frm_str .='<select name="title" id="title" >';
							$frm_str .='<option value="">'._CHOOSE_TITLE.'</option>';
							foreach(array_keys($titles) as $key)
							{
								$frm_str .='<option value="'.$key.'" ';
								if($key == $default_title)
								{
									$frm_str .= 'selected="selected"';
								}
								$frm_str .='>'.$titles[$key].'</option>';
							}
						$frm_str .='</select>';
					$frm_str .= '</td>';
				$frm_str .= '</tr>';
		 $frm_str .= '<tr id="lastname_p" style="display:';
				$frm_str .= $display_value;
			$frm_str .='">';
				$frm_str .= '<td colspan="2">';
						$frm_str .='<label for="lastname">'._LASTNAME.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<input name="lastname" type="text"  id="lastname" value="" /> ';
					$frm_str .='<span class="red_asterisk" id="lastname_mandatory" style="display:inline;">*</span>';
				$frm_str .= '</td>';
			$frm_str .= '</tr>';
		 	$frm_str .= '<tr id="firstname_p" style="display:';		
				$frm_str .= $display_value;
			$frm_str .='">';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<label for="firstname">'._FIRSTNAME.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
				$frm_str .='<input name="firstname" type="text"  id="firstname" value=""/>';
		 		$frm_str .= '</td>';
			$frm_str .= '</tr>';

			$frm_str .= '<tr>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<label for="society">'._SOCIETY.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<input name="society" type="text"  id="society" value="" />';
					$frm_str .='<span class="red_asterisk" id="society_mandatory" style="display:inline;">*</span>';
				$frm_str .= '</td>';
			$frm_str .= '</tr>';

			$frm_str .= '<tr id="function_p" style="display:';			
				$frm_str .= 'block';
			$frm_str .='">';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<label for="function">'._FUNCTION.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<input name="function" type="text"  id="function" value="" />';
		 		$frm_str .= '</td>';
			$frm_str .= '</tr>';
			$frm_str .= '<tr>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<label for="phone">'._PHONE.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<input name="phone" type="text"  id="phone" value="" />';
		 		$frm_str .= '</td>';
			$frm_str .= '</tr>';

			$frm_str .= '<tr>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<label for="mail">'._MAIL.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<input name="mail" type="text" id="mail" value="" />';
		 		$frm_str .= '</td>';
			$frm_str .= '</tr>';

		  	$frm_str .= '<tr>';
				$frm_str .= '<td>';
					$frm_str .='<label for="num">'._NUM.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td>';
					$frm_str .='<input name="num" type="text" class="small"  id="num" value="" />';
		 		$frm_str .= '</td>';
				$frm_str .= '<td>';
					$frm_str .='<label for="street">'._STREET.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td>';
					$frm_str .='<input name="street" type="text" class="medium"  id="street" value="" />';
		 		$frm_str .= '</td>';
			$frm_str .= '</tr>';

		 	$frm_str .= '<tr>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<label for="add_comp">'._COMPLEMENT.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<input name="add_comp" type="text"  id="add_comp" value="" />';
		 		$frm_str .= '</td>';
			$frm_str .= '</tr>';

		 	$frm_str .= '<tr>';
				$frm_str .= '<td>';
					$frm_str .='<label for="cp">'._POSTAL_CODE.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td>';
					$frm_str .='<input name="cp" type="text" id="cp" value="" class="small" />';
		 		$frm_str .= '</td>';
				$frm_str .= '<td>';
					$frm_str .='<label for="town">'._TOWN.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td>';
					$frm_str .='<input name="town" type="text" id="town" value="" class="medium" />';
		 		$frm_str .= '</td>';
			$frm_str .= '</tr>';

			$frm_str .= '<tr>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<label for="country">'._COUNTRY.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<input name="country" type="text"  id="country" value="" />';
				$frm_str .= '</td>';
			$frm_str .= '</tr>';

		 	$frm_str .= '<tr>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<label for="comp_data">'._COMP_DATA.' : </label>';
				$frm_str .= '</td>';
				$frm_str .= '<td colspan="2">';
					$frm_str .='<textarea name="comp_data" id="comp_data" ></textarea>';
		 		$frm_str .= '</td>';
			$frm_str .= '</tr>';
		$frm_str .= '</table>';
			$frm_str .='<div align="center">';
				$frm_str .='<input name="submit" type="button" value="'._VALIDATE.'"  class="button" onclick="create_contact(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&page=create_contact\', \''.$id_action.'\');" />';
			$frm_str .=' <input name="cancel" type="button" value="'._CANCEL.'"  onclick="new Effect.toggle(\'create_contact_div\', \'blind\', {delay:0.2});clear_form(\'indexingfrmcontact\');return false;" class="button" />';
		$frm_str .='</div>';
		$frm_str .= '</fieldset >';
		$frm_str .='</form >';
			$frm_str .= '</div><br/>';
		$frm_str .= '</div>';
		$frm_str .= '<script type="text/javascript">show_admin_contacts( true);</script>';

		$path_file = get_file_path($res_id, $coll_id);
		$frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view&id='.$res_id.'&coll_id='.$coll_id.'" name="viewframevalid" id="viewframevalid"  scrolling="auto" frameborder="0" ></iframe>';
		$frm_str .= '</div>';

		/*** Extra javascript ***/
		$frm_str .= '<script type="text/javascript">resize_frame_process("modal_'.$id_action.'", "viewframevalid", true, true);resize_frame_process("modal_'.$id_action.'", "hist_doc", true, false);window.scrollTo(0,0);launch_autocompleter_contacts(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=autocomplete_contacts\');';
		if($core_tools->is_module_loaded('folder'))
		{
		  $frm_str .= 'launch_autocompleter_folders(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=folder&page=autocomplete_folders&mode=project\', \'project\');launch_autocompleter_folders(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=folder&page=autocomplete_folders&mode=market\', \'market\');';
	 	 }
		$frm_str .='init_validation(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=autocomplete_contacts\', \''.$display_value.'\', \''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=change_category\',  \''.$_SESSION['config']['businessappurl'].'index.php?display=true&page=get_content_js\');$(\'baskets\').style.visibility=\'hidden\';var item = $(\'valid_div\'); if(item){item.style.display=\'block\';}';
		$frm_str .='var type_id = $(\'type_id\');';
		$frm_str .='if(type_id){change_doctype(type_id.options[type_id.selectedIndex].value, \''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=change_doctype\', \''._ERROR_DOCTYPE.'\', \''.$id_action.'\', \''.$_SESSION['config']['businessappurl'].'index.php?display=true&page=get_content_js\' , \''.$display_value.'\', '.$res_id.', \''. $coll_id.'\' );}';
		$frm_str .='</script>';

	return addslashes($frm_str);
}

/**
 * Checks the action form
 *
 * @param $form_id String Identifier of the form to check
 * @param $values Array Values of the form
 * @return Bool true if no error, false otherwise
 **/
function check_form($form_id,$values)
{
	$_SESSION['action_error'] = '';
	if(count($values) < 1 || empty($form_id))
	{
		$_SESSION['action_error'] =  _FORM_ERROR;
		return false;
	}
	else
	{
		$attach = get_value_fields($values, 'attach');
		$coll_id = get_value_fields($values, 'coll_id');
		if(!$attach)
		{
			$cat_id = get_value_fields($values, 'category_id');

			if($cat_id == false)
			{
				$_SESSION['action_error'] = _CATEGORY.' '._IS_EMPTY;
				return false;
			}
			$no_error = process_category_check($cat_id, $values);
			return $no_error;
		}
		else
		{
			$title = get_value_fields($values, 'attach_title');
			if(!$title || empty($title))
			{
				$_SESSION['action_error'] .= _TITLE.' '._IS_EMPTY.'<br/>';
			}
			$id_doc = get_value_fields($values, 'res_id');
			if(!$id_doc || empty($id_doc))
			{
				$_SESSION['action_error'] .= _NUM_GED.' '._IS_EMPTY.'<br/>';
			}
			if(!empty($_SESSION['action_error']))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}
}


/**
 * Checks the values of the action form for a given category
 *
 * @param $cat_id String Category identifier
 * @param $values Array Values of the form to check
 * @return Bool true if no error, false otherwise
 **/
function process_category_check($cat_id, $values)
{
	$core = new core_tools();
	// If No category : Error
	if(!isset($_ENV['categories'][$cat_id]))
	{
		$_SESSION['action_error'] = _CATEGORY.' '._UNKNOWN.': '.$cat_id;
		return false;
	}

	// Simple cases
	for($i=0; $i<count($values); $i++)
	{
		if($_ENV['categories'][$cat_id][$values[$i]['ID']]['mandatory'] == true  && (empty($values[$i]['VALUE']) )) //&& ($values[$i]['VALUE'] == 0 && $_ENV['categories'][$cat_id][$values[$i]['ID']]['type_form'] <> 'integer')
		{

			$_SESSION['action_error'] = $_ENV['categories'][$cat_id][$values[$i]['ID']]['label'].' '._IS_EMPTY;
			return false;
		}
		if($_ENV['categories'][$cat_id][$values[$i]['ID']]['type_form'] == 'date' && !empty($values[$i]['VALUE']) && preg_match($_ENV['date_pattern'],$values[$i]['VALUE'])== 0)
		{
			$_SESSION['action_error'] = $_ENV['categories'][$cat_id][$values[$i]['ID']]['label']." "._WRONG_FORMAT."";
			return false;
		}
		if($_ENV['categories'][$cat_id][$values[$i]['ID']]['type_form'] == 'integer' && (!empty($values[$i]['VALUE']) || $values[$i]['VALUE'] == 0) && preg_match("/^[0-9]*$/",$values[$i]['VALUE'])== 0)
		{
			$_SESSION['action_error'] = $_ENV['categories'][$cat_id][$values[$i]['ID']]['label']." "._WRONG_FORMAT."";
			return false;
		}
		if($_ENV['categories'][$cat_id][$values[$i]['ID']]['type_form'] == 'radio' && !empty($values[$i]['VALUE']) && !in_array($values[$i]['VALUE'], $_ENV['categories'][$cat_id][$values[$i]['ID']]['values']))
		{
			$_SESSION['action_error'] = $_ENV['categories'][$cat_id][$values[$i]['ID']]['label']." "._WRONG_FORMAT."";
			return false;
		}
	}

	///// Checks the complementary indexes depending on the doctype
	require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_types.php');
	$type = new types();
	$type_id =  get_value_fields($values, 'type_id');
	$coll_id =  get_value_fields($values, 'coll_id');
	$indexes = $type->get_indexes( $type_id,$coll_id, 'minimal');
	$val_indexes = array();
	for($i=0; $i<count($indexes);$i++)
	{
		$val_indexes[$indexes[$i]] =  get_value_fields($values, $indexes[$i]);
	}
	$test_type = $type->check_indexes($type_id, $coll_id,$val_indexes );
	if(!$test_type)
	{
		$_SESSION['action_error'] .= $_SESSION['error'];
		$_SESSION['error'] = '';
		return false;
	}

	///////////////////////// Other cases
	// Process limit Date
	$_SESSION['store_process_limit_date'] = "";
	if(isset($_ENV['categories'][$cat_id]['other_cases']['process_limit_date']))
	{
		$process_limit_date_use_yes = get_value_fields($values, 'process_limit_date_use_yes');
		$process_limit_date_use_no = get_value_fields($values, 'process_limit_date_use_no');
		if($process_limit_date_use_yes == 'yes')
		{
			$_SESSION['store_process_limit_date'] = "ok";
			$process_limit_date = get_value_fields($values, 'process_limit_date');
			if(trim($process_limit_date) == "" || preg_match($_ENV['date_pattern'], $process_limit_date)== 0)
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['process_limit_date']['label']." "._WRONG_FORMAT."";
				return false;
			}
		}
		elseif($process_limit_date_use_no == 'no')
		{
			$_SESSION['store_process_limit_date'] = "ko";
		}
	}


	// Contact
	if(isset($_ENV['categories'][$cat_id]['other_cases']['contact']))
	{
		$contact_type = get_value_fields($values, 'type_contact_external');
		if(!$contact_type)
		{
			$contact_type = get_value_fields($values, 'type_contact_internal');
		}
		if(!$contact_type)
		{
			$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['type_contact']['label']." "._MANDATORY."";
			return false;
		}
		$contact = get_value_fields($values, 'contact');
		if($_ENV['categories'][$cat_id]['other_cases']['contact']['mandatory'] == true)
		{
			if(empty($contact))
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['contact']['label'].' '._IS_EMPTY;
				return false;
			}
		}
		if(!empty($contact) )
		{
			if($contact_type == 'external' && !preg_match('/\(\d+\)$/', trim($contact)))
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['contact']['label']." "._WRONG_FORMAT.".<br/>".' '._USE_AUTOCOMPLETION;
				return false;
			}
			//elseif($contact_type == 'internal' && preg_match('/\([A-Za-Z0-9-_ ]+\)$/', $contact) == 0)
			elseif($contact_type == 'internal' && preg_match('/\((\s|\d|\h|\w)+\)$/i', $contact) == 0)
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['contact']['label']." "._WRONG_FORMAT.".<br/>"._USE_AUTOCOMPLETION;
				return false;
			}
		}
	}

	if($core->is_module_loaded('entities'))
	{
		// Diffusion list
		if(isset($_ENV['categories'][$cat_id]['other_cases']['diff_list']) && $_ENV['categories'][$cat_id]['other_cases']['diff_list']['mandatory'] == true)
		{
			if(empty($_SESSION['indexing']['diff_list']['dest']['user_id']) || !isset($_SESSION['indexing']['diff_list']['dest']['user_id']))
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['diff_list']['label']." "._MANDATORY."";
				return false;
			}
		}
	}
	if($core->is_module_loaded('folder'))
	{
		$db = new dbquery();
		$db->connect();
		$market = get_value_fields($values, 'market');
		$project_id = '';
		$market_id = '';
		if(isset($_ENV['categories'][$cat_id]['other_cases']['market']) && $_ENV['categories'][$cat_id]['other_cases']['market']['mandatory'] == true)
		{
			if(empty($market))
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['market']['label'].' '._IS_EMPTY;
				return false;
			}
		}
		if(!empty($market) )
		{
			if(!preg_match('/\([0-9]+\)$/', $market))
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['market']['label']." "._WRONG_FORMAT."";
				return false;
			}
			$market_id = str_replace(')', '', substr($market, strrpos($market,'(')+1));
			$db->query("select folders_system_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$market_id);
			if($db->nb_result() == 0)
			{
				$_SESSION['action_error'] = _MARKET.' '.$market_id.' '._UNKNOWN;
				return false;
			}
		}
		$project = get_value_fields($values, 'project');
		if(isset($_ENV['categories'][$cat_id]['other_cases']['project']) && $_ENV['categories'][$cat_id]['other_cases']['project']['mandatory'] == true)
		{
			if(empty($project))
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['project']['label'].' '._IS_EMPTY;
				return false;
			}
		}
		if(!empty($project) )
		{
			if(!preg_match('/\([0-9]+\)$/', $project))
			{
				$_SESSION['action_error'] = $_ENV['categories'][$cat_id]['other_cases']['project']['label']." "._WRONG_FORMAT."";
				return false;
			}
			$project_id = str_replace(')', '', substr($project, strrpos($project,'(')+1));
			$db->query("select folders_system_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$project_id);
			if($db->nb_result() == 0)
			{
				$_SESSION['action_error'] = _MARKET.' '.$project_id.' '._UNKNOWN;
				return false;
			}
		}
		if(!empty($project_id) && !empty($market_id))
		{
			$db->query("select folders_system_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$market_id." and parent_id = ".$project_id);
			if($db->nb_result() == 0)
			{
				$_SESSION['action_error'] = _INCOMPATIBILITY_MARKET_PROJECT;
				return false;
			}
		}
		if(!empty($type_id ) &&  (!empty($project_id) || !empty($market_id)))
		{
			$foldertype_id = '';
			if(!empty($market_id))
			{
				$db->query("select foldertype_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$market_id);
			}
			else //!empty($project_id)
			{
				$db->query("select foldertype_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$project_id);
			}
			$res = $db->fetch_object();
			$foldertype_id = $res->foldertype_id;
			$db->query("select fdl.foldertype_id from ".$_SESSION['tablename']['fold_foldertypes_doctypes_level1']." fdl, ".$_SESSION['tablename']['doctypes']." d where d.doctypes_first_level_id = fdl.doctypes_first_level_id and fdl.foldertype_id = ".$foldertype_id." and d.type_id = ".$type_id);
			if($db->nb_result() == 0)
			{
				$_SESSION['action_error'] .= _ERROR_COMPATIBILITY_FOLDER;
				return false;
			}
		}
	}

	if($core->is_module_loaded('physical_archive'))
	{
		// Arbox id
		$box_id = get_value_fields($values, 'arbox_id');
		if(isset($_ENV['categories'][$cat_id]['other_cases']['arbox_id']) && $_ENV['categories'][$cat_id]['other_cases']['arbox_id']['mandatory'] == true)
		{
			if($box_id == false)
			{
				$_SESSION['action_error'] = _NO_BOX_SELECTED.' ';
				return false;
			}
		}
		if($box_id != false && preg_match('/^[0-9]+$/', $box_id))
		{
			require_once('modules'.DIRECTORY_SEPARATOR.'physical_archive'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
			$physical_archive = new physical_archive();
			$pa_return_value = $physical_archive->load_box_db($box_id, $cat_id, $_SESSION['user']['UserId']);
			if ($pa_return_value == false)
			{
				$_SESSION['action_error'] = _ERROR_TO_INDEX_NEW_BATCH_WITH_PHYSICAL_ARCHIVE;
				return false;
			}
			else
			{
				return true;
			}
		}
	}

		//For specific case => chrono number
/*	$chrono_out = get_value_fields($values, 'chrono_number');
	if(isset($_ENV['categories'][$cat_id]['other_cases']['chrono_number']) && $_ENV['categories'][$cat_id]['other_cases']['arbox_id']['mandatory'] == true)
	{
		if($chrono_out == false)
		{
			$_SESSION['action_error'] = _NO_CHRONO_NUMBER_DEFINED.' ';
			return false;
		}
	}
	if($chrono_out != false && preg_match('/^[0-9]+$/', $chrono_out))
	{
		require_once('modules/physical_archive'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
		$physical_archive = new physical_archive();
		$pa_return_value = $physical_archive->load_box_db($box_id, $cat_id, $_SESSION['user']['UserId']);
		if ($pa_return_value == false)
		{
			$_SESSION['action_error'] = _ERROR_TO_INDEX_NEW_BATCH_WITH_PHYSICAL_ARCHIVE;
			return false;
		}
	}*/

	return true;
}

/**
 * Get the value of a given field in the values returned by the form
 *
 * @param $values Array Values of the form to check
 * @param $field String the field
 * @return String the value, false if the field is not found
 **/
function get_value_fields($values, $field)
{
	for($i=0; $i<count($values);$i++)
	{
		if($values[$i]['ID'] == $field)
		{
			return 	$values[$i]['VALUE'];
		}
	}
	return false;
}

/**
 * Action of the form : update the database
 *
 * @param $arr_id Array Contains the res_id of the document to validate
 * @param $history String Log the action in history table or not
 * @param $id_action String Action identifier
 * @param $label_action String Action label
 * @param $status String  Not used here
 * @param $coll_id String Collection identifier
 * @param $table String Table
 * @param $values_form String Values of the form to load
 **/
function manage_form($arr_id, $history, $id_action, $label_action, $status,  $coll_id, $table, $values_form )
{

	if(empty($values_form) || count($arr_id) < 1 || empty($coll_id))
	{
		$_SESSION['action_error'] = _ERROR_MANAGE_FORM_ARGS;
		return false;
	}

	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_resource.php");
	$db = new dbquery();
	$sec = new security();
	$core = new core_tools();
	$resource = new resource();
	$table = $sec->retrieve_table_from_coll($coll_id);
	$ind_coll = $sec->get_ind_collection($coll_id);
	$table_ext = $_SESSION['collections'][$ind_coll]['extensions'][0];
	$res_id = $arr_id[0];

	$attach = get_value_fields($values_form, 'attach');

	if($attach == false)
	{

		$query_ext = "update ".$table_ext." set ";
		$query_res = "update ".$table." set ";

		$cat_id = get_value_fields($values_form, 'category_id');

		$query_ext .= " category_id = '".$cat_id."' " ;
	//	$query_res .= " status = 'NEW' " ;


		// Specific indexes : values from the form
		// Simple cases
		for($i=0; $i<count($values_form); $i++)
		{
			if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['type_field'] == 'integer' && $_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] <> 'none')
			{
				if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'res')
				{
					$query_res .= ", ".$values_form[$i]['ID']." = ".$values_form[$i]['VALUE'];
				}
				else if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'coll_ext')
				{
					$query_ext .= ", ".$values_form[$i]['ID']." = ".$values_form[$i]['VALUE'];
				}
			}
			else if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['type_field'] == 'string' && $_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] <> 'none')
			{
				if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'res')
				{
					$query_res .= ", ".$values_form[$i]['ID']." = '".$db->protect_string_db($values_form[$i]['VALUE'])."'";
				}
				else if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'coll_ext')
				{
					$query_ext .= ", ".$values_form[$i]['ID']." = '".$db->protect_string_db($values_form[$i]['VALUE'])."'";
				}
			}
			else if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['type_field'] == 'date' && $_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] <> 'none')
			{
				if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'res')
				{
					$query_res .= ", ".$values_form[$i]['ID']." = '".$db->format_date_db($values_form[$i]['VALUE'])."'";
				}
				else if($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'coll_ext')
				{
					$query_ext .= ", ".$values_form[$i]['ID']." = '".$db->format_date_db($values_form[$i]['VALUE'])."'";
				}
			}
		}

		///////////////////////// Other cases
		require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_types.php');
		$type = new types();
		$type->inits_opt_indexes($coll_id, $res_id);
		$type_id =  get_value_fields($values_form, 'type_id');
		$indexes = $type->get_indexes( $type_id,$coll_id, 'minimal');
		$val_indexes = array();
		for($i=0; $i<count($indexes);$i++)
		{
			$val_indexes[$indexes[$i]] =  get_value_fields($values_form, $indexes[$i]);
		}
		$query_res .=  $type->get_sql_update($type_id, $coll_id, $val_indexes);


		// Process limit Date

		if(isset($_ENV['categories'][$cat_id]['other_cases']['process_limit_date']))
		{
			$process_limit_date = get_value_fields($values_form, 'process_limit_date');
			if($_ENV['categories'][$cat_id]['other_cases']['process_limit_date']['table'] == 'res')
			{
				$query_res .= ", process_limit_date = '".$db->format_date_db($process_limit_date)."'";
			}
			else if($_ENV['categories'][$cat_id]['other_cases']['process_limit_date']['table'] == 'coll_ext')
			{
				if($_SESSION['store_process_limit_date'] == "ok")
				{
					$query_ext .= ", process_limit_date = '".$db->format_date_db($process_limit_date)."'";
				}
				$_SESSION['store_process_limit_date'] = "";
			}
		}

		// Contact
		if(isset($_ENV['categories'][$cat_id]['other_cases']['contact']))
		{
			$contact = get_value_fields($values_form, 'contact');
			$contact_type = get_value_fields($values_form, 'type_contact_external');
			if(!$contact_type)
			{
				$contact_type = get_value_fields($values_form, 'type_contact_internal');
			}
			//echo 'contact '.$contact.', type '.$contact_type;
			$contact_id = str_replace(')', '', substr($contact, strrpos($contact,'(')+1));
			if($contact_type == 'internal')
			{
				if($cat_id == 'incoming')
				{
					$query_ext .= ", exp_user_id = '".$db->protect_string_db($contact_id)."'";
				}
				else if($cat_id == 'outgoing' || $cat_id == 'internal')
				{
					$query_ext .= ", dest_user_id = '".$db->protect_string_db($contact_id)."'";
				}
			}
			elseif($contact_type == 'external')
			{
				if($cat_id == 'incoming')
				{
					$query_ext .= ", exp_contact_id = ".$contact_id."";
				}
				else if($cat_id == 'outgoing' || $cat_id == 'internal')
				{
					$query_ext .= ", dest_contact_id = ".$contact_id."";
				}
			}
		}
		if($core->is_module_loaded('folder'))
		{
			$folder_id = '';
			$market = get_value_fields($values_form, 'market');
			$db->connect();
			$db->query("select folders_system_id from ".$table ." where res_id = ".$res_id);
			$res = $db->fetch_object();
			$old_folder_id = $res->folders_system_id;
			if(!empty($market))
			{
				$folder_id = str_replace(')', '', substr($market, strrpos($market,'(')+1));
			}
			else
			{
				$project = get_value_fields($values_form, 'project');
				$folder_id = str_replace(')', '', substr($project, strrpos($project,'(')+1));
			}
			if(!empty($folder_id))
			{
				$query_res .= ", folders_system_id = ".$folder_id."";
			}

			if($folder_id <> $old_folder_id && $_SESSION['history']['folderup'])
			{
				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
				$hist = new history();
				$hist->add($_SESSION['tablename']['fold_folders'], $folder_id, "UP", _DOC_NUM.$res_id._ADDED_TO_FOLDER, $_SESSION['config']['databasetype'],'apps');
				if(isset($old_folder_id) && !empty($old_folder_id))
				{
					$hist->add($_SESSION['tablename']['fold_folders'], $old_folder_id, "UP", _DOC_NUM.$res_id._DELETED_FROM_FOLDER, $_SESSION['config']['databasetype'],'apps');
				}
			}
		}

		if($core->is_module_loaded('entities'))
		{
			// Diffusion list
			$load_list_diff = false;
			if(isset($_ENV['categories'][$cat_id]['other_cases']['diff_list']) )
			{
				if(!empty($_SESSION['indexing']['diff_list']['dest']['user_id']) && isset($_SESSION['indexing']['diff_list']['dest']['user_id']))
				{
					$query_res .= ", dest_user = '".$db->protect_string_db($_SESSION['indexing']['diff_list']['dest']['user_id'])."'";
				}
				$load_list_diff = true;
			}
		}

		if($core->is_module_loaded('physical_archive') && ($_SESSION['arbox_id'] == "1" || $_SESSION['arbox_id'] == ""))
		{
			// Arbox_id + Arbatch_id
			$box_id = get_value_fields($values_form, 'arbox_id');
			$query_res .= ", arbox_id = ".$box_id."";
			require_once('modules'.DIRECTORY_SEPARATOR.'physical_archive'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
			$physical_archive = new physical_archive();
			$pa_return_value = $physical_archive->load_box_db($box_id, $cat_id, $_SESSION['user']['UserId']);
			$query_res .= ", arbatch_id = ".$pa_return_value."";
		}
		$query_res = preg_replace('/set ,/', 'set ', $query_res);
		//$query_res = substr($query_res, strpos($query_string, ','));
		$_SESSION['arbox_id'] = "";
		$db->connect();
		$db->query($query_res." where res_id =".$res_id);
		$db->query($query_ext." where res_id =".$res_id);
		//$db->show();
		if($core->is_module_loaded('entities'))
		{
			if($load_list_diff)
			{
				require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_listdiff.php');
				$diff_list = new diffusion_list();
				$params = array('mode'=> 'listinstance', 'table' => $_SESSION['tablename']['ent_listinstance'], 'coll_id' => $coll_id, 'res_id' => $res_id, 'user_id' => $_SESSION['user']['UserId']);
				$diff_list->load_list_db($_SESSION['indexing']['diff_list'], $params);
			}
		}
		//$_SESSION['indexing'] = array();
		unset($_SESSION['upfile']);

		//$_SESSION['indexation'] = true;
		return array('result' => $res_id.'#', 'history_msg' => '');
	}
	else
	{
		$data = array();
		$fields = "typist,docserver_id,format,offset_doc,logical_adr,scan_user,scan_date, filename, path";
		$db->connect();
		$db->query("select ".$fields." from ".$table." where res_id = ".$res_id);
		$res = $db->fetch_object();
		array_push($data, array('column' => "typist", 'value' => $res->typist, 'type' => "string"));
		array_push($data, array('column' => "docserver_id", 'value' => $res->docserver_id, 'type' => "string"));
		array_push($data, array('column' => "format", 'value' => $res->format, 'type' => "string"));
		array_push($data, array('column' => "status", 'value' => 'NEW', 'type' => "string"));
		array_push($data, array('column' => "offset_doc", 'value' => $res->offset_doc, 'type' => "string"));
		array_push($data, array('column' => "logical_adr", 'value' => $res->logical_adr, 'type' => "string"));
		if(!empty($res->scan_user))
		{
			array_push($data, array('column' => "scan_user", 'value' => $res->scan_user, 'type' => "string"));
		}
		if(isset($res->scan_date))
		{
			array_push($data, array('column' => "scan_date", 'value' => $res->scan_date, 'type' => "function"));
		}
		$title = get_value_fields($values_form, 'attach_title');
		$id_doc = get_value_fields($values_form, 'res_id');

		array_push($data, array('column' => "title", 'value' => $db->protect_string_db($title), 'type' => "string"));
		array_push($data, array('column' => "res_id_master", 'value' => $id_doc, 'type' => "integer"));
		array_push($data, array('column' => "coll_id", 'value' => $db->protect_string_db($coll_id), 'type' => "string"));
		$path = $res->path;
		$filename = $res->filename;
		$docserver_id = $res->docserver_id;
		$db->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$docserver_id."'");
		$res = $db->fetch_object();
		$id = $resource->load_into_db($_SESSION['tablename']['attach_res_attachments'],$path, $filename, $res->path_template,$docserver_id,  $data, $_SESSION['config']['databasetype']);

		if($id == false)
		{
			$_SESSION['action_error'] = _ERROR_RES_ID.' <br/>'.$resource->get_error();
			return false;
		}
		else
		{
			$msg = '';
			if($_SESSION['history']['attachadd'] == "true")
			{
				$msg = ucfirst(_DOC_NUM).$id.' '._ATTACH_TO_DOC_NUM.$res_id;
			}
			$db->query("update ".$table." set status = 'DEL' where res_id =".$res_id);
			$_SESSION['action_error'] = _NEW_ATTACH_ADDED;
			return array('result' => $res_id.'#', 'history_msg' => $msg, 'table_dest' => $_SESSION['tablename']['attach_res_attachments']);
		}
	}
}

?>
