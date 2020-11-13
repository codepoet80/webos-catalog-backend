<?php
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




	echo "starting...<br>";
	flush(); ob_flush();usleep(10000);
set_time_limit(300);		   // set our excecution time limit
ini_set('memory_limit', '-1'); // don't do this :)
	
	// all.json will be our base object.
	$myfile  = fopen("./masterDataM/masterAppData.json", "r");
	$appList = json_decode(fread($myfile,filesize("all.json")), true);
	fclose($myfile);


	echo "list loaded...<br>";
	flush(); ob_flush();usleep(10000);
	
	// I want to do an extra test...to find as many apps as possible.
	$appIDs = array();
	foreach($appList as $app) {
		array_push($appIDs, $app['id']);
	}
	natsort($appIDs);
	
	$previousID = 1000000;
	foreach($appIDs as $id) {
		$currentID = intval($id);
		if ($previousID < $currentID) {
			set_time_limit(300);
			if ($currentID-$previousID > 1) {
				for ($previousID+1; $previousID < $currentID; $previousID++) {
					$iconName = "http://cdn.downloads.palm.com/public/{$previousID}/icon/icon48.png";
					//$iconName = "http://cdn.downloads.palm.com/public/{$previousID}/icon/icon_1_0_9.png";
					$exists = remoteFileExists($iconName);
					if ($exists) {
						echo "--> " . $previousID . "<br>";
						echo "<img src = '{$iconName}' style='height:48px; width:48px; border: 1px solid orange;'></img><br>";
					} else {
						//echo "--{$previousID}--<br>";
					}
					flush(); ob_flush();//usleep(10000);

					if ($previousID % 100 === 0) {
						echo "....{$currentID}<br>";
						flush(); ob_flush();//usleep(10000);
					}
				}
			} else {
				//echo "=={$previousID}==<br>";
				//flush(); ob_flush();//usleep(10000);
			}
			$previousID = $currentID;
		} else {
			//echo "..{$currentID}..<br>";
			//flush(); ob_flush();usleep(10000);
		}
		if ($currentID % 100 === 0) {
			echo "....{$currentID}<br>";
			flush(); ob_flush();//usleep(10000);
		}
	}
?>