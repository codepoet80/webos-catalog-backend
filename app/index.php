<?php
$config = include('../WebService/config.php');

//figure out what protocol to use
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $protocol = "https://";
else
    $protocol = "http://";

//figure out what they're looking for
$req = explode('/', $_SERVER['REQUEST_URI']);
$query = end($req);
$dest_page = $protocol. $config["service_host"];
$dest_page .= "/showMuseum.php?search=" . $query;
header("Location: $dest_page");
die();
$app_path = $protocol . $config["service_host"] . "/WebService/getSearchResults.php?app=". $query;

//get the results
$app_file = fopen($app_path, "rb");
$app_content = stream_get_contents($app_file);
fclose($app_file);
$app_response = json_decode($app_content, true);

//send them to result if exact match, or search page if not
$dest_page = $protocol. $config["service_host"];
if (isset($app_response) && isset($app_response['data'][0]) && count($app_response['data']) == 1) {
    $dest_page .= "/showMuseumDetails.php?app=" . $app_response['data'][0]['id'];
} else {
    $dest_page .= "/showMuseum.php?search=" . $query;
}
//echo $dest_page;
header("Location: $dest_page");
?>
