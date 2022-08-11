<?php
//Figure out what protocol the client wanted
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $protocol = "https://";
else
    $protocol = "http://";

echo file_get_contents($protocol."www.webosarchive.org/menu.php?content=appcatalog");
?>