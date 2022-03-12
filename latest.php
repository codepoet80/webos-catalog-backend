<?php
$config = include('WebService/config.php');

//Get the app info
$download_path = "http://" . $config["package_host"] . "/";

$meta_path = "http://" . $config["metadata_host"] . "/0.json";
$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);
$outputObj = json_decode($content, true);

$attachment_location = $download_path . $outputObj["filename"];
header("Location: $attachment_location");

?>