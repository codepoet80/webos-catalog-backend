<?PHP
header('Content-Type: application/javascript');
session_start();
?>
function getLink(encodedLink)
{
    var encodeSalt = "<?php echo ($_SESSION['encode_salt']) ?>";
    encodedLink = encodedLink.replace(encodeSalt, "");
    var downloadURL = atob(encodedLink);
    window.open(downloadURL);
}