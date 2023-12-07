<?php
/*--------------------------------------------------------------------------------------
     File: funcs.php
   Author: Kevin Messina
  Created: Mar. 28, 2018
 Modified: Nov. 16, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/16 - Added new date Constants.
            - Added returnDateToFormat()
            - Added includeVendor()
            - Added convertArrayFromNullToEmptyArray
2018/11/04 - Added logExceptionError.
2018/10/13 - Added convertDateStringToSQLDate.
2018/10/10 - Added my_json_encode() typesafe return of empty $records.
2018/10/04 - Updated Server Paths.
2018/09/29 - Added centralized Server Paths.
2018/09/26 - Added SF Server information.
2018/09/14 - Added diferentiator for CAS and SF Servers.
2018-08-27 - Added logging Category.
2018-08-26 - Changed log table name.
2018-06-13 - Added Globals.
--------------------------------------------------------------------------------------*/

$sharedScriptVersion = "3.02a";

/* GLOBALS */
$dashes = "----------------------------------------------";
$timestamp = getServerDateTime();
$tab = "&nbsp;&nbsp;&nbsp;&nbsp;";
// DATE CONSTANTS
$dateFormat_Ymd_His_T = "Y-m-d H:i:s T"; // 2018-06-01 24:59:59 EST
$dateFormat_Ymd_His_e = "Y-m-d H:i:s e"; // 2018-06-01 24:59:59 America/New_York
$dateFormat_Ymd_at_His_A_T = "Y-m-d @ H:i:s A T"; // 2018-06-01 24:59:59 EST
$dateFormat_Ymd_at_his_A_T = "Y-m-d @ h:i:s A T"; // 2018-06-01 11:59:59 EST
$dateFormat_mdY_his_A_e = "m-d-Y h:i:s A e"; // 06-01-2018 12:59:59 AM/PM America/New_York
$dateFormat_YmdTHisZ = "Y-m-dTH:i:sZ"; // 2018-06-0124:59:59EST
$dateFormat_Ymd_at_His_A = "Y-m-d @ H:i:s A"; // 2018-06-01 @ 24:59:59 AM/PM // 24-hour
$dateFormat_Ymd_His = "Y-m-d H:i:s"; // 2018-06-01 24:59:59 // 24-hour
$dateFormat_Ymd_His = "Y-m-d h:i:s"; // 2018-06-01 12:59:59
$dateFormat_Ymd = "Y-m-d"; // 2018-06-01
$dateFormat_His_A = "H:i:s A"; // 24:59:59 AM/PM  // 24-hour
$dateFormat_his_A = "h:i:s A"; // 12:59:59 AM/PM
$dateFormat_D_DayNum_M_of_Y_his_A_e = "l jS \of F Y h:i:s A e";  // Monday 1st of June 2018 12:59:59 AM/PM America/New_York
$dateFormat_D_DayNum_M_of_Y_at_his_A_e = "l jS \of F Y @ h:i A e";  // Monday 1st of June 2018 @ 12:59 AM/PM America/New_York
$dateFormat_D_Month_DayNum_M_Y_at_his_A_e = "l F jS Y @ h:i A e";  // Monday June 1st, 2018 @ 12:59 AM/PM America/New_York
$dateFormat_Day_Month_comma_Y_at_hi_A = "l F jS Y @ h:i A"; // Monday June 1st, 2018 @ 12:59 AM/PM
$dateFormat_Day_Month_comma_Y = "l F jS Y"; // Monday June 1st, 2018
$dateFormat_Month_comma_Y_Day = "F jS Y (l)"; // June 1st, 2018 (Monday)
$dateFormat_Month_Day_comma_Year = "F j, Y"; // June 1, 2018
$dateFormat_Month_Day_comma_Year_at_his_A_e = "F j, Y @ h:i:s A e"; // June 1, 2018 @ 12:59:59 AM/PM America/New_York
$dateFormat_Month_Day_comma_Year_at_hi_A_e = "F j, Y @ h:i A e"; // June 1, 2018 @ 12:59 AM/PM America/New_York
$dateFormat_Month_Day_comma_Year_at_hi_A_T = "F j, Y @ h:i A T"; // June 1, 2018 @ 12:59 AM/PM America/New_York

