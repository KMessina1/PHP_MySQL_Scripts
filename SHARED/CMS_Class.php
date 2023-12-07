<?php
/*--------------------------------------------------------------------------------------
    File: CMS_Class.php
  Author: Kevin Messina
 Created: Nov. 23, 2018
Modified:

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

--------------------------------------------------------------------------------------*/

$CMS_ClassVersion = "1.02a";

/* CMS Class */
class CMS{
    public $records;
    public $tableName;
    public $connection;
    public $lastInsertedID;
    public $numRecords;
    public $hasRecords;
    public $lastActionSuccess;

    /* Setters */
    public function setTableName($tableName){ $this->tableName = $tableName; }

    // init()
    public function connectToAndinitDB(){
        self::resetParams();
        self::connectToDB();
    }

    // reset()
    public function resetParams(){
        $this->records = [];
        $this->lastInsertedID = -1;
        $this->numRecords = 0;
        $this->hasRecords = false;
        $this->lastActionSuccess = false;
    }

    // fetchRecordID() returns $success = found > 0
    public function firstRecord(){
        return $this->records[0];
    }

    // fetchRecordID() returns $success = found > 0
    public function fetchRecordID($id){
        $query = "SELECT * FROM $this->tableName WHERE id=$id LIMIT 1;";
        writeToStackAndConsole(__LINE__,__CLASS__.": ".__FUNCTION__."()",$query);
        $result = mysqli_query($this->connection, $query);
        self::resetParams();
        while ($r = mysqli_fetch_array($result)) {
            $this->records[] = $r;
        }

        $success = is_array($this->records)
            ?(count($this->records) > 0)
            :false;

        $this->hasRecords = $success;
        $this->lastActionSuccess = $success;
        self::outputResultSuccess($success,"FETCHED record by ID: $id.");

        return $success;
    }

    // fetchRecordsWhere() returns $records
    public function fetchRecordsWhere($where = "",$orderBy = ""){
        $query = "SELECT * FROM $this->tableName";

        if ($where != "") {
            $query .= " WHERE $where";
        }

        if ($orderBy != "") {
            $query .= " ORDER BY $orderBy";
        }

        $query .= ";";

        writeToStackAndConsole(__LINE__,__CLASS__.": ".__FUNCTION__."()",$query);
        $queryResults = mysqli_query($this->connection, $query);
        self::resetAndReturnInfoForQueryResults($queryResults);

        return $this->records;
    }

    function resetAndReturnInfoForQueryResults($queryResults){
        self::resetParams();

        while ($r = mysqli_fetch_array($queryResults)) {
            $this->records[] = $r;
        }

        if (is_array($this->records)) {
            $this->numRecords = count($this->records);
        }
        $success = ($this->numRecords > 0);
        $this->hasRecords = $success;
        $this->lastActionSuccess = $success;
        self::outputResultSuccess($success,"FETCHED $this->numRecords records.");
    }

    function outputResultSuccess($success,$msg) {
        global $debug;

        if ($debug) {
            echo ($success ?"✅ CMS: Succesfully " :"❌ Failed to ").$msg;
        }
    }

    // recordExistsWhere() return $succss = found > 0
    public function recordExistsWhere($where){
        $query = "SELECT * FROM $this->tableName WHERE $where LIMIT 1;";
        writeToStackAndConsole(__LINE__,__CLASS__.": ".__FUNCTION__."()",$query);
        $result = mysqli_query($this->connection, $query);
        self::resetParams();
        while ($r = mysqli_fetch_array($result)) {
            $this->records[] = $r;
        }
        $this->hasRecords = self::hasRecords();
        $this->lastActionSuccess = $this->hasRecords;

        return $this->lastActionSuccess;
    }

// update() returns $success
    public function update($set,$where){
        global $dbAction_Update;

        $query = "UPDATE $this->tableName SET $set WHERE $where;";
        writeToStackAndConsole(__LINE__,"CMS: ",$query);
        $result = mysqli_query($this->connection, $query);

        self::resetParams();
        $this->lastActionSuccess = (bool)($result == "1");;
        $title = "($this->tableName --> $dbAction_Update) record.";
        $msg = ($this->lastActionSuccess) ?"Succeeded." :"Failed.";
        ($this->lastActionSuccess) ?logSuccess($msg) :logFailure($msg);
        writeToStackAndConsole(__LINE__,$title,$msg);

        return $this->lastActionSuccess;
    }

