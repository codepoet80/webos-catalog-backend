<?PHP
$config = include('config.php');

$string = file_get_contents("../extantAppData.json");
if ($string === false) {
	echo ("ERROR: Could not find catalog file");
	die;
}

$json_a = json_decode($string, true);
if ($json_a === null) {
	echo ("ERROR: Could not parse catalog file");
	die;
}

$found_id = "null";
$search_str = $_SERVER["QUERY_STRING"];
$search_str = urldecode(strtolower($search_str));
//echo ("Searching for: " . $search_str . "<br>");
foreach ($json_a as $this_app => $app_a) {
	if (strtolower($app_a["title"]) == $search_str || $app_a["id"] == $search_str) {
		//echo ("Found app: " . $app_a["title"] . "-" . $app_a["id"] . ".json<br>");
		$found_id = $app_a["id"];
	}
}

if ($found_id == "null") {
	echo("ERROR: No matching app found");
	die;
}
$meta_path = "http://" . $config["package_host"] . "/WebService/getMuseumDetails.php?id=" . $found_id;
//echo ("Load file: " . $meta_path);

$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);
$json_m = json_decode($content, true);
//echo ($content);
echo $json_m["version"];
?>
