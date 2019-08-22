<?php

/**
 * Description of OSXObject
 */
class OSXFacade extends OSXLibraryItem {

    protected $type = null;

    protected $textureScaleH = null;
    protected $texturescaleV = null;
    protected $textureWidth = null;
    protected $textureHeight = null;

    protected $layerGroupName = null;
    protected $layerGroupOffset = null;

    protected $graded = null;
    protected $ring = null;
    protected $doubled = null;

    protected $wallSurfaceType = null;
    protected $roofSurfaceType = null;

    protected $floorsMin = null;
    protected $floorsMax = null;

    protected $lods = array();

    protected $basementDepth = null;

    function __construct($path, $url) {
        parent::__construct($path, $url);
    }

    protected function parse() {
        parent::parse();

        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Type:\s+(.*)/', $line, $matches) === 1) {
                $this->type = $matches[1];
                continue;
            }

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

            if (preg_match('/^Layer Offset:\s+(.*)/', $line, $matches) === 1) {
                $this->layerGroupOffset = $matches[1];
                continue;
            }

            if (preg_match('/^Graded:\s+(.*)/', $line, $matches) === 1) {
                $this->graded = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Ring:\s+(.*)/', $line, $matches) === 1) {
                $this->ring = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Wall Surface Type:\s+(.*)/', $line, $matches) === 1) {
                $this->wallSurfaceType = $matches[1];
                continue;
            }

            if (preg_match('/^Roof Surface Type:\s+(.*)/', $line, $matches) === 1) {
                $this->roofSurfaceType = $matches[1];
                continue;
            }

            if (preg_match('/^Doubled:\s+(.*)/', $line, $matches) === 1) {
                $this->doubled = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Floors Min:\s+(.*)/', $line, $matches) === 1) {
                $this->floorsMin = $matches[1];
                continue;
            }

            if (preg_match('/^Floors Max:\s+(.*)/', $line, $matches) === 1) {
                $this->floorsMax = $matches[1];
                continue;
            }

            if (preg_match('/^LOD:\s+(.*?)\s+(.*)/', $line, $matches) === 1) {
                $this->lods[] = array('min' => $matches[1], 'max' => $matches[2]);
                continue;
            }

            if (preg_match('/^Basement Depth:\s+(.*)/', $line, $matches) === 1) {
                $this->basementDepth = $matches[1];
                continue;
            }
        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        if ($this->type) {
            $result .= "<li><span class='fieldTitle'>Facade Type </span><dfn class='tooltip'>ⓘ<span>This is a type " . $this->type . " facade. A type 1 facade is a series of walls defined by a rectangular grid of adjacent texture squares, while a type 2 facade has its walls defined by a series of 3-d meshes. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . $this->type . "</span></li>\n";
        }

        if ($this->textureScaleH !== null && $this->textureScaleV !== null) {
            $result .= "<li><span class='fieldTitle'>Texture Scale</span> <dfn class='tooltip'>ⓘ<span>The scale command defines the scale of the albedo texture used for walls – it is the number of meters the entire texture would fill horizontally, then vertically. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>h: " . self::dimension($this->textureScaleH, self::UNITS_METRES) . " (" . self::dimension($this->textureScaleH, self::UNITS_FEET) . "), v: " . self::dimension($this->textureScaleV, self::UNITS_METRES) . " (" . self::dimension($this->textureScaleV, self::UNITS_FEET) . ")</span></li>\n";
        }

        if ($this->layerGroupName !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Group</span> <dfn class='tooltip'>ⓘ<span>This facade is drawn as part of layer group <em>" . $this->layerGroupName . "</em>. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation on the .fac format</a>, and the <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#ATTR_layer_group_ltnamegt_ltoffsetgt' target='_blank'>official documentation on layer group names in the .obj format</a></span></dfn>: <span class='fieldValue'>" . $this->layerGroupName . "</span></li>\n";
        }

