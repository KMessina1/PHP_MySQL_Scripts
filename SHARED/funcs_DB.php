<?php
/*--------------------------------------------------------------------------------------
    File: funcs_DB.php
  Author: Kevin Messina
 Created: Nov. 19, 2018
Modified:

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

--------------------------------------------------------------------------------------*/

$bbScriptVersion = "1.03a";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GLOBALS */
// DATABASE CONSTANTS
$dbAction_Delete = "Delete";
$dbAction_Insert = "Insert";
$dbAction_Update = "Update";
$dbAction_Search = "Search";
$dbAction_Fetch = "Fetch";
// TABLENAME CONSTANTS
$table_customers = "customers";
$table_accounts = "accounts";
$table_addresses = "addresses";
$table_carts = "carts";
$table_coupons = "coupons";
$table_customers = "customers";
$table_emailer = "emailer";
$table_faq = "faq";
$table_frameColors = "frameColors";
$table_frameMaterial = "frameMaterial";
$table_frameShapes = "frameShapes";
$table_frameSize = "frameSize";
$table_frameStyle = "frameStyle";
$table_fulfillment = "fulfillment";
$table_issues = "issues";
$table_items = "items";
$table_knowledgebase = "knowledgebase";
$table_matteColors = "matteColors";
$table_orders = "orders";
$table_orderStatus = "orderStatus";
$table_photoSize = "photoSize";
$table_phplog = "phplog";
$table_products = "products";
$table_redemptions = "redemptions";
$table_resources = "resources";
$table_servicesAvailable = "servicesAvailable";
$table_sfAppImages = "sfAppImages";
$table_sfFrameGallery = "sfFrameGallery";
$table_shippers = "shippers";
$table_shippingPriority = "shippingPriority";
$table_skuVersions = "skuVersions";
$table_taxableStates = "taxableStates";
$table_teams = "teams";

/* DATABASE FUNCTIONS */
function connectToCMS() {
    $serverName = getServerRoot();
    $serverName = trim(strtoupper($serverName));
    $isSF_Server = (bool)(stripos($serverName,"SQFRAM5") !== false);
    $isCAS_Server = (bool)(stripos($serverName,"CREATIW8") !== false);

    // if ($isCAS_Server) { echo "CAS server: $serverName<br>"; }
    // elseif ($isSF_Server) { echo "Squareframe Server: $serverName<br>"; }
    // else { echo "Current server: UNKNOWN = $serverName<br>"; }

    if ($isSF_Server) { $connection = mysqli_connect("localhost", "sqfram5_admin", "sqframe_admin!!", "sqfram5_CMS"); }
    elseif ($isCAS_Server) { $connection = mysqli_connect("localhost", "creatiw8_admin", "casNY**2!!", "creatiw8_cms"); }
    else { $connection = nil; }

    /* Force character encoding of database php interaction. Has been set globally in php.ini also */
    mysqli_query($connection,"SET CHARACTER_SET_CLIENT='utf8mb4'");
    mysqli_query($connection,"SET CHARACTER_SET_RESULTS='utf8mb4'");
    mysqli_query($connection,"SET CHARACTER_SET_CONNECTION='utf8mb4'");

    return $connection;
}

function outputRecordsToConsole($records) {
    global $version,$stack,$debug;

    $msg = "";

    if ($records != NULL) {
        foreach($records as $r) {
            $id = $r["id"];
            $msg .= "<br>------------ Start of Record ID: $id ------------<br>";
            foreach($r as $key => $val) {
                if (is_numeric($key)) {
                    $msg .= "<li> id: $key,";
                }else{
                    $msg .= " $key = $val</li>";
                }
            }
            $msg .= "<br>------------ End of Record ID: $id ------------<br><br>";
        }
    }

    if ($debug) {
        echo (setBodyColor('black','<br><br>'));
        echo (setTitleColor('red',"=== RECORDS DETAIL DUMP START (key => val) ===<br>"));
        echo (setBodyColor('black',$msg));
        echo (setTitleColor('red',"=== RECORDS DETAIL DUMP END ==="));
        echo (setBodyColor('black','<br><br>'));
    }
}

function convertArrayFromNullToEmptyArray($records){
    global $version,$stack,$debug;

    if ($records == null) {
        $records = array();
        if ($debug) {
            echo (setBodyColor('black',"<br>"));
            echo(setTitleColor('red',"!!!!!!! ARRAY = NULL, REPLACING WITH EMPTY ARRAY(), RECORDS COUNT IS 0 !!!"));
            echo (setBodyColor('black',"<br>"));
        }
    }else if (($records != null) && empty($records) == false) {
        if ((int)count((array)$records) < 1) {
            if ($debug) {
                echo (setBodyColor('black',"<br>"));
                echo(setTitleColor('red',"!!! ARRAY COUNT IS 0 !!!"));
                echo (setBodyColor('black',"<br>"));
            }
        }
    }

    return $records;
}

