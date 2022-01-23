<?PHP
header('Content-Type: application/javascript');
session_start();
?>
function getLink(encodedLink, appId)
{
    countAppDownloads(appId);
    //This is a simple obfuscation that uses a session variable from PHP
    //  Please don't try to brute-force download apps -- my bandwidth can't take it
    //  The entire archive will be available at http://appcatalog.webosarchive.com
    var encodeSalt = "<?php echo ($_SESSION['encode_salt']) ?>";
    encodedLink = encodedLink.replace(encodeSalt, "");
    var downloadURL = atob(encodedLink);
    window.open(downloadURL);
}

function countAppDownloads(appId) {
    try {
        var pageParts = window.location.pathname.split("/");
        var lastPage = pageParts[pageParts.length-1];
        var urlParts = window.location.href.split(lastPage);
        var url = urlParts[0] + 'WebService/countAppDownload.php?appid=' + appId + "&source=web";
        
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.send();
    } catch (ex) {
        console.log("Error counting app download: " + ex);
    }
}