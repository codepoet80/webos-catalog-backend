<html>
<head>
<link rel="shortcut icon" href="favicon.ico">
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
?>
<title>webOS App Museum II - Web Catalog</title>
<link rel="stylesheet" href="webmuseum.css">
</head>
<body class="show-museum" style="margin-right:1.3em">
<h2><a href="/"><img src="icon.png" style="height:64px;width:64px;margin-top:-10px;" align="middle"></a> &nbsp;<a href="/">webOS App Museum II</a></h2>
<div class="museumMaster" style="margin-left:1.3em;">
	<div class="categoryMenu">
		<?php
			foreach ($category_list as $array_key) {
				$catname = $array_key;
				$catcount = $category_master["appCount"][$array_key];
				if ($catname != "All" && $catname != "Missing Apps" && $catcount > 0)
				{
					$catencode = (urlencode($array_key));
					echo "<span ";
					if (strtolower($catname) == strtolower($_GET['category']))
						echo ("class='categorySelected'");
					echo ("><a href='showMuseumCategories.php?category={$catencode}&count={$catcount}'>{$catname}</a></span> <span class='legal'>({$catcount} Apps)</span><br/>");
				}
			}
		?>
	</div>

	<div class="appsList">
		<?php
		if (count($app_response["data"]) > 0)
		{
			echo("<table cellpadding='5'>");
			foreach($app_response["data"] as $app) {
				echo("<tr><td align='center' valign='top'><a href='showMuseumDetails.php?{$app["id"]}'><img style='width:64px; height:64px' src='http://packages.webosarchive.com/AppImages/{$app["appIcon"]}' border='0'></a>");
				echo("<td width='100%' style='padding-left: 14px'><b><a href='showMuseumDetails.php?{$app["id"]}'>{$app["title"]}</a></b><br/>");
				echo("<small>" . substr($app["summary"],0, 180) . "...</small><br/>&nbsp;");
				echo("</td></tr>");
			}
			echo("</table>");
			include 'footer.php';
		}
		else
		{
			echo ("<p align='middle'><img src='webos-apps.png'></p>");
			echo ("<p align='middle'><i>Choose a category to view apps...</i></p>");
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