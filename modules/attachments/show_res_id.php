<?php

/*
*   Copyright 2015 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

$core = new core_tools();
$core->test_user();

?>

<script type="text/javascript">
var input_res = window.opener.$('res_id_link');
<?php 
if(_ID_TO_DISPLAY == 'chrono_number'){ 
?>
    var input_chrono_id = window.opener.$('input_chrono_id');
<?php 
    require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php';
    $db = new Database();
    $arrayChrono = array();
    foreach ($_SESSION['stockCheckbox'] as $key => $value) {
        $stmt = $db->query("SELECT alt_identifier FROM mlb_coll_ext WHERE res_id = ?", array($value));
        $res = $stmt->fetchObject();
        $chrono = $res->alt_identifier;
        array_push($arrayChrono, $chrono);
    }
} ?>

//VALIDATE_MAIL
if (input_res) {
	input_res.value=<?php echo json_encode($_SESSION['stockCheckbox']);?>;
        <?php 
        if(_ID_TO_DISPLAY == 'chrono_number'){ 
        ?>
            input_chrono_id.value=<?php echo json_encode($arrayChrono);?>;       
        <?php  } ?>
	window.opener.$('attach_link').click();
} else { //INDEX_MLB
	window.opener.$('res_id').value=<?php echo json_encode($_SESSION['stockCheckbox']);?>;
        <?php 
        if(_ID_TO_DISPLAY == 'chrono_number'){ 
        ?>
            window.opener.$('chrono_id').value=<?php echo json_encode($arrayChrono);?>;
        <?php  } ?>
}

<?php
if ($_SESSION['current_basket']['id'] == "IndexingBasket") {
	?>window.opener.$('attach').click();<?php
}
//$_SESSION['stockCheckbox'] = '';
?>
self.close();
</script>
