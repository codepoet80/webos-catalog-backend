<?PHP
	header('Content-Type: application/json');

	$id = @$_GET['id'];
	
	function gMD_startOutputBuffer() {
		ob_start();					// Buffer all upcoming output...
		ob_start('ob_gzhandler');	// ...and make sure it will be compressed with either gzip or deflate if accepted by the client
	}
	function gMD_endOutputBuffer() {
		ob_end_flush();						// Flush the gzippend buffer (see: http://php.net/manual/en/function.ob-get-length.php#59294)
	
		$size = ob_get_length();			// get the size of our output
		header("Content-Encoding: gzip");	// ensure compression
		header("Content-Length:{$size}");	// set the content length of the response
		header("Connection: close");		// close the connection
	
		ob_end_flush();						// Flush all output
		ob_flush();
		flush();
	}
	
	function getDetailData($host, $myIdx) {
		if (!isset($myIdx)) {$myIdx = $id;}
		//Get the JSON file over HTTP to this same server, to allow Virtual Directory support
		$mypath = "http://{$host}/AppMetadata/{$myIdx}.json";
		$myfile  = fopen($mypath, "rb");
		$content = stream_get_contents($myfile);
		fclose($myfile);

		if(!isset($_REQUEST['appIds']) || $_REQUEST['appIds'] !== "random") {
			gMD_startOutputBuffer();
				echo($content);
			gMD_endOutputBuffer();
		} else {
			return json_decode($content, true);
		}
	}

	if(!isset($_REQUEST['appIds']) || $_REQUEST['appIds'] !== "random") {
		$config = include('config.php');
		getDetailData($config["package_host"], $id);
	}
?>
