<?php

/**
 * Este fichero forma parte de la librería Savemanga
 * @category   Savemanga
 * @package    Savemanga_Narutouchiha
 * @author     Rubén Monge <rubenmonge@gmail.com>
 * @copyright  Copyright (c) 2011-2012 Rubén Monge. (http://www.rubenmonge.es/)
 */
class Savemanga_Narutouchiha extends Savemanga
{
    /*
     * pattern manga: http://narutouchiha.com/manga/reader/read/naruto/es/0/640/page/1 : 
     * Where "naruto" == manga identifier (id)
     * "640" = episode
     * "1" = page
     */

    public function getManga($url)
    {        
        $pageContent = $this->file_get_contents_curl($url);        
        if (strlen($pageContent)) {
            $this->setMangaID($url);
            $this->setMangaNameAndEp($this->id);
            $this->write("<strong>Manga:</strong>" . $this->manga_name . " #" . $this->manga_ep);
            libxml_use_internal_errors(true);
            $dom     = DOMDocument::loadHTML($pageContent);
            libxml_clear_errors();
            
            $options = $dom->getElementsByTagName('a');
            
            foreach ($options as $option) {
                
                $value   = $option->getAttribute('href');
                $num = count(explode('/',$value));				
                if ($num==12 && strpos($value,"page")){
                    $links[] = $value;
                }
                
            }
			

            ksort($links);
            $links = array_unique($links);			
            
            $this->write($this->_messages['searching']);
            foreach ($links as $k => $url) {
			
                /* GETTING IMAGE URLS */
                $url       = $this->file_get_contents_curl($url);       

                $imgpatter = "/<img class=\"open\" (.*)/";                 
                preg_match_all($imgpatter, $url, $matches);
                $thing     = explode("src", $matches[0][0]);
                $parts     = explode("\"", $thing[1]);
				
				
                $imgs[$k]  = $parts[1];
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

    public function setMangaID($url)
    {
	
        $aux      = str_replace("http://narutouchiha.com/manga/reader/read/", "", $url);
        $this->id = $aux;
    }

    final protected function setMangaNameAndEp($id)
    {			
        if (strlen(trim($id))) {
            $aux              = explode("/", $id);
            $name             = trim($aux[0]);
            $name             = str_replace(" ", "_", ucwords(strtolower(str_replace("-", " ", $name))));
            $this->manga_name = $name;


            $this->manga_ep = $aux[3];

            if ($this->manga_ep < 10) {
                $this->manga_ep = "00" . $this->manga_ep;
            } else if ($this->manga_ep < 100) {
                $this->manga_ep        = "0" . $this->manga_ep;
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
            $this->write("<br/>No se han encontrado imágenes o Narutouchiha.com ha tardado demasiado en contestar");
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