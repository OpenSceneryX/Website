<?php

/**
 * Description of OSXObject
 */
class OSXObject extends OSXLibraryItem {

    protected $width = null;
    protected $height = null;
    protected $depth = null;

    protected $animated = false;


    function __construct($path, $url) {
        parent::__construct($path, $url);
    }

    protected function parse() {
        parent::parse();

        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Width:\s+(.*)/', $line, $matches) === 1) {
                $this->width = $matches[1];
                continue;
            }

            if (preg_match('/^Height:\s+(.*)/', $line, $matches) === 1) {
                $this->height = $matches[1];
                continue;
            }

            if (preg_match('/^Depth:\s+(.*)/', $line, $matches) === 1) {
                $this->depth = $matches[1];
                continue;
            }

            if (preg_match('/^Animated:\s+(.*)/', $line, $matches) === 1) {
                $this->animated = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }
        }
    }

    protected function getTypeSpecificHTML() {
        $result = "<h2>Object-specific Details</h2>\n";
        $result .= "<ul>\n";

        if ($this->width !== null && $this->height !== null && $this->depth !== null) {
            $result .= "<li><span class='fieldTitle'>Dimensions:</span>\n";
            $result .= "<ul class='dimensions'>\n";
            $result .= "<li id='width'><span class='fieldTitle'>w:</span> " . $this->width . "</li>\n";
            $result .= "<li id='height'><span class='fieldTitle'>h:</span> " . $this->height . "</li>\n";
            $result .= "<li id='depth'><span class='fieldTitle'>d:</span> " . $this->depth . "</li>\n";
            $result .= "</ul>\n";
            $result .= "</li>\n";
        }

        $result .= "</ul>\n";
        return $result;
    }
}
