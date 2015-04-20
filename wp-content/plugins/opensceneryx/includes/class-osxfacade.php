<?php

/**
 * Description of OSXObject
 */
class OSXFacade extends OSXLibraryItem {

    function __construct($path, $url) {
        parent::__construct($path, $url);
        $this->parse();
    }

    protected function parse() {
        parent::parse();
    }

    protected function getTypeSpecificHTML() {
        return "";
    }
}
