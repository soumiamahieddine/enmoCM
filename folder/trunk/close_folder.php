<?php

if (isset($_SESSION['folder'])
    && ($_SESSION['folder']['status'] == 'NEW')
) {
    ?>
    <input type="submit" name="closeFolder" id="closeFolder" class="button" value="<?php
    echo _CLOSE_FOLDER;
    ?>" />
    <?php
}