<?php

/**
 * Este fichero forma parte de la librería Savemanga
 * @category   Savemanga
 * @package    Savemanga_Jokerfansub
 * @author     Rubén Monge <rubenmonge@gmail.com>
 * @copyright  Copyright (c) 2011-2012 Rubén Monge. (http://www.rubenmonge.es/)
 */
class Savemanga_Mangadoor extends Savemanga
{
	/*
     * pattern manga: http://mangadoor.com/manga/shuumatsu-no-valkyrie/1/7 : 
     * Where "one_piece" == manga identifier (id)
     * "970" = episode
     * "2" = page
     */

	protected $_pattern = "http://mangadoor.com/manga/";
	protected $_domain  = "http://mangadoor.com/";

	public function getManga($url)
	{
		$this->currentUrl = $url;
		$pageContent = $this->file_get_contents_curl($url);
		$pageContent = preg_replace('#<head(.*?)>(.*?)</head>#is', '', $pageContent);
		$pageContent = preg_replace('#<ul(.*?)>(.*?)</ul>#is', '', $pageContent);
		//$pageContent = mb_convert_encoding($pageContent, "Windows-1252", "UTF-8");
		if (strlen($pageContent)) {

			$this->setMangaID($url);
			$this->setMangaNameAndEp($this->id);

			$auxId        = explode('/', $this->id);
			$auxId        = $auxId[0] . "/" . $auxId[1];
			$patternImage = $this->_pattern . $auxId;


			$this->write("<strong>Manga:</strong>" . $this->manga_name . " #" . $this->manga_ep);
			libxml_use_internal_errors(true);
			$dom     = DOMDocument::loadHTML($pageContent);
			libxml_clear_errors();
			$this->write($this->_messages['searching']);
			$xp      = new DOMXPath($dom);
			$options = $xp->query('//img[@class="img-responsive"]');
			foreach ($options as $k => $option) {
				$imgs[$k] = "http:/" . $option->getAttribute('data-src');
			}

			$this->write($this->_messages['processing']);
			$this->write("[" . count($imgs) . "]");
			$this->write($this->_messages['saving']);
			$this->images = $imgs;
			$this->saveImages();
			$this->write("[" . count($imgs) . "]");
			$this->zipManga();
			$this->renameManga();
			return $this;
		}
		return false;
	}

	public function setMangaID($url)
	{

		$aux      = str_replace($this->_pattern, "", $url);
		$aux      = explode("/", $aux);
		$auxiliar = $aux[0];
		$auxiliar = trim(str_replace(["_", "-"], " ", $auxiliar));
		$auxiliar = ucwords($auxiliar);
		$auxiliar = str_replace(" ", "_", $auxiliar);

		$this->id = $auxiliar . "/" . $aux[1];
	}

	protected function setMangaNameAndEp($id)
	{

		if (strlen(trim($id))) {
			$aux              = explode("/", $id);
			$name             = trim($aux[0]);
			$this->manga_name = $name;
			if (isset($aux[1]) && strpos($aux[1], "_")) {
				$this->manga_ep = $aux[1];
			} else {
				$this->manga_ep = isset($aux[1]) ? intval($aux[1]) : 1;
			}

			if ($this->manga_ep < 10) {
				$this->manga_ep = "00" . $this->manga_ep;
			} else if ($this->manga_ep < 100) {
				$this->manga_ep = "0" . $this->manga_ep;
			}
			$this->file_manga_name = $this->manga_name . "_" . $this->manga_ep . ".cbr";
			return true;
		}

		return false;
	}

	public function getSavedMangas()
	{
		$files = array_reverse(glob($this->path . "*/*.cbr", GLOB_MARK));

		if (is_array($files) && count($files)) {
			foreach ($files as $k => $file) {
				$manga_name                                   = explode("/", $file);
				$name                                         = array_pop($manga_name);
				$key                                          = explode("_", $name);
				$aMangas[$key[0] . "_" . $key[1]][$k]['name'] = $name;
				$aMangas[$key[0] . "_" . $key[1]][$k]['url']  = $file;
			}
			ksort($aMangas);
		}
		return (isset($aMangas)) ? $aMangas : false;
	}

	final protected function zipManga()
	{
		$dest_zip_file = $this->path . $this->manga_ep . ".zip";
		file_put_contents($dest_zip_file, "");
		$zip           = new ZipArchive;
		if ($zip->open($dest_zip_file) === TRUE) {
			foreach (glob($this->path . "*.jpg") as $filename) {
				$destfile = array_pop(explode("/", $filename));
				$zip->addFile($filename, $destfile);
			}

			$result = $zip->close();
			if ($result) {
				foreach (glob($this->path . "*.jpg") as $filename) {
					unlink($filename);
				}
				$this->write("<br/>Ok<br/>");
				return true;
			} else {
				$this->write("<br/>error en compresion - no se han borrado los ficheros");
				return false;
			}
		} else {
			$this->write("<br/>Fallo");
			return false;
		}
	}

	public function renameManga()
	{
		return rename($this->path . $this->manga_ep . ".zip", $this->path . $this->file_manga_name);
	}

	final protected function saveImages()
	{

		$this->path = $this->path . $this->manga_name . "/";
		if (!is_dir($this->path)) {
			mkdir($this->path, 0777);
		}

		if (is_array($this->images)) {
			foreach ($this->images as $k => $imagen) {
				set_time_limit(0);
				$page    = ($k < 10) ? "0" . $k : $k;
				$destino = $this->path . $page . ".jpg";
				if (!$this->saveImage(trim($imagen), $destino)) {
					$this->write("<br/>Petada al guardar la imagen: " . $imagen);
					return false;
				}
				set_time_limit(0);
				if (($k) == count($this->images)) {
					$this->write("[" . ($k) . "]");
				}
			}
			return true;
		} else {
			$this->write("<br/>No se han encontrado imágenes o Jokerfansub.com ha tardado demasiado en contestar");
			return false;
		}
	}

	final protected function saveImage($url, $destino)
	{
		if (!file_exists($destino)) {
			set_time_limit(0);
			$actual = $this->file_get_contents_curl(trim($url), $this->currentUrl);
			if (strlen(trim($actual))) {
				file_put_contents($destino, $actual);
				$this->write($this->_messages['processing']);
				return true;
			}
			return false;
		}
		$this->write($this->_messages['overwritting']);
		return true;
	}
}
