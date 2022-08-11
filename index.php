<html>
<head>
<link rel="shortcut icon" href="favicon.ico">
<meta name="viewport" content="width=300, initial-scale=0.6">

<?php
$config = include('WebService/config.php');

//Get the app info
$download_path = "http://" . $config["package_host"] . "/";
$meta_path = "http://" . $config["metadata_host"] . "/0.json";
$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);
$outputObj = json_decode($content, true);
if (strpos($outputObj["filename"], "://") === false) {
  $use_uri = $download_path . $outputObj["filename"];
} else {
  $use_uri = $outputObj["filename"];
}
?>

<title>webOS App Museum II</title>
<link rel="stylesheet" href="webmuseum.css">
<style>
td { padding: 20px;}
</style>
</head>
<body class="content">
<?php include('menu.php'); ?>
<p align='middle' style='margin-top:50px;'>

  <img src="assets/icon.png"><br/>
  &nbsp;<br/>
  <strong>webOS App Museum II</strong><br/>
  <small>A project of <a href="http://www.webosarchive.org">webOS Archive</a></small><br>

</p>

<div id="wrapper" style="text-align: center; padding-top:28px;">
  <div id="col1" style="display: inline-block; vertical-align: top;" class="layoutCell">
    <h3>Download for webOS 2.0+ Devices</h3>
    <a href="<?php echo $use_uri?>">Get Current Version: <?php echo $outputObj["version"]?></a><br><br>
    <small>
    Requires <a href="http://www.webosarchive.org/activation/org.webosinternals.preware_1.9.14_arm.ipk">Preware</a><br>
    Need <a href="http://www.webosarchive.org/docs/appstores/">help installing</a>?<br>
    <div style="margin-top: 8px">Source available on <a href="https://www.github.com/codepoet80/webos-catalog-backend">GitHub</a></div>
    <br>
    </small>
  </div>

  <div id="col2" style="display: inline-block; vertical-align: top;" class="layoutCell">
    <h3>Other Ways to view the Museum</h3>
    <p><a href="showMuseum.php">Browse catalog online</a></p>
    <p><a href="https://forums.webosnation.com/webos-development/332697-refurbishing-app-museum-4.html#post3458072">Add feed to Preware</a></p>
    <p><a href="https://archive.org/details/webOSAppCatalogArchive-Complete">Full archive from archive.org</a><br/>
    <!-- Update to 3821 after next archival -->
    <small><i>36.9 GB: 3818 apps cataloged, 2033 un-cataloged</i></small></p>
  </div>
</div>

<p align='middle'><small>
  webOS Archive provides the Museum infrastructure and metadata, but relies on community mirrors to host the files themselves.<br>
  Do you have some historical IPKs archived that you want to contribute? Check the Wanted list (<a href="http://appcatalog.webosarchive.org/wanted.txt">TXT</a>, <a href="http://appcatalog.webosarchive.org/wanted.csv">CSV</a>)<br>
  <?php
    if (isset($config['contact_email']) && !empty($config['contact_email'])) {
      echo "If you can help, <a href=\"javascript:document.location=atob('" . base64_encode($config['contact_email']) . "')'>email the curator</a>!";
    }
  ?>
</small></p>
</body>
</html>
