<?PHP
$config = include('config.php');
header('Content-Type: application/json');

//Load archives
$fullcatalog = load_catalogs(array("newerAppData.json", "archivedAppData.json"));

//Try to prepare the logs
$logpath = null;
try {
	clearstatcache();
	$logpath = "logs";
        if (!file_exists($logpath)) {
                mkdir($logpath, 0774, true);
        }
        $logpath = getcwd() . "/" . $logpath . "/updatecheck.log";
        if (!file_exists($logpath)) {
                $logfile=fopen($logpath, "x");
                fwrite($logfile, "TimeStamp,IP,AppChecked,DeviceData,ClientInfo".PHP_EOL);
                fclose($logfile);
        }
} catch (exception $e) {
	//Fail with web server log and move on
	unset($logpath);
	error_log("Non-fatal error: " . $_SERVER ['SCRIPT_NAME'] . " was unable to create a log file. Check directory permissions for web server user.", 0);
}

$found_id = "null";
//Determine what the request was
$devicedata = str_replace(",", "", $_SERVER['HTTP_USER_AGENT']);
if (isset($_COOKIE["clientid"])) {
	$clientinfo = $_COOKIE['clientid'];
} else {
	$clientinfo = uniqid();
	setcookie ("clientid", $clientinfo, 2147483647);
}
if (isset($_GET["clientid"])) {
	$clientinfo = $_GET["clientid"];
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
	$meta_path = "http://" . $config["metadata_host"] . "/0.json";
}
else	//Any other app
{
	if (isset($logpath)) { $logpath = write_log_data($logpath, $search_str, $devicedata, $clientinfo); }
	//strip out version number if present
	$name_parts = explode("/", $search_str);
	$search_str = $name_parts[0];

	foreach ($fullcatalog as $this_app => $app_a) {
		if (strtolower($app_a["title"]) == $search_str || $app_a["id"] == $search_str) {
			//echo ("Found app: " . $app_a["title"] . "-" . $app_a["id"] . ".json<br>");
			$found_id = $app_a["id"];
		}
	}

	if ($found_id == "null") {
		echo "{\"error\": \"No matching app found for " . $search_str . "\"}";
		die;
	}
	$meta_path = "http://" . $config["service_host"] . "/WebService/getMuseumDetails.php?id=" . $found_id;
}

if (isset($meta_path)) {
	$meta_file = fopen($meta_path, "rb");
	$content = stream_get_contents($meta_file);
	fclose($meta_file);

	$json_m = json_decode($content, true);
	if (strpos($json_m["filename"], "://") === false) {
		$use_uri = "http://" . $config["package_host"] . '/AppPackages/' . $json_m["filename"];
	} else {
		$use_uri = $json_m["filename"];
	}
	$outputObj = array (
		"version" => $json_m["version"],
		"versionNote" => get_last_version_note($json_m["versionNote"]),
		"lastModifiedTime" => $json_m["lastModifiedTime"],
		"downloadURI" => $use_uri,
	);
}
echo (json_encode($outputObj));

function get_last_version_note($versionNote){
	$lastVersionNote = explode("\r\n", $versionNote);
	return end($lastVersionNote);
}

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
		$ip = gethostbyname($config['hostname']);
	}
	else 
	{
		// Get real visitor IP behind CloudFlare network
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
				$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
				$_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if(filter_var($client, FILTER_VALIDATE_IP))
		{
			$ip = $client;
		}
		elseif(filter_var($forward, FILTER_VALIDATE_IP))
		{
			$ip = $forward;
		}
		else
		{
			$ip = $remote;
		}
	}

    return $ip;
}
?>
