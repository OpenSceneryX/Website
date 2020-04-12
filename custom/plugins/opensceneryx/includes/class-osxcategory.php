<?php

/**
 * Description of OSXItem
 */
class OSXCategory extends OSXItem {

    protected $subcategories = array();

    protected $items = array();

    function __construct($path, $url, $itemType) {
        parent::__construct($path, $url, $itemType);

        $contents = file_get_contents($this->path . '/category.txt');
        $this->fileLines = explode(PHP_EOL, $contents);

        $this->parse();
    }

    protected function parse() {
        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Title:\s+(.*)/', $line, $matches) === 1) {
                $this->title = $matches[1];
                continue;
            }

            if (preg_match('/^Sub-category:\s+"(.*?)"\s+"(.*?)"/', $line, $matches) === 1) {
                $this->subcategories[] = array('title' => $matches[1], 'path' => $matches[2]);
                continue;
            }

            if (preg_match('/^Item:\s+"(.*?)"\s+"(.*?)"/', $line, $matches) === 1) {
                $this->items[] = array('title' => $matches[1], 'path' => $matches[2]);
                continue;
            }
        }
    }

    public function getHTML() {
        $result = '';

        if (count($this->subcategories) > 0) {
            $result .= '<h2>Sub-categories</h2>';
            $result .= '<div class="subcategories">';
            foreach ($this->subcategories as $subcategory) {
                $result .= "<h3 class='inline " . $this->getCSSClass() . "'><a href='" . $subcategory['path'] . "'>" . $subcategory['title'] . "</a></h3>\n";
            }
            $result .= '</div>';
        }

        if (count($this->items) > 0) {
            $result .= '<h2>Items in this Category</h2>';

            foreach ($this->items as $item) {
                $result .= "<div class='thumbnailcontainer'>\n";
                $result .= "<h3><a href='/" . $item['path'] . "'>" . $item['title'] . "</a></h3><a href='/" . $item['path'] . "' class='nounderline'>";
                if (is_file($item['path'] . '/screenshot.jpg')) {
                    $result .= "<img src='/" . $item['path'] . "/screenshot.jpg' alt='Screenshot of " . \str_replace("'", "&apos;", $item['title']) . "' />";
                } else {
                    $result .= "<img src='/doc/screenshot_missing.png' alt='No Screenshot Available' />";
                }
                $result .=  "</a>\n";
                $result .=  "</div>\n";
            }
        }

        $result .= "<div class='clear'>&nbsp;</div>";

        return $result;
    }

    public function getCSSClass() {
        // Note that itemType for categories is plural
        return 'osxcategory-' . $this->itemType;
    }

    public function getMetaDescription($description) {
        // Yoast description always takes precedence. This allows any category to be overridden with
        // a CMS-managed page
        if ($description) return $description;

        // Build a description for the category, containing its name, sub-categories and sub-items
        $result = 'A set of ' . $this->title . ' ' . $this->itemType . ', containing';

        if (count($this->subcategories) > 0) {
            $result .= ' the following sub-categories: ';
            $result .= '"' . $this->subcategories[0]['title'] . '"';
            for ($i = 1; $i < count($this->subcategories); $i++) $result .= ', "' . $this->subcategories[$i]['title'] . '"';
        } else {
            $result .= ' no sub-categories';
        }

        if (count($this->items) > 0) {
            $result .= ' and the following ' . $this->itemType . ': ';
            $result .= '"' . $this->items[0]['title'] . '"';
            for ($i = 1; $i < count($this->items); $i++) $result .= ', "' . $this->items[$i]['title'] . '"';
        }

        return $result;
    }

    /**
     * Ensure we highlight the appropriate menu items
     */
    public function menuItemClasses($classes, $item, $args) {
        // The main menu location on our theme is 'primary'
        if ('primary' !== $args->theme_location) return $classes;

        // Highlight our item type in the menu. 'Contents' is highlighted by the superclass.
        if (ucfirst($this->itemType) == $item->title) $classes[] = 'current-menu-item';

        return parent::menuItemClasses($classes, $item, $args);
    }
}
