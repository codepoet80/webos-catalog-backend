<?php
$latest = "com.palm.app-museum2_2.7.2_all.ipk";
$attachment_location = $_SERVER["DOCUMENT_ROOT"] . "/" . $latest;
if (file_exists($attachment_location)) {

    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
    header("Cache-Control: public"); // needed for internet explorer
    header("Content-Type: application/octet-stream");
    header("Content-Transfer-Encoding: Binary");
    header("Content-Length:".filesize($attachment_location));
    header("Content-Disposition: attachment; filename=". $latest);
    readfile($attachment_location);
    die();        
} else {
    die("Error: File not found.");
} 
?>