<?php

/**
 * Description of OSXItem
 */
abstract class OSXItem {

    public $path;

    public $url;

    public $title = "Undefined";

    // The item type is used to generate the CSS class on the container <article> and is also used to find the item to highlight in the menu, default just to 'item'.
    protected $itemType = "item";

    public $ancestors = array();

    protected $fileLines = array();

    const DIMENSION_PRECISION = 2;
    const UNITS_METRES = 0;
    const UNITS_FEET = 1;
    const UNITS_MILES = 2;


    function __construct($path, $url, $itemType) {
        $this->path = $path;
        $this->url = $url;
        $this->itemType = $itemType;

        // Intercept the main navigation menu call
        add_filter('nav_menu_css_class', array($this, 'menuItemClasses'), 10, 3);
        add_filter('wpseo_metadesc', array($this, 'getMetaDescription')); // Override Yoast meta description

        $this->buildAncestors();
    }

    /**
     * Ensure we highlight the appropriate menu items
     */
    public function menuItemClasses($classes, $item, $args) {
        // The main menu location on our theme is 'primary'
        if ('primary' !== $args->theme_location) return $classes;

        // We are on a library item page, always highlight the 'Contents' item
        if ('Contents' == $item->title) $classes[] = 'current-menu-item';

        return array_unique($classes);
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
                array_unshift($this->ancestors, new OSXCategory($path, $url, $this->type));
            }
        }
    }

    function enqueueScript() {
        // Default does nothing, subclasses can override
    }

    abstract protected function parse();

    abstract public function getCSSClass();

    abstract public function getMetaDescription($description);
}
