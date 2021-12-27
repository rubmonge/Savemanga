<?php

/**
 * Este fichero forma parte de la librería Savemanga
 * @category   Savemanga
 * @package    Savemanga
 * @author     Rubén Monge <rubenmonge@gmail.com>
 * @copyright  Copyright (c) 2011-2012 Rubén Monge. (http://www.rubenmonge.es/)
 */
abstract class Savemanga
{

	public $path;
	public $file_manga_name;
	protected $id;
	protected $manga_ep;
	protected $manga_name;
	protected $images    = array();
	protected $_messages = array(
		"searching"     => "\nSearching:",
		"saving"        => "\nSaving:",
		"processing"    => "[]",
		"overwritting"  => "[!]",
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

	public function file_get_contents_curl($url, $referer = null, $compress = null)
	{


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		//Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $url);
		//If referer not null.
		if ($referer !== null) {
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
		if ($compress !== null) {
			curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		}
		$data = curl_exec($ch);
		if (curl_errno($ch)) {
			$info = curl_getinfo($ch);
			$this->ver($info);
			echo 'Se tardó ', $info['total_time'], ' segundos en enviar una petición a ', $info['url'], "\n";
		}

		curl_close($ch);
		$data = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $data);
		return $data;
	}

	public function getDom($url, $referer = null, $postParams = null)
	{

		$this->pageContent = $this->file_get_contents_curl($url, $referer, $postParams);

		//$this->pageContent = mb_convert_encoding($this->pageContent, "Windows-1252", "UTF-8");

		libxml_use_internal_errors(true);
		$replaces  = [
			'#<script(.*?)>(.*?)</script>#is',
			'#<style(.*?)>(.*?)</style>#is'
		];
		$dom       = DOMDocument::loadHTML(preg_replace($replaces, '', $this->pageContent));
		libxml_clear_errors();
		$xp        = new DOMXPath($dom);
		$this->dom = $xp;
		return $xp;
	}

	protected function write($text)
	{
		echo $text;
		flush();
		ob_flush();
		usleep(1);
	}

	protected function ver($var)
	{
		echo "<pre>";
		var_dump($var);
		echo "</pre>";
	}
}
