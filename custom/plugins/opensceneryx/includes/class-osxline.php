<?php

/**
 * Description of OSXObject
 */
class OSXLine extends OSXLibraryItem {

    function __construct($path, $url) {
        parent::__construct($path, $url);
    }

    protected function parse() {
        parent::parse();
    }

    protected function getTypeSpecificHTML() {
        $result = "";
        
        if ($result != "") {
            $result = "<h2>Line-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }

        return $result;
    }
}
