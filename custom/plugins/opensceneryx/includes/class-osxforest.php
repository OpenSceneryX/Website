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
            $result .= "<li><span class='fieldTitle'>Spacing X / Z </span><dfn class='tooltip'>ⓘ<span>The items in this forest use " . $this->spacingX . "m spacing in the X dimension and " . $this->spacingZ . "m spacing in the Z dimension. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#SPACING_ltx_spacinggt_ltz_spacinggt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . $this->spacingX . "m / " . $this->spacingZ . "m</span></li>\n";
        }

        if ($this->randomX !== null && $this->randomZ !== null) {
            $result .= "<li><span class='fieldTitle'>Random X / Z </span><dfn class='tooltip'>ⓘ<span>The items in this forest can deviate from the spacing by up to " . $this->spacingX . "m in the X dimension and " . $this->spacingZ . "m in the Z dimension. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#RANDOM_ltx_spacinggt_ltz_spacinggt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . $this->randomX . "m / " . $this->randomZ . "m</span></li>\n";
        }

        if ($this->skipSurfaces !== null) {
            $result .= "<li><span class='fieldTitle'>Skip Surfaces </span><dfn class='tooltip'>ⓘ<span>X-Plane® will not place this forest on " . $this->skipSurfaces . " surfaces. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#SKIP_SURFACE_ltsurface_typegt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . $this->skipSurfaces . "</span></li>\n";
        }

        if ($this->group) {
            $result .= "<li><span class='fieldTitle'>Contains Forest Groups</span> <dfn class='tooltip'>ⓘ<span>Forest groups are used to create clusters of differing trees within a single forest. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#GROUP_layer_percent' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->perlin) {
            $result .= "<li><span class='fieldTitle'>Contains Perlin Noise Randomisation</span> <dfn class='tooltip'>ⓘ<span>This forest uses Perlin noise to distribute density, tree choice or height. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#DENSITY_PARAMS_ltperlin_paramsgt' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->lod !== null) {
            $result .= "<li><span class='fieldTitle'>LOD Range </span><dfn class='tooltip'>ⓘ<span>This forest is drawn up to a distance of " . $this->lod . "m from the user. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#LOD_ltmax_lodgt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . $this->lod . "</span></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Forest-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }

        return $result;
    }
}
