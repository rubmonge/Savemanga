<?php

class Savemanga_Followmanga extends Savemanga
{
	/*
     * pattern manga: https://followmanga.com/noticia/5d565847b0e01/cascade/1 : 
     * Where "5c0a35b83fa33" == manga identifier (id)     
     * "2" = page
	 * https://img1.followmanga.com/uploads/5af6d430b9262/00_5a90867ef1151.jpg
     */

	protected $_pattern    = "https://followmanga.com/noticia/";
	protected $_patternImg = "https://img1.followmanga.com/uploads/";
	protected $_domain     = "https://followmanga.com";

	public function getManga($url)
	{
		$dom = $this->getDom($url, $this->_domain);
		
		if ($dom) {
			$this->setMangaNameAndEp($dom);

			$this->write("<strong>Manga:</strong>" . $this->manga_name . " #" . $this->manga_ep);

			$aux = $dom->query('//img[contains(@class,"viewer-image")]');

			$this->write($this->_messages['searching']);
			foreach ($aux as $k => $imgUrl) {
				$imgs[$k] = $imgUrl->getAttribute('src');
				$this->write($this->_messages['processing']);
			}

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

	protected function setMangaID($url)
	{
		return false;
	}

	protected function setMangaNameAndEp($dom)
	{
		$aux              = $dom->query('//h1');
		$this->manga_name = str_replace([" ", ":"], ["_", ""], ucwords(trim($aux[0]->nodeValue)));
		$aux            = $dom->query('//h2');
		$this->manga_ep = preg_replace('/[^0-9\.]/', '', $aux[0]->nodeValue);
		if ($this->manga_ep < 10) {
			$this->manga_ep = "00" . $this->manga_ep;
		} else if ($this->manga_ep < 100) {
			$this->manga_ep = "0" . $this->manga_ep;
		}

		$this->file_manga_name = $this->manga_name . "_" . $this->manga_ep . ".cbr";

		return true;
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
				if (!$this->saveImage($imagen, $destino)) {
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
			$this->write("<br/>No se han encontrado imágenes o la web ha tardado demasiado en contestar");
			return false;
		}
	}

	final protected function saveImage($url, $destino)
	{
		if (!file_exists($destino)) {
			set_time_limit(0);
			$actual = $this->file_get_contents_curl($url);
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
