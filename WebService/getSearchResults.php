<?PHP
$config = include('config.php');
include("../common.php");
header('Content-Type: application/json');

$fullcatalog = load_catalogs(array("../archivedAppData.json", "../newerAppData.json"));

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
foreach ($fullcatalog as $this_app => $app_a) {
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
		(strtolower(str_replace(" ", "", $app_a["author"])) == $search_str) || 
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
