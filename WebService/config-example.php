<?php
//copy this file to config.php
//  put global config here, subdirectories are supported, but no trailing slahes
//  you must host these repositories over HTTPS AND HTTP without redirecting to HTTPS
//  (you can use the Upgrade-Insecure-Requests header in your server config to serve HTTPS to modern web clients)
//  Note: the contents of this file are available via an API call to any client, do not embed any secrets

$image_mirrors = array('imagemirror1.com/AppImages', 'imagemirror2.com/AppImages');
$package_mirrors = array('packagemirror1.com/AppPackages', 'packagemirror2.com/AppPackages');

return array(
    'service_host' => 'appcatalog.myhost.com',
    'metadata_host' => 'appmetadata.myhost.com',
    'image_host' => select_lb_resource($image_mirrors),
    'package_host' => select_lb_resource($package_mirrors),
    'contact_email' => ''   //leave empty for none
);

//TODO: If we had a state machine, we could be more sophisticated.
//  For now, just pick a random resource each time
function select_lb_resource($resource_array) {
    return($resource_array[array_rand($resource_array)]);
}
?>
