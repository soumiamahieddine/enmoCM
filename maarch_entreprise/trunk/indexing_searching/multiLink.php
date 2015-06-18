<?php
/**
est appelé par la function ajax multiLink. Elle permet de mettre en session les données lorsqu'on clique sur le checkbox. 
*/
//$_SESSION['stockCheckbox'] = null;
  if (isset($_REQUEST['courrier_purpose'])) {
	$key=false;
  # Append something onto the $name variable so that you can see that it passed through your PHP script.
   $courrier = $_REQUEST['courrier_purpose'];

   if(!empty($_SESSION['stockCheckbox'])){
		$key = in_array($_REQUEST['courrier_purpose'], $_SESSION['stockCheckbox']);
		if($key ==true){
			unset($_SESSION['stockCheckbox'][array_search($_REQUEST['courrier_purpose'], $_SESSION['stockCheckbox'])]);
			$_SESSION['stockCheckbox']=array_values($_SESSION['stockCheckbox']);
			echo json_encode($_SESSION['stockCheckbox']);
			exit();
		}

   }



   if(empty($_SESSION['stockCheckbox'])){
   	$tableau[] = $_REQUEST['courrier_purpose'];
   	$_SESSION['stockCheckbox'] = $tableau;
   	echo json_encode($_SESSION['stockCheckbox']);
   }elseif($key==false and !empty($_SESSION['stockCheckbox'])){
   	array_push($_SESSION['stockCheckbox'],$_REQUEST['courrier_purpose']);
   	echo json_encode($_SESSION['stockCheckbox']);
   }
   

    # I'm sending back a json structure in case there are multiple pieces of information you'd like
    # to pass back.
    

    }


exit;

?>