    // updateID() returns $success
    public function updateID($id,$set,$where = ""){
        global $dbAction_Update;

        if ($where == "") {
            $where = "id=$id";
        }

        $query = "UPDATE $this->tableName SET $set WHERE $where;";
        writeToStackAndConsole(__LINE__,"CMS: ",$query);
        $result = mysqli_query($this->connection, $query);

        self::resetParams();
        $this->lastActionSuccess = (bool)($result == "1");;
        $title = "($this->tableName --> $dbAction_Update) record.";
        $msg = ($this->lastActionSuccess) ?"Succeeded." :"Failed.";
        ($this->lastActionSuccess) ?logSuccess($msg) :logFailure($msg);
        writeToStackAndConsole(__LINE__,$title,$msg);

        return $this->lastActionSuccess;
    }

// delete()
    public function delete($where){
        global $dbAction_Delete;

        $query = "DELETE FROM $this->tableName WHERE $where;";
        writeToStackAndConsole(__LINE__,"CMS:",$query);
        $result = mysqli_query($this->connection, $query);
        self::resetParams();

        $this->lastActionSuccess = (bool)($result == "1");;

        $title = "($this->tableName --> $dbAction_Delete) record.";
        $msg = ($this->lastActionSuccess) ?"Succeeded." :"Failed.";
        ($this->lastActionSuccess) ?logSuccess($msg) :logFailure($msg);
        writeToStackAndConsole(__LINE__,$title,$msg);

        return $this->lastActionSuccess;
    }

// deleteID()
    public function deleteID($id){
        global $dbAction_Delete;

        $query = "DELETE FROM $this->tableName WHERE id=$id;";
        writeToStackAndConsole(__LINE__,"CMS:",$query);
        $result = mysqli_query($this->connection, $query);
        self::resetParams();

        $this->lastActionSuccess = (bool)($result == "1");;

        $title = "($this->tableName --> $dbAction_Delete) record.";
        $msg = ($this->lastActionSuccess) ?"Succeeded." :"Failed.";
        ($this->lastActionSuccess) ?logSuccess($msg) :logFailure($msg);
        writeToStackAndConsole(__LINE__,$title,$msg);

        return $this->lastActionSuccess;
    }

// insert() returns $success
    public function insert($values){
        global $dbAction_Insert;

        $query = "INSERT INTO $this->tableName VALUES (NULL,$values);";
        writeToStackAndConsole(__LINE__,"CMS:",$query);
        $result = mysqli_query($this->connection, $query);

        self::resetParams();
        $this->lastActionSuccess = (bool)($result == "1");;

        if ($this->lastActionSuccess) {
            $this->lastInsertedID = $this->connection->insert_id;
        }

        $title = "($this->tableName --> $dbAction_Insert) record.";
        $msg = ($this->lastActionSuccess) ?"Succeeded; ID: $this->lastInsertedID." :"Failed.";
        ($this->lastActionSuccess) ?logSuccess($msg) :logFailure($msg);
        writeToStackAndConsole(__LINE__,$title,$msg);

        return $this->lastActionSuccess;
    }

// connectToDB()
    public function connectToDB(){
        $this->connection = mysqli_connect("localhost", "sqfram5_admin", "sqframe_admin!!", "sqfram5_CMS");

        mysqli_query($this->connection,"SET CHARACTER_SET_CLIENT='utf8mb4'");
        mysqli_query($this->connection,"SET CHARACTER_SET_RESULTS='utf8mb4'");
        mysqli_query($this->connection,"SET CHARACTER_SET_CONNECTION='utf8mb4'");

        writeToStackAndConsole(__LINE__,__CLASS__.": ".__FUNCTION__."()","Connection to CMS...");
    }

// numRecords()
    public function numRecords(){
        $this->numRecords = is_array($this->records) ?count($this->records) :0;
        debugPrint(($this->numRecords > 0 ?"✅" :"❌")." Found: $this->numRecords record(s).");

        return $this->numRecords;
    }

// hasRecords()
    public function hasRecords(){
        $this->hasRecords = (self::numRecords() > 0);
        debugPrint(($this->hasRecords ?"✅" :"❌")." Query returned/has ".($this->hasRecords ? $this->numRecords." " :"NO ")."record(s).");

        return $this->hasRecords;
    }
}
