<?php
/*--------------------------------------------------------------------------------------
    File: archiveFolder.php
  Author: Sascha Wise
 Created: Jan. 29, 2018
 Modified: Jun. 24, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
 ------------------------------------------------------------------------------------------
 NOTES:

 2018_06_22 - Updated to latest settings.
 2018_06_24 - Increased timeout in php.ini.
 ----------------------------------------------------------------------------------------*/

 /* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/archive/archiveFolder.php
 */

$version = "1.05l";

/* INITIALIZE: Script */
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
clearstatcache();

/* FUNCTIONS */
function getServerDateTimeConverted() { return Date('F j, Y @ h:i:s A e'); } // June 1, 2018 @ 12:59:59 AM/PM EST
function getServerDateTime(){ return Date('Y-m-d H:i:s'); } // 2018-06-01 24:59:59

/* INIT DEFAULTS */
$timestamp = getServerDateTime();
$archiveFilename = 'archive-Orders-'.$timestamp.'.zip';
$timestamp = getServerDateTimeConverted();
$rootPath = realpath('../Orders'); // Get real path for our folder
echo '*** STARTED '.$scriptName.' v'.$version.' @ '.$timestamp.' ***';

/* INITIALIZE: Libraries */
require 'vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

/* Initialize archive object */
$timestamp = getServerDateTimeConverted();
echo '<br>Starting .Zip Compression of files @'.$timestamp;
$zip = new ZipArchive();
$zip->open('backups/' . $archiveFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

$client = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'nyc3',
    'endpoint' => 'https://nyc3.digitaloceanspaces.com',
    'credentials' => new Aws\Credentials\Credentials('736PLVJ4TGVOXBLSUY45', 'hE3pU0Oqf/X7Q+PNoT+PiGJVsFHgN2NztsQlD+t6a4I'),
]);

/* Create recursive directory iterator */
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$counter_Files = 0;
$counter_Orders = 0;
$timestamp = getServerDateTimeConverted();
echo '<br>Building Files/Folders to .zip Process @'.$timestamp;
foreach ($files as $name => $file) {
    // Skip directories (they would be added automatically)
    if (!$file->isDir()) {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
        $counter_Files++;
        // echo '→ '.$counter_Files.') File: '.$filePath.PHP_EOL;
    }else{
        $counter_Orders++;
    }
}

/* Zip archive will be created only after closing object */
$timestamp = getServerDateTimeConverted();
echo '<br>Finalizing .zip Process @'.$timestamp;
$zip->close();

$timestamp = getServerDateTimeConverted();
echo '<br>Archive (.zip) success @'.$timestamp.' for:<br> ∙ Total Orders: '.$counter_Orders.'<br> ∙ Total Files: '.$counter_Files.'<br> ∙ Archive: '.$archiveFilename;

$timestamp = getServerDateTimeConverted();
echo '<br>Starting copy of '.$archiveFilename.' to SF DO Backup Server @'.$timestamp;
try {
    $insert = $client->putObject([
        'Bucket' => 'sqframe-backup',
        'Key'    => $archiveFilename,
        'SourceFile' => 'backups/' . $archiveFilename
    ]);

    echo '<br>Finished copying of '.$archiveFilename.' to SF DO Backup Server @'.$timestamp;
} catch (S3Exception $e) {
    echo '<br>Failed copying of '.$archiveFilename.' to SF DO Backup Server @'.$timestamp;
    echo $e->getMessage() . "\n";
}

/* DELETE OLDER FILES */
$timestamp = getServerDateTimeConverted();
echo '<br>Starting delete of old archives @'.$timestamp;
$files = preg_grep('/^([^.])/', scandir(realpath('./backups')));
rsort($files);
$i = 0;
$deletedFiles = 0;
foreach ($files as $file) {
    echo '<br> ∙ File to delete: '.$file;
    $i += 1;
    if($i > 7){
        $deletedFiles += 1;
        unlink('./backups/' . $file);
        $client->deleteObject(array(
            'Bucket' => 'sqframe-backup',
            'Key'    => $file
        ));
    }
}

echo '<br>Deleted '.$deletedFiles.' old archives @'.$timestamp;

$timestamp = getServerDateTimeConverted();
echo '<br>*** FINISHED @ '.$timestamp.'***';

?>
