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

            if (preg_match('/^Layer Offset:\s+(.*)/', $line, $matches) === 1) {
                $this->layerGroupOffset = $matches[1];
                continue;
            }

            if (preg_match('/^Surface Type:\s+(.*)/', $line, $matches) === 1) {
                $this->surfaceName = $matches[1];
                continue;
            }
        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        if ($this->textureScaleH !== null && $this->textureScaleV !== null) {
            $result .= "<li><span class='fieldTitle'>Texture Scale</span> <dfn class='tooltip'>ⓘ<span>By default, one iteration of this polygon's texture covers " . self::dimension($this->textureScaleH, self::UNITS_METRES) . " (" . self::dimension($this->textureScaleH, self::UNITS_FEET) . ") horizontally and " . self::dimension($this->textureScaleV, self::UNITS_METRES) . " (" . self::dimension($this->textureScaleV, self::UNITS_FEET) . ") vertically, but this can be overridden when placing the polygon. For more information, <a href='https://developer.x-plane.com/article/draped-polygon-polfac-file-format-specification/' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>h: " . self::dimension($this->textureScaleH, self::UNITS_METRES) . " (" . self::dimension($this->textureScaleH, self::UNITS_FEET) . "), v: " . self::dimension($this->textureScaleV, self::UNITS_METRES) . " (" . self::dimension($this->textureScaleV, self::UNITS_FEET) . ")</span></li>\n";
        }

        if ($this->layerGroupName !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Group</span> <dfn class='tooltip'>ⓘ<span>This polygon is drawn as part of layer group <em>" . $this->layerGroupName . "</em>. For more information, <a href='https://developer.x-plane.com/article/draped-polygon-polfac-file-format-specification/' target='_blank'>see the official documentation on the .pol format</a>, and the <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#ATTR_layer_group_ltnamegt_ltoffsetgt' target='_blank'>official documentation on layer group names in the .obj format</a></span></dfn>: <span class='fieldValue'>" . $this->layerGroupName . "</span></li>\n";
        }

        if ($this->layerGroupOffset !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Offset</span> <dfn class='tooltip'>ⓘ<span>This polygon is drawn at layer offset " . $this->layerGroupOffset . " within its layer group. -ve offsets are drawn earlier (underneath) and +ve are drawn later (on top). For more information, <a href='https://developer.x-plane.com/article/draped-polygon-polfac-file-format-specification/' target='_blank'>see the official documentation on the .pol format</a>, and the <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#ATTR_layer_group_ltnamegt_ltoffsetgt' target='_blank'>official documentation on layer group offsets in the .obj format</a></span></dfn>: <span class='fieldValue'>" . $this->layerGroupOffset . "</span></li>\n";
        }

        if ($this->surfaceName !== null) {
            $result .= "<li><span class='fieldTitle'>Surface Type</span> <dfn class='tooltip'>ⓘ<span>This polygon emulates the hard surface type <em>" . $this->surfaceName . "</em>. Surface types determine how the aircraft behaves when taxiing over the surface. For more information, <a href='https://developer.x-plane.com/article/draped-polygon-polfac-file-format-specification/' target='_blank'>see the official documentation on the .pol format</a>, and the <a href='https://developer.x-plane.com/article/obj8-file-format-specification/' target='_blank'>official documentation on hard surface types in the .obj format</a></span></dfn>: <span class='fieldValue'>" . $this->surfaceName . "</span></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Polygon-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }

        return $result;
    }
}
