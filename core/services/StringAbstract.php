<?php

/**
*   @copyright 2016 capgemini
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

require_once 'core'. DIRECTORY_SEPARATOR . 'services'. DIRECTORY_SEPARATOR . 'Abstract.php';

class Core_StringAbstract_Service extends Core_Abstract_Service {

    /**
    * Delete accents
    *
    * @param  $str (string)
    * @param  $charset = 'utf-8' (string)
    *
    * @return  string $str
    */
    public static function wd_remove_accents(
        $str,
        $charset ='utf-8'
    )
    {
        $str = htmlentities(
            $str,
            ENT_NOQUOTES,
            "utf-8"
        );
        $str = preg_replace(
            '#\&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring)\;#',
            '\1',
            $str
        );
        $str = preg_replace(
            '#\&([A-za-z]{2})(?:lig)\;#',
            '\1',
            $str
        );
        $str = preg_replace(
            '#\&[^;]+\;#',
            '',
            $str
        );

        return $str;
    }
}
