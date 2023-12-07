<?php
/*--------------------------------------------------------------------------------------
    File: archiveFolder.php
  Author: Kevin Messina
 Created: Jan. 29, 2018
Modified: Feb. 16, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
--------------------------------------------------------------------------------------------------------------
NOTES:
------------------------------------------------------------------------------------------------------------*/

$version = "1.05";


/* INITIALIZE */
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');

require 'vendor/autoload.php';
clearstatcache();


/* INIT DEFAULTS */
$dashes = "----------------------------------------------";
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();

/* PRIMARY FUNCTIONS */
function archiveFolder() {
    global $timestamp;

    $timestamp = getServerDateTime();
    $archiveFilename = 'archive Orders ('.$timestamp.').zip';

    // Get real path for our folder
    $rootPath = realpath('Orders');

    // Initialize archive object
    $zip = new ZipArchive();
    $zip->open($archiveFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // Create recursive directory iterator
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $counter_Files = 0;
    $counter_Orders = 0;

    foreach ($files as $name => $file) {
        // Skip directories (they would be added automatically)
        if (!$file->isDir()) {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);

            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
            $counter_Files++;
        }else{
            $counter_Orders++;
        }
    }

    // Zip archive will be created only after closing object
    $zip->close();

    $msg = "Archive (.zip) success for ".$counter_Orders." orders with ".$counter_Files." files in archive: ".$archiveFilename;
    logSuccess($msg);
    echo $msg;
}

function getServerDateTime(){ return Date('Y-m-d H:i:s'); }
function logSuccess($msg){ writeLog("✅",$msg); }
function logFailure($msg){ writeLog("❌",$msg); }

function writeLog($status,$msg){
    global $scriptName,$timestamp;

    $timestamp = getServerDateTime();
    $calledfromApp = isset($_GET['calledFromApp']) ? $_GET['calledFromApp'] : 'n/a';
    $msg = "🗓".$timestamp." ".$status." (📜".$scriptName." 📝".$msg." 📱App: ".$calledfromApp.")".PHP_EOL;

    try {
        $fp = fopen('/Logs/SF-Admin_Log.txt', 'a');
        fwrite($fp, $msg);
        fclose($fp);
    } catch (Exception $e) {
        log_exception($e);
    }
}


/* RUN SCRIPT */
archiveFolder();


?>
