<?PHP
header('Content-Type: application/javascript');
session_start();
?>
function getLink(encodedLink)
{
    //This is a simple obfuscation that uses a session variable from PHP
    //  Please don't try to brute-force download apps -- my bandwidth can't take it
    //  The entire archive will be available at http://appcatalog.webosarchive.com
    var encodeSalt = "<?php echo ($_SESSION['encode_salt']) ?>";
    encodedLink = encodedLink.replace(encodeSalt, "");
    var downloadURL = atob(encodedLink);
    window.open(downloadURL);
}