<?PHP
$config = include('config.php');
header('Content-Type: application/json');
echo(json_encode($config));
?>