<html>
<head>
<link rel="shortcut icon" href="favicon.ico">
<meta name="viewport" content="width=760, initial-scale=0.6">
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-12254772-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-12254772-3');
</script>
<script>
function showHelp() {
	alert("Most webOS Devices should use the App Museum II native app to browse and install from the catalog. Older devices that can't run the Museum can Option+Tap (Orange or White Key) or Long Press (if enabled) on the Preware link on this page and copy it to your clipboard. Then you can use the 'Install Package' menu option in Preware to paste in and install the app using that link.");
}
</script>

<?php
$config = include('WebService/config.php');
session_start();
if (!isset($_SESSION['encode_salt']))
{
	$_SESSION['encode_salt'] = uniqid();
}
$string = file_get_contents("extantAppData.json");
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
if (isset($_GET["app"])) {
	$search_str = $_GET["app"];
	$search_str = urldecode(strtolower($search_str));
	$found_app;
	foreach ($json_a as $this_app => $app_a) {
		if (strtolower($app_a["title"]) == $search_str || $app_a["id"] == $search_str) {
			$found_app = $app_a;
			$found_id = $found_app["id"];
		}
	}
}
if ($found_id == "null") {
	echo("ERROR: No matching app found");
	die;
}

//Figure out what protocol the client wanted
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $protocol = "https://";
else
    $protocol = "http://";

$meta_path = $protocol . $config["service_host"] . "/WebService/getMuseumDetails.php?id=" . $found_id;

$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);

$app_detail = json_decode($content, true);

//Improve some strings for web output
$img_path = $protocol . $config["image_host"] . "/";
$app_detail["description"] = str_replace("\n", "<br>", $app_detail["description"]);
$app_detail["description"] = str_replace("\r\n", "<br>", $app_detail["description"]);
$app_detail["versionNote"] = str_replace("\n", "<br>", $app_detail["versionNote"]);
$app_detail["versionNote"] = str_replace("\r\n", "<br>", $app_detail["versionNote"]);

//Make some URLs
$author_url = "author/" . $found_app["author"];
$share_url = $protocol . $config["service_host"] . "/app/" . str_replace(" " , "", $found_app["title"]);

//Encode URL to reduce brute force downloads
//	The complete archive will be posted elsewhere to save my bandwidth
$plainURI = $protocol . $config["package_host"] . "/AppPackages/" . $app_detail["filename"];
$downloadURI = base64_encode($plainURI);
$splitPos = rand(1, strlen($downloadURI) - 2);
$downloadURI = substr($downloadURI, 0, $splitPos) . $_SESSION['encode_salt'] . substr($downloadURI, $splitPos);

