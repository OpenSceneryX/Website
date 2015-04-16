<?php

/**
 * Description of OSXItem
 */
class OSXItem {

    public $path;

    public $url;

    public $title = "Undefined";

    function __construct($path, $url) {
        $this->path = $path;
        $this->url = $url;
    }
}
