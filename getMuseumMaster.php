<?PHP
	/*
	 * query inputs:
	 * - device
	 * - search query
	 * - category
	 * - page (page index)
	 * - index (returns a single index (overwrites page and count))
	 * - vendorId
	 * - count (items per page)
	 * - excluded appIds (comma-separated)
	 * - blacklisted vendors (comma-separated)
	 * - ignore blacklist (true or false)
	 * - hideMissing (true or false)
	 * - showOnlyMissing (true or false)
	 *
	 * - useAppId (true or false)
	 * - appId list (comma-separated)	// only used with 'useAppId' and overwrites everything else
	 *
	 * query outputs:
	 * - array of masterdata (with the key being their original index number)
	 */

	/* >> NOTE: I use $_REQUEST so I can use both GET and POST (although the app
     * >>       uses POST). This isn't a security risk in this particular case,
     * >>       and it makes debugging a bit easier.
	 */

	function gMM_startOutputBuffer() {
		ob_start();					// Buffer all upcoming output...
		ob_start('ob_gzhandler');	// ...and make sure it will be compressed with either gzip or deflate if accepted by the client
	}
	function gMM_endOutputBuffer() {
		ob_end_flush();						// Flush the gzippend buffer (see: http://php.net/manual/en/function.ob-get-length.php#59294)

		$size = ob_get_length();			// get the size of our output
		header("Content-Encoding: gzip");	// ensure compression
		header("Content-Length:{$size}");	// set the content length of the response
		header("Connection: close");		// close the connection

		ob_end_flush();						// Flush all output
		ob_flush();
		flush();
	}
	function getClientRetrievedDataFromKey($key) {
		if (!is_dir("__museumSessions")) {
			mkdir("__museumSessions", 0777, true);
		}
		if (!file_exists("__museumSessions/{$key}.json")) {
			$_sessionData = array(
				"knownIdx" => array()
			);
		} else {
			$myfile  = fopen("./__museumSessions/{$key}.json", "r");
			$session = fread($myfile,filesize("./__museumSessions/{$key}.json"));
			fclose($myfile);

			$_sessionData = json_decode($session, true);
			if (!isset($_sessionData['knownIdx'])) {
				$_sessionData['knownIdx'] = array();
			}
		}
		return $_sessionData;
	}
	function removeOldClientKeys() {
		if (!is_dir("__museumSessions")) {
			return;
		}
		// from: https://stackoverflow.com/questions/8965778/the-correct-way-to-delete-all-files-older-than-2-days-in-php
		$files = glob("__museumSessions/*");
		$now   = time();

		foreach ($files as $file) {
			if (is_file($file)) {
				if ($now - filemtime($file) >= 60 * 60 * 24 * 2) { // 2 days
					unlink($file);
				}
			}
		}
	}
	function storeClientRetrievedDataByKey($key, $data) {
		if (!is_dir("__museumSessions")) {
			mkdir("__museumSessions", 0777, true);
		}
		$myfile = fopen("./__museumSessions/{$key}.json", "w");
		fwrite($myfile, json_encode($data));
		fclose($myfile);
	}

	$_key = @$_REQUEST['key'];
	if (!isset($_key) || (isset($_REQUEST['page']) && $_REQUEST['page'] < 0)) {
		gMM_startOutputBuffer();
		echo(json_encode(array(
			"indices" => array(),
			"data"    => array()
		)));
		gMM_endOutputBuffer();

		removeOldClientKeys();
		die();
	}

	mb_internal_encoding("UTF-8");//Sets the internal character encoding to UTF-8, for mb_substr to work
	$_sessionData = getClientRetrievedDataFromKey($_key);

	$_device      = 'All'; if (isset($_REQUEST['device'])) 				{$_device = $_REQUEST['device'];}
	$_category    = 'All'; if (isset($_REQUEST['category'])) 			{$_category = $_REQUEST['category'];}
	$_query       = '';	   if (isset($_REQUEST['query'])) 				{$_query = $_REQUEST['query'];}
	$_page        = 0;     if (isset($_REQUEST['page'])) 				{$_page = $_REQUEST['page'];}
	$_index       = null;  if (isset($_REQUEST['index'])) 				{$_index = $_REQUEST['index'];}
	$_vendorId    = null;  if (isset($_REQUEST['vendorId'])) 			{$_vendorId = $_REQUEST['vendorId'];}
	$_count       = 20;    if (isset($_REQUEST['count'])) 				{$_count = $_REQUEST['count'];}
	$_exclAppIds  = '';    if (isset($_REQUEST['excluded_appIds'])) 	{$_exclAppIds = $_REQUEST['excluded_appIds'];}
	$_useAppId    = false; if (isset($_REQUEST['useAppId'])) 			{$_useAppId = $_REQUEST['useAppId'];}
	$_appIdList   = '';    if (isset($_REQUEST['appIds'])) 				{$_appIdList = $_REQUEST['appIds'];}
	$_blacklisted = '';    if (isset($_REQUEST['blacklist'])) 			{$_blacklisted = $_REQUEST['blacklist'];}
	$_ignoreBL    = false; if (isset($_REQUEST['ignore_blacklist'])) 	{$_ignoreBL = $_REQUEST['ignore_blacklist'];}
	$_hideMissing = false; if (isset($_REQUEST['hide_missing'])) 		{$_hideMissing = $_REQUEST['hide_missing'];}
	$_showOnlyMis = false; if (isset($_REQUEST['show_only_missing'])) 	{$_showOnlyMis = $_REQUEST['show_only_missing'];}
	
	if (gettype($_useAppId    === "string")) {$_useAppId    = strtolower($_useAppId)    === "true" ? true : false;}
	if (gettype($_ignoreBL    === "string")) {$_ignoreBL    = strtolower($_ignoreBL)    === "true" ? true : false;}
	if (gettype($_showOnlyMis === "string")) {$_showOnlyMis = strtolower($_showOnlyMis) === "true" ? true : false;}
	if (gettype($_hideMissing === "string")) {$_hideMissing = strtolower($_hideMissing) === "true" ? true : false;}
	
	$_exclAppIds =  explode(",", $_exclAppIds);
	$_appIdList =   explode(",", $_appIdList);
	$_blacklisted = explode(",", $_blacklisted);
	
	$_device = !is_null($_vendorId) || empty($_vendorId) ? $_device : "All";
	$_category = !is_null($_vendorId) || empty($_vendorId) ? $_category : "All";
	
	if ($_showOnlyMis) {
		$_hideMissing = false;
	}

	$extraData = array();

	$myfile  = fopen("masterAppData.json", "r");
	$masterdata = json_decode(fread($myfile,filesize("masterAppData.json")), true);
	fclose($myfile);
	
	$myfile  = fopen("missingApps.json", "r");
	$missing = json_decode(fread($myfile,filesize("missingApps.json")), true);
	fclose($myfile);
	
	$output         = array();
	$indices        = array();
	$return_indices = array();
	$firstPos       = array();
	
	$appCount       = array(
							"All"=>0,
							"Missing Apps"=>0
							);

	foreach($masterdata as $key => $app) {
		if ($_hideMissing  && (isset($missing[$app['id']]) && $missing[$app['id']] == 0)) {
			$appCount['Missing Apps'] ++;
			continue;
		}
		if ($_showOnlyMis && (!isset($missing[$app['id']]) || (isset($missing[$app['id']]) && $missing[$app['id']] != 0))) {
			continue;
		}
		if (!$_ignoreBL && $_blacklisted[0] !== "" && in_array($app['vendorId'],$_blacklisted)) {
			continue;
		}
		$validDevice  = ($_device === 'All'   || $app[$_device] === true);
		$category     = ($_category === 'All'|| $app['category'] === $_category);
		$vendorId     = !is_null($_vendorId);
		$titleFound   = empty($_query) || strpos(strtolower($app['title']), strtolower($_query)) !== false;
		$authorFound  = empty($_query) || strpos(strtolower($app['author']), strtolower($_query)) !== false;
		$summaryFound = empty($_query) || strpos(strtolower($app['summary']), strtolower($_query)) !== false;
		if ($vendorId) {
			if ($app['vendorId'] === $_vendorId) {
				array_push($indices, $key);
			}			
		} else {
			if ($validDevice && $category && ($titleFound || $authorFound || $summaryFound)) {
				array_push($indices, $key);
			}
		}
		if ($validDevice && ($titleFound || $authorFound || $summaryFound)) {
			// for our app count we need to exclude the category-filter (it makes little sense to set 'games' as a filter only to show
			// there are no apps in any other category that match that criteria. For the other filters, however, it makes sense to keep them.
			$appCount["All"] ++;
			if (isset($appCount[$app['category']])) {
				$appCount[$app['category']] ++;
			} else {
				$appCount[$app['category']] = 1;
			}
			if (isset($missing[$app['id']]) && $missing[$app['id']] === 0) {
				$appCount['Missing Apps'] ++;
			}
		}
	}
	$_random   = false;
	if (count($_appIdList) === 1 &&  $_appIdList[0] === "random") {
		$_useAppId = false;
		$_random   = true;

		$_index = array_rand($indices);
		$extraData['randomOffset'] = $_index;
	}
	
	if (!is_null($_vendorId)) {	// Note: when we request all app from a vendor, we get all of them in one go.
		$page = 0;
		$count = count($indices);
		$_count = $count;
	}
	
	$top = $_page * $_count;
	if (!is_null($_index)) {
		$top   = $_index;
		if (!$_random) {
			$_count = 1;			
		}
	};

	switch($_useAppId) {
		case true:
			$count = 0;
			$lastChar = "";
			$biggerThanZ = false;
			foreach($masterdata as $key => $app) {
				if (in_array($app['id'], $_appIdList)) {
					if (isset($missing[$appId])) {
						$app['archived'] = false;
						if ($missing[$appId] == 1) {
							$app['_archived'] = true;	// partial (local) archive exists
						}
					}
					array_push($output, $app);
					array_push($indices, $key);
					array_push($return_indices, $count);

					$firstLetter = mb_strtoupper(mb_substr($app['title'], 0, 1));
					if ($firstLetter !== $lastChar) {
						if ($firstLetter < "A") {
							$firstPos["#"] = 0;
						} else {
							if ($firstLetter > "Z" && !isset($firstPost["%"])) {
								$firstPos["%"] = $key;
							} else {
								if ($firstLetter <= "Z" || ($firstLetter > "Z" && !$biggerThanZ)) {
									$lastChar = $firstLetter;
									$firstPos[$firstLetter] = $key;
									$biggerThanZ = $firstLetter > "Z";
								}
							}
						}
					}
					$count++;
				}
			}
			$extraData['listCount'] = count($_appIdList);
			break;
		default:
			$lastChar = "";
			$biggerThanZ = false;
			
			$bottom = $top+$_count;
			$temp   = array();

			for ($i = $top; $i<($bottom); $i++) {
				if(!isset($indices[$i])) {break;}
				if ($_random || !in_array($indices[$i], $_sessionData['knownIdx'])) {
					// we only return the full data if the client doesn't know it yet
					$appId = $masterdata[$indices[$i]]['id'];
					$masterdata[$indices[$i]]['archived'] = true;
					$masterdata[$indices[$i]]['_archived'] = false;
					if (isset($missing[$appId])) {
						$masterdata[$indices[$i]]['archived'] = false;
						if ($missing[$appId] == 1) {
							$masterdata[$indices[$i]]['_archived'] = true;	// partial (local) archive exists
						} else {
							$appCount['Missing Apps'] ++;
						}
					}
					if ($_random && $i === $_index ) {
						include_once("getMuseumDetails.php");	// NOTE: I don't like includes in the middle of my code.
						// But in this case this is the only place I want to get the details,
						// so I keep it together with the function call.
						$masterdata[$indices[$i]]['detail'] = getDetailData($masterdata[$indices[$i]]['id']);
					}
					array_push($output, $masterdata[$indices[$i]]);
				} else {
					array_push($output, null);	// we will return null if we already know this app
				}
				// but we will always return the indices to make sure the client knows
				// what data to display
				array_push($return_indices, $i);
				array_push($temp, $indices[$i]);
			}
			
			$extraData['listCount'] = count($indices);
				for ($i = 0; $i<count($indices); $i++) {
					$app = $masterdata[$indices[$i]];
					$firstLetter = mb_strtoupper(mb_substr($app['title'], 0, 1));
					if ($firstLetter !== $lastChar) {
						if ($firstLetter < "A") {
							$firstPos["#"] = 0;
						} else {
							if ($firstLetter <= "Z" || ($firstLetter > "Z" && !$biggerThanZ)) {
								$lastChar = $firstLetter;
								$firstPos[$firstLetter] = $i;
								$biggerThanZ = $firstLetter > "Z";
							}
						}
					}
				}
				$indices = $temp;
			break;
	}
	
	
	$data = array(
		"return_indices" => $return_indices,
		"indices"        => $indices,
		"data"           => $output,
		"first_position" => $firstPos,
		"request"        => $_REQUEST,
		"extraData"		 => $extraData,
		"appCount"       => $appCount
	);

	gMM_startOutputBuffer();
		echo(json_encode($data));
	gMM_endOutputBuffer();

	$_sessionData = getClientRetrievedDataFromKey($_key);
		foreach ($data['indices'] as $k => $idx) {
		if (!in_array($idx, $_sessionData['knownIdx'])) {
			array_push($_sessionData['knownIdx'], $idx);
		}
	}
	sort($_sessionData['knownIdx']);

	storeClientRetrievedDataByKey($_key, $_sessionData);
	removeOldClientKeys();
?>