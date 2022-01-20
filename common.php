<?php
function render_social($link) {
	$imgsrc = "../icons/web.png";
	if (strpos($link, "discord.com") !== false || strpos($link, "webosarchive.com/discord") !== false)
		$imgsrc = "../icons/discord.png";
	if (strpos($link, "facebook.com") !== false)
		$imgsrc = "../icons/facebook.png";
	if (strpos($link, "github.com") !== false)
		$imgsrc = "../icons/github.png";
	if (strpos($link, "instagram.com") !== false)
		$imgsrc = "../icons/instagram.png";
	if (strpos($link, "linkedin.com") !== false)
		$imgsrc = "../icons/linkedin.png";
	if (strpos($link, "reddit.com") !== false)
		$imgsrc = "../icons/reddit.png";
	if (strpos($link, "snapchat.com") !== false)
		$imgsrc = "../icons/snapchat.png";
	if (strpos($link, "twitter.com") !== false)
		$imgsrc = "../icons/twitter.png";
	if (strpos($link, "youtube.com") !== false)
		$imgsrc = "../icons/youtube.png";
	return "<img src='" . $imgsrc . "' class='authorSocial'>";
}
?>