/* DATE/TIME FUNCTIONS */
// http://php.net/manual/en/datetime.createfromformat.php
function getServerDateTime(){ global $dateFormat_Ymd_His_T; return Date($dateFormat_Ymd_His_T); }
function getServerDateTimeConverted(){ global $dateFormat_mdY_his_A_e; return Date($dateFormat_mdY_his_A_e); }
function getServerDateAndTimeAMPM(){ global $dateFormat_Ymd_at_his_A_T; return Date($dateFormat_Ymd_at_his_A_T); }
function getServerDateAndTime(){ global $dateFormat_Ymd_His; return  Date($dateFormat_Ymd_His); }
function getServerDate(){ global $dateFormat_Ymd; return Date($dateFormat_Ymd); }
function getServerTime(){ global $dateFormat_his_A; return Date($dateFormat_his_A); }
function getServerExtendedDate(){ global $dateFormat_D_DayNum_M_of_Y_his_A_e; return Date($dateFormat_D_DayNum_M_of_Y_his_A_e); }
function getTodaysDateConverted() { global $dateFormat_Month_Day_comma_Year; return Date(Month_Day_comma_Year); }
function getTodaysDateTimeConverted() { global $dateFormat_Month_Day_comma_Year_at_hi_A_e; return Date($dateFormat_Month_Day_comma_Year_at_hi_A_e); }

function getWeekStartDate() {
    $date = new DateTime("7 days ago");
    return $date->format("Y-m-d");
}

function getWeekStartDateConverted() {
    $date = new DateTime("7 days ago");
    return $date->format("F j, Y");
}

function get30DaysStartDate() {
    $date = new DateTime("30 days ago");
    return $date->format("Y-m-d");
}

function returnDateFromString($dateString) {
    global $version,$stack,$debug;

    if ($dateString == "" || $dateString == "n/a") { return ""; }
    else{
        $ymd = DateTime::createFromFormat("Y-m-d",$dateString)->format("F j, Y");

        if ($debug) {
            echo "<br><br>    Date String before conversion: $dateString<br>";
            echo "    Date String after conversion: $ymd<br>";
        };

        return $ymd;
    }
}

function returnDateToFormat($toDateFormat,$dateString) {
    global $version,$stack,$debug;
    global $dateFormat_D_Month_DayNum_M_Y_at_his_A_e,
            $dateFormat_Month_comma_Y_Day,
            $dateFormat_Day_Month_comma_Y,
            $dateFormat_Ymd_His_e,
            $dateFormat_mdY_his_A_e,
            $dateFormat_YmdTHisZ,
            $dateFormat_Ymd_at_His_A,
            $dateFormat_Ymd_His,
            $dateFormat_Ymd_His,
            $dateFormat_Ymd,
            $dateFormat_His_A,
            $dateFormat_his_A,
            $dateFormat_D_DayNum_M_of_Y_his_A_e,
            $dateFormat_D_DayNum_M_of_Y_at_his_A_e,
            $dateFormat_D_Month_DayNum_M_Y_at_his_A_e,
            $dateFormat_Day_Month_comma_Y_at_hi_A,
            $dateFormat_Month_Day_comma_Year,
            $dateFormat_Month_Day_comma_Year_at_his_A_e,
            $dateFormat_Month_Day_comma_Year_at_hi_A_e;

    if ($dateString == "" || $toDateFormat == "") { return ""; }
    else{
        $newDate = date($toDateFormat, strtotime($dateString));

        if ($debug) {
            echo "<br><br>    Date String before conversion: $dateString<br>";
            echo "    Date String after conversion: $newDate<br>";
        };

        return $newDate;
    }
}

