<?php

class Savemanga_Readcomic extends Savemanga
{
    

protected $_pattern = "https://read-comic.com/oblivion-song-007-2018/";
protected $_patternImg ="https://3.bp.blogspot.com/-de3eIC3iJD4/W5x9_c9nhHI/AAAAAAACrVk/EDZp-4p15igkeRe5z3ZfYoyGm2cjJ-bwwCLcBGAs/s1600/073_003.jpg";
protected $_domain = "https://read-comic.com";

    public function getManga($url)
    {
        $dom = $this->getDom($url);


        if ($dom) {
            $this->setMangaNameAndEp($dom);
            $this->write("<strong>Manga:</strong>" . $this->manga_name . " #" . $this->manga_ep);

			
			$this->write($this->_messages['searching']);
$aux = $dom->query('//div[contains(@class,"pinbin-copy")]/*/*/img');
            foreach ($aux as  $imgUrl) {
                $imgs[] = $imgUrl->getAttribute('src');
                $this->write($this->_messages['processing']);
			}            
$aux = $dom->query('//div[contains(@class,"pinbin-copy")]/*/img');
foreach ($aux as  $imgUrl) {
$imgs[] = $imgUrl->getAttribute('src');
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
		$aux = $dom->query('//div[contains(@class,"pinbin-category")]/p/a');
		$this->manga_name = trim($aux[0]->nodeValue);

        $aux            = $dom->query('//h1');
        $ep             = $aux[0]->nodeValue;
        $ep             = explode("(", $ep);        
        $this->manga_ep =(int) trim(str_replace($this->manga_name,"", $ep[0]));

        if ($this->manga_ep < 10) {
            $this->manga_ep = "00" . $this->manga_ep;
        } else if ($this->manga_ep < 100) {
            $this->manga_ep = "0" . $this->manga_ep;
		}
		$this->manga_name=str_replace(" ","_",ucwords($this->manga_name));
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