<?php

/**
 * Description of OSXItem
 */
class OSXItem {

    public $path;

    public $url;

    public $title = "Undefined";

    public $ancestors = array();

    protected $fileLines = array();

    function __construct($path, $url) {
        $this->path = $path;
        $this->url = $url;
    }
}
