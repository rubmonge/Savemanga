<?php

class Savemanga_Tumangaonline extends Savemanga {
    /*
     * pattern manga: http://www.tumangaonline.com/visor/One%20Piece/45/756/74/3 : 
     * Where "One%20Piece/45/756/74" == manga identifier (id)     
     * "3" = page
     */

    protected $_pattern = "http://www.tumangaonline.com/#!/lector/";
    protected $_patternImg = "http://img1.tumangaonline.com/subidas/";
    protected $_domain = "http://www.tumangaonline.com";

    public function getManga($url) {
        $pageContent = $this->file_get_contents_curl($url);
var_dump($pageContent);die;
        if (strlen($pageContent)) {

            $this->setMangaID($url);
            $this->setMangaNameAndEp($this->id);
            $auxId = explode("/", $this->id);
            array_shift($auxId);
            $auxId = implode("/", $auxId);
            $patternImage = $this->_patternImg . $auxId . "/";

            $this->write("<strong>Manga:</strong>" . $this->manga_name . " #" . $this->manga_ep);

            libxml_use_internal_errors(true);
            $dom = DOMDocument::loadHTML($pageContent);
            libxml_clear_errors();
            $xp = new DOMXPath($dom);
            $options = $xp->query('//input[@hidden="true"]');
			
			
            foreach ($options as $option) {
                $value = $option->getAttribute('value');
				if (strlen(trim($value))>20) {
					break;
				}
            }			
			
            $values = explode(";", $value);
            $imagesNames = explode("%", $values[3]);

            $this->write($this->_messages['searching']);
            foreach ($imagesNames as $k => $value) {
                $imgs[$k] = $patternImage . str_replace(" ", "%20", $value);
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

    public function setMangaID($url) {

        $aux = str_replace($this->_pattern, "", $url);
        $aux = explode("/", $aux);
        array_pop($aux);
        $this->id = implode("/", $aux);
    }

    protected function setMangaNameAndEp($id) {

        if (strlen(trim($id))) {
            $aux = explode("/", $id);

            $name = trim($aux[0]);
            $name = str_replace(" ", "_", ucwords(strtolower(str_replace(array(" ", "%20", "-", "+"), " ", $name))));
            $this->manga_name = $name;
            $this->manga_ep = isset($aux[2]) ? floatval($aux[2]) : 1;

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

    public function getSavedMangas() {
        $files = array_reverse(glob($this->path . "*/*.cbr", GLOB_MARK));

        if (is_array($files) && count($files)) {
            foreach ($files as $k => $file) {
                $manga_name = explode("/", $file);
                $name = array_pop($manga_name);
                $key = explode("_", $name);
                $aMangas[$key[0] . "_" . $key[1]][$k]['name'] = $name;
                $aMangas[$key[0] . "_" . $key[1]][$k]['url'] = $file;
            }
            ksort($aMangas);
        }
        return (isset($aMangas)) ? $aMangas : false;
    }

    final protected function zipManga() {
        $dest_zip_file = $this->path . $this->manga_ep . ".zip";
        file_put_contents($dest_zip_file, "");
        $zip = new ZipArchive;
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

    public function renameManga() {
        return rename($this->path . $this->manga_ep . ".zip", $this->path . $this->file_manga_name);
    }

    final protected function saveImages() {

        $this->path = $this->path . $this->manga_name . "/";
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777);
        }

        if (is_array($this->images)) {
            foreach ($this->images as $k => $imagen) {
                set_time_limit(0);
                $page = ($k < 10) ? "0" . $k : $k;
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
            $this->write("<br/>No se han encontrado imágenes o Jesulink.com ha tardado demasiado en contestar");
            return false;
        }
    }

    final protected function saveImage($url, $destino) {
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
