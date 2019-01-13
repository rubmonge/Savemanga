<?php
require 'classes/Savemanga.php';
require 'classes/Savemanga_Factory.php';
require 'classes/Savemanga_Mangareader.php';
require 'classes/Savemanga_Mangapanda.php';
require 'classes/Savemanga_Narutouchiha.php';
require 'classes/Savemanga_Mangafox.php';
require 'classes/Savemanga_Fanfox.php';
require 'classes/Savemanga_Batoto.php';
require 'classes/Savemanga_Jesulink.php';
require 'classes/Savemanga_Jokerfansub.php';
require 'classes/Savemanga_Submanga.php';
require 'classes/Savemanga_Soulmanga.php';
require 'classes/Savemanga_Sekaimanga.php';
require 'classes/Savemanga_Tumangaonline.php';
require 'classes/Savemanga_Otakusmash.php';
//require 'classes/Savemanga_Readcomics.php';
require 'classes/Savemanga_Readcomic.php';
require 'classes/Savemanga_Viewcomic.php';

header('Content-Type: text/html; charset=utf-8');
$htmlCode = "<style>"
	. "body{"
	. "background-color:black; "
	. "color:green; "
	. "font-size:14px; "
	. "font-family:'Lucida Console', Monaco, monospace;"
	. "}"
	. "</style>";
echo $htmlCode;
$url = filter_input(INPUT_POST, 'url');
if (!strlen(trim($url))) {
	$url = filter_input(INPUT_GET, 'url');
}

if (strlen(trim($url))) {
	$urls = explode("|", $url);
	foreach ($urls as $url) {
		$url = trim($url);
		if (strlen($url)) {
			$object = Savemanga_Factory::getInstanceOf($url);
			$object->path = "mangas/";
			$object->getManga($url);
		}
	}
}
?>