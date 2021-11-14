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

//Try to prepare the logs
$logpath = null;
try {
	clearstatcache();
	$logpath = "logs";
	if (!file_exists($logpath)) {
		mkdir($logpath, 0755, true);
	}
	$logpath = $logpath . "/updatecheck.log";
        $logfile=fopen($logpath, "x");	//this is stupid because it generates a server error, but file_exists isn't working
        if ($logfile) {
                fwrite($logfile, "TimeStamp,IP,AppChecked,DeviceData,ClientInfo".PHP_EOL);
                fclose($logfile);
        }
} catch (exception $e) {
	//Fail with web server log and move on
	unset($logpath);
	error_log("Non-fatal error: " . $_SERVER [‘SCRIPT_NAME’] . " was unable to create a log file. Check directory permissions for web server user.", 0);
}

//Determine what the request was
$found_id = "null";
$devicedata = $_SERVER['HTTP_USER_AGENT'];
if (isset($_COOKIE['clientid'])) {
	$clientinfo = $_COOKIE['clientid'];
} else {
	$clientinfo = uniqid();
	setcookie ('clientid', $clientinfo, 2147483647);
}
if (isset($_GET["app"]))
{
	$search_str = $_GET["app"];
	if (isset($_GET["device"])) {
		$devicedata = $_GET["device"];
	}
	if (isset($_GET["client"])) {
		$clientinfo = $_GET["client"];
	}
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
	if (isset($logpath)) { $logpath = write_log_data($logpath, "app museum 2", $devicedata, $clientinfo); }
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
	if (isset($logpath)) { $logpath = write_log_data($logpath, $search_str, $devicedata, $clientinfo); }

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

	$meta_file = fopen($meta_path, "rb");
	$content = stream_get_contents($meta_file);
	fclose($meta_file);

	$json_m = json_decode($content, true);
	$lastVersionNote = $json_m["versionNote"];
	$lastVersionNote = explode("\r\n", $lastVersionNote);
	$lastVersionNote =  $lastVersionNote[count($lastVersionNote)-1];

	$outputObj = array (
		"version" => $json_m["version"],
		"versionNote" => $lastVersionNote,
		"lastModifiedTime" => $json_m["lastModifiedTime"],
		"downloadURI" => "http://" . $config["package_host"] . '/AppPackages/' . $json_m["filename"],
	);
}
echo (json_encode($outputObj));

function write_log_data($logpath, $appname, $devicedata, $clientinfo) {
	if (file_exists($logpath)) {
		$timestamp = date('Y/m/d H:i:s');
		$logdata = $timestamp . "," . getVisitorIP() . "," . $appname . "," . $devicedata . "," . $clientinfo . PHP_EOL;
		file_put_contents($logpath, $logdata, FILE_APPEND);
		return $logpath;
	} else {
		return null;
	}
}

function getVisitorIP()
{
	$serverIP = explode('.',$_SERVER['SERVER_ADDR']);
	$localIP  = explode('.',$_SERVER['REMOTE_ADDR']);
	$isLocal = ( ($_SERVER['SERVER_NAME'] == 'localhost') ||
		    ($serverIP[0] == $localIP[0]) && 
		    (in_array($serverIP[0],array('192') ) ||
		    in_array($serverIP[0],array('127') ) ) 
	);
	if($isLocal)
	{
		$visitorIP = gethostbyname($config['hostname']);
	}
	else 
	{
		$visitorIP = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] : ($_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']); 
	}
	return $visitorIP;
}
?>
