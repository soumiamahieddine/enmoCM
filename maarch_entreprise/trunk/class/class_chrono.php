<?php
/**
* Chrono number Class
*
* Contains all the specific functions of chrono number
*
* @package  Maarch LetterBox 3.0
* @version 3.0
* @since 06/2007
* @license GPL
* @author  Loïc Vinet  <dev@maarch.org>
*
*/
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_db.php';
require_once 'core/core_tables.php';

class chrono
{
    public function get_chrono_number($resId, $view)
    {
        $db = new dbquery();
        $db->connect();
        $db->query(
            "select alt_identifier from " . $view . " where res_id = "
            . $resId . " "
        );
        $res = $db->fetch_object();
        return $res->alt_identifier;
    }
    /**
    * Return an array with all structure readed in chorno.xml
    *
    * @param string $xml_file add or up (a supprimer)
    */
    public function get_structure($idChrono)
    {
        $globality = array();
        $parameters = array();
        $chronoArr = array();

        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
            . 'chrono.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'xml'
                  . DIRECTORY_SEPARATOR . 'chrono.xml';
        } else {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
                  . 'chrono.xml';
        }
        $chronoConfig = simplexml_load_file($path);
        if ($chronoConfig) {
            foreach ($chronoConfig -> CHRONO as $chronoTag) {
                if ($chronoTag->id == $idChrono) {
                    $chronoId = (string) $chronoTag->id;
                    $separator = (string) $chronoTag->separator;
                    array_push(
                        $parameters,
                        array(
                            'ID' => $chronoId ,
                            'SEPARATOR' => $separator,
                        )
                    );
                    foreach ($chronoTag->ELEMENT as $item) {
                        $type = $item->type;
                        $value = (string) $item->value;
                        array_push(
                            $chronoArr,
                            array(
                                'TYPE' => $type,
                                'VALUE' => $value,
                            )
                        );
                    }
                }
            }
            array_push(
                $globality,
                array(
                    'PARAMETERS' => $parameters,
                    'ELEMENTS' => $chronoArr,
                )
            );

            return $globality;
        } else {
            echo "chrono::get_structure error";
        }

    }

    public function convert_date_field($chronoArray)
    {
        for ($i = 0; $i <= count($chronoArray); $i ++) {
            if (isset($chronoArray[$i]['TYPE'])
                && $chronoArray[$i]['TYPE'] == "date"
            ) {
                if ($chronoArray[$i]['VALUE'] == "year") {
                    $chronoArray[$i]['VALUE'] = date('Y');
                } else if ($chronoArray[$i]['VALUE'] == "month") {
                    $chronoArray[$i]['VALUE'] = date('m');
                } else if ($chronoArray[$i]['VALUE'] == "day") {
                    $chronoArray[$i]['VALUE'] = date('d');
                } else if ($chronoArray[$i]['VALUE'] == "full_date") {
                    $chronoArray[$i]['VALUE'] = date('dmY');
                }
            }
        }
        return $chronoArray;
    }


    public function convert_maarch_var($chronoArray, $phpVar)
    {
        for ($i = 0; $i <= count($chronoArray); $i ++) {
            if (isset($chronoArray[$i]['TYPE'])
            && $chronoArray[$i]['TYPE'] == "maarch_var"
            ) {
                if ($chronoArray[$i]['VALUE'] == "arbox_id") {
                    $chronoArray[$i]['VALUE'] = $phpVar['arbox_id'];
                } else if ($chronoArray[$i]['VALUE'] == "entity_id") {
                    $chronoArray[$i]['VALUE'] = $phpVar['entity_id'];;
                } else if ($chronoArray[$i]['VALUE'] == "type_id") {
                    $chronoArray[$i]['VALUE'] = $phpVar['type_id'];;
                }
            }
        }
        return $chronoArray;
    }


    public function convert_maarch_forms($chronoArray, $forms)
    {
        for ($i = 0; $i <= count($chronoArray); $i ++) {
            if (isset($chronoArray[$i]['TYPE'])
                && $chronoArray[$i]['TYPE'] == "maarch_form"
            ) {
                foreach ($forms as $key => $value) {
                    if ($chronoArray[$i]['VALUE'] == $key) {
                        $chronoArray[$i]['VALUE'] = $value;
                    }
                }
            }
        }
        return $chronoArray;
    }


    public function convert_maarch_functions($chronoArray, $phpVar = 'false')
    {
        for ($i = 0; $i <= count($chronoArray); $i ++) {
            if (isset($chronoArray[$i]['TYPE'])
                && $chronoArray[$i]['TYPE'] == "maarch_functions"
            ) {
                if ($chronoArray[$i]['VALUE'] == "chr_global") {
                    $chronoArray[$i]['VALUE'] = $this->execute_chrono_for_this_year();
                } else if ($chronoArray[$i]['VALUE'] == "chr_by_entity") {
                    $chronoArray[$i]['VALUE'] = $this->execute_chrono_by_entity(
                        $phpVar['entity_id']
                    );
                } else if ($chronoArray[$i]['VALUE'] == "chr_by_category") {
                    $chronoArray[$i]['VALUE'] = $this->execute_chrono_by_category(
                        $phpVar['category_id']
                    );
                } else if ($chronoArray[$i]['VALUE'] == "category_char") {
                    $chronoArray[$i]['VALUE'] = $this->_executeCategoryChar(
                        $phpVar
                    );
                } else if ($chronoArray[$i]['VALUE'] == "chr_by_folder") {
                   $chronoArray[$i]['VALUE'] = $this->execute_chrono_by_folder(
                       $phpVar['folder_id']
                   );
                }
            }
        }
        return $chronoArray;
    }


    public function execute_chrono_for_this_year()
    {
        $db = new dbquery();
        $db->connect();
        //Get the crono key for this year
        $db->query(
            "SELECT param_value_int from " . PARAM_TABLE
            . " where id = 'chrono_global_" . date('Y') . "' "
        );
        if ($db->nb_result() == 0) {
            $chrono = $this->_createNewChronoGlobal($db);
        } else {
            $fetch = $db->fetch_object();
            $chrono = $fetch->param_value_int;
        }
        $this->_updateChronoForThisYear($chrono, $db);
        return $chrono;
    }


    public function execute_chrono_by_entity($entity)
    {
        $db = new dbquery();
        $db->connect();
        //Get the crono key for this year
        $db->query(
            "SELECT param_value_int from " . PARAM_TABLE
            . " where id = 'chrono_" . $entity . "_" . date('Y') . "' "
        );
        if ($db->nb_result() == 0) {
            $chrono = $this->_createNewChronoForEntity($db, $entity);
        } else {
            $fetch = $db->fetch_object();
            $chrono = $fetch->param_value_int;
        }
        $this->_updateChronoForEntity($chrono, $db, $entity);
        return $chrono;
    }

    public function execute_chrono_by_category($category)
    {
        $db = new dbquery();
        $db->connect();
        //Get the crono key for this year
        $db->query(
            "SELECT param_value_int from " . PARAM_TABLE
            . " where id = 'chrono_" . $category . "_" . date('Y') . "' "
        );
        if ($db->nb_result() == 0) {
            $chrono = $this->_createNewChronoForCategory($db, $category);
        } else {
            $fetch = $db->fetch_object();
            $chrono = $fetch->param_value_int;
        }
        $this->_updateChronoForCategory($chrono, $db, $category);
        return $chrono;
    }

    public function execute_chrono_by_folder($folder)
    {
        $db = new dbquery();
        $db->connect();
        $folders_system_id = $_SESSION['folderId'];
        //Get the crono key for this folder
        $db->query(
                "SELECT param_value_int from " . PARAM_TABLE
            . " where id = 'chrono_folder_" . $folders_system_id .  "' "
        );
        if ($db->nb_result() == 0) {
                $chrono = $this->_createNewChronoForFolder($db, $folder);
        } else {
                $fetch = $db->fetch_object();
                $chrono = $fetch->param_value_int;
        }
        $this->_updateChronoForFolder($chrono, $db, $folder);
        return $chrono;
    }

    private function _executeCategoryChar($phpVar)
    {
        if (! $phpVar['category_id']) {
            return "category::php_var error";
        } else {
            if ($phpVar['category_id'] == "incoming") {
                return "E";
            } else if ($phpVar['category_id'] == "outgoing") {
                return "S";
            } else {
                return '';
            }
        }
    }
    
    //For global chrono
    private function _updateChronoForThisYear($actualChrono, $db)
    {
        $actualChrono++;
        $db->query(
            "UPDATE " . PARAM_TABLE . " SET param_value_int = '" . $actualChrono
            . "'  WHERE id = 'chrono_global_" . date('Y') . "' "
        );
    }

    private function _createNewChronoGlobal($db)
    {
        $db->query(
            "INSERT INTO " . PARAM_TABLE . " (id, param_value_int) VALUES "
            . "('chrono_global_" . date('Y') . "', '1')"
        );
        return 1;
    }


    //For specific chrono =>category
    private function _updateChronoForCategory($actualChrono, $db, $category)
    {
        $actualChrono++;
        $db->query(
            "UPDATE " . PARAM_TABLE . " SET param_value_int = '" . $actualChrono
            . "' WHERE id = 'chrono_" . $category . "_" . date('Y') . "' "
        );
    }

    private function _createNewChronoForCategory($db, $category)
    {
        $db->query(
            "INSERT INTO " . PARAM_TABLE . " (id, param_value_int) VALUES "
            . "('chrono_" . $category . "_" . date('Y') . "', '1')"
        );
        return 1;
    }


    //For specific chrono =>entity
    private function _updateChronoForEntity($actualChrono, $db, $entity)
    {
        $actualChrono++;
        $db->query(
            "UPDATE " . PARAM_TABLE . " SET param_value_int = '" . $actualChrono
            . "'  WHERE id = 'chrono_" . $entity . "_" . date('Y') . "' "
        );
    }

    private function _createNewChronoForEntity($db, $entity)
    {
        $db->query(
            "INSERT INTO " . PARAM_TABLE . " (id, param_value_int) VALUES "
            . "('chrono_" . $entity . "_" . date('Y') . "', '1')"
        );
        return 1;
    }
    
    // For specific chrono => folder
    private function _updateChronoForFolder($actualChrono, $db, $folder)
    {
        $actualChrono++;
        $db->query(
                "UPDATE " . PARAM_TABLE . " SET param_value_int = '" . $actualChrono
            . "'  WHERE id = 'chrono_folder_" . $folder .  "' "
        );
    }
    
    private function _createNewChronoForFolder($db, $folder)
    {
        $db->query(
                "INSERT INTO " . PARAM_TABLE . " (id, param_value_int) VALUES "
            . "('chrono_folder_" . $folder .  "', '1')"
        );
        return 1;
    }
    
    public function generate_chrono($chronoId, $phpVar='false', $form='false')
    {
        $tmp = $this->get_structure($chronoId);
        $elements = $tmp[0]['ELEMENTS'];
        $parameters = $tmp[0]['PARAMETERS'];

        //Launch any conversion needed for value in the chrono array
        $elements = $this->convert_date_field($elements); //For type date
        $elements = $this->convert_maarch_var($elements, $phpVar); //For php var in maarch
        $elements = $this->convert_maarch_functions($elements, $phpVar);
        $elements = $this->convert_maarch_forms($elements, $form); //For values used in forms

        //Generate chrono string
        $string = $this->convert_in_string($elements, $parameters);
        return $string;
    }


    public function convert_in_string($elements, $parameters)
    {
        $separator = $parameters[0]['SEPARATOR'];

        $thisString = '';
        //Explode each elements of this array
        foreach ($elements as $array) {
            $thisString .= $separator;
            $thisString .= $array['VALUE'];
        }

        //$thisString = substr($thisString, 1);
        return $thisString;
    }
}
