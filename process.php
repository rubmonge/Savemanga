<?php
require 'classes/Savemanga.php';
require 'classes/Savemanga_Factory.php';
require 'classes/Savemanga_Mangareader.php';
require 'classes/Savemanga_Mangapanda.php';
require 'classes/Savemanga_Narutouchiha.php';
require 'classes/Savemanga_Mangafox.php';
require 'classes/Savemanga_Batoto.php';
require 'classes/Savemanga_Jesulink.php';
require 'classes/Savemanga_Jokerfansub.php';
require 'classes/Savemanga_Submanga.php';
require 'classes/Savemanga_Soulmanga.php';
require 'classes/Savemanga_Sekaimanga.php';
require 'classes/Savemanga_Tumangaonline.php';

header('Content-Type: text/html; charset=utf-8');
$htmlCode = "<style>"
        ."body{"
        . "background-color:black; "
        . "color:green; "
        . "font-size:14px; "
        . "font-family:'Lucida Console', Monaco, monospace;"
        . "}"
        . "</style>";
echo $htmlCode;
if (isset($_POST['url'])) {
    $urls = explode("|", $_POST['url']);
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