<?php
/*
*
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
*
/**
* @brief  Reports frame : show reports in frame (include type))
*
* @file
* @author Claire Yves Christian KPAKPO <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup reports
*/
include('core/init.php'); 

require_once("core/class/class_functions.php");
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('show_reports', 'reports');
echo '';
?>
<br>
<iframe src="<?php echo $_SESSION['urltomodules'];?>/reports/user_reports.php" name="user_reports" id="user_reports" frameborder="0" width="100%" height="560" scrolling="no"></iframe>


