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
            $db = new dbquery();
            $db->connect();
            $cpt = 0;
            if (
                isset($whereClause->whereClause)
                && !empty($whereClause->whereClause)
            ) {
                $sqlQuery = "select * from contacts WHERE "
                    . $whereClause->whereClause . " ORDER BY contact_id ASC";
            } else {
                $sqlQuery = "select * from contacts ORDER BY contact_id ASC";
            }

            $db->query($sqlQuery);

            if ($db->nb_result() > 0) {
                while ($line = $db->fetch_object()) {
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
}
