<?php
/*----------------------------------------------------------------------------------------
    File: getPHPVersion.php
  Author: Kevin Messina
 Created: Nov. 14, 2016
Modified: Sep. 26, 2018

©2016-2018 Creative App Solutions, LLC. - All Rights Reserved.
------------------------------------------------------------------------------------------
NOTES:

2018_09_26 - Added Server display to PhP version output.
----------------------------------------------------------------------------------------*/

$version = "1.02a";

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/getPHPVersion.php
-or-
https://creativeapps.us/client-tools/squareframe/scripts/getPHPVersion.php
*/


/* Return information */
$serverName = $_SERVER["DOCUMENT_ROOT"];
$serverName = trim(strtoupper($serverName));
$isSF_Server = (bool)(stripos($serverName,"SQFRAM5") !== false);
$isCAS_Server = (bool)(stripos($serverName,"CREATIW8") !== false);

if ($isCAS_Server) { echo "CAS server: $serverName<br />"; }
elseif ($isSF_Server) { echo "Squareframe Server: $serverName<br />"; }
else { echo "Current server: UNKNOWN = $serverName<br />"; }

echo "<br />Current PhP Version on this server: ".phpversion()."<br />";

?>
