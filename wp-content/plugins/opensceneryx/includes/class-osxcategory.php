<?php

/**
 * Description of OSXItem
 */
class OSXCategory extends OSXItem {

    protected $subcategories = array();

    protected $items = array();

    function __construct($path, $url) {
        parent::__construct($path, $url);

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

        if (count($this->subcategories > 0)) {
            $result .= '<h2>Sub-categories</h2>';

            foreach ($this->subcategories as $subcategory) {
                $result .= "<h3 class='inline'><a href='" . $subcategory['path'] . "'>" . $subcategory['title'] . "</a></h3>\n";
            }
        }

        if (count($this->items) > 0) {
            $result .= '<h2>Objects</h2>';

            foreach ($this->items as $item) {
                $result .= "<div class='thumbnailcontainer'>\n";
                $result .= "<h4><a href='/" . $item['path'] . "'>" . $item['title'] . "</a></h4><a href='/" . $item['path'] . "' class='nounderline'>";
                if (is_file($item['path'] . '/screenshot.jpg')) {
                    $result .= "<img src='/" . $item['path'] . "/screenshot.jpg' alt='Screenshot of " . \str_replace("'", "&apos;", $item['title']) . "' />";
                } else {
                    $result .= "<img src='/doc/screenshot_missing.png' alt='No Screenshot Available' />";
                }
                $result .=  "</a>\n";
                $result .=  "</div>\n";
            }
        }

        return $result;
    }
}
