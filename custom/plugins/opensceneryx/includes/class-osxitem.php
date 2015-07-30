<?php

/**
 * Description of OSXItem
 */
abstract class OSXItem {

    public $path;

    public $url;

    public $title = "Undefined";

    public $ancestors = array();

    protected $fileLines = array();

    function __construct($path, $url) {
        $this->path = $path;
        $this->url = $url;
        
        $this->buildAncestors();
    }
    
    private function buildAncestors()
    {
        $path = $this->path;
        $url = $this->url;
        
        while (strlen($path) > strlen(ABSPATH)) {
            $path = dirname($path);
            $url = dirname($url);
            
            if (is_file($path . '/category.txt')) {
                array_unshift($this->ancestors, new OSXCategory($path, $url));
            }
        }
    }
    
    abstract protected function parse();
}
