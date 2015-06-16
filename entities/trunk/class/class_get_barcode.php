<?php 
/**
* File : class_get_barcode.php
*
* Frame able to list boxes in physical archives modules
*
* @package  Maarch  3.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Loic Vinet <dev@maarch.org>

*/

class barcocdeFPDF extends FPDI {

  	function generateBarCode( $type, $code, $hh=60, $hr, $hw, $showtype ) {
    	$img_file_name = $_SESSION['user']['UserId'].time().rand()."_".$_SESSION['physical_archive']['tmp']['current_batch_in_progress'].".png";
		$objCode = new pi_barcode( $type, $code, $hh, $hr, $hw, $showtype );
		$name = $objCode -> makeImage($img_file_name);
		return  $img_file_name;
  	}
}
