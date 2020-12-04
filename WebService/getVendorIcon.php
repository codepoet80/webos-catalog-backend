<?PHP
	$url = $_GET["url"];
	$desiredSize = 32;	
	if (!isset($url)) {
		echo "";
		die();
	}
	
	$ch  =  curl_init   (); 
		    curl_setopt ($ch, CURLOPT_URL, $url); 
		    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		    curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
    $page = curl_exec   ($ch); 
            curl_close  ($ch);   

	$re   = '/(<link).*?icon.*?(>)/i';
	preg_match_all($re, $page, $icons, PREG_SET_ORDER, 0);
	$diff     = 100000;
	$favicon  = "";
	$myUrl    = explode(".", $url);
	if (count($myUrl) > 2) {
		array_shift($myUrl) ;
	}
	$myUrl    = implode(".", $myUrl);
	foreach($icons as $icn) {
		$re = '/sizes="?\'?(\d*)/';
		preg_match($re, $icn[0], $size);

		if (!empty($size[0])) {
			$s = $size[1];
			if (abs($s-$desiredSize) < $diff) {
				$diff = abs($s-$desiredSize);
				$re = '/href="(.*)"/U';
				preg_match($re, $icn[0], $icon);

				$linker = "";
				$home = "";
				if (strpos($icon[1], $myUrl) === false) {
					$home = $url;
					if ($icon[1][0] != "/") {
						$linker = "/";
					}
				}
				if (strpos($icon[1], "http") === 0) {
					$home   = "";
					$linker = "";
				}
				$bestIcon = $home . $linker . $icon[1];
			}
		} else {
			if (strpos($icn[0], ".ico") != false || strpos($icn[0], ".png") != false) {
				$re = '/href="(.*)"/U';
				preg_match($re, $icn[0], $icon);
				$linker = "";
				$home = "";
				if (isset($icon[1])) {
					if (strpos($icon[1], $myUrl) === false) {
						$home = $url;
						if ($icon[1][0] != "/") {
							$linker = "/";
						}
					}
					if (strpos($icon[1], "http") === 0) {
						$home   = "";
						$linker = "";
					}
					$favicon = $home . $linker . $icon[1];
				}
			}
		}
	}
	if (!isset($bestIcon)) {
		$bestIcon = $favicon;
	}
	
	header("Location: $bestIcon", true, 301);
?>