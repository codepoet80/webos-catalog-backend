<?PHP
	$id = $_GET['id'];

	$myfile  = fopen("{$id}.json", "r");
	$content = fread($myfile,filesize("{$id}.json"));
	fclose($myfile);

	echo($content);
?>