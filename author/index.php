<?php
$config = include('../WebService/config.php');
include('../common.php');

session_start();
if (!isset($_SESSION['encode_salt']))
{
	$_SESSION['encode_salt'] = uniqid();
}
//Load archives
//$json_a = load_catalogs(array("../newerAppData.json", "../archivedAppData.json"));

//figure out what protocol to use
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $protocol = "https://";
else
    $protocol = "http://";

//figure out where images are
$img_path = $protocol . $config["image_host"] . "/";

//figure out where metadata is
$author_path = $protocol . $config["service_host"] . "/AuthorMetadata/";

//figure out what they're looking for
$req = explode('/', $_SERVER['REQUEST_URI']);
$query = end($req);
$favicon_search = false;
if ($query == "favicon.ico") {	//this is a special case in support of Enyo front-end
	array_pop($req);
	$query = end($req);
	$favicon_search = true;
}
$app_path = $protocol . $config["service_host"] . "/WebService/getSearchResults.php?author=". $query;

//find all the apps
$app_file = fopen($app_path, "rb");
$app_content = stream_get_contents($app_file);
fclose($app_file);
$app_response = json_decode($app_content, true);

//find info about author
//	from query
$author_data = [
	"author" => mb_convert_case(urldecode($query), MB_CASE_TITLE),
	"favicon" => "../../favicon.ico",
	"iconBig" => "../../author.png"
];

//	from app results list (better)
if (isset($app_response) && isset($app_response["data"][0]) && isset($app_response["data"][0]["author"])) {
	$author_data["author"] = $app_response["data"][0]["author"];
}

//	from explicit author file (best)
if (isset($app_response) && isset($app_response["data"][0]) && isset($app_response["data"][0]["vendorId"])) {
	$author_path .= $app_response["data"][0]["vendorId"];
	//get vendor data (if available)
	$author_file = fopen($author_path . "/author.json", "rb");
	$author_content = stream_get_contents($author_file);
	fclose($author_file);
	if (isset($author_content) && $author_content != ""){ 
		$author_data = json_decode($author_content, true);
		$favicon_path = $author_path . "/" . $author_data['favicon'];
	}
}

if ($favicon_search) {	//return just the favicon
	if (isset($favicon_path)) {
		$image = file_get_contents($favicon_path);
		header('content-type: image/x-icon');
		echo $image;
	} else {
		http_response_code(404);
	}
	die();
}
?>
<html>
<head>
<link rel="shortcut icon" href="<?php echo $author_path . "/" . $author_data['favicon']; ?>">
<meta name="viewport" content="width=760, initial-scale=0.6">
<?php
//Figure out where to go back to
parse_str($_SERVER["QUERY_STRING"], $query);
unset($query["app"]);
$homePath = $protocol . $config["service_host"]. "";
?>
<title><?php echo $author_data['author']; ?> - webOS App Museum II</title>
<link rel="stylesheet" href="<?php echo $protocol . $config["service_host"]; ?>/webmuseum.css">
</head>
<body>
<?php include("../menu.php") ?>
<div class="show-museum" style="margin-right:1.3em">
	<h2><a href="<?php echo ($homePath); ?>"><img src="<?php echo $protocol . $config["service_host"]; ?>/assets/icon.png" style="height:64px;width:64px;margin-top:-10px;" align="middle"></a> &nbsp;<a href="<?php echo ($homePath); ?>">webOS App Museum II</a></h2>
	<br>
	<table border="0" style="margin-left:1.3em; width:100%; margin-bottom: 40px;">
		<tr>
			<td colspan="2">
				<h1><?php echo $author_data['author']; ?></h1>
				<?php if (isset($author_data['summary'])) { echo "<p>" . $author_data['summary'] . "</p>"; } ?>
				<?php 
					if (isset($author_data['sponsorMessage'])) { 
						echo "<p>" . $author_data['sponsorMessage']; 
						if isset($author_data['sponsorLink']) {
							echo "<br><a href='" . $author_data['sponsorLink']. "'>" . $author_data['sponsorLink'] . "</a>";
						}
						echo "</p>";
					} 
				?>
				<?php
					if (isset($author_data['socialLinks'])) {
						//Social icons by Shawn Rubel
						foreach($author_data['socialLinks'] as $social) {
							echo "<a href='" . $social . "'>" . render_social($social, $protocol . $config["service_host"]) . "</a> ";
						}
					}
				?>
			</td>
			<td rowspan="2" valign="top">
				<img src="<?php echo $author_path . "/" . $author_data['iconBig']; ?>" class="appIcon" onerror="this.onerror=null; this.src='../author.png';" >
			</td>
		</tr>
	</table>
	<div style="margin-left:20px">
	<h3>Apps by <?php echo $author_data["author"] ?>:</h3>
	<?php
		echo("<table cellpadding='5'>");
		foreach($app_response["data"] as $app) {
			if (strpos($app["appIcon"], "://") === false) {
				$use_img = $img_path.strtolower($app["appIcon"]);
			} else {
				$use_img = $app["appIcon"];
			}
			echo("<tr><td align='center' valign='top'><a href='{$protocol}{$config["service_host"]}/showMuseumDetails.php?{$_SERVER["QUERY_STRING"]}&app={$app["id"]}'><img style='width:64px; height:64px' src='{$use_img}' border='0'></a>");
			echo("<td width='100%' style='padding-left: 14px'><b><a href='{$protocol}{$config["service_host"]}/showMuseumDetails.php?{$_SERVER["QUERY_STRING"]}&app={$app["id"]}'>{$app["title"]}</a></b><br/>");
			echo("<small>" . substr($app["summary"],0, 180) . "...</small><br/>&nbsp;");
			echo("</td></tr>");
		}
		echo("</table>");
	?>
	</div>
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
