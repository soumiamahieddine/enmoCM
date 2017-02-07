<?php

/**
*   @copyright 2017 Maarch
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
require_once 'core/services/Logs.php';

/**
 * Service des gestion des appels rest
 */
class Core_Rest_Service {
	/**
	 * Constructeur
	 */
	public function __construct() {
	}

	/**
	 * Encodage en json
	 * @param  array $json Liste d'arguments
	 *	- string status [description]
	 *	- string result [description]
	 *	- string errors [description]
	 *	- string debug [description]
	 */
	public function json(array $json) {
		header('Content-Type: application/json');
		if ( !isset($json['status']) ) {
			throw new \Exception('$oServiceRest->return([...]) : status not-isset');
		}
		if ( !isset($json['result']) ) {
			throw new \Exception('$oServiceRest->return([...]) : result not-isset');
		}
		if ( !isset($json['errors']) ) {
			throw new \Exception('$oServiceRest->return([...]) : error not-isset');
		}
		if ( empty($json['debug']) ) {
			$json['debug'] = null;
		}
		if ( is_array($json['result']) ) {
			$json['result_count'] = count($json['result']);
		}
		echo json_encode($json);
    	exit;
	}

	/**
	 * Renvoi avec succès des données encodées en json
	 * @param  array $json Liste d'arguments
	 *	- string status [description]
	 *	- string result [description]
	 *	- string errors [description]
	 *	- string debug [description]
	 */
	public function returnSuccess(array $json) {
		$this->json([
			'status' => empty($json['status']) ? 0 : $json['status'],
			'result' => $json['result'],
			'errors' => [],
			'debug'  => empty($json['debug'])? null : $json['debug'],
		]);
	}

	/**
	 * Renvoi avec erreur des données encodées en json
	 * @param  array $json Liste d'arguments
	 *	- string status [description]
	 *	- string result [description]
	 *	- string errors [description]
	 *	- string debug [description]
	 */
	public function returnError(array $json) {
		if ( is_string($json['errors'])) {
			$json['errors'] = [$json['errors']];
		}
		$json['status'] = empty($json['status']) ? -1 : $json['status'];
		$json['debug'] = empty($json['debug'])? null : $json['debug'];
		Core_Logs_Service::error([
			'message' => 'Exception : '.$json['errors'][0]."\n".$json['debug'],
			'code'    => $json['status'],
			'file'    => __FILE__,
		]);
		$this->json([
			'status' => $json['status'],
			'result' => false,
			'errors' => $json['errors'],
			'debug'  => DEBUG ? $json['debug'] : null,
		]);
	}

	public function returnWarning(array $json) {
		if ( is_string($json['errors'])) {
			$json['errors'] = [$json['errors']];
		}
		$json['status'] = empty($json['status']) ? -1 : $json['status'];
		$json['debug'] = empty($json['debug'])? null : $json['debug'];
		Core_Logs_Service::warning([
			'message' => 'Exception : '.$json['errors'][0]."\n".$json['debug'],
			'code'    => $json['status'],
			'file'    => __FILE__,
		]);
		$this->json([
			'status' => $json['status'],
			'result' => false,
			'errors' => $json['errors'],
			'debug'  => DEBUG ? $json['debug'] : null,
		]);
	}

}
