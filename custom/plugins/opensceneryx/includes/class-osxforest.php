<?php

/**
 * Description of OSXObject
 */
class OSXForest extends OSXLibraryItem {

    protected $spacingX = null;
    protected $spacingZ = null;
    protected $randomX = null;
    protected $randomZ = null;
    protected $skipSurfaces = null;

    protected $group = false;
    protected $perlin = false;

    protected $lod = null;

    function __construct($path, $url) {
        parent::__construct($path, $url);
    }

    protected function parse() {
        parent::parse();

        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Spacing X:\s+(.*)/', $line, $matches) === 1) {
                $this->spacingX = $matches[1];
                continue;
            }

            if (preg_match('/^Spacing Z:\s+(.*)/', $line, $matches) === 1) {
                $this->spacingZ = $matches[1];
                continue;
            }

            if (preg_match('/^Random X:\s+(.*)/', $line, $matches) === 1) {
                $this->randomX = $matches[1];
                continue;
            }

            if (preg_match('/^Random Z:\s+(.*)/', $line, $matches) === 1) {
                $this->randomZ = $matches[1];
                continue;
            }

            if (preg_match('/^Skip Surfaces:\s+(.*)/', $line, $matches) === 1) {
                $this->skipSurfaces = $matches[1];
                continue;
            }

            if (preg_match('/^Group:\s+(.*)/', $line, $matches) === 1) {
                $this->group = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Perlin:\s+(.*)/', $line, $matches) === 1) {
                $this->perlin = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^LOD:\s+(.*)/', $line, $matches) === 1) {
                $this->lod = $matches[1];
                continue;
            }
        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        if ($this->spacingX !== null && $this->spacingZ !== null) {
            $result .= "<li><span class='fieldTitle'>Spacing X / Z:</span> <span class='fieldValue'>" . $this->spacingX . " / " . $this->spacingZ . "</span></li>\n";
        }

        if ($this->randomX !== null && $this->randomZ !== null) {
            $result .= "<li><span class='fieldTitle'>Random X / Z:</span> <span class='fieldValue'>" . $this->randomX . " / " . $this->randomZ . "</span></li>\n";
        }

        if ($this->skipSurfaces !== null) {
            $result .= "<li><span class='fieldTitle'>Skip Surfaces:</span> <span class='fieldValue'>" . $this->skipSurfaces . "</span></li>\n";
        }

        if ($this->group) {
            $result .= "<li><span class='fieldValue'>Contains Forest Groups</span></li>\n";
        }

        if ($this->perlin) {
            $result .= "<li><span class='fieldValue'>Contains Perlin Noise Randomisation</span></li>\n";
        }

        if ($this->lod !== null) {
            $result .= "<li><span class='fieldTitle'>LOD Range:</span> <span class='fieldValue'>" . $this->lod . "</span></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Forest-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }

        return $result;
    }
}
