<?PHP
$config = include('WebService/config.php');

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
$search_str = $_SERVER["QUERY_STRING"];
$search_str = urldecode(strtolower($search_str));
$found_app;
foreach ($json_a as $this_app => $app_a) {
	if (strtolower($app_a["title"]) == $search_str || $app_a["id"] == $search_str) {
		$found_app = $app_a;
		$app_title = $app_a["title"];
		$icon_path = "http://" . $config["package_host"] . "/AppImages/" . $app_a["appIconBig"];
		$found_id = $app_a["id"];
	}
}

if ($found_id == "null") {
	echo("ERROR: No matching app found");
	die;
}
$meta_path = "http://" . $config["package_host"] . "/WebService/getMuseumDetails.php?id=" . $found_id;

$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);

$app_detail = json_decode($content, true);
$lastVersionNote = $json_m["versionNote"];
$lastVersionNote = explode("\r\n", $lastVersionNote);
$lastVersionNote =  $lastVersionNote[count($lastVersionNote)-1];
$downloadURI = "http://" . $config["package_host"] . "/AppPackages/" . $app_detail["filename"];

$outputObj = array (
	"version" => $app_detail["version"],
	"versionNote" => $lastVersionNote,
	"lastModifiedTime" => $app_detail["lastModifiedTime"],
	"downloadURI" => "http://" . $config["package_host"] . "/AppPackages/" . $app_detail["filename"],
);

echo ("<img src='" . $icon_path . "' style='width:128px;height:128px;float:right;'>");
echo ("<h1>" . $app_title . "</h1>");
?>
<table>
<tr><td>Museum ID</td><td><?php echo $found_app["id"] ?></td></tr>
<tr><td>Application ID</td><td><?php echo $app_detail["publicApplicationId"] ?></td></tr>
<tr><td>Author</td><td><?php echo "<a href='" . $app_detail["homeURL"] . "'>" . $found_app["author"] . "</a>"?></td></tr>
<tr><td>Version</td><td><?php echo $app_detail["version"] ?></td></tr>
<tr><td>Description</td><td><?php echo $app_detail["description"] ?></td></tr>
<tr><td>Version Note</td><td><?php echo $app_detail["versionNote"] ?></td></tr>
<tr><td>Download</td><td><?php echo $downloadURI ?></td></tr>
<tr><td>Device Support</td>
<td>
	<ul>
	<li>Pre: <?php echo $found_app["Pre"] ?>
	<li>Pixi: <?php echo $found_app["Pixi"] ?>
	<li>Pre2: <?php echo $found_app["Pre2"] ?>
	<li>Veer: <?php echo $found_app["Veer"] ?>
	<li>Pre3: <?php echo $found_app["Pre3"] ?>
	<li>TouchPad: <?php echo $found_app["TouchPad"] ?>
	</ul>
</td>
</tr>
<tr><td>Screenshots</td><td>
<?php
foreach ($app_detail["images"] as $value) {
	$imgPath = "http://" . $config["package_host"] . "/AppImages/";
    echo("<a href='" . $imgPath . $value["screenshot"] . "'><img src='" . $imgPath. $value["thumbnail"] . "' style='width:64px'></a>");
}
?>
</td></tr>
<tr><td>Copyright</td><td><?php echo $app_detail["copyright"] ?></td></tr>
</table>

<div style="display:none;margin-top:18px">
<?php
echo (json_encode($app_a) . "<br><br>");
echo $content;
?>