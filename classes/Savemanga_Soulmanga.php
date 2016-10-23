<?php

class Savemanga_Soulmanga extends Savemanga {
    /*
     * pattern manga: http://soulmanga.net/m/c664/bleach/1/ : 
     * Where "664/bleach" == manga identifier (id)
     * 664 -> capitulo | bleach => serie     
     * "1" = page
     */

    protected $_pattern = "http://soulmanga.net/m/c";
    protected $_domain = "http://soulmanga.net";

    public function getManga($url) {

        $pageContent = $this->file_get_contents_curl($url);

        if (strlen($pageContent)) {

            $this->setMangaID($url);
            $this->setMangaNameAndEp($url);

            $auxId = explode('/', $this->id);
            $auxId = $auxId[0] . "/" . $auxId[1];
            $patternImage = $this->_pattern . $auxId;

            $this->write("<strong>Manga:</strong>" . $this->manga_name . " #" . $this->manga_ep);

            libxml_use_internal_errors(true);
            $dom = DOMDocument::loadHTML($pageContent);
            libxml_clear_errors();
            $xp = new DOMXPath($dom);
            $options = $xp->query("//select[@id='select_paginas']/option");
            foreach ($options as $option) {
                $value = $option->getAttribute('value');
                $links[] = $patternImage . "/" . $value;
            }
            ksort($links);
            $links = array_unique($links);
            $this->write($this->_messages['searching']);
            foreach ($links as $k => $url) {
                /* GETTING IMAGE URLS */
                $pageContent = $this->file_get_contents_curl($url);
                libxml_use_internal_errors(true);
                $dom = DOMDocument::loadHTML($pageContent);
                libxml_clear_errors();
                $xp = new DOMXPath($dom);
                $options = $xp->query("//img[@id='page_img']");
                foreach ($options as $option) {
                    $imgs[$k] = $option->getAttribute('src');
                }
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

    protected function setMangaNameAndEp($url) {
        $aux = explode("/", str_replace($this->_pattern, "", $url));
        $this->manga_ep = (int) $aux[0];
        $auxName = str_replace(array("-", "_"), " ", trim($aux[1]));
        $this->manga_name = str_replace(" ", "_", ucwords(strtolower($auxName)));
        if ($this->manga_ep < 10) {
            $this->manga_ep = "00" . $this->manga_ep;
        } else if ($this->manga_ep < 100) {
            $this->manga_ep = "0" . $this->manga_ep;
        }
        $this->file_manga_name = $this->manga_name . "_" . $this->manga_ep . ".cbr";
        return true;
    }

    public function setMangaID($url) {
        $this->id = str_replace($this->_pattern, "", $url);
    }

    public function getSavedMangas() {
        $files = array_reverse(glob("_files/soulmanga/*/*.cbr", GLOB_MARK));
        foreach ($files as $k => $file) {
            $manga_name = explode("/", $file);
            $aMangas[$manga_name[2]][$k]['name'] = $manga_name[3];
            $aMangas[$manga_name[2]][$k]['url'] = PATH_ABS . $file;
        }
        ksort($aMangas);
        return (isset($aMangas)) ? $aMangas : false;
    }

    protected function zipManga() {
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

    protected function saveImages() {
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
            $this->write("<br/>No se han encontrado imágenes o soulmanga.com ha tardado demasiado en contestar");
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

    public function discoverIds() {
        $url = file_get_contents_curl(processVar('soulmanga_url', null));
        //$reg_exUrl = "/(http)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\d+\/\d+)?/";
        $reg_exUrl = "/http\:\/\/soulmanga\.com\/(\w+|[a-zA-Z0-9-_]+)\/(\d+)\/(\d+)?/";
        preg_match_all($reg_exUrl, $url, $matches);
        $urls = $matches[0];
        if (is_array($urls)) {
            foreach ($urls as $k => $url) {
                $aux = explode("/", $url);
                $discover[$k]['id'] = array_pop($aux);
                $discover[$k]['ep'] = array_pop($aux);
                $discover[$k]['name'] = array_pop($aux);
            }
            return $discover;
        }
        return false;
    }

}
