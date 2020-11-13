<?php
function decodeText($string, $id = 0) {
	$outString = preg_replace('/\X([0-9a-f]{4})/', '&#x$1;', $string);
	$outString = str_replace("\\", "", $outString);
	//return html_entity_decode($outString, ENT_COMPAT, 'UTF-8');
	return $outString;
}

function getImages($appData) {
	$remoteFileExists = function($url) {
		set_time_limit(3600);		   // set our excecution time limit
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
	};

	$images = $appData['images'];
	$imgObj  = array();
	
	$height = 0;
	$orientation = "";
	
	foreach($images as $idx => $img) {
		$key = explode("/", $img['imageKey']);
		if (isset($key[1]) && $key[0] !== 'icon') {
			$imgCount = $key[1];
			$imgKey   = $key[0];
		} else if (stripos($key[0], "appScaledImage") == 0) {
			$imgKey   = "appScaledImage";
			$imgCount = intval (str_replace($imgKey, "", $key[0]));
			if ($imgCount == 0) {continue;} // we know that our images start with a 1, so 0 implies that text was converted to a number.
		} else {
			continue;
		}
		if (!isset($imgObj[$imgCount])) {
			$imgObj[$imgCount] = array();
		}

		$large  = stripos($img['uri'], "/L/");
//		$medium = stripos($img['uri'], "/A/");
		$small  = stripos($img['uri'], "/S/");
		
		$image = str_replace(".hpsvcs.", ".palm.", $img['uri']);
		$image = str_replace($appData['publicApplicationId'], $appData['id'], $image);
		
		if ($large != false && !isset($imgObj[$imgCount]["screenshot"])) {
			if ($remoteFileExists($image) == false) {
				$image = str_replace($appData['id'], $appData['publicApplicationId'], $image);
			}
			$image = str_replace("http://cdn.downloads.palm.com/public/", "", $image);
			
			$imgObj[$imgCount]["screenshot"] = $image;
			$height      = $img['height'];
			$orientation = $img['orientation'];
			
		}
/*		if ($medium != false && !isset($imgObj[$imgCount]["medium"])) {
			$imgObj[$imgCount]["medium"] = $image;
		}*/
		if ($small!= false && !isset($imgObj[$imgCount]["thumbnail"])) {
			if ($remoteFileExists($image) == false) {
				$image = str_replace($appData['id'], $appData['publicApplicationId'], $image);
			}
echo "NOTE: we need to do this only once per app!";
exit;
			$image = str_replace("http://cdn.downloads.palm.com/public/", "", $image);
			
			$imgObj[$imgCount]["thumbnail"] = $image;
		}
	}
	foreach($imgObj as $idx => $img) {
		if (!isset($img['thumbnail'])) {
			echo "no thumbnail found for image {$appData['id']}:{$idx}<br>";
			$imgObj[$idx]['thumbnail'] = $imgObj[$idx]['screenshot'];
		}
		
		$imgObj[$idx]['orientation'] = $orientation;
		if ($height < 600) {
			$imgObj[$idx]['device'] = 'P';
		} else {
			$imgObj[$idx]['device'] = 'T';
		}
	}
	return $imgObj;
}




	echo "starting...<br>";
	flush(); ob_flush();usleep(10000);