function convertDateStringToSQLDate($orderDate) {
// Looks for 2 - or / in date string and assumes if there date is in the proper format,
// returns the same input string back to caller. The only other date format used to date
// is "Mmm dd, yyyy" and that is the format handled in this route. If future formats already
// added or to make more generic, additional work will have to be done. This was strictly
// meant to handle only 2 date types for backward compatibility with a limited number of
// company scripts and not as a generic date handler.
    global $version,$stack,$debug;

    $lookFor = "-";
    $lookForLength = strlen($lookFor);
    $lastPos = 0;
    $positions = array();

    if (strpos($orderDate, $lookFor, $lastPos) === false) {
        $lookFor = "/";
    }

    while (($lastPos = strpos($orderDate, $lookFor, $lastPos)) !== false) {
        $positions[] = $lastPos;
        $lastPos = $lastPos + $lookForLength;
    }

    if (count($positions) != 2) { // Convert to new format YYYY-MM-DD, bypass
        if ($debug) { echo "<br>$orderDate is in NOT in new format, needs conversion.<br>"; }

        /* Month */
        $length_Month = 3;
        $monthName = strtoupper(substr($orderDate,0,$length_Month));
        if ($monthName == "JAN") { $month = "01"; }
        else if ($monthName == "FEB") { $month = "02"; }
        else if ($monthName == "MAR") { $month = "03"; }
        else if ($monthName == "APR") { $month = "04"; }
        else if ($monthName == "MAY") { $month = "05"; }
        else if ($monthName == "JUN") { $month = "06"; }
        else if ($monthName == "JUL") { $month = "07"; }
        else if ($monthName == "AUG") { $month = "08"; }
        else if ($monthName == "SEP") { $month = "09"; }
        else if ($monthName == "OCT") { $month = "10"; }
        else if ($monthName == "NOV") { $month = "11"; }
        else { $month = "12"; }

        /* Year */
        $length_OrderDate = strlen($orderDate);
        $length_Year = 4;
        $year = substr($orderDate,($length_OrderDate - $length_Year), $length_Year);

        /* Day */
        $pos_comma = strpos($orderDate, ",", 0);
        $length_Day = ($pos_comma - ($length_Month + 1));
        $day = substr($orderDate,($length_Month + 1),$length_Day);
        if (strlen($day) < 2) { $day = "0$day"; }

        $orderDate = "$year-$month-$day";
        if ($debug) {
            echo "  year: $year<br>";
            echo "  day: $day<br>";
            echo "  monthName: $monthName<br>";
            echo "  month: $month<br>";
            echo "  Converted order date is: $orderDate.<br>";
        }
    }else{
        if ($debug) { echo "<br>$orderDate is in new format already, no conversion needed.<br>"; }
    }

    return $orderDate;
}

/* SERVER FUNCTIONS */
function getServerRoot() { return $_SERVER["DOCUMENT_ROOT"]; }
function getServerPath() { return getServerRoot()."/client-tools/squareframe/"; }
function getScriptsPath() { return getServerPath()."/scripts/"; }
function getOrdersPath() { return getServerPath()."Orders/"; }
function getOrderImagesPath() { return getHTTPPath()."client-tools/squareframe/Orders/"; }
function getLogPath() { return getServerPath()."/Logs/"; }
function getLogoPath() { return getHTTPPath()."client-tools/squareframe/Logos/"; }
function getPHPMailerPath() { return getServerRoot()."/mail23/class.phpmailer.php"; }
function getSendGridPath() { return getServerPath().""; }
function getVendorPath() { return getServerRoot()."/vendor/"; }

function getHTTPPath() {
    $serverName = getServerRoot();
    $serverName = trim(strtoupper($serverName));
    $isSF_Server = (bool)(stripos($serverName,"SQFRAM5") !== false);
    $isCAS_Server = (bool)(stripos($serverName,"CREATIW8") !== false);

    if ($isSF_Server) { return "http://sqframe.com/"; }
    elseif ($isCAS_Server) { return "http://creativeapps.us/"; }
    else { return "n/a"; }
}

function getHTTPSPath() {
    $serverName = getServerRoot();
    $serverName = trim(strtoupper($serverName));
    $isSF_Server = (bool)(stripos($serverName,"SQFRAM5") !== false);
    $isCAS_Server = (bool)(stripos($serverName,"CREATIW8") !== false);

    if ($isSF_Server) { return "https://sqframe.com/"; }
    elseif ($isCAS_Server) { return "https://creativeapps.us/"; }
    else { return "n/a"; }
}

function getServerName() {
    $serverName = getServerRoot();
    $serverName = trim(strtoupper($serverName));
    $isSF_Server = (bool)(stripos($serverName,"SQFRAM5") !== false);
    $isCAS_Server = (bool)(stripos($serverName,"CREATIW8") !== false);

    if ($isSF_Server) { return "Server: SF"; }
    elseif ($isCAS_Server) { return "Server: CAS"; }
    else { return "n/a"; }
}

function includeVendor() { // Andy Stein
  $folder_depth = substr_count(realpath(__FILE__), "/");
  $directory_level = 2; //If file exist in folder after root use 2
  if ($folder_depth == false) {
    $folder_depth = 1;
  }
  echo $folder_depth; die;
  $vendor_path = str_repeat("../", $folder_depth - $directory_level) . 'vendor/autoload.php';
  require_once($vendor_path);
}

