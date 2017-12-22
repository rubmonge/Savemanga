<?php

/**
 * Este fichero forma parte de la librería Savemanga
 * @category   Savemanga
 * @package    Savemanga_Factory
 * @author     Rubén Monge <rubenmonge@gmail.com>
 * @copyright  Copyright (c) 2011-2012 Rubén Monge. (http://www.rubenmonge.es/)
 */
class Savemanga_Factory {

    static public function getInstanceOf($url) {
        $domain = str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
        switch ($domain) {
            case 'mangareader.net':
                $object = new Savemanga_Mangareader();
                break;

            case 'mangapanda.com':
                $object = new Savemanga_Mangapanda();
                break;

            case 'narutouchiha.com':
                $object = new Savemanga_Narutouchiha();
                break;
            case 'batoto.net':
                $object = new Savemanga_Batoto();
                break;

            case 'jesulink.com':
                $object = new Savemanga_Jesulink();
                break;

            case 'mangafox.la':
                $object = new Savemanga_Mangafox();
                break;

            case 'submanga.com':
                $object = new Savemanga_Submanga();
                break;

            case 'tumangaonline.com':
                $object = new Savemanga_Tumangaonline();
                break;

            case 'soulmanga.net':
                $object = new Savemanga_Soulmanga();
                break;
            case 'sekaimanga.net':
                $object = new Savemanga_Sekaimanga();
                break;
            
            case 'reader.jokerfansub.com':
                $object = new Savemanga_Jokerfansub();
                break;
            
            case 'otakusmash.com':
                $object = new Savemanga_Otakusmash();
                break;
            
            case 'readcomics.io':
                $object = new Savemanga_Readcomics();
                break;
        }

        if (is_object($object)) {
            return $object;
        }
        return false;
    }

}
