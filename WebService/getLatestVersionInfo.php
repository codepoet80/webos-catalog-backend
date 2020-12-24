<?PHP
$config = include('config.php');
header('Content-Type: application/json');

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
if (isset($_GET["app"]))
{
	$search_str = $_GET["app"];
}
else
{
	$search_str = $_SERVER["QUERY_STRING"];
}
$search_str = urldecode(strtolower($search_str));

if ($search_str == "0" ||	//Treat the museum itself differently
 $search_str == "app museum" ||
 $search_str == "app museum 2" ||
 $search_str == "app museum ii" ||
 $search_str == "appmuseum" ||
 $search_str == "appmuseum2" ||
 $search_str == "appmuseumii")
{
	$found_id = "0";
	$meta_path = "http://" . $config["service_host"] . "/appinfo.json";
	//echo ("Load file: " . $meta_path);

	$meta_file = fopen($meta_path, "rb");
	$content = stream_get_contents($meta_file);
	fclose($meta_file);
	//echo $content;

	$json_m = json_decode($content, true);
	$outputObj = array (
		"version" => $json_m["version"],
		"lastModifiedTime" => $json_m["lastModifiedTime"],
		"versionNote" => $json_m["versionNote"],
		"downloadURI" => "http://" . $config["service_host"] . "/" . $json_m["id"] . "_" . $json_m["version"] . "_all.ipk",
	);
}
else
{
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
	$meta_path = "http://" . $config["service_host"] . "/WebService/getMuseumDetails.php?id=" . $found_id;
	//echo ("Load file: " . $meta_path);

	$meta_file = fopen($meta_path, "rb");
	$content = stream_get_contents($meta_file);
	fclose($meta_file);
	//echo ($content);

	$json_m = json_decode($content, true);
	$lastVersionNote = $json_m["versionNote"];
	$lastVersionNote = explode("\r\n", $lastVersionNote);
	$lastVersionNote =  $lastVersionNote[count($lastVersionNote)-1];
	//echo ($lastVersionNote);

	$outputObj = array (
		"version" => $json_m["version"],
		"versionNote" => $lastVersionNote,
		"lastModifiedTime" => $json_m["lastModifiedTime"],
		"downloadURI" => "http://" . $config["package_host"] . '/AppPackages/' . $json_m["filename"],
	);
}
echo (json_encode($outputObj));
//echo json_encode($outputObj, JSON_UNESCAPED_SLASHES);
?>
