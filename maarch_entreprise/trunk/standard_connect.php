<?php

echo '<form name="formlogin" id="formlogin" method="post" action="'.$_SESSION['config']['businessappurl'].'index.php?display=true&page=log" class="forms">';
			echo '<input type="hidden" name="display" value="true" />';
			echo '<input type="hidden" name="page" value="log" />';
            echo '<p>';
                echo '<label for="login">'._ID.' :</label>';
                echo '<input name="login" id="login" value="" type="text"  />';
            echo '</p>';

            echo '<p>';
                echo '<label for="pass">'._PASSWORD.' :</label>';
                echo '<input name="pass" id="pass" value="" type="password"  />';
            echo '</p>';
            echo '<p class="buttons">';
                echo '<input type="submit" class="button" name="submit" value="'._SEND.'" />';
            echo '</p>';
            echo '<div class="error">'.$error;
            $_SESSION['error'] = '';
            
            echo '</div>';
        echo '</form>';

?>
