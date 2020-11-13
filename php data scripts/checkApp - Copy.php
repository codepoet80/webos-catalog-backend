<?PHP
function remoteFileExists($url) {
    $curl = curl_init($url);

    //don't fetch the actual page, you only want to check the connection is ok
    curl_setopt($curl, CURLOPT_NOBODY, true);

    //do request
    $result = curl_exec($curl);

    $ret = false;

    //if request did not fail
    if ($result !== false) {
        //if request was ok, check response code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  

        if ($statusCode == 200) {
            $ret = true;   
        }
    }

    curl_close($curl);

    return $ret;
}


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
								echo "local copy found: {$appLocation}<br>";
								echo "{$app['title']} by: {$app['creator']}<br>";
								echo "id: {$id}<br>";
								echo "location: {$l}<br>";
								echo "==========<br>";
								echo "<br>";
								flush(); ob_flush();usleep(10000);
							}
						}
					}
					if ($found == 0) {
						echo "app file not found: {$id}--{$appLocation}<br>";
						echo "{$app['title']} by: {$app['creator']}<br>";
						echo "==========<br>";
						echo "<br>";
						flush(); ob_flush();usleep(10000);
					}
				}
			}
		}
		closedir($handle);
	}


?>