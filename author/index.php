<?php
$config = include('../WebService/config.php');

session_start();
if (!isset($_SESSION['encode_salt']))
{
	$_SESSION['encode_salt'] = uniqid();
}
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

//figure out what protocol to use
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $protocol = "https://";
else
    $protocol = "http://";

//Figure out where images are
$img_path = $protocol . $config["image_host"] . "/";

//Figure out where metadata is
$author_path = $protocol . $config["service_host"] . "/author/";

//figure out what they're looking for
$req = explode('/', $_SERVER['REQUEST_URI']);
$query = end($req);
$app_path = $protocol . $config["service_host"] . "/WebService/getSearchResults.php?author=". $query;

//get the results
$app_file = fopen($app_path, "rb");
$app_content = stream_get_contents($app_file);
fclose($app_file);
$app_response = json_decode($app_content, true);

$author_data = ["author" => mb_convert_case(urldecode($query), MB_CASE_TITLE)];

if (isset($app_response) && isset($app_response["data"][0]) && isset($app_response["data"][0]["author"])) {
	$author_data = [
		"author" => $app_response["data"][0]["author"]
	];
}
if (isset($app_response) && isset($app_response["data"][0]) && isset($app_response["data"][0]["vendorId"])) {
	$author_path .= $app_response["data"][0]["vendorId"] . "/author.json";
	//get vendor data (if available)
	echo $author_path;
	$app_file = fopen($app_path, "rb");
	$app_content = stream_get_contents($author_path);
	fclose($app_file);
	if ($app_content)
		$author_data = json_decode($app_content, true);
}
echo $authorPath;
print_r($author_data);

?>
<html>
<head>
<link rel="shortcut icon" href="../favicon.ico">
<meta name="viewport" content="width=760, initial-scale=0.6">
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-12254772-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-12254772-3');
</script>
<?php
//Figure out where to go back to
parse_str($_SERVER["QUERY_STRING"], $query);
unset($query["app"]);
$homePath = "showMuseum.php?" . http_build_query($query);
?>
<title><?php echo $author_data['author']; ?> - webOS App Museum II</title>
<link rel="stylesheet" href="../webmuseum.css">
</head>
<body>
<?php include("../menu.php") ?>
<div class="show-museum" style="margin-right:1.3em">
	<h2><a href="<?php echo ($homePath); ?>"><img src="../icon.png" style="height:64px;width:64px;margin-top:-10px;" align="middle"></a> &nbsp;<a href="<?php echo ($homePath); ?>">webOS App Museum II</a></h2>
	<br>
	<table border="0" style="margin-left:1.3em; width:100%;">
		<tr><td colspan="2"><h1><?php echo $author_data['author']; ?></h1></td>
			<td rowspan="2">
			<img src="../icon.png" class="appIcon" >
		</td></tr>
	</table>
	<?php
		echo("<table cellpadding='5'>");
		foreach($app_response["data"] as $app) {
			echo("<tr><td align='center' valign='top'><a href='showMuseumDetails.php?{$_SERVER["QUERY_STRING"]}&app={$app["id"]}'><img style='width:64px; height:64px' src='{$img_path}{$app["appIcon"]}' border='0'></a>");
			echo("<td width='100%' style='padding-left: 14px'><b><a href='../showMuseumDetails.php?{$_SERVER["QUERY_STRING"]}&app={$app["id"]}'>{$app["title"]}</a></b><br/>");
			echo("<small>" . substr($app["summary"],0, 180) . "...</small><br/>&nbsp;");
			echo("</td></tr>");
		}
		echo("</table>");
	?>
	<!--
	<?php print_r($app_response); ?>
	-->
	<?php
	include '../footer.php';
	?>
	<div style="display:none;margin-top:18px">
</div>
</body>
</html>
