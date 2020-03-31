<?php

/**
 * Description of OSXItem
 */
abstract class OSXItem {

    public $path;

    public $url;

    public $title = "Undefined";

    // The item type is used as the CSS class on the container <article>. All subclasses should set this, default to 'osxitem'.
    private $type = "osxitem";

    public $ancestors = array();

    protected $fileLines = array();

    const DIMENSION_PRECISION = 2;
    const UNITS_METRES = 0;
    const UNITS_FEET = 1;
    const UNITS_MILES = 2;


    function __construct($path, $url, $type) {
        $this->path = $path;
        $this->url = $url;
        $this->type = $type;

        $this->buildAncestors();
    }

    public function getType() {
        return $this->type;
    }

    protected static function dimension($m, $units) {
        switch ($units) {
            case self::UNITS_METRES:
                return round($m, self::DIMENSION_PRECISION) . "m";
            case self::UNITS_FEET:
                return round($m * 3.280839895, self::DIMENSION_PRECISION) . "ft";
            case self::UNITS_MILES:
                $value = round($m * 0.000621371192, self::DIMENSION_PRECISION);
                return $value . " mile" . ($value == 1 ? "" : "s");
        }
    }

    private function buildAncestors()
    {
        $path = $this->path;
        $url = $this->url;

        while (strlen($path) > strlen(ABSPATH)) {
            $path = dirname($path);
            $url = dirname($url);

            if (is_file($path . '/category.txt')) {
                array_unshift($this->ancestors, new OSXCategory($path, $url, 'osxcategory-' . $this->type));
            }
        }
    }

    function enqueueScript() {
        // Default does nothing, subclasses can override
    }

    abstract protected function parse();
}
