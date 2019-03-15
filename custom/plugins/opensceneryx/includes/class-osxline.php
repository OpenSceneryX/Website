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

        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Layer Group:\s+(.*)/', $line, $matches) === 1) {
                $this->layerGroupName = $matches[1];
                continue;
            }

            if (preg_match('/^Layer Offset:\s+(.*)/', $line, $matches) === 1) {
                $this->layerGroupOffset = $matches[1];
                continue;
            }

            if (preg_match('/^Line Width:\s+(.*)/', $line, $matches) === 1) {
                $this->lineWidth = $matches[1];
                continue;
            }

            if (preg_match('/^Mirror:\s+(.*)/', $line, $matches) === 1) {
                $this->mirror = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }
        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        if ($this->layerGroupName !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Group:</span> <span class='fieldValue'>" . $this->layerGroupName . "</span></li>\n";
        }

        if ($this->layerGroupOffset !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Offset:</span> <span class='fieldValue'>" . $this->layerGroupOffset . "</span></li>\n";
        }

        if ($this->lineWidth !== null) {
            $result .= "<li><span class='fieldTitle'>Line Width:</span> <span class='fieldValue'>" . $this->lineWidth . "m</span></li>\n";
        }

        if ($this->mirror) {
            $result .= "<li><span class='fieldValue'>Mirrored</span></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Line-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }

        return $result;
    }
}
