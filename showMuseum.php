<html>
<head>
<link rel="shortcut icon" href="favicon.ico">
<meta name="viewport" content="width=700, initial-scale=0.5">
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-12254772-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-12254772-3');
</script>

<?php
$config = include('WebService/config.php');

function repositionArrayElement(array &$array, $value, int $order): void
{
	$array_count = 0;
	$a = 0;
	foreach ($array as $array_value) {
		if ($array_value == $value)
		{
			$a = $array_count;
		}
		$array_count++;
	}
	$p1 = array_splice($array, $a, 1);
	$p2 = array_splice($array, 0, $order);
	$array = array_merge($p2, $p1, $array);
}
//Figure out where images are
$imgPath = "http://" . $config["image_host"] . "/";

//Get the category list
$category_path = "http://" . $config["service_host"] . "/WebService/getMuseumMaster.php?count=All&device=All&category=All&query=&page=0&blacklist=&key=web_categories&hide_missing=false";
$category_file = fopen($category_path, "rb");
$category_content = stream_get_contents($category_file);
fclose($category_file);
$category_master = json_decode($category_content, true);
$category_list = array_keys($category_master["appCount"]);
sort($category_list);

//Get the app list if there is a category query
if ($_GET['category'] != null && $_GET['count'] != null)
{
	$app_path = "http://" . $config["service_host"] . "/WebService/getMuseumMaster.php?count=". $_GET['count'] ."&device=All&category=". urlencode($_GET['category']) ."&query=&page=0&blacklist=&key=webapp_". uniqid() ."&hide_missing=false";
	$app_file = fopen($app_path, "rb");
	$app_content = stream_get_contents($app_file);
	fclose($app_file);
	$app_response = json_decode($app_content, true);
}
elseif ($_GET['search'] != null)
{
	$app_path = "http://" . $config["service_host"] . "/WebService/getSearchResults.php?". urlencode($_GET['search']);
	$app_file = fopen($app_path, "rb");
	$app_content = stream_get_contents($app_file);
	fclose($app_file);
	$app_response = json_decode($app_content, true);
}

//Figure out where to go back to
$homePath = "/";
if (isset($app_response))
	$homePath = "showMuseum.php";
?>
<title>webOS App Museum II - Web Catalog</title>
<link rel="stylesheet" href="webmuseum.css">
</head>
<body class="show-museum" style="margin-right:1.3em" onload="document.getElementById('txtSearch').focus()">
<h2><a href="<?php echo ($homePath); ?>"><img src="icon.png" style="height:64px;width:64px;margin-top:-10px;" align="middle"></a> &nbsp;<a href="<?php echo ($homePath); ?>">webOS App Museum II</a></h2>
<div class="museumMaster" style="margin-left:1.3em;">
	<div class="categoryMenu">
		<?php
			repositionArrayElement($category_list, "Revisionist History", 1);
			repositionArrayElement($category_list, "Curator's Choice", 1);
			foreach ($category_list as $array_key) {
				$catname = $array_key;
				$catcount = $category_master["appCount"][$array_key];
				if ($catname != "All" && $catname != "Missing Apps" && $catcount > 0)
				{
					$catencode = (urlencode($array_key));
					echo "<span ";
					if (strtolower($catname) == strtolower($_GET['category']))
						echo ("class='categorySelected'");
					echo ("><a href='showMuseum.php?category={$catencode}&count={$catcount}'>{$catname}</a></span> <span class='legal'>({$catcount} Apps)</span><br/>");
				}
			}
		?>
	</div>

	<div class="appsList">
		<?php
		if (count($app_response["data"]) > 0)
		{
			if (isset($_GET['category'])) {
				echo ("<h3>Category: " . $_GET['category'] . "</h3>");
			}
			if (isset($_GET['search'])) {
				echo ("<h3>Search Results: '" . $_GET['search'] . "'</h3>");
			}
			echo("<table cellpadding='5'>");
			foreach($app_response["data"] as $app) {
				echo("<tr><td align='center' valign='top'><a href='showMuseumDetails.php?{$_SERVER["QUERY_STRING"]}&app={$app["id"]}'><img style='width:64px; height:64px' src='{$imgPath}{$app["appIcon"]}' border='0'></a>");
				echo("<td width='100%' style='padding-left: 14px'><b><a href='showMuseumDetails.php?{$_SERVER["QUERY_STRING"]}&app={$app["id"]}'>{$app["title"]}</a></b><br/>");
				echo("<small>" . substr($app["summary"],0, 180) . "...</small><br/>&nbsp;");
				echo("</td></tr>");
			}
			echo("</table>");
			include 'footer.php';
		}
		else
		{
			?>
			<p align='middle' style='margin-top:50px;'><img src='webos-apps.png'></p>
			<p align='middle' style='margin-bottom:30px;'><i>Choose a category to view apps, or...</i></p>
			<form action="" method="get">
				<div style="margin-left:auto;margin-right:auto;text-align:center;">
				<input type="text" id="txtSearch" name="search" class="search" placeholder="Just type...">
				<input type="submit" class="search-button" value="Search">
				</div>
			</form>
			<?php
		}
		?>
	</div>
</div>
<?php
if (count($app_response["data"]) == 0)
{
	include 'footer.php';
}
?>
<div style="display:none">
<?php
//echo ($app_content);
?>
</div>
</body>
</html>
