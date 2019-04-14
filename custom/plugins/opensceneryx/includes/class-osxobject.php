<?php

/**
 * Description of OSXObject
 */
class OSXObject extends OSXLibraryItem {

    protected $width = null;
    protected $height = null;
    protected $depth = null;

    protected $animated = false;

    protected $lods = array();

    protected $lightsCustom = false;
    protected $lightsNamed = false;
    protected $lightsParameterised = false;
    protected $lightsSpill = false;

    protected $tilted = false;
    protected $smokeBlack = false;
    protected $smokeWhite = false;

    protected $wedRotationLockAngle = null;

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

            if (preg_match('/^LOD:\s+(.*?)\s+(.*)/', $line, $matches) === 1) {
                $this->lods[] = array('min' => $matches[1], 'max' => $matches[2]);
                continue;
            }

            if (preg_match('/^Custom Lights:\s+(.*)/', $line, $matches) === 1) {
                $this->lightsCustom = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Named Lights:\s+(.*)/', $line, $matches) === 1) {
                $this->lightsNamed = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Parameterised Lights:\s+(.*)/', $line, $matches) === 1) {
                $this->lightsParameterised = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Spill Lights:\s+(.*)/', $line, $matches) === 1) {
                $this->lightsSpill = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Tilted:\s+(.*)/', $line, $matches) === 1) {
                $this->tilted = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Black Smoke:\s+(.*)/', $line, $matches) === 1) {
                $this->smokeBlack = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^White Smoke:\s+(.*)/', $line, $matches) === 1) {
                $this->smokeWhite = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Rotation Lock:\s+(.*)/', $line, $matches) === 1) {
                $this->wedRotationLockAngle = $matches[1];
                continue;
            }

        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        if ($this->width !== null && $this->height !== null && $this->depth !== null) {
            $result .= "<li><span class='fieldTitle'>Dimensions:</span>\n";
            $result .= "<ul class='dimensions'>\n";
            $result .= "<li id='width'><span class='fieldTitle'>w:</span> " . $this->width . "m</li>\n";
            $result .= "<li id='height'><span class='fieldTitle'>h:</span> " . $this->height . "m</li>\n";
            $result .= "<li id='depth'><span class='fieldTitle'>d:</span> " . $this->depth . "m</li>\n";
            $result .= "</ul>\n";
            $result .= "</li>\n";
        }

        if ($this->animated) {
            $result .= "<li class='animated'><span class='fieldValue'>Animated</span></li>\n";
        }

        if (count($this->lods) > 0) {
            foreach ($this->lods as $lod) {
                $result .= "<li><span class='fieldTitle'>LOD Range:</span> <span class='fieldValue'>" . $lod['min'] . " to " . $lod['max'] . "</span></li>\n";
            }
        }

        if ($this->lightsCustom) {
            $result .= "<li><span class='fieldValue'>Contains Custom Lights</span></li>\n";
        }

        if ($this->lightsNamed) {
            $result .= "<li><span class='fieldValue'>Contains Named Lights</span></li>\n";
        }

        if ($this->lightsParameterised) {
            $result .= "<li><span class='fieldValue'>Contains Parameterised Lights</span></li>\n";
        }

        if ($this->lightsSpill) {
            $result .= "<li><span class='fieldValue'>Contains Spill Lights</span></li>\n";
        }

        if ($this->tilted) {
            $result .= "<li><span class='fieldValue'>Will Render Tilted on Sloped Terrain</span></li>\n";
        }

        if ($this->smokeBlack) {
            $result .= "<li><span class='fieldValue'>Emits Black Smoke</span></li>\n";
        }

        if ($this->smokeWhite) {
            $result .= "<li><span class='fieldValue'>Emits White Smoke</span></li>\n";
        }

        if ($this->wedRotationLockAngle != null) {
            $result .= "<li><span class='fieldTitle'>Placement Locked at:</span> <span class='fieldValue'>" . $this->wedRotationLockAngle . "°</span> <span class='fieldTitle'>in WED</span></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Object-specific Details</h2><ul class='mainItemDetails'>\n" . $result . "</ul>\n";
        }

        return $result;
    }
}
