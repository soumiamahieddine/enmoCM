<?php
/*
*    Copyright 2008-2011 Maarch
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

/*
* @brief This script make a "svn_update" for the directory $_REQUEST['dir'], 
* reload the parent page and then close this one
*
*
* @file
* @author Arnaud Veber
* @date $date$
* @version $Revision$
* @ingroup admin
*/

if (isset($_REQUEST['dir']) && !empty($_REQUEST['dir'])) {
    svn_update(realpath($_REQUEST['dir']));
}
$dossier = end(explode('/', $_REQUEST['dir']));
$_SESSION['error'] = $dossier.' à été mis à jour';
?>

<script language="javascript">
    window.opener.location.href = '../../index.php?page=svn_monitoring_controler&admin=svn_monitoring&show=<?php echo $dossier; ?>#<?php echo $dossier; ?>';
    window.close();
</script>
