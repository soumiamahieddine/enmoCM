<?php

//Loads the required class
try {
    require_once 'core/class/class_request.php';
    require_once 'core/core_tables.php';
    require_once 'core/class/ObjectControlerAbstract.php';
    require_once 'core/class/ObjectControlerIF.php';
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

/**
 * Class for controling docservers objects from database
 */
class contacts_controler extends ObjectControler implements ObjectControlerIF
{
    public function listContacts($whereClause) {
        $listResult = array();
        try {
            $db = new Database();
            $cpt = 0;
            if (
                isset($whereClause->whereClause)
                && !empty($whereClause->whereClause)
            ) {
                $sqlQuery = "SELECT * FROM contacts WHERE "
                    . $whereClause->whereClause . " ORDER BY contact_id ASC";
            } else {
                $sqlQuery = "SELECT * FROM contacts ORDER BY contact_id ASC";
            }

            $stmt = $db->query($sqlQuery);

            if ($stmt->rowCount() > 0) {
                while ($line = $stmt->fetchObject()) {
                    $listResult[$cpt]['contact_id'] = $line->contact_id;
                    $listResult[$cpt]['lastname'] = $line->lastname;
                    $listResult[$cpt]['firstname'] = $line->firstname;
                    $listResult[$cpt]['society'] = $line->society;
                    $listResult[$cpt]['function'] = $line->function;
                    $listResult[$cpt]['address_num'] = $line->address_num;
                    $listResult[$cpt]['address_street'] = $line->address_street;
                    $listResult[$cpt]['address_complement'] = $line->address_complement;
                    $listResult[$cpt]['address_town'] = $line->address_town;
                    $listResult[$cpt]['address_postal_code'] = $line->address_postal_code;
                    $listResult[$cpt]['address_country'] = $line->address_country;
                    $listResult[$cpt]['email'] = $line->email;
                    $listResult[$cpt]['phone'] = $line->phone;
                    $listResult[$cpt]['other_data'] = $line->other_data;
                    $listResult[$cpt]['is_corporate_person'] = $line->is_corporate_person;
                    $listResult[$cpt]['user_id'] = $line->user_id;
                    $listResult[$cpt]['title'] = $line->title;
                    $listResult[$cpt]['enabled'] = $line->enabled;
                    $cpt++;
                }
            } else {
                $error = 'Aucun Contacts dans la base';
            }
        } catch (Exception $e) {
            $fault = new SOAP_Fault($e->getMessage(), '1');
            return $fault->message();
        }
        $func = new functions();
        $resultArray = array();
        $resultArray = $func->object2array($listResult);
        $return = array(
            'status' => $sqlQuery,
            'value' => $resultArray,
            'error' => $error,
        );
        return $return;
    }

    /**
     * Save given object in database.
     * Return true if succeeded.
     * @param unknown_type $object
     * @return boolean
     */
    function save($object)
    {
        return true;
    }

    /**
     * Return object with given id
     * if found.
     * @param $object_id
     */
    function get($object_id)
    {
        return true;
    }

    /**
     * Delete given object from
     * database.
     * Return true if succeeded.
     * @param unknown_type $object
     * @return boolean
     */
    function delete($object)
    {
        return true;
    }

    public function CreateContact($data){

        try {
            $func = new functions();
            $data = $func->object2array($data);
            $db = new Database();
            $queryContactFields = '(';
            $queryContactValues = '(';
            $queryAddressFields = '(';
            $queryAddressValues = '(';
            $iContact = 0;
            $iAddress = 0;
            $currentContactId = "0";
            $currentAddressId = "0";

            for ($i=0;$i<count($data);$i++) {
                if (strtoupper($data[$i]['column']) == strtoupper('email') && $data[$i]['value'] <> "") {
                    $theString = str_replace(">", "", $data[$i]['value']);
                    $mail = explode("<", $theString);
                    $stmt = $db->query("SELECT contact_id, ca_id FROM view_contacts WHERE email = '" . $mail[count($mail) -1] . "' and enabled = 'Y'");
                    $res = $stmt->fetchObject();
                    if ($res->ca_id <> "") {
                        $contact_exists = true;

                        $returnResArray = array(
                            'returnCode' => (int) 0,
                            'contactId' => $res->contact_id,
                            'addressId' => $res->ca_id,
                            'error' => '',
                        );
                        return $returnResArray;
                        
                    } else {
                        $contact_exists = false;
                    }
                }

                $data[$i]['column'] = strtolower($data[$i]['column']);

                if ($data[$i]['table'] == "contacts_v2") {
                    //COLUMN
                    $queryContactFields .= $data[$i]['column'] . ',';
                    //VALUE
                    if ($data[$i]['type'] == 'string' || $data[$i]['type'] == 'date') {
                        $queryContactValues .= "'" . $data[$i]['value'] . "',";
                    } else {
                        $queryContactValues .= $data[$i]['value'] . ",";
                    }
                } else if ($data[$i]['table'] == "contact_addresses") {
                    //COLUMN
                    $queryAddressFields .= $data[$i]['column'] . ',';
                    //VALUE
                    if ($data[$i]['type'] == 'string' || $data[$i]['type'] == 'date') {
                        $queryAddressValues .= "'" . $data[$i]['value'] . "',";
                    } else {
                        $queryAddressValues .= $data[$i]['value'] . ",";
                    }
                }
            }

            $queryContactFields .= "user_id, entity_id, creation_date)";
            $queryContactValues .= "'superadmin', 'SUPERADMIN', current_timestamp)";

            if (!$contact_exists) {
                $queryContact = " INSERT INTO contacts_v2 " . $queryContactFields
                       . ' values ' . $queryContactValues ;

                $db->query($queryContact);

                $currentContactId = $db->last_insert_id('contact_v2_id_seq');

                $queryAddressFields .= "contact_id, user_id, entity_id)";
                $queryAddressValues .=  $currentContactId . ", 'superadmin', 'SUPERADMIN')";

                $queryAddress = " INSERT INTO contact_addresses " . $queryAddressFields
                       . ' values ' . $queryAddressValues ;

                $db->query($queryAddress);
                $currentAddressId = $db->last_insert_id('contact_addresses_id_seq');
            }

            $returnResArray = array(
                'returnCode' => (int) 0,
                'contactId' => $currentContactId,
                'addressId' => $currentAddressId,
                'error' => '',
            );
            
            return $returnResArray;
            
        } catch (Exception $e) {
            $returnResArray = array(
                'returnCode' => (int) -1,
                'contactId' => '',
                'addressId' => '',
                'error' => 'unknown error' . $e->getMessage(),
            );
            return $returnResArray;
        }
    }
}
