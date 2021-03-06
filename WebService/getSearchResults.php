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
$results = [];
//Loop through all apps
foreach ($json_a as $this_app => $app_a) {
	//Look for matches
	if (strtolower($app_a["title"]) == $search_str || (strpos(strtolower($app_a["title"]), $search_str) !== false) || $app_a["id"] == $search_str) 
	{
		array_push($results, $app_a);
	}
}
$responseObj->data = $results;
echo (json_encode($responseObj));

?>
