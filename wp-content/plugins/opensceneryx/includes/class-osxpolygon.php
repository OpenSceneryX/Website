<?php

/**
 * Description of OSXObject
 */
class OSXPolygon extends OSXLibraryItem {

    protected $textureScaleH = null;
    protected $texturescaleV = null;

    protected $layerGroupName = null;
    protected $layerGroupOffset = null;
    protected $surfaceName = null;

    function __construct($path, $url) {
        parent::__construct($path, $url);
    }

    protected function parse() {
        parent::parse();

        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Texture Scale H:\s+(.*)/', $line, $matches) === 1) {
                $this->textureScaleH = $matches[1];
                continue;
            }

            if (preg_match('/^Texture Scale V:\s+(.*)/', $line, $matches) === 1) {
                $this->textureScaleV = $matches[1];
                continue;
            }

            if (preg_match('/^Layer Group:\s+(.*)/', $line, $matches) === 1) {
                $this->layerGroupName = $matches[1];
                continue;
            }

            if (preg_match('/^Layer Group Offset:\s+(.*)/', $line, $matches) === 1) {
                $this->layerGroupOffset = $matches[1];
                continue;
            }

            if (preg_match('/^Surface:\s+(.*)/', $line, $matches) === 1) {
                $this->surfaceName = $matches[1];
                continue;
            }
        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";
        
        if ($this->textureScaleH !== null && $this->textureScaleV !== null) {
            $result .= "<li><span class='fieldTitle'>Texture Scale:</span> <span class='fieldValue'>h: " . $this->textureScaleH . "m, v: " + $this->textureScaleV + "m</span></li>\n";
        }

        if ($this->layerGroupName !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Group:</span> <span class='fieldValue'>" . $this->layerGroupName . "</span></li>\n";
        }

        if ($this->layerGroupOffset !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Offset:</span> <span class='fieldValue'>" . $this->layerGroupOffset . "</span></li>\n";
        }

        if ($this->surfaceName !== null) {
            $result .= "<li><span class='fieldTitle'>Surface Type:</span> <span class='fieldValue'>" . $this->surfaceName . "</span></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Polygon-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }
        
        return $result;
    }
}
