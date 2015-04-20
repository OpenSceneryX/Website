<?php

/**
 * Description of OSXObject
 */
abstract class OSXLibraryItem extends OSXItem {

    protected $textures = array();

    protected $virtualPaths = array();
    protected $deprecatedVirtualPaths = array();

    protected $authors = array();
    protected $authorEmails = array();
    protected $authorURLs = array();
    protected $textureAuthors = array();
    protected $textureAuthorEmails = array();
    protected $textureAuthorURLs = array();
    protected $conversionAuthors = array();
    protected $conversionAuthorEmails = array();
    protected $conversionAuthorURLs = array();

    protected $description = null;

    protected $logo = null;

    protected $note = null;


    function __construct($path, $url) {
        parent::__construct($path, $url);

        $contents = file_get_contents($this->path . '/info.txt');
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

            if (preg_match('/^Ancestor:\s+"(.*)"\s+"(.*)"/', $line, $matches) === 1) {
                $this->ancestors[] = array('title' => $matches[1], 'url' => $matches[2]);
                continue;
            }

            if (preg_match('/^Texture:\s+(.*)/', $line, $matches) === 1) {
                $this->textures[] = array('name' => $matches[1], 'sharedwith' => array());
                continue;
            }

            if (preg_match('/^Texture Shared With:\s+"(.*)"\s+"(.*)"/', $line, $matches) === 1) {
                $texture = end(array_keys($this->textures));
                $texture['sharedwith'][] = array('title' => $matches[1], 'url' => $matches[2]);
                continue;
            }

            if (preg_match('/^Export:\s+(.*)/', $line, $matches) === 1) {
                $this->virtualPaths[] = $matches[1];
                continue;
            }

            if (preg_match('/^Export Deprecated v(.*):\s+(.*)/', $line, $matches) === 1) {
                $this->deprecatedVirtualPaths[] = array('version' => $matches[1], 'path' => $matches[2]);
                continue;
            }

            if (preg_match('/^Author:\s+(.*)/', $line, $matches) === 1) {
                $this->authors[] = $matches[1];
                continue;
            }

            if (preg_match('/^Email:\s+(.*)/', $line, $matches) === 1) {
                $this->authorEmails[] = $matches[1];
                continue;
            }

            if (preg_match('/^URL:\s+(.*)/', $line, $matches) === 1) {
                $this->authorURLs[] = $matches[1];
                continue;
            }

            if (preg_match('/^Author, texture:\s+(.*)/', $line, $matches) === 1) {
                $this->textureAuthors[] = $matches[1];
                continue;
            }

            if (preg_match('/^Email, texture:\s+(.*)/', $line, $matches) === 1) {
                $this->textureAuthorEmails[] = $matches[1];
                continue;
            }

            if (preg_match('/^URL, texture:\s+(.*)/', $line, $matches) === 1) {
                $this->textureAuthorURLs[] = $matches[1];
                continue;
            }

            if (preg_match('/^Author, conversion:\s+(.*)/', $line, $matches) === 1) {
                $this->conversionAuthors[] = $matches[1];
                continue;
            }

            if (preg_match('/^Email, conversion:\s+(.*)/', $line, $matches) === 1) {
                $this->conversionAuthorEmails[] = $matches[1];
                continue;
            }

            if (preg_match('/^URL, conversion:\s+(.*)/', $line, $matches) === 1) {
                $this->conversionAuthorURLs[] = $matches[1];
                continue;
            }

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

            if (preg_match('/^Logo:\s+(.*)/', $line, $matches) === 1) {
                $this->logo = $matches[1];
                continue;
            }

            if (preg_match('/^Note:\s+(.*)/', $line, $matches) === 1) {
                $this->note = $matches[1];
                continue;
            }

            if (preg_match('/^Description:\s+(.*)/', $line, $matches) === 1) {
                $this->description = $matches[1];
                continue;
            }
            
            // Default is to append to the description.  This handles any amount of extra text at the end of the file
            $this->description .= $line;
        }
    }

    public function getHTML() {
        $result = '';

        if (count($this->virtualPaths) > 0) {
            $result .= "<div class='virtualPath'><h2>Virtual Paths</h2>\n";

            foreach ($this->virtualPaths as $virtualPath) {
                $result .= $virtualPath . "<br />\n";
            }

            $result .= "</div>\n";
        }

        if (count($this->deprecatedVirtualPaths) > 0) {
            $result .= "<div class='deprecatedVirtualPath'><h2>Deprecated Paths</h2>\n";

            foreach ($this->deprecatedVirtualPaths as $deprecatedVirtualPath) {
                $result .= "<strong>From v" . $deprecatedVirtualPath['version'] . "</strong>: " . $deprecatedVirtualPath['path'] . "<br />\n";
            }

            $result .= "</div>\n";
        }

        if (is_file($this->path . "/screenshot.jpg")) {
            $result .= "<img class='screenshot' src='/" . $this->url . "/screenshot.jpg' alt='Screenshot of " . \str_replace("'", "&apos;", $this->title) . "' />\n";
        } else {
            $result .= "<img class='screenshot' src='/doc/screenshot_missing.png' alt='No Screenshot Available' />\n";
        }

        if ($this->logo !== null) {
            $result .= "<div class='objectlogocontainer'>\n";
            $result .= "<img src='/doc/" . $this->logo . "' alt='Object branding logo' />\n";
            $result .= "</div>\n";
        }

        $result .= "<h2>General Details</h2>\n";
        $result .= "<ul class='mainItemDetails'>\n";

        $authorCount = count($this->authors);
        if ($authorCount > 0) {
            $result .= "<li><span class='fieldTitle'>Original Author" . ($authorCount > 1 ? "s" : "") . ":</span> ";

            for ($i = 0; $i < $authorCount; $i++) {
                if (isset($this->authorURLs[$i])) {
                    $result .= "<span class='fieldValue'><a href='" . $this->authorURLs[$i] . "' onclick='window.open(this.href);return false;'>" . $this->authors[$i] . "</a></span>";
                } else {
                    $result .= "<span class='fieldValue'>" . $this->authors[$i] . "</span>";
                }
            }
        }

        $authorCount = count($this->textureAuthors);
        if ($authorCount > 0) {
            $result .= "<li><span class='fieldTitle'>Original Texture Author" . ($authorCount > 1 ? "s" : "") . ":</span> ";

            for ($i = 0; $i < $authorCount; $i++) {
                if (isset($this->textureAuthorURLs[$i])) {
                    $result .= "<span class='fieldValue'><a href='" . $this->textureAuthorURLs[$i] . "' onclick='window.open(this.href);return false;'>" . $this->textureAuthors[$i] . "</a></span>";
                } else {
                    $result .= "<span class='fieldValue'>" . $this->textureAuthors[$i] . "</span>";
                }
            }
        }

        $authorCount = count($this->conversionAuthors);
        if ($authorCount > 0) {
            $result .= "<li><span class='fieldTitle'>Object Conversion By:</span> ";

            for ($i = 0; $i < $authorCount; $i++) {
                if (isset($this->conversionAuthorURLs[$i])) {
                    $result .= "<span class='fieldValue'><a href='" . $this->conversionAuthorURLs[$i] . "' onclick='window.open(this.href);return false;'>" . $this->conversionAuthors[$i] . "</a></span>";
                } else {
                    $result .= "<span class='fieldValue'>" . $this->conversionAuthors[$i] . "</span>";
                }
            }
        }

        if ($this->description !== null) {
            $result .= "<li><span class='fieldTitle'>Description:</span> <span class='fieldValue'>" . $this->description . "</span></li>\n";
        }

        if ($this->note !== null) {
            $result .= "<li class='note'><span class='fieldTitle'>Important Note:</span> <span class='fieldValue'>" . $this->note . "</span></li>\n";
        }

        $result .= "</ul>\n";

        $result .= $this->getTypeSpecificHTML();

        foreach ($this->textures as $texture) {
            if (count($texture['sharedwith']) > 0) {
                $result .= "<li><span class='fieldTitle'>Texture '" . $texture['name'] . "' shared with:</span>\n";
                $result .= "<ul>\n";

                foreach ($texture['sharedwith'] as $item) {
                    $result .= "<li><span class='fieldValue'><a href='/" . $item['url'] . "'>" . $item['title'] . "</a></span></li>\n";
                }

                $result .= "</ul></li>\n";
            }
        }

        $result .= "<div class='clear'>&nbsp;</div>";
        
        return $result;
    }

    protected abstract function getTypeSpecificHTML();
}
