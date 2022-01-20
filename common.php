<?php
function render_social($link) {
	$imgsrc = "/social/web.png";
	if (strpos($link, "discord.com") !== false || strpos($link, "webosarchive.com/discord") !== false)
		$imgsrc = "/social/discord.png";
	if (strpos($link, "facebook.com") !== false)
		$imgsrc = "/social/facebook.png";
	if (strpos($link, "github.com") !== false)
		$imgsrc = "/social/github.png";
	if (strpos($link, "instagram.com") !== false)
		$imgsrc = "/social/instagram.png";
	if (strpos($link, "linkedin.com") !== false)
		$imgsrc = "/social/linkedin.png";
	if (strpos($link, "reddit.com") !== false)
		$imgsrc = "/social/reddit.png";
	if (strpos($link, "snapchat.com") !== false)
		$imgsrc = "/social/snapchat.png";
	if (strpos($link, "twitter.com") !== false)
		$imgsrc = "/social/twitter.png";
	if (strpos($link, "youtube.com") !== false)
		$imgsrc = "/social/youtube.png";
	return "<img src='" . $imgsrc . "' class='authorSocial'>";
}
?>