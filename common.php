<?php
function render_social($link, $basePath) {
	$imgsrc = $basePath. "/social/web.png";
	if (strpos($link, "discord.com") !== false || strpos($link, "webosarchive.com/discord") !== false)
		$imgsrc = $basePath. "/social/discord.png";
	if (strpos($link, "facebook.com") !== false)
		$imgsrc = $basePath. "/social/facebook.png";
	if (strpos($link, "github.com") !== false)
		$imgsrc = $basePath. "/social/github.png";
	if (strpos($link, "instagram.com") !== false)
		$imgsrc = $basePath. "/social/instagram.png";
	if (strpos($link, "linkedin.com") !== false)
		$imgsrc = $basePath. "/social/linkedin.png";
	if (strpos($link, "reddit.com") !== false)
		$imgsrc = $basePath. "/social/reddit.png";
	if (strpos($link, "snapchat.com") !== false)
		$imgsrc = $basePath. "/social/snapchat.png";
	if (strpos($link, "twitter.com") !== false)
		$imgsrc = $basePath. "/social/twitter.png";
	if (strpos($link, "youtube.com") !== false)
		$imgsrc = $basePath. "/social/youtube.png";
	return "<img src='" . $imgsrc . "' class='authorSocial'>";
}
?>