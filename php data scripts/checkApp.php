<?PHP

/*$missingApps = array();
if ($handle = opendir('./detailData')) {
	while (false !== ($entry = readdir($handle))) {
		set_time_limit(300);		   // set our excecution time limit
		if ($entry != "." && $entry != "..") {
			$myfile  = fopen("./detailData/".$entry, "r");
			$app = json_decode(fread($myfile,filesize("./detailData/".$entry)), true);
			fclose($myfile);
			
			$app = $app['OutGetAppDetailV2']['appDetail'];
			$id  = $app['id'];
			$appLocation = explode("/", $app['appLocation']);
			$appLocation = end($appLocation);
			$filename = "/webOS App Backup Full/Catalog Pull/{$id}--{$appLocation}";
			
			$exists = file_exists($filename);
			if (!$exists) {
				$missingApps[$id] = 0;
				// check whether I have a local copy
				$locations = array(
					"/webOS App Backup Full",
					"n:/palm IPKs/ipk jes",
					"n:/palm IPKs/ipk misj",
					"n:/palm IPKs/beta ipk",
					"n:/palm IPKs/catalog ipks",
					"/webosLocalBackup",
				);
				$found = 0;
				foreach($locations as $idx => $l) {
					if ($found == 0) {
						$existsLocally = file_exists("{$l}/{$appLocation}");
						if ($existsLocally) {
							$found = 1;
							$missingApps[$id] = 1;
							flush(); ob_flush();usleep(10000);
							if ($l != "/webOS App Backup Full") {
								echo $l . " -- {$id}--{$appLocation}<br>";
								copy("{$l}/{$appLocation}","./moreApps/{$id}--{$appLocation}");
							}
						}
					}
				}
			}
		}
	}
	closedir($handle);
}
//$detailFile = fopen("./missingApps.json", "w");
//fwrite($detailFile, json_encode($missingApps));
//fclose($detailFile);
echo "done...";*/

$fileList = array();
function listFolderFiles($dir, $fileList = null, $ftpList = null){
	set_time_limit(300);		   // set our excecution time limit
	if ($fileList == null) {$fileList = array();}
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

    echo '<ol>';
    foreach($ffs as $ff){
		$show = 0;
		if (!is_dir("{$dir}/{$ff}")) {
			if (strpos($ff, ".ipk") != false) {
				$gg = $ff;
				$show = 1;
				if (strpos($ff, "--") != false) {
					$gg = explode("--", $ff);
					$gg = trim(end($gg));
				}
				if ($ftpList != null) {
					if (array_search($gg, $ftpList, true) == false) {
						copy("{$dir}/{$ff}","./moreApps/{$ff}");
						echo "--copied--<br>";
					}
				}
				array_push($fileList, $gg);
				echo "<ul>{$gg}</ul>";
			}
		}
		if ($show || is_dir($dir."/".$ff)) {
			$show = 0;
			echo "<ul>";
			if(is_dir($dir.'/'.$ff)) {
				$folderFiles = listFolderFiles($dir.'/'.$ff);
				if (is_array($folderFiles)) {
					$fileList = array_merge($fileList, listFolderFiles($dir.'/'.$ff));
				}
			};
			echo '</ul>';
		}
    }
    echo '</ol>';
	return $fileList;
}

$ftpList = listFolderFiles('/webOS App Backup Full/', $fileList);


$locations = array(
	"n:/palm IPKs/ipk jes",
	"n:/palm IPKs/ipk misj",
	//"n:/palm IPKs/beta ipk",
	"n:/palm IPKs/catalog ipks",
	"/webosLocalBackup",
);

$localList = array();
foreach($locations as $k=>$l) {
	echo "==> {$l}<br>";
	$localList[$k] = listFolderFiles($l, null, $ftpList);
	//array_merge($localList);
}
$finalList = array_merge($localList[0], $localList[1], $localList[2], $localList[3]);

$finalList = array_unique($finalList);
$ftpList   = array_unique($ftpList);
$diff = array_diff(array_unique($finalList), array_unique($ftpList));
echo "==========================================<br>";
foreach($diff as $d) {
	echo "{$d}<br>";
}

echo ("<br><br>" . count($diff) . "<br><br>");
/*
echo "******************************************<br>";
foreach($ftpList as $d) {
	echo "** {$d}<br>";
}
echo "++++++++++++++++++++++++++++++++++++++++++<br>";
foreach($finalList as $d) {
	echo "++ {$d}";
	if (array_search($d, $ftpList, true) != false) {
		echo " *";
	}
	echo "<br>";
}
*/
echo "<br><br>done...";
?>