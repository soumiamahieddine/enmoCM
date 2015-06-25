<?php

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
 * Implementing few generic features for controlers of type
 * "all-the-properties-of-the-object-are-the-columns-of-the-
 * database-table", i.e. BaseObject-kind.
 *
 * @author boulio
 *
 */
abstract class ObjectControler
{
    static protected $db;
    static protected $computed_properties = array();
    //"docserver_id","user_id","mr_owner_entity_id"
    static protected $foolish_ids = array();
    static protected $specific_id ;

    protected function set_foolish_ids($array)
    {
        if (isset($array) && is_array($array)){
            self::$foolish_ids = $array;
        }
    }

    protected function set_specific_id($id)
    {
        self::$specific_id = $id;
    }

    /**
     * Insert given object in given table.
     * Return inserted object if succeeded.
     * @param unknown_type $object
     * @return unknown_type
     */
    protected function advanced_insert($object)
    {
        $tableName = get_class($object);
        if (!isset($object) ) {
            return false;
        }

        // Inserting object
        $preparation = self::insert_prepare(
            $object, self::$computed_properties
        );
        $query = "insert into $tableName (" . $preparation['properties']
               . ") values(" . $preparation['values'] . ")";
        self::$db = new dbquery();
        self::$db->connect();
        try{
            if (_DEBUG) {
                echo "insert: " . functions::xssafe($query) . " // ";
            }
            self::$db->query($query);
            $result = true;
        } catch (Exception $e) {
            echo 'Impossible to insert object ' . functions::xssafe($object->toString()) . ' // ';
            $result = false;
        }
        self::$db->disconnect();
        return $result;
    }

    /**
     * Prepare two strings for insert query :
     * - 'properties' for properties field of insert query,
     * - 'values' for values field of insert query.
     * Needs list of values to _exclude_ of insert query (i.e.
     * usually values computed in the get() function of controler).
     * Result in an array.
     * @param Any $object
     * @param string[] $computed_properties
     * @return string[]
     */
    protected function insert_prepare($object, $computed_properties)
    {
        $result = array();
        $properties = array();
        $values = array();
        foreach ($object->getArray() as $key => $value) {
            if( !in_array($key,$computed_properties)) {
                // Adding property
                $properties[] = $key;
                // Adding property value
                if (substr_compare($key, '_id', -3) == 0
                    || substr_compare($key, '_number', -7) == 0) {
                    if (in_array($key, self::$foolish_ids)) {
                    /*
                     * UNBELIEVABLE! THERE ARE IDS WHICH ARE NOT LONG INTEGERS!
                     * A choice needs to be done, and if string is kept, random
                     * generating must be implemented.
                     */
                        $values[] = "'" . $value . "'";
                    } else {
                        // Number
                        if (empty($value)) {
                            // Default value
                            $value = 0;
                        }
                        $values[] = $value;
                    }
                } elseif(substr_compare($key, "is_", 0, 3) == 0
                    || substr_compare($key, "can_", 0, 4) == 0) {
                    // Boolean
                    if ($value === true) {
                        $values[] = "'Y'";
                    } elseif ($value === false) {
                        $values[] = "'N'";
                    } else {
                        $values[] = "'" . $value . "'";
                    }
                } else {
                    // Character or date
                    if ($value == 'CURRENT_TIMESTAMP' || $value == 'SYSDATE') {
                        $values[] = $value;
                    } else {
                        $values[] = "'" . $value . "'";
                    }
                }
            }
        }
        $result['properties'] = implode(",", $properties);
        $result['values'] = implode(",", $values);
        return $result;
    }

    /**
     * Update given object in given table, according
     * with given table id name.
     * Return updated object if succeeded.
     * @param unknown_type $object
     * @return unknown_type
     */
    protected function advanced_update($object)
    {
        if (!isset($object)){
            return false;
        }
        $tableName = get_class($object);
        $table_id = $tableName .'_id';

        if (isset(self::$specific_id) && !empty(self::$specific_id)){
            $table_id = self::$specific_id;
        }

        if (in_array($table_id, self::$foolish_ids)) {
            $query = "update $tableName set "
                   . self::update_prepare($object, self::$computed_properties)
                   . " where $table_id='".$object->$table_id."'";
        } else {
            $query = "update $tableName set "
                   . self::update_prepare($object, self::$computed_properties)
                   . " where $table_id=".$object->$table_id;
        }
        self::$db=new dbquery();
        self::$db->connect();
        try{
            if (_DEBUG) {
               echo "update: " . functions::xssafe($query) . " // ";
            }
            self::$db->query($query);
            $result = true;
        } catch (Exception $e) {
            echo 'Impossible to update object ' . functions::xssafe($object->toString()) . ' // ';
            $result = false;
        }
        self::$db->disconnect();
        return $result;
    }

