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

  	function generateBarCode($type, $code, $hh=60, $hr, $hw, $showtype) 
  	{
    	$img_file_name = $_SESSION['user']['UserId'] . time() . rand() . ".png";
		
		$objCode = new pi_barcode();

		$objCode->setCode($code);

		$objCode->setType($type);

		$objCode->setSize($hh, $hw);
		  
		$objCode->setText($code);
		  
		$objCode->hideCodeType();
		  
		$objCode->setFiletype('PNG');               

		$objCode->writeBarcodeFile($_SESSION['config']['tmppath'] . $img_file_name);
		
		return  $img_file_name;
  	}
}