function outputResultsToConsole($found,$success,$records) {
    global $version,$stack,$debug;

    $records = convertArrayFromNullToEmptyArray($records);

    $tab2 = "&nbsp;&nbsp";
    $tab4 = "&nbsp;&nbsp;&nbsp;&nbsp;";
    $successText = ($success) ?"Succeeded" :"Failed";
    $debugText = ($debug) ?"True" :"False";

    $detailStack = "*** STACK DETAILS ***<br>";
    foreach ($stack as $item) {
        $detailStack .= "$tab4$tab4 |--> $item";
    }

    $encodedRecords = json_encode($records);

    if ($debug) {
        $msg = "<li>version: $version</li>";
        $msg .= "<li>success: $successText</li>";
        $msg .= "<li>  found: $found</li>";
        $msg .= "<li>  stack: $detailStack</li>";
        $msg .= "<li>records: $encodedRecords</li>";

        echo (setBodyColor('black',"<br><br>"));
        echo (setTitleColor('red',"=== RETURNED VALUES FROM SCRIPT ===<br>"));
        echo (setBodyColor('black',$msg));
        echo (setTitleColor('red',"=== END RETURNED VALUES FROM SCRIPT ==="));
        echo (setBodyColor('black',"<br><br>"));
    }
}

function writeMsgToStackAndConsole($line,$title,$msg) {
    global $version,$stack,$debug;

    // Takes a list (array) from $msg, adds to stack array and replaces the
    // Swift String \n with HTML <br> for console output.
    array_push($stack,addLogEntry($line,$title,true,$msg));
    if ($debug) {
        echo (setBodyColor('black',"<br>"));
        if ($title != "") {
            echo (setTitleColor('tomato',$title));
        }
        echo (setBodyColor('black',str_replace("\n","<br>",$msg)));
        echo (setBodyColor('black',"<br>"));
    }
}

function writeToStackAndConsole($line,$title,$msg) {
    global $version,$stack,$debug;

    // Takes a string from $msg, adds to stack array and outputs to console.
    array_push($stack,addLogEntry($line,$title,true,$msg));

    if ($debug) {
        echo (setBodyColor('black',"<br>"));
        if ($title != "") {
            echo (setTitleColor('mediumvioletred',$title));
        }
        echo (setBodyColor('black',$msg));
        echo (setBodyColor('black',"<br>"));
    }
}

function debugPrint($msg) {
    global $debug;

    if ($debug) {
        echo (setBodyColor('black',"$msg<br>"));
    }
}

function setTitleColor($color,$title) {
    echo '<div style="font-size:1.25em;color:white;background-color:'.$color.'">'.$title.'</div>';
}

function setBodyColor($color,$body) {
    echo '<div style="font-size:1.25em;background-color:white;color:'.$color.'">'.$body.'</div>';
}

function endScriptMsg($lineNum,$scriptName,$calledfromApp,$category,$success){
    global $version,$stack,$debug;

    $title = "*** Script End ***";
    $msg = "-->".getServerName().", Script: $scriptName, Finished: ".getServerDateAndTimeAMPM();
    array_push($stack,addLogEntry($lineNum,$title,true,$msg));
    if ($debug) {
        echo (setBodyColor('black',"<br>"));
        echo (setTitleColor('rebeccapurple',"$title $msg"));
        echo (setBodyColor('black',"<br>"));
    }
    array_push($stack,saveLog($scriptName,$calledfromApp,$category,$stack,$success,0));
}

function startScriptMsg($lineNum,$scriptName,$calledfromApp,$category) {
    global $version,$stack,$debug;

    $title = "*** Script Start ***";
    $msg = "-->".getServerName().", Script: $scriptName, Started: ".getServerDateAndTimeAMPM();
    $stack = array(addLogEntry($lineNum,$title,true,$msg));
    if ($debug) {
        echo(setTitleColor('rebeccapurple',"$title $msg<br>"));
    }
    array_push($stack,saveLog($scriptName,$calledfromApp,$category,$stack,true,0));

    return $stack;
}

function returnNotNullArray($array) {
    if ($array == null) { $array = array(); }

    return $array;
}