//Figure out where to go back to
parse_str($_SERVER["QUERY_STRING"], $query);
unset($query["app"]);
$homePath = "showMuseum.php?" . http_build_query($query);
?>
<title><?php echo $found_app["title"] ?> - webOS App Museum II</title>
<link rel="stylesheet" href="webmuseum.css">
<script src="downloadHelper.php"></script>
</head>
<body>
<?php include("menu.php") ?>
<div class="show-museum" style="margin-right:1.3em">
	<h2><a href="<?php echo ($homePath); ?>"><img src="icon.png" style="height:64px;width:64px;margin-top:-10px;" align="middle"></a> &nbsp;<a href="<?php echo ($homePath); ?>">webOS App Museum II</a></h2>
	<br>
	<table border="0" style="margin-left:1.3em;">
	<tr><td colspan="2"><h1><?php echo $found_app["title"] ?></h1></td>
		<td rowspan="2">
		<img src="<?php echo $img_path. $found_app["appIconBig"]?>" class="appIcon" >
	</td></tr>
	<tr><td class="rowTitle">Museum ID</td><td class="rowDetail"><?php echo $found_app["id"] ?></td></tr>
	<tr><td class="rowTitle">Application ID</td><td colspan="2" class="rowDetail"><?php echo $app_detail["publicApplicationId"] ?></td></tr>
	<tr><td class="rowTitle">Share Link</td><td colspan="2" class="rowDetail"><?php echo "<a href='" . $share_url . "'>" . $share_url . "</a>"?></td></tr>
	<tr><td class="rowTitle">Author</td><td colspan="2" class="rowDetail"><?php echo "<a href='" . $author_url . "'>" . $found_app["author"] . "</a>"?></td></tr>
	<tr><td class="rowTitle">Version</td><td class="rowDetail"><?php echo $app_detail["version"] ?></td><td></td></tr>
	<tr><td class="rowTitle">Description</td><td colspan="2" class="rowDetail"><?php echo $app_detail["description"] ?></td></tr>
	<tr><td class="rowTitle">Version Note</td><td colspan="2" class="rowDetail"><?php echo $app_detail["versionNote"] ?></td></tr>
	<?php
	$browserAsString = $_SERVER['HTTP_USER_AGENT'];
	if (strstr(strtolower($browserAsString), "webos") || strstr(strtolower($browserAsString), "hpwos")) {
	?>
		<tr><td class="rowTitle">Download</td><td colspan="2" class="rowDetail">
			<a href="<?php echo $plainURI ?>">Preware Link</a> 
			&nbsp;<a href="javascript:showHelp()">(?)</a>
		</td></tr>
	<?php
	} else {
	?>
		<tr><td class="rowTitle">Download</td><td colspan="2" class="rowDetail"><a href="javascript:getLink('<?php echo $downloadURI ?>', <?php echo $found_app["id"] ?>);">Direct Link</a></td></tr>
	<?php
	}
	?>

	<tr><td class="rowTitle">Device Support</td>
	<td class="rowDetail">
		<ul>
		<li class="deviceSupport<?php echo $found_app["Pre"] ?>">Pre: 
		<li class="deviceSupport<?php echo $found_app["Pixi"] ?>">Pixi: 
		<li class="deviceSupport<?php echo $found_app["Pre2"] ?>">Pre2: 
		<li class="deviceSupport<?php echo $found_app["Veer"] ?>">Veer:
		<li class="deviceSupport<?php echo $found_app["Pre3"] ?>">Pre3:
		<li class="deviceSupport<?php echo $found_app["TouchPad"] ?>">TouchPad:
		<li class="deviceSupport<?php echo $found_app["LuneOS"] ?>">LuneOS:
		</ul>
	</td>
	<td></td>
	</tr>
	<tr><td class="rowTitle">Screenshots</td>
	<td colspan="2" class="rowDetail">
	<?php
	foreach ($app_detail["images"] as $value) {
		echo("<a href='" . $img_path . $value["screenshot"] . "' target='_blank'><img class='screenshot' src='" . $img_path. $value["thumbnail"] . "' style='width:64px'></a>");
	}
	?>
	</td></tr>
	<tr><td class="rowTitle">Home Page</td><td colspan="2" class="rowDetail"><a href="<?php echo $app_detail["homeURL"] ?>" target="_blank"><?php echo $app_detail["homeURL"] ?></a></td></tr>
	<tr><td class="rowTitle">Support URL</td><td colspan="2" class="rowDetail"><a href="<?php echo $app_detail["supportURL"] ?>" target="_blank"><?php echo $app_detail["supportURL"] ?></a></td></tr>
	<tr><td class="rowTitle">File Size</td><td colspan="2" class="rowDetail"><?php echo round($app_detail["appSize"]/1024,2) ?> KB</td></tr>
	<tr><td class="rowTitle" class="rowDetail">License</td><td colspan="2"><?php echo $app_detail["licenseURL"] ?></td></tr>
	<tr><td class="rowTitle" class="rowDetail">Copyright</td><td colspan="2"><?php echo $app_detail["copyright"] ?></td></tr>
	</table>
	<?php
	include 'footer.php';
	?>
	<div style="display:none;margin-top:18px">
	<?php
	//echo (json_encode($app_a) . "<br><br>");
	//echo $content;
	?>
</div>
</body>
</html>