/// Returns array [String:Any].
function returnStandardOutputRecord($found,$success,$records) {
    global $version,$stack,$debug;

    $records = convertArrayFromNullToEmptyArray($records);
    $standardArray = [
        "version" => $version,
        "stack" => $stack,
        "found" => $found,
        "success" => $success,
        "debug" => $debug,
        "records" => $records
    ];

    return $standardArray;
}

// Returns bool of success.
function updateDB($tableName,$set,$id) {
    global $version,$stack,$debug;
    global $dbAction_Update;

    $title = "($tableName --> $dbAction_Update) record.";
    $query = "UPDATE $tableName SET $set WHERE id=$id;";

    /* PERFORM QUERY */
    $result = mysqli_query(connectToCMS(), $query);

    /* PROCESS RESULTS */
    $success = (bool)($result == "1");

    // Post Query
    array_push($stack,addLogEntry(__LINE__,$title,$success,"Query: $query"));
    if ($debug) { echo("<br>Query: $query"); }

    // Post Results
    $msg = "($tableName --> $dbAction_Update) ";
    $msg .= ($success) ?"Succeeded." :"Failed.";

    array_push($stack,addLogEntry(__LINE__,$title,$success,$msg));
    if ($debug) { echo "<br>$msg"; }
    ($success) ?logSuccess($msg) :logFailure($msg);

    return $success;
}

function my_json_encode($found,$success,$records) {
    global $version,$stack,$debug;

    $encodedArray = json_encode(returnStandardOutputRecord($found,$success,$records));

    if ($debug) {
        $records = convertArrayFromNullToEmptyArray($records);
        outputRecordsToConsole($records);

        echo (setBodyColor('black',"<br>"));
        echo (setTitleColor('rebeccapurple',"** Script finished all functions, next step is to return results. If no results follow, error has happened. **"));
        echo (setBodyColor('black',"<br>"));
        outputResultsToConsole($found,$success,$records);

        echo (setBodyColor('black',"<br><br>"));
        echo (setTitleColor('red',"=== OUTPUT RETURNED BY SCRIPT ==="));
        echo (setBodyColor('black',$encodedArray));
        echo (setTitleColor('red',"=== END OUTPUT RETURNED BY SCRIPT ==="));
        echo (setBodyColor('black',"<br>"));
    }

    return $encodedArray;
}

function my_json_encodeCustom($found,$success,$records,$itemsToEncode) {
    global $version,$stack,$debug;

    $std_itemsToEncode = returnStandardOutputRecord($found,$success,$records);
    $encodedArray = [];

    try {
        $newItemsToEncode = $itemsToEncode + $std_itemsToEncode;
        $encodedArray = json_encode($newItemsToEncode);
    } catch (Exception $e) {
        logExceptionError($e->getMessage());
    }

    if ($debug) {
        outputRecordsToConsole($records);

        echo (setBodyColor('black',"<br>"));
        echo(setTitleColor('rebeccapurple',"** Script finished all functions, next step is to return results. If no results follow, error has happened. **"));
        echo (setBodyColor('black',"<br>"));
        outputResultsToConsole($found,$success,$records);

        echo (setBodyColor('black',"<br>"));
        echo (setTitleColor('red',"=== OUTPUT RETURNED BY SCRIPT ===<br>"));
        echo (setBodyColor('black',$encodedArray));
        echo (setTitleColor('red',"=== END OUTPUT RETURNED BY SCRIPT ==="));
        echo (setBodyColor('black',"<br>"));
    }

    return $encodedArray;
}

function executeDB($query,$tableName,$action = "(CRUD)") {
    global $version,$stack,$debug;
    global $dbAction_Delete,$dbAction_Insert,$dbAction_Update,$dbAction_Search,$dbAction_Fetch;

    $title = "($tableName --> $action) record.";

    /* INIT DB */
    $connection = connectToCMS();

    /* PERFORM QUERY */
    $result = mysqli_query($connection, $query);

    /* PROCESS RESULTS */
    $success = (bool)($result == "1");

    // Post Query
    array_push($stack,addLogEntry(__LINE__,$title,$success,"Query: $query"));
    if ($debug) { echo("<br>Query: $query"); }

    // Post Results
    $msg = "($tableName --> $action) ";
    if ($action == $dbAction_Insert) {
        $newID= $connection->insert_id;
        $title .= " with id = $newID.";
        $msg .= "id:$newID ";
    }
    $msg .= ($success) ?"Succeeded." :"Failed.";

    array_push($stack,addLogEntry(__LINE__,$title,$success,$msg));
    if ($debug) { echo "<br>$msg"; }
    ($success) ?logSuccess($msg) :logFailure($msg);

    return $success;
}

function deleteIDfromDB($tableName,$id){
    global $dbAction_Delete;

    return executeDB("DELETE FROM $tableName WHERE id=$id;",$tableName,$dbAction_Delete);
}