set_time_limit(300);		   // set our excecution time limit
ini_set('memory_limit', '-1'); // don't do this :)
	$appList = array();
	
	// all.json will be our base object.
	$myfile  = fopen("all.json", "r");
	$content = json_decode(fread($myfile,filesize("all.json")), true);
	fclose($myfile);

	$appNames = array();
	foreach($content as $item) {
		// first we need to check whether the detail data also exists (we only add times that include the detailData
		$id = $item['id'];
		if (file_exists("./detailData/{$id}.json")) {
			$detailFile = fopen("./detailData/{$id}.json", "r");
			$details    = json_decode(fread($detailFile,filesize("./detailData/{$id}.json")), true);
			fclose($detailFile);
			$details    = $details['OutGetAppDetailV2'];
			$country    = $details['country'];
			$details    = $details['appDetail'];
			
			$itm = array(
				$id => array(
					'id'         => $id,
					'title'      => $item['title'],
					'author'     => $item['author'],
					'summary'    => $item['summary'],
					'appIcon'    => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $item['appIcon'])),
					'appIconBig' => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $details['appIconBig'])),
					'category'   => $details['primaryCategory'],
					'vendorId'   => $details['vendorid'],
					'Pixi'       => false,
					'Pre'        => false,
					'Pre 2'      => false,
					'Pre3'       => false,
					'Veer'       => false,
					'TouchPad'   => false
				)
			);
			foreach($details['supportedDevices'] as $device) {
				$itm[$id][$device] = true;
			}
			$dtl = array(
				'publicApplicationId' => $details['publicApplicationId'],
				'description' => $details['description'],
				'version' => $details['version'],
				'versionNote' => $details['versionNote'],
				
				'homeURL' => $details['homeURL'],
				'supportURL' => $details['supportURL'],
				'custsupportemail' => $details['custsupportemail'],
				'custsupportphonenum' => $details['custsupportphonenum'],
				'copyright'   => $details['copyright'],
				'licenseURL' => $details['licenseURL'],
				
				'locale' => $details['locale'],
				'appSize' => $details['appSize'],
				'installSize' => $details['installSize'],

				'isEncrypted' => $details['isEncrypted'],
				'adultRating' => $details['adultRating'],
				'islocationbased' => $details['islocationbased'],
				'lastModifiedTime' => $details['lastModifiedTime'],
				
				'mediaLink' => @$details['mediaLink'],
				'mediaIcon' => @$details['mediaIcon'],
				'attributes' => $details['attributes'],
				
				'price' => $details['price'],
				'currency' => $details['currency'],
				'isAdvertized' => $item['isAdvertized']
			);
			$dtl['filename'] = explode("/", $details['appLocation']);
			$dtl['filename'] = end($dtl['filename']);
			
			if ($details['price'] === 0) {
				$dtl['free'] = true;
			} else {
				$dtl['free'] = false;
			}
			if (isset($item['badges'][0]) && $item['badges'][0] == 'touchpad_exclusive') {
				$itm[$id]['touchpad_exclusive'] = true;
			} else {
				$itm[$id]['touchpad_exclusive'] = false;
			}
			
//			echo "getImages ($id)...<br>";
//			flush(); ob_flush();usleep(10000);
//			$dtl['images'] = getImages($details);
			// let's get rid of some redundant images
/*			foreach($details['images'] as $image) {
				if ($image['width'] >= 320) {
					array_push($dtl['images'], array(
						'imageKey' => $image['imageKey'],
						'orientation' => $image['orientation'],
						'height' => $image['height'],
						'width' => $image['width'],
						'uri' => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $image['uri']))
					));
				}
			}*/
			$detailFile = fopen("./detailDataM/{$id}.json", "w");
			fwrite($detailFile, json_encode($dtl));
			fclose($detailFile);

			
			$appList[$item['id']] = $itm[$item['id']];
			array_push($appNames, strtolower($item['title']) . "|||" . $item['id']);
		}
	}
	echo "opened all.json...<br>";
	flush(); ob_flush();usleep(10000);
	
	// we add out.json should there be anything in there that's not in all (I doubt it).
	$myfile  = fopen("out.json", "r");
	$content = json_decode(fread($myfile,filesize("out.json")), true);
	fclose($myfile);
	foreach($content as $item) {
		$id = $item['id'];
		if (!isset($appList[$id]) && file_exists("./detailData/{$id}.json")) {
			$detailFile = fopen("./detailData/{$id}.json", "r");
			$details    = json_decode(fread($detailFile,filesize("./detailData/{$id}.json")), true);
			$details    = $details['OutGetAppDetailV2'];
			$details    = $details['appDetail'];
			
			$itm = array(
				$id => array(
					'id'         => $id,
					'title'      => $item['title'],
					'author'     => $item['author'],
					'summary'    => $item['summary'],
					'appVersion' => $item['appVersion'],
					'appIcon'    => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $item['appIcon'])),
					'appIconBig' => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $details['appIconBig'])),
					'category'   => $details['primaryCategory'],
					'vendorId'   => $details['vendorid'],
				)
			);
			foreach($details['supportedDevices'] as $device) {
				$itm[$id][$device] = true;
			}
			$dtl = array(
				'publicApplicationId' => $details['publicApplicationId'],
				'description' => $details['description'],
				'version' => $details['version'],
				'versionNote' => $details['versionNote'],
				
				'homeURL' => $details['homeURL'],
				'supportURL' => $details['supportURL'],
				'custsupportemail' => $details['custsupportemail'],
				'custsupportphonenum' => $details['custsupportphonenum'],
				'copyright'   => $details['copyright'],
				'licenseURL' => $details['licenseURL'],
				
				'locale' => $details['locale'],
				'appSize' => $details['appSize'],
				'installSize' => $details['installSize'],

				'isEncrypted' => $details['isEncrypted'],
				'adultRating' => $details['adultRating'],
				'islocationbased' => $details['islocationbased'],
				'lastModifiedTime' => $details['lastModifiedTime'],
				
				'mediaLink' => @$details['mediaLink'],
				'mediaIcon' => @$details['mediaIcon'],
				'attributes' => $details['attributes'],
				
				'price' => $details['price'],
				'currency' => $details['currency'],
				'isAdvertized' => $item['isAdvertized']
			);
			$dtl['filename'] = explode("/", $details['appLocation']);
			$dtl['filename'] = end($dtl['filename']);
			
			if ($details['price'] === 0) {
				$dtl['free'] = true;
			} else {
				$dtl['free'] = false;
			}
			if (isset($item['badges'][0]) && $item['badges'][0] == 'touchpad_exclusive') {
				$itm[$id]['touchpad_exclusive'] = true;
			} else {
				$itm[$id]['touchpad_exclusive'] = false;
			}
