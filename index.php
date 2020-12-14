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

//Get the app info
$meta_path = "http://" . $config["service_host"] . "/WebService/getLatestVersionInfo.php?0";
$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);
$outputObj = json_decode($content, true);
?>

<title>webOS App Museum II</title>
<link rel="stylesheet" href="webmuseum.css">
<style>
td { padding: 20px;}
</style>
</head>
<body>
<table width=100% height=100% border=0 style="margin-top:-40px">
<tr>
<td width=100% height=100% align="center" valign="middle" class="layoutCell">


<table>
<tr>
<td colspan="2" align="center">
<img src="icon.png"><br/>
&nbsp;<br/>
<strong>webOS App Museum II</strong><br/>
<small>A project of <a href="http://www.webosarchive.com">webOSArchive.com</a></small><br>
</td>
</tr>

<tr>
<td valign="top" class="layoutCell">
<h3>Download for webOS 2.0 Devices</h3>
<a href="<?php echo $outputObj["downloadURI"]?>">Get Current Version: <?php echo $outputObj["version"]?></a><br><br>
<small>
Requires <a href="http://www.webosarchive.com/activation/org.webosinternals.preware_1.9.14_arm.ipk">Preware</a><br>
Need <a href="http://www.webosarchive.com/docs/appstores/#install-webos-app-museum-ii">help installing</a>?<br>
<br>
</small>
</td>

<td valign="top" class="layoutCell">
<h3>Other Ways to view the Museum</h3>
<p><a href="showMuseumCategories.php">Browse Online by Category</a></p>
<p>Add feeds to PreWare (for webOS 1.x)</a><br/>
<small>Coming Soon</small></p>
<p>Download Entire Archive<br/>
<small>Coming Soon</small></p>
</td>
</tr>

<tr>
<td colspan="2" align="center">
Got some IPKs archived that you want to contribute?<br>Check the Wanted list (<a href="http://appcatalog.webosarchive.com/wanted.txt">TXT</a>, <a href="http://appcatalog.webosarchive.com/wanted.csv">CSV</a>) and <a href="mailto:curator@webosarchive.com">email the curator</a>!

</td>
</tr>
</td>
</tr>
</table>
</body>
</html>