function insertToDB($tableName,$values) {
    global $version,$stack,$debug;
    global $dbAction_Insert;

    $query = "INSERT INTO $tableName VALUES (NULL,$values);";
    $result = mysqli_query(connectToCMS(), $query);
    $success = (bool)($result == "1");

    $title = "($tableName --> $dbAction_Insert) record.";
    $msg = ($success) ?"Succeeded." :"Failed.";
    ($success) ?logSuccess($msg) :logFailure($msg);
    writeToStackAndConsole(__LINE__,$title,$query);
    writeToStackAndConsole(__LINE__,$title,$msg);

    return $success;
}

function insertToDB_returnID($tableName,$values) {
    global $version,$stack,$debug;
    global $dbAction_Insert;

    $query = "INSERT INTO $tableName VALUES (NULL,$values);";
    $result = mysqli_query(connectToCMS(), $query);
    $success = (bool)($result == "1");
    $insertID = ($success) ?$connection->insert_id :-1;
    ($success) ?logSuccess($msg) :logFailure($msg);

    $title = "($tableName --> $dbAction_Insert) record.";
    writeToStackAndConsole(__LINE__,$title,$query);
    writeToStackAndConsole(__LINE__,$title,($success) ?"Succeeded, new id:$insertID." :"Failed.");

    return $insertID;
}

function insertDBreturnID($query,$tableName) {
    global $version,$stack,$debug;
    global $dbAction_Insert;

    $action = $dbAction_Insert;
    $title = "($tableName --> $action) record.";
    $insertID = -1;

    /* INIT DB */
    $connection = connectToCMS();

    /* PERFORM QUERY */
    $result = mysqli_query($connection, $query);

    /* PROCESS RESULTS */
    $success = (bool)($result == "1");

    // Post Query
    array_push($stack,addLogEntry(__LINE__,$title,$success,"Query: $query"));
    if ($debug) { echo("<br>Query: $query"); }

    // Post Results
    $insertID= $connection->insert_id;
    $title .= " with id = $insertID.";
    $msg = "($tableName --> $action) ";
    $msg .= "id: $insertID ";
    $msg .= ($success) ?"Succeeded." :"Failed.";

    array_push($stack,addLogEntry(__LINE__,$title,$success,$msg));
    if ($debug) { echo "<br>$msg"; }
    ($success) ?logSuccess($msg) :logFailure($msg);

    return $insertID;
}

// Returns a single array
function fetchByIDFromDB($tableName,$id){
    $record = fetchFromDB("SELECT * FROM $tableName WHERE id=$id LIMIT 1;",$tableName);
    return $record;
}

function fetchAllByIDFromDB($tableName,$id){
    $records = fetchFromDB("SELECT * FROM $tableName WHERE id=$id;",$tableName);
    return $records;
}

function fetchAllFromDB($tableName,$where = "",$orderBy = "") {
    $query = "SELECT * FROM $tableName";

    if ($where != "") {
        $query .= " WHERE $where";
    }

    if ($orderBy != "") {
        $query .= " ORDER BY $orderBy";
    }

    $query .= ";";

    $records = fetchFromDB($query,$tableName);

    return $records;
}

function fetchOneFromDB($tableName,$where = "",$orderBy = "") {
    $query = "SELECT * FROM $tableName";

    if ($where != "") {
        $query .= " WHERE $where";
    }

    if ($orderBy != "") {
        $query .= " ORDER BY $orderBy";
    }

    $query .= " LIMIT 1;";

    $records = fetchFromDB($query,$tableName);

    return $records;
}

function fetchFromDB($query,$tableName,$where = "",$orderBy = "") {
    global $version,$stack,$debug;

    $title = "Fetch $tableName entries";
    $records = null;

    if ($where != "") {
        $query .= " WHERE $where";
    }

    if ($orderBy != "") {
        $query .= " ORDER BY $orderBy";
    }

    /* INIT DB */
    $connection = connectToCMS();

    /* PERFORM QUERY */
    $result = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($result)) {
        $records[] = $r;
    }

    /* PROCESS RESULTS */
    $found = ($records != null) ?(int)count($records) :0;
    $success = (bool)($found > 0);

    // Post Query
    array_push($stack,addLogEntry(__LINE__,$title,$success,"Query: $query"));
    if ($debug) { echo "<br>Query: $query"; }

    // Post Found
    $msg = "Found: $found record";
    $msg .= ($found > 1) ?"s." :".";
    array_push($stack,addLogEntry(__LINE__,$title,$success,$msg));
    if ($debug) { echo "<br>$msg<br>"; }

    // Post Success to log
    ($success) ?logSuccess($msg) :logFailure($msg);

    return $records;
}

function returnSuccessFromArrayCount($records) {
    return returnSuccessFromCount(returnCountFrom($records));
}

function returnSuccessFromCount($found) {
    return ($found > 0);
}

function returnCountFrom($records) {
    return ($records != null) ?(int)count($records) :0;
}
