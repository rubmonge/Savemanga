<?php

/**
 * Este fichero forma parte de la librería Savemanga
 * @category   Savemanga
 * @package    Savemanga
 * @author     Rubén Monge <rubenmonge@gmail.com>
 * @copyright  Copyright (c) 2011-2012 Rubén Monge. (http://www.rubenmonge.es/)
 */
abstract class Savemanga {

    public $path;
    public $file_manga_name;
    protected $id;
    protected $manga_ep;
    protected $manga_name;
    protected $images = array();
    protected $_messages = array(
        "searching" => "\nSearching:",
        "saving" => "\nSaving:",
        "processing" => "[]",
        "overwritting" => "[!]",
        "connect_error" => "\nUnable to connect to:"
    );

    abstract public function getManga($url);

    abstract public function renameManga();

    abstract public function getSavedMangas();

    abstract protected function setMangaNameAndEp($url);

    abstract protected function setMangaID($url);

    abstract protected function saveImage($url, $destiny);

    abstract protected function saveImages();

    abstract protected function zipManga();

    public function file_get_contents_curl($url, $referer = null) {
        /*
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_HEADER, 0);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
          curl_setopt($ch, CURLOPT_URL, $url);
          $data = curl_exec($ch);
          curl_close($ch);


         */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20100101 Firefox/21.0');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        //Set curl to return the data instead of printing it to the browser.
        curl_setopt($ch, CURLOPT_URL, $url);
        //If referer not null.
        if ($referer !== null) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        $data = curl_exec($ch);
        curl_close($ch);


        return $data;
    }

    protected function write($text) {
        echo $text;
        flush();
        ob_flush();
        usleep(1);
    }

    protected function ver($var) {
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
    }

}