/*			echo "getImages ($id)...<br>";
			flush(); ob_flush();usleep(10000);
			$dtl['images'] = getImages($details);*/
			
/*			$dtl['images'] = array();
			// let's get rid of some redundant images
			foreach($details['images'] as $image) {
				if ($image['width'] >= 320) {
					array_push($dtl['images'], array(
						'imageKey' => $image['imageKey'],
						'orientation' => $image['orientation'],
						'height' => $image['height'],
						'width' => $image['width'],
						'uri' => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $image['uri']))
					));
				}
			}*/
			$detailFile = fopen("./detailDataM/{$id}.json", "w");
			fwrite($detailFile, json_encode($dtl));
			fclose($detailFile);
			
			$appList[$item['id']] = $itm[$item['id']];
			array_push($appNames, strtolower($item['title']) . "|||" . $item['id']);
		}
	}
	echo "added out.json...<br>";
	flush(); ob_flush();usleep(10000);
	
	// finally we add everything from the other dumps that's not yet in our applist
	$masterData = array();
	if ($handle = opendir('./masterData')) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				$myfile  = fopen("./masterData/".$entry, "r");
				$content = fread($myfile,filesize("./masterData/".$entry));
				fclose($myfile);
				array_push($masterData, json_decode($content,true));
				echo "opened {$entry}...<br>";
			}
		}
		closedir($handle);
	}
	foreach($masterData as $mD) {
		foreach ($mD as $item) {
			$id = $item['id'];
			if (!isset($appList[$id]) && file_exists("./detailData/{$id}.json")) {
				$detailFile = fopen("./detailData/{$id}.json", "r");
				$details    = json_decode(fread($detailFile,filesize("./detailData/{$id}.json")), true);
				$details    = $details['OutGetAppDetailV2'];
				$details    = $details['appDetail'];
				
				if (!isset($appList[$item['id']])) {
					// we will only add the item if it doesn't yet exist.
					$itm = array(
						$id => array(
							'id'         => $id,
							'title'      => $item['title'],
							'author'     => $item['author'],
							'summary'    => $item['summary'],
							'appVersion' => $item['appVersion'],
							'appIcon'    => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $item['appIcon'])),
							'appIconBig' => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $details['appIconBig'])),
							'category'   => $details['primaryCategory'],
							'vendorId'   => $details['vendorid'],
						)
					);
					foreach($details['supportedDevices'] as $device) {
						$itm[$id][$device] = true;
					}
					
					$dtl = array(
						'publicApplicationId' => $details['publicApplicationId'],
						'description' => $details['description'],
						'version' => $details['version'],
						'versionNote' => $details['versionNote'],
						
						'homeURL' => $details['homeURL'],
						'supportURL' => $details['supportURL'],
						'custsupportemail' => $details['custsupportemail'],
						'custsupportphonenum' => $details['custsupportphonenum'],
						'copyright'   => $details['copyright'],
						'licenseURL' => $details['licenseURL'],
						
						'locale' => $details['locale'],
						'appSize' => $details['appSize'],
						'installSize' => $details['installSize'],

						'isEncrypted' => $details['isEncrypted'],
						'adultRating' => $details['adultRating'],
						'islocationbased' => $details['islocationbased'],
						'lastModifiedTime' => $details['lastModifiedTime'],
						
						'mediaLink' => @$details['mediaLink'],
						'mediaIcon' => @$details['mediaIcon'],
						'attributes' => $details['attributes'],
						
						'price' => $details['price'],
						'currency' => $details['currency'],
						'isAdvertized' => $item['isAdvertized']
					);
					$dtl['filename'] = explode("/", $details['appLocation']);
					$dtl['filename'] = end($dtl['filename']);
					
					if ($details['price'] === 0) {
						$dtl['free'] = true;
					} else {
						$dtl['free'] = false;
					}
					if (isset($item['badges'][0]) && $item['badges'][0] == 'touchpad_exclusive') {
						$itm[$id]['touchpad_exclusive'] = true;
					} else {
						$itm[$id]['touchpad_exclusive'] = false;
					}
