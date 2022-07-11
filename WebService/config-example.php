<?php
return array(
//copy this file to config.php

//put global config here, subdirectories are supported, but no trailing slahes
//you must host these repositories over HTTP without redirecting to HTTPS -- webOS has poor support for HTTPS
//you can use the Upgrade-Insecure-Requests header in your server config to serve HTTPS to modern web clients
//(Note: the contents of this file are available via an API call to any client, do not embed any secrets)
'package_host' => 'packages.myhost.com',
'service_host' => 'appcatalog.myhost.com',
'metadata_host' => 'appmetadata.myhost.com',
'image_host' => 'appcatalog.myhost.com/AppImages'
);
?>
