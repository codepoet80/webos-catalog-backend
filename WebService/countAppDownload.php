<?php
if (isset($_GET["appid"]) && $_GET["appid"] != "") {
    //Try to prepare the logs
    $logpath = null;
    try {
        clearstatcache();
        $logpath = "logs";
            if (!file_exists($logpath)) {
                    mkdir($logpath, 0774, true);
            }
            $logpath = getcwd() . "/" . $logpath . "/downloadcount.log";
            if (!file_exists($logpath)) {
                    $logfile=fopen($logpath, "x");
                    fwrite($logfile, "TimeStamp,AppId,Source".PHP_EOL);
                    fclose($logfile);
            }
    } catch (exception $e) {
        //Fail with web server log and move on
        unset($logpath);
        error_log("Non-fatal error: " . $_SERVER ['SCRIPT_NAME'] . " was unable to create a log file. Check directory permissions for web server user.", 0);
    }

    if (file_exists($logpath)) {
        $source = "app";
        if (isset($_GET["source"]) && $_GET["source"] != "") {
            $source = urldecode($_GET["source"]);
            $source = str_replace(",", "", $source);
        }
        $timestamp = date('Y/m/d H:i:s');
        $logdata = $timestamp . "," . $_GET["appid"] . "," . $source . PHP_EOL;
        file_put_contents($logpath, $logdata, FILE_APPEND);
    }
}
?> 