<?php
/**
* File : fulltext_search_help.php
*
* Help for fulltext search
*
* @package  Maarch Entreprise 1.3
* @version 1.3
* @since 04/2012
* @license GPL
* @author  Sébastien NANA  <dev@maarch.org>
*/

function show_helper($mode)
{
	$core_tools = new core_tools();
	$core_tools->load_lang();
	$core_tools->load_html();
	?>
	<div class="block small_text" >


	<h3><img src ="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_detail_b.gif" /> <?php echo _TIPS_FULLTEXT; ?></h3>
	<?php

			echo "<p align='right'>";
				//echo "<b><u>"._TIPS_CHARACTER.":</u></b><br/><br/>";
			echo "</p>";
			echo "<p> <br/><br/> </p>";
			echo "<p align='justify'>";
				echo "<p><b> * : </b><em>"._TIPS_KEYWORD1."</em></p>";
				echo "<div style='border:1px black solid; padding:3px;'><b>"._HELP_FULLTEXT_SEARCH_EXEMPLE1."</b></div>";
				echo "<p><b> \" \" ou + : </b><em>"._TIPS_KEYWORD2."</em></p>";
				echo "<div style='border:1px black solid; padding:3px;'><b>"._HELP_FULLTEXT_SEARCH_EXEMPLE2."</b></div>";
				echo "<p><b> ~ : </b><em>"._TIPS_KEYWORD3."</em><br/></p>";
				echo "<div style='border:1px black solid; padding:3px;'><b>"._HELP_FULLTEXT_SEARCH_EXEMPLE3."</b></div>";
				echo "<p> <br/> </p>";
				echo "<p><em>"._TIPS_FULLTEXT_TEXT."</em></p>";
			echo "</p>";
	echo "</div>";
	echo "<div class='block_end'>&nbsp;</div>";
	if($mode == 'popup')
	{
		echo '<br/><div align="center"><input type="button" class="button" name="close" value="'._CLOSE_WINDOW.'" onclick="self.close();"</div>';
	}
}

if(isset($_REQUEST['mode']))
{
	$mode = trim($_REQUEST['mode']);
}
else
{
	$mode = '';
}

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
echo '<div id="header">';
show_helper($mode);
echo '</div></body></html>';


