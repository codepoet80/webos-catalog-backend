<?php
return array(
//copy this file to config.php

//put global config here, subdirectories are supported, but no trailing slahes
//you must host these repositories over HTTP -- webOS has poor support for HTTPS
// (and no secrets, the contents of this file are available via an API call to any client)
'package_host' => 'packages.myhost.com',
'service_host' => 'appcatalog.myhost.com',
'metadata_host' => 'appmetadata.myhost.com',
'image_host' => 'appcatalog.myhost.com/AppImages'
);
?>
