<?PHP

	if ($handle = opendir('/webOS App Backup Full/Catalog Pull/')) {
		while (false !== ($entry = readdir($handle))) {
			set_time_limit(300);		   // set our excecution time limit
			if ($entry != "." && $entry != "..") {
				$id = explode("--", $entry);
				$app = $id[1];
				$id = $id[0];
				
				$exists = file_exists("./detailData/{$id}.json");
				if (!$exists) {
					echo "{$id}  ... {$app}<br>";
					flush(); ob_flush();usleep(10000);
				}
			}
		}
		closedir($handle);
	}


?>