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
*
*
* @file
* @author Loic Vinet <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
include_once 'modules/tags/route.php';

if (!$tag_customsize)
{
	$tag_customsize = '400px';
}

if (!$tag_customsize)
{
	$tag_customcols = '35';
}

if ($core_tools->test_service('add_tag_to_res', 'tags',false) == 1)
{
	$frm_str .='<textarea rows="2" cols="'.$tag_customcols.'" id="tag_userform" style="width=:'.$tag_customsize.';" >'.$tag.'</textarea>&nbsp;';
	$frm_str .='<input type="button" class="button tagbutton" value="'._ADD.'" onclick="add_this_tags('.$route_tag_add_tags_from_res.', '.$route_tag_ui_script.')">';
	$frm_str .='<p class="tinyminihelp" align="center">'._TAG_SEPARATOR_HELP.'</p>';
}

?>