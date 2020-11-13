<?PHP
	if ($handle = opendir('./detailDataM')) {
		while (false !== ($entry = readdir($handle))) {
			set_time_limit(300);		   // set our excecution time limit
			if ($entry != "." && $entry != "..") {
				$myfile  = fopen("./detailDataM/".$entry, "r");
				$app = json_decode(fread($myfile,filesize("./detailDataM/".$entry)), true);
				fclose($myfile);
				$myfile  = fopen("./imageData/".$entry, "r");
				$imageList = json_decode(fread($myfile,filesize("./imageData/".$entry)), true);
				fclose($myfile);
				
				foreach($imageList as $key => $item) {
					$imageList[$key]['screenshot'] = str_replace("http://cdn.downloads.palm.com/public/", "", $imageList[$key]['screenshot']);
					$imageList[$key]['thumbnail']  = str_replace("http://cdn.downloads.palm.com/public/", "", $imageList[$key]['thumbnail']);
				}
				
				$app['images'] = $imageList;
				
				$myfile = fopen("./detailDataMFinal/".$entry, "w");
				fwrite($myfile, json_encode($app));
				fclose($myfile);
			}
		}
		closedir($handle);
	}

?>
