<?php

/*
*    Copyright 2008 - 2011 Maarch
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
* @brief Core tables declarations
*
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/


if (! defined('_DOCSERVERS_TABLE_NAME')) {
    define('_DOCSERVERS_TABLE_NAME', 'docservers');
}
if (!defined('_DOCSERVER_TYPES_TABLE_NAME')) {
    define('_DOCSERVER_TYPES_TABLE_NAME', 'docserver_types');
}
if (! defined('_LC_CYCLE_STEPS_TABLE_NAME')) {
    define('_LC_CYCLE_STEPS_TABLE_NAME', 'lc_cycle_steps');
}
if (! defined('HISTORY_TABLE')) {
    define('HISTORY_TABLE', 'history');
}
if (! defined('PARAM_TABLE')) {
    define('PARAM_TABLE', 'parameters');
}
if (! defined('SAVED_QUERIES')) {
    define('SAVED_QUERIES', 'saved_queries');
}
if (! defined('SECURITY_TABLE')) {
    define('SECURITY_TABLE', 'security');
}
if (! defined('SESSION_SECURITY_TABLE')) {
    define('SESSION_SECURITY_TABLE', 'session_security');
}
if (! defined('STATUS_TABLE')) {
    define('STATUS_TABLE', 'status');
}
if (! defined('USERGROUPS_TABLE')) {
    define('USERGROUPS_TABLE', 'usergroups');
}
if (! defined('USERGROUPS_SERVICES_TABLE')) {
    define('USERGROUPS_SERVICES_TABLE', 'usergroups_services');
}
if (! defined('USERS_TABLE')) {
    define('USERS_TABLE', 'users');
}
