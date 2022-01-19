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
$app_path = $protocol . $config["service_host"] . "/WebService/getSearchResults.php?query=". $query;

//get the results
$app_file = fopen($app_path, "rb");
$app_content = stream_get_contents($app_file);
fclose($app_file);
$app_response = json_decode($app_content, true);

//send them to result if exact match, or search page if not
if (isset($app_response) && isset($app_response['data'][0]) && count($app_response['data']) == 1) {
    echo $protocol. $config["service_host"] . "/showMuseumDetails.php?app=" . $app_response['data'][0]['id'];
} else {
    echo $protocol. $config["service_host"] . "/showMuseum.php?search=" . $query;
}
?>