        if ($this->layerGroupOffset !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Offset</span> <dfn class='tooltip'>ⓘ<span>This facade is drawn at layer offset " . $this->layerGroupOffset . " within its layer group. -ve offsets are drawn earlier (underneath) and +ve are drawn later (on top). For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation on the .fac format</a>, and the <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#ATTR_layer_group_ltnamegt_ltoffsetgt' target='_blank'>official documentation on layer group offsets in the .obj format</a></span></dfn>: <span class='fieldValue'>" . $this->layerGroupOffset . "</span></li>\n";
        }

        if ($this->graded) {
            $result .= "<li><span class='fieldValue'>Graded</span> <dfn class='tooltip'>ⓘ<span>This facade is graded, where the center of the first wall is placed on the terrain and the facade is then kept in a flat plane. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        } else {
            $result .= "<li><span class='fieldValue'>Draped</span> <dfn class='tooltip'>ⓘ<span>This facade is draped, where every vertex of the facade’s polygon is individually placed on the terrain. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->ring) {
            $result .= "<li><span class='fieldValue'>Ring facade</span> <dfn class='tooltip'>ⓘ<span>This facade is a ring, where X-Plane® automatically connects the last facade point to the first, closing the loop. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->wallSurfaceType !== null) {
            $result .= "<li><span class='fieldTitle'>Wall Surface Type</span> <dfn class='tooltip'>ⓘ<span>The walls of this texture are hard for collision detection, and simulate the " . $this->wallSurfaceName . " surface type. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</a></span></dfn>: <span class='fieldValue'>" . $this->wallSurfaceType . "</span></li>\n";
        }

        if ($this->roofSurfaceType !== null) {
            $result .= "<li><span class='fieldTitle'>Roof Surface Type</span> <dfn class='tooltip'>ⓘ<span>The roof of this texture is hard for collision detection, and simulates the " . $this->wallSurfaceName . " surface type. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</a></span></dfn>: <span class='fieldValue'>" . $this->roofSurfaceType . "</span></li>\n";
        }

        if ($this->doubled) {
            $result .= "<li><span class='fieldValue'>Doubled</span> <dfn class='tooltip'>ⓘ<span>This facade is drawn with walls facing both inside and outside. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->floorsMin !== null) {
            $result .= "<li><span class='fieldTitle'>Minimum Numer of Floors</span> <dfn class='tooltip'>ⓘ<span>This texture is limited to a minimum of " . $this->floorsMin . " Floors. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</a></span></dfn>: <span class='fieldValue'>" . $this->floorsMin . "</span></li>\n";
        }

        if ($this->floorsMax !== null) {
            $result .= "<li><span class='fieldTitle'>Maximum Numer of Floors</span> <dfn class='tooltip'>ⓘ<span>This texture is limited to a maximum of " . $this->floorsMax . " Floors. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</a></span></dfn>: <span class='fieldValue'>" . $this->floorsMax . "</span></li>\n";
        }

        if (count($this->lods) > 0) {
            foreach ($this->lods as $lod) {
                $result .= "<li><span class='fieldTitle'>LOD Range</span> <dfn class='tooltip'>ⓘ<span>This facade contains multiple Levels of Detail (LODs). To improve frame rate, simpler models are used at further distances. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . self::dimension($lod['min'], self::UNITS_METRES) . " to " . self::dimension($lod['max'], self::UNITS_METRES) . " (" . self::dimension($lod['min'], self::UNITS_MILES) . " to " . self::dimension($lod['max'], self::UNITS_MILES) . ")</span></li>\n";
            }
        }

        if ($this->basementDepth !== null) {
            $result .= "<li><span class='fieldTitle'>Basement Depth</span> <dfn class='tooltip'>ⓘ<span>This facade extends " . $this->basementDepth . " pixels below the ground, to make it look better on sloped terrain. For more information, <a href='https://developer.x-plane.com/article/x-plane-10-facade-fac-file-format-specification/' target='_blank'>see the official documentation</a>.</a></span></dfn>: <span class='fieldValue'>" . $this->basementDepth . "</span></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Facade-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }

        return $result;
    }

    protected function getTypeExtension() {
        return ".fac";
    }
}
