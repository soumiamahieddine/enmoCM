<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief iParapheur Controller
 * @author dev@maarch.org
 */


class iParapheurController
{
    public static function getModal()
    {
        $html ='<div align="center">';
        $html .='<input type="button" name="cancel" id="cancel" class="button" value="valider"/>';
        $html .='<input type="button" name="cancel" id="cancel" class="button" value="annuler"/>';
        $html .='</div>';

        return $html;
    }
}