/*					echo "getImages ($id)...<br>";
					flush(); ob_flush();usleep(10000);
					$dtl['images'] = getImages($details);*/
					
/*					$dtl['images'] = array();
					// let's get rid of some redundant images
					foreach($details['images'] as $image) {
						if ($image['width'] >= 320) {
							array_push($dtl['images'], array(
								'imageKey' => $image['imageKey'],
								'orientation' => $image['orientation'],
								'height' => $image['height'],
								'width' => $image['width'],
								'uri' => str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $image['uri']))
							));
						}
					}*/
					$detailFile = fopen("./detailDataM/{$id}.json", "w");
					fwrite($detailFile, json_encode($dtl));
					fclose($detailFile);
					
					$appList[$item['id']] = $itm[$item['id']];
					array_push($appNames, strtolower($item['title']) . "|||" . $item['id']);
				}
			}
		}
	}
	echo "added all dumps...<br>";
	flush(); ob_flush();usleep(10000);
	
	// now we need to sort our items by name. We use the $appNames array for that.
	$masterDataSorted = array();
	asort($appNames);
	foreach($appNames as $name) {
		$n = explode("|||", $name);
		array_push($masterDataSorted, $appList[$n[1]]);
	}
	echo "sorted applist alphabetically...<br>";
	flush(); ob_flush();usleep(10000);
	
	unset($masterData);
	//unset($appList);
	echo "cleared memory...<br>";
	flush(); ob_flush();usleep(10000);
	
	$myfile = fopen("./masterDataM/masterAppData.json", "w");
	fwrite($myfile, json_encode($masterDataSorted));
	fclose($myfile);
	
	echo "masterData Saved...<br>";
	echo "<br>";
	echo "get the catalog text description...<br>";
	flush(); ob_flush();usleep(10000);
	
	// all.json will be our base object.
	$myfile  = fopen("palm-catalog.txt", "r");
	$content = fread($myfile,filesize("palm-catalog.txt"));
	fclose($myfile);
	
	
	$list = preg_split("/\n{2,}/", $content);
	
	// all.json will be our base object.
	$myfile  = fopen("./masterDataM/masterAppData.json", "r");
	$master  = fread($myfile,filesize("./masterDataM/masterAppData.json"));
	fclose($myfile);
	
	$counter = 0;
	$masterData = json_decode($master, true);
	foreach($list as $item) {
		$detailData = array();
		$app = array();
		$keys = array(
			"Package",
			"Version",
			"Section",
			"Architecture",
			"Maintainer",
			"Size",
			"Filename",
			"Source",
			"Description"
		);
		foreach($keys as $key) {
			$temp = preg_match("/({$key}: )(.*)/", $item, $matches, PREG_OFFSET_CAPTURE);
			if ($temp == 0) {
				print_r($item);
				echo ("<br>{$key}<br>");
			}
			$app[$key] = $matches[2][0];
			if ($key == "Source") {
				$app[$key] = json_decode($matches[2][0]);
			}
		}
		$app = json_decode(json_encode($app), true);
		// populate our masterData
		$source = $app['Source'];
		$l = $source['Languages'];
		
		preg_match("/(.*)(apps\/)(.*)(\/files)/U", $source['Location'], $matches, PREG_OFFSET_CAPTURE);
		$id = $matches[3][0];
		
		// we check whether this id already exists in our list. Because if it does, we don't need to process it any further.
		$foundApp = preg_match("/\"id\":{$id},/U", $master, $matches, PREG_OFFSET_CAPTURE);
		
		if ($foundApp == 0) {
			$title      = decodeText($source['Title']);
			$author     = decodeText($app['Maintainer'], $id);
			$version    = $app['Version'];
			$appIcon    = str_replace("http://cdn.downloads.hpsvcs.com/public/", "", str_replace("http://cdn.downloads.palm.com/public/", "", $source['Icon']));
			$appIconBig = str_replace("/S", "", $appIcon);
			$category   = $source['Category'];
			$description= $source['FullDescription'];
			$vendorId   = 0;
			$pixi       = false;
			$pre        = false;
			$pre2       = false;
			$pre3       = false;
			$veer       = false;
			$touchpad   = false;
			foreach($source['DeviceCompatibility'] as $device) {
				if ($device = "Pixi")     {$pixi = true;};
				if ($device = "Pre")      {$pre = true;};
				if ($device = "Pre2")     {$pre2 = true;};
				if ($device = "Pre3")     {$pre3 = true;};
				if ($device = "Veer")     {$veer = true;};
				if ($device = "Touchpad") {$touchpad = true;};
			}
			// note: we want to see whether we can find the same company in our existing list. If we do, we can set the vendorId;
			// (AccuWeather, Inc.)(.*)(\"vendorId\":\")(\d*)
			$found = preg_match("/({$author})(.*)(\"vendorId\":\")(\d*)/", $master, $matches, PREG_OFFSET_CAPTURE);
			echo decodeText($author) . "<br>";
			
			echo "{$title} -- {$author}<br>";
			echo "<img src = 'http://cdn.downloads.palm.com/public/{$appIcon}'></img><br>";
			if ($found == 0) {
				echo "unknown vendor id";
			} else {
				echo "vendorId: {$matches[3][1]}";
				$vendorId = $matches[3][1];
			}
			echo "<br><br>";
			
			$appLocation = explode("/", $source['Location']);
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
							echo "location: {$l}<br>";
							echo "==========<br>";
							echo "<br>";
							flush(); ob_flush();usleep(10000);
						}
					}
				}
				if ($found == 0) {
					echo "app file not found: {$id}--{$appLocation}<br>";
					echo "==========<br>";
					echo "<br>";
					flush(); ob_flush();usleep(10000);
				}
			}
			
			flush(); ob_flush();usleep(10000);
			$counter ++;
			
			
			
			$itm = array(
				$id => array(
					'id'         => $id,
					'title'      => $title,
					'author'     => $author,
					'summary'    => implode(' ', array_slice(explode(' ', $description), 0, 100)),
					'appIcon'    => $appIcon,
					'appIconBig' => $appIconBig,
					'category'   => $category,
					'vendorId'   => $vendorId,
					'Pixi'       => $pixi,
					'Pre'        => $pre,
					'Pre 2'      => $pre2,
					'Pre3'       => $pre3,
					'Veer'       => $veer,
					'TouchPad'   => $touchpad
				)
			);
			$itm[$id]['touchpad_exclusive'] = false;
			if (!$pixi && !$pre && !$pre2 && !$pre3 && !$veer && $touchpad) {
				$itm[$id]['touchpad_exclusive'] = true;
			}			
			$dtl = array(
				'publicApplicationId' => $app['Package'],
				'description' => $description,
				'version' => $version,
				
				'homeURL' => $source['Homepage'],
				'supportURL' => $source['Homepage'],
				'custsupportemail' => "",
				'custsupportphonenum' => "",
				'copyright'   => $source['License'],
				'licenseURL' => $source['Homepage'],
				
				'locale' => null,
				'appSize' => $app['Size'],
				'installSize' => $app['Size'],

				'isEncrypted' => null,
				'adultRating' => null,
				'islocationbased' => null,
				'lastModifiedTime' => null,
				
				'mediaLink' => null,
				'mediaIcon' => null,
				'attributes' => array(),
				
				'price' => @$source['Price'],
				'currency' => 'USD',
			);
			
			if ($dtl['price'] == false) {
				$dtl['price'] = 0;
			}
			$dtl['filename'] = $app['Filename'];
			
			if ($dtl['price'] == 0) {
				$dtl['free'] = true;
			} else {
				$dtl['free'] = false;
			}
			
			if (isset($source["Screenshots"])) {
				$screenshots = $source["Screenshots"];
			} else {
				$screenshots = array($appIconBig);
			}
			
			$dtl['images'] = array();
			foreach($screenshots as $s) {
				$size = @getimagesize($s);
				if ($size == false) {
					$s = $appIconBig;
					$size = @getimagesize($s);
					if ($size == false) {
						$s = $appIcon;
						$size = @getimagesize($s);
					}
				}
				$imgData = array(
					"thumbnail"  => $s,
					"screenshot" => $s
				);
				$imgData['orientation'] = "P";
				if ($size[0] > $size[1]) {
					$imgData['orientation'] = "L";
				}
				
				$imgData['device'] = "P";
				if ($size[0] > 600) {
					$imgData['device'] = "T";
				}
				
				array_push(
					$dtl['images'],
					$imgData
				);
			}
			
			array_push($masterData, $itm);

			$detailFile = fopen("./detailDataN/{$id}.json", "w");
			fwrite($detailFile, json_encode($dtl));
			fclose($detailFile);

			echo "<br>";
		}
	}
	$masterFile = fopen("./masterDataN/masterAppData.json", "w");
	fwrite($masterFile, json_encode($masterData));
	fclose($masterFile);
	
	echo "new apps found: {$counter}";
?>