    /**
     * Prepare string for update query
     * @param Any $object
     * @param string[] $computed_properties
     * @return String
     */
    private function update_prepare($object, $computed_properties)
    {
        $result = array();
        foreach ($object->getArray() as $key => $value) {
            if (!in_array($key,$computed_properties)) {
                if($key == self::$specific_id) {
                    // do not update key
                } elseif (substr_compare($key, '_id', -3) == 0
                    || substr_compare($key, '_number', -7) == 0) {
                    if (in_array($key, self::$foolish_ids)) {
                        $result[] = $key . "='" . $value . "'";
                    } else {
                        // Number
                        if (empty($value)) {
                            // Default value
                            $value = 0;
                        }
                        $result[] = $key . "=" . $value;
                    }
                } elseif (substr_compare($key, 'is_', 0, 3) == 0
                    || substr_compare($key, 'can_', 0, 4) == 0) {
                    // Boolean
                    if ($value === true) {
                        $result[] = $key . "='Y'" ;
                    } elseif ($value === false) {
                        $result[] = $key . "='N'";
                    } else {
                        $result[] = $key . "='" . $value . "'";
                    }
                } else {
                    // Character or date
                    $result[] = $key . "='" . $value . "'";
                }
            }
        }
        // Return created string minus last ", "
        return implode(",", $result);
    }

    /**
     * Get object of given class with given id from
     * good table and according with given class name.
     * Can return null if no corresponding object.
     * @param long $id Id of object to get
     * @param string $class_name
     * @return unknown_type
     */
    protected function advanced_get($id, $table_name, $whereComp='')
    {
        if (strlen($id) == 0) {
            return null;
        }
        $object_name = $table_name;
        $table_id = $table_name . '_id';

        if( isset(self::$specific_id) && !empty(self::$specific_id)) {
            $table_id = self::$specific_id;
        }
        self::$db = new dbquery();
        self::$db->connect();
        if (in_array($table_id, self::$foolish_ids)) {
             $select = "select * from $table_name where $table_id='$id' ".$whereComp;
        } else {
            $select = "select * from $table_name where $table_id=$id" .  $whereComp;
        }

        try {
            self::$db->query($select);
            if (self::$db->nb_result() == 0) {
                return null;
            } else {
                // Constructing result
                $object = new $object_name();
                $queryResult = self::$db->fetch_object();
                foreach ((array)$queryResult as $key => $value) {
                    if (_ADVANCED_DEBUG) {
                        echo "Getting property: " . functions::xssafe($key) 
                            . " with value: " . functions::xssafe($value) . " // ";
                    }
                    if ($value == 't') {          /* BUG FROM PGSQL DRIVER! */
                        $value = true;            /*                        */
                    } elseif ($value == 'f') {    /*                        */
                        $value = false;           /*                        */
                    }                            /**************************/
                    $object->$key = $value;
                }
            }
        } catch (Exception $e) {
            echo "Impossible to get object " . functions::xssafe($id) . " // ";
        }

        self::$db->disconnect();
        return $object;
    }

    /**
     * Get object of given class with given id from
     * good table and according with given class name.
     * Can return null if no corresponding object.
     * @param long $id Id of object to get
     * @param string $class_name
     * @return unknown_type
     */
    protected function advanced_getWithPDO($id, $table_name, $whereComp='', $params=array())
    {
        if (strlen($id) == 0) {
            return null;
        }

        $object_name = $table_name;
        $table_id = $table_name . '_id';

        if(isset(self::$specific_id) && !empty(self::$specific_id)) {
            $table_id = self::$specific_id;
        }

        require_once 'core/class/class_db_pdo.php';
        $database = new Database();
        $theQuery = "SELECT * FROM $table_name WHERE $table_id = :id " . $whereComp;
        $database->query($theQuery);
        $database->bind(':id', $id);

        if (count($params > 0)) {
            foreach ($params as $keyParam => $keyValue) {
                $database->bind(":" . $keyParam, $keyValue);
            }
        }
        $database->execute();
        
        if ($database->rowCount() == 0) {
            return null;
        } else {
            // Constructing result
            $object = new $object_name();
            $rows = $database->resultset();    
            
            for ($cpt=0;$cpt<count($rows);$cpt++) {
                foreach ($rows[$cpt] as $key => $value) {
                    if (_ADVANCED_DEBUG) {
                        echo "Getting property: $key with value: " . functions::xssafe($value) . " // ";
                    }
                    if ($value == 't') {          /* BUG FROM PGSQL DRIVER! */
                        $value = true;            /*                        */
                    } elseif ($value == 'f') {    /*                        */
                        $value = false;           /*                        */
                    }                            /**************************/
                    $object->$key = $value;
                }
            }
        }

        return $object;
    }

     /**
     * Delete given object from given table, according with
     * given table id name.
     * Return true if succeeded.
     * @param Any $object
     * @return boolean
     */
    protected function advanced_delete($object)
    {
        if (!isset($object)){
            return false;
        }
        $table_name = get_class($object);
        $table_id = $table_name . '_id';

        if (isset(self::$specific_id) && !empty(self::$specific_id)) {
            $table_id = self::$specific_id;
        }
        self::$db = new dbquery();
        self::$db->connect();

        if (isset(self::$foolish_ids)
            && in_array($table_id, self::$foolish_ids)) {
             $query = "delete from $table_name where $table_id='"
                    . $object->$table_id . "'";
        } else {
            $query = "delete from $table_name where $table_id="
                   . $object->$table_id;
        }

        try{
            if (_DEBUG) {
                echo "delete: " . functions::xssafe($query) . " // ";
            }
            self::$db->query($query);
            $result = true;
        } catch (Exception $e) {
            echo 'Impossible to delete object with id=' . functions::xssafe($object->$table_id)
                . ' // ';
            $result = false;
        }
        self::$db->disconnect();
        return $result;
    }

    /**
     * Enable given object from given table, according with
     * given table id name.
     * Return true if succeeded.
     * @param Any $object
     * @return boolean
     */
    protected function advanced_enable($object)
    {
        if (!isset($object)) {
            return false;
        }
        $table_name = get_class($object);
        $table_id = $table_name . '_id';

        if (isset(self::$specific_id) && !empty(self::$specific_id)) {
            $table_id = self::$specific_id;
        }
        self::$db = new dbquery();
        self::$db->connect();
        if (in_array($table_id, self::$foolish_ids) ){
             $query = "update $table_name set enabled = 'Y' where $table_id='"
                    . $object->$table_id . "'";
        } else {
            $query="update $table_name set enabled = 'Y' where $table_id=".$object->$table_id;
        }
        try{
            if(_DEBUG){
                echo "enable: " . functions::xssafe($query) . " // ";
            }
            self::$db->query($query);
            $result = true;
        } catch (Exception $e) {
            echo 'Impossible to enable object with id=' . functions::xssafe($object->$table_id)
                . ' // ';
            $result = false;
        }
        self::$db->disconnect();
        return $result;
    }

    /**
     * Reactivate given object from given table, according with
     * given table id name.
     * Return true if succeeded.
     * @param Any $object
     * @return boolean
     */
    protected function advanced_reactivate($object)
    {
        if (!isset($object)) {
            return false;
        }
        $table_name = get_class($object);
        $table_id = $table_name . '_id';

        if (isset(self::$specific_id) && !empty(self::$specific_id)) {
            $table_id = self::$specific_id;
        }
        self::$db = new dbquery();
        self::$db->connect();
        if (in_array($table_id, self::$foolish_ids) ){
             $query = "update $table_name set status = 'OK' where lower($table_id)=lower('"
                    . $object->$table_id . "')";
        } else {
            $query="update $table_name set status = 'OK' where lower($table_id)=lower(".$object->$table_id.")";
        }
        try{
            if(_DEBUG){
                echo "enable: " . functions::xssafe($query) . " // ";
            }
            self::$db->query($query);
            $result = true;
        } catch (Exception $e) {
            echo 'Impossible to enable object with id=' . functions::xssafe($object->$table_id)
                . ' // ';
            $result = false;
        }
        self::$db->disconnect();
        return $result;
    }

    /**
     * Disable given object from given table, according with
     * given table id name.
     * Return true if succeeded.
     * @param Any $object
     * @return boolean
     */
    protected function advanced_disable($object)
    {
        if (!isset($object)) {
            return false;
        }
        $table_name = get_class($object);
        $table_id=$table_name."_id";

        if (isset(self::$specific_id) && !empty(self::$specific_id)) {
            $table_id = self::$specific_id;
        }
        self::$db = new dbquery();
        self::$db->connect();
        if (in_array($table_id, self::$foolish_ids)) {
             $query = "update $table_name set enabled = 'N' where $table_id='"
                    . $object->$table_id . "'";
        } else {
            $query = "update $table_name set enabled = 'N' where $table_id="
                   . $object->$table_id;
        }
        try {
            if (_DEBUG) {
                echo "disable: " . functions::xssafe($query) . " // ";
            }
            self::$db->query($query);
            $result = true;
        } catch (Exception $e) {
            echo 'Impossible to disable object with id=' . functions::xssafe($object->$table_id)
                . ' // ';
            $result = false;
        }
        self::$db->disconnect();
        return $result;
    }
}