/* DEBUGGER FUNCTIONS */
function returnTextFromBool($boolVal){
    return ($boolVal) ?"True" :"False";
}

function printToScreen($key,$val,$skipLine){
    echo "<br>";
    print_r("→→ ".$key.": ".$val);
    if ($skipLine == true) {
        echo "<br>";
    }
}

function printLineToScreen(){
    echo "<br>";
    print_r("-----------------------------------------------------------");
    echo "<br>";
}

function printSuccessToScreen($succeeded) {
    echo "<br>";
    printToScreen("success",($succeeded == true) ?"yes" :"no",false);
    echo "<br>";
}

function logSuccess($msg){ writeLog("✅",$msg); }
function logFailure($msg){ writeLog("❌",$msg); }
function writeLog($status,$msg){
    global $scriptName,$timestamp,$version;

    $timestamp = getServerDateTime();
    $calledfromApp = isset($_GET["calledFromApp"]) ? $_GET["calledFromApp"] : "n/a";
    $msg = "🗓".$timestamp." ".$status." (📜".$scriptName.", v".$version." 📝".$msg." 📱App: ".$calledfromApp.")<br>";
    $logFileName = (strpos($calledfromApp, "(R&D)") !== false) ?"SF-Admin_Log_TEST.txt" :"SF-Admin_Log.txt";
    $logFilePath = getLogPath().$logFileName;

    try {
        $fp = fopen($logFilePath, "a");
        fwrite($fp, $msg);
        fclose($fp);
    } catch (Exception $e) {
        log_exception($e);
    }
}

function appendLogFile($logFileName,$msg) {
    $logFilePath = getLogPath().$logFileName;

    try {
        $fileConnection = fopen($logFilePath, "a");
        fwrite($fileConnection, $msg);
        fclose($fileConnection);
    } catch (Exception $e) {
        log_exception($e);
    }
}

/* LOGGING FUNCTIONS */
function logExceptionError($error) {
    global $version,$stack,$debug;

    $msg = "Exception error occured: ".$error;
    logFailure($msg);
    if ($debug) { echo "<br>$msg"; }
}

function addLogEntry($line,$func,$succeeded,$details) {
    $succeededString = ($succeeded) ?"YES" :"NO";

    $details = str_replace("'", "''", $details);
    $txt = trim("Line #$line: Function: $func| Succeeded: $succeededString| Details: $details<br>");

    return $txt;
}

function saveLog($scriptName,$calledfromApp,$category,$success,$orderNum) {
    global $version,$stack,$debug;

    $successSaveToLog = false;

    $dateAndTime = getServerDateAndTime();
    $date = getServerDate();
    $time = getServerTime();
    $success = (int)(isset($success) ?1 :0);
    $orderNum = (int)(isset($orderNum) ?(int)$orderNum :0);
    $scriptName = ((bool)(isset($scriptName) && !empty($scriptName)) ?$scriptName :"n/a");
    $calledfromApp = ((bool)(isset($calledfromApp) && !empty($calledfromApp)) ?$calledfromApp :"n/a");
    $category = ((bool)(isset($category) && !empty($category)) ?$category :"n/a");
    $stack = ((bool)(isset($stack) && !empty($stack)) ?$stack :["n/a"]);
    $stackText = "";
    foreach ($stack as $text) {
        $text = str_replace("'", "''", $text);
        $stackText .= trim($text)."<br><br>";
    }

    try {
        /* INIT DB */
        $connection = connectToCMS();

        /* PERFORM QUERY: Update log */
        $query = "INSERT INTO phplog VALUES (NULL,$orderNum,'$dateAndTime','$date','$time',$success,'$category','$scriptName','$calledfromApp','$stackText')";
        $results = mysqli_query($connection, $query);
        $successSaveToLog = (bool)($results == "1");
        $successSaveToLogText = $successSaveToLog ?"YES" :"NO";
        logSuccess("LOG SAVE: $successSaveToLogText");
    }catch(Exception $e){
        logFailure("LOG SAVE Exception error occured: ".$e->getMessage());
    }

    $succeededString = ($successSaveToLog) ?"YES" :"NO";
    $detailTxt = ($successSaveToLog) ?"Log save Succeeded." :"Log save Failed.";
    $txt = (string)'Line: '.__LINE__.", Function: LOG SAVE Succeeded: $succeededString, Details: $detailTxt.<br>";

    return $txt;
}
