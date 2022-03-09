<?PHP
$config = include('config.php');
header('Content-Type: application/json');

$string = file_get_contents("../archivedAppData.json");
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
$search_type = "app";
if (isset($_REQUEST["app"]))
	$search_str = $_REQUEST["app"];
if (isset($_GET["app"]))
	$search_str = $_GET["app"];

if (isset($_REQUEST["author"])) {
	$search_str = $_REQUEST["author"];
	$search_type = "author";
}
if (isset($_GET["author"])) {
	$search_str = $_GET["author"];
	$search_type = "author";
}
$search_str = urldecode(strtolower($search_str));

$_adult		  = false; if (isset($_GET['adult']))			{$_adult = $_GET['adult'];}
$_onlyLuneOS  = false; if (isset($_GET['onlyLuneOS']))	{$_onlyLuneOS = $_GET['onlyLuneOS'];}

$results = [];
//Loop through all apps
foreach ($json_a as $this_app => $app_a) {
	//Look for matches
	if ($search_type == "app" && (strtolower($app_a["title"]) == $search_str || 
		$search_str == $app_a["id"] ||
		(strpos(strtolower($app_a["title"]), $search_str) !== false) || 
		(strpos(strtolower(str_replace(" ", "", $app_a["title"])), $search_str) !== false) 
	  )) 
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
	if ($search_type == "author" && 
		(strtolower($app_a["author"]) == $search_str || 
		(strpos(strtolower($app_a["author"]), $search_str) !== false) ||
		(str_replace(strtolower(" ", "", $app_a["author"])) == $search_str) || 
		(strpos(strtolower(str_replace(" ", "", $app_a["author"])), $search_str) !== false)
	 )) 
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
