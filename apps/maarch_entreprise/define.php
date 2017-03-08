<?php
/*
*    Copyright 2008-2017 Maarch
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

/**
 * Ce fichier a mettre en paralléle dans le custom, permet de mettre des definitions spécifique au custom (exemple langue ou remplacement de define de ce fichier de base)
 **/
require_once 'apps/maarch_entreprise/define_custom.php';

// Variable pour activer les vues V2
if (!defined('V2_ENABLED')) {
	define('V2_ENABLED', false);
}
if (!defined('PROD_MODE')) {
	define('PROD_MODE', true);
}
