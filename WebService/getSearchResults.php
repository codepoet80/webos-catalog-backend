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

$search_str = $_SERVER["QUERY_STRING"];
$search_str = urldecode(strtolower($search_str));
if (isset($_REQUEST["query"]))
	$search_str = $_REQUEST["query"];
if (isset($_GET["query"]))
	$search_str = $_GET["query"];
$_adult		  = false; if (isset($_GET['adult']))			{$_adult = $_GET['adult'];}
$_onlyLuneOS  = false; if (isset($_GET['onlyLuneOS']))	{$_onlyLuneOS = $_GET['onlyLuneOS'];}

$results = [];
//Loop through all apps
foreach ($json_a as $this_app => $app_a) {
	//Look for matches
	if (strtolower($app_a["title"]) == $search_str || (strpos(strtolower($app_a["title"]), $search_str) !== false) || $app_a["id"] == $search_str) 
	{
		//Filter out adult apps (unless requested)
		if (!$_adult && $app_a['Adult']) {
			continue;
		}
		//Optionally show only LuneOS tested apps
		if ($_onlyLuneOS && !$app_a['LuneOS']) {
			continue;
		}
		array_push($results, $app_a);
	}
}
$responseObj->data = $results;
echo (json_encode($responseObj));

?>
