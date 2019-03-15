<?php

/**
 * Description of OSXObject
 */
abstract class OSXLibraryItem extends OSXItem {

    protected $textures = array();

    protected $virtualPaths = array();
    protected $deprecatedVirtualPaths = array();
    protected $externalVirtualPaths = array();
    protected $extendedVirtualPaths = array();

    protected $authors = array();
    protected $authorEmails = array();
    protected $authorURLs = array();
    protected $textureAuthors = array();
    protected $textureAuthorEmails = array();
    protected $textureAuthorURLs = array();
    protected $conversionAuthors = array();
    protected $conversionAuthorEmails = array();
    protected $conversionAuthorURLs = array();
    protected $modificationAuthors = array();
    protected $modificationAuthorEmails = array();
    protected $modificationAuthorURLs = array();

    protected $description = null;

    protected $logo = null;

    protected $note = null;

    protected $since = null;

    protected $screenshotPath = null;

    protected $seasonal = null;

    /**
     * @var boolean If true, author email addresses will be output.  This should only be enabled if an email obfuscator plugin is installed
     * or if a proxy service is used (such as Cloudflare) that obfuscates emails
    */
    const OUTPUT_EMAILS = true;


    function __construct($path, $url) {
        parent::__construct($path, $url);

        $contents = file_get_contents($this->path . '/info.txt');
        $this->fileLines = explode(PHP_EOL, $contents);

        if (is_file($this->path . "/screenshot.jpg")) {
            $this->screenshotPath = "/" . $this->url . "screenshot.jpg";
        }

        // Intercept the yoast opengraph call
        add_action('wpseo_opengraph', array($this, 'openGraph'));

        $this->parse();
    }

    protected function parse() {
        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Title:\s+(.*)/', $line, $matches) === 1) {
                $this->title = $matches[1];
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

            if (preg_match('/^Export External (.*):\s+(.*)/', $line, $matches) === 1) {
                $this->externalVirtualPaths[] = array('library' => $matches[1], 'path' => $matches[2]);
                continue;
            }

            if (preg_match('/^Export Extended:\s+(.*)/', $line, $matches) === 1) {
                $this->extendedVirtualPaths[] = $matches[1];
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

            if (preg_match('/^Author, modifications:\s+(.*)/', $line, $matches) === 1) {
                $this->modificationAuthors[] = $matches[1];
                continue;
            }

            if (preg_match('/^Email, modifications:\s+(.*)/', $line, $matches) === 1) {
                $this->modificationAuthorEmails[] = $matches[1];
                continue;
            }

            if (preg_match('/^URL, modifications:\s+(.*)/', $line, $matches) === 1) {
                $this->modificationAuthorURLs[] = $matches[1];
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

            if (preg_match('/^Since:\s+(.*)/', $line, $matches) === 1) {
                $this->since = $matches[1];
                continue;
            }

            if (preg_match('/^Seasonal:\s+(.*)/', $line, $matches) === 1) {
                $this->seasonal = ($matches[1] == "True" || $matches[1] == "Yes");
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
            $result .= "<div class='virtualPath'><h2>Paths</h2>\n";

            foreach ($this->virtualPaths as $virtualPath) {
                $result .= $virtualPath . "<br />\n";
            }

            $result .= "</div>\n";
        }

        if (count($this->extendedVirtualPaths) > 0) {
            $result .= "<div class='extendedVirtualPath'><h2>Extended Library Paths</h2>\n";

            foreach ($this->extendedVirtualPaths as $extendedVirtualPath) {
                $result .= $extendedVirtualPath . "<br />\n";
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

        if (count($this->externalVirtualPaths) > 0) {
            $result .= "<div class='externalVirtualPath'><h2>3rd Party Library Paths</h2>\n";

            foreach ($this->externalVirtualPaths as $externalVirtualPath) {
                $result .= "<strong>To '" . $externalVirtualPath['library'] . "'</strong>: " . $externalVirtualPath['path'] . "<br />\n";
            }

            $result .= "</div>\n";
        }

        if ($this->screenshotPath !== null) {
            $result .= "<img class='screenshot' src='" . $this->screenshotPath . "' alt='Screenshot of " . \str_replace("'", "&apos;", $this->title) . "' />\n";
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
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'><a href='" . $this->authorURLs[$i] . "' onclick='window.open(this.href);return false;'>" . $this->authors[$i] . "</a></span>";
                } elseif (self::OUTPUT_EMAILS && isset($this->authorEmails[$i])) {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'><a href='mailto:" . $this->authorEmails[$i] . "'>" . $this->authors[$i] . "</a></span> ";
                } else {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'>" . $this->authors[$i] . "</span> ";
                }
            }
        }

        $authorCount = count($this->textureAuthors);
        if ($authorCount > 0) {
            $result .= "<li><span class='fieldTitle'>Original Texture Author" . ($authorCount > 1 ? "s" : "") . ":</span> ";

            for ($i = 0; $i < $authorCount; $i++) {
                if (isset($this->textureAuthorURLs[$i])) {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'><a href='" . $this->textureAuthorURLs[$i] . "' onclick='window.open(this.href);return false;'>" . $this->textureAuthors[$i] . "</a></span>";
                } elseif (self::OUTPUT_EMAILS && isset($this->textureAuthorEmails[$i])) {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'><a href='mailto:" . $this->textureAuthorEmails[$i] . "'>" . $this->textureAuthors[$i] . "</a></span> ";
                } else {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'>" . $this->textureAuthors[$i] . "</span>";
                }
            }
        }

        $authorCount = count($this->conversionAuthors);
        if ($authorCount > 0) {
            $result .= "<li><span class='fieldTitle'>Object Conversion By:</span> ";

            for ($i = 0; $i < $authorCount; $i++) {
                if (isset($this->conversionAuthorURLs[$i])) {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'><a href='" . $this->conversionAuthorURLs[$i] . "' onclick='window.open(this.href);return false;'>" . $this->conversionAuthors[$i] . "</a></span>";
                } elseif (self::OUTPUT_EMAILS && isset($this->conversionAuthorEmails[$i])) {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'><a href='mailto:" . $this->conversionAuthorEmails[$i] . "'>" . $this->conversionAuthors[$i] . "</a></span> ";
                } else {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'>" . $this->conversionAuthors[$i] . "</span> ";
                }
            }
        }

        $authorCount = count($this->modificationAuthors);
        if ($authorCount > 0) {
            $result .= "<li><span class='fieldTitle'>Object Modifications By:</span> ";

            for ($i = 0; $i < $authorCount; $i++) {
                if (isset($this->modificationAuthorURLs[$i])) {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'><a href='" . $this->modificationAuthorURLs[$i] . "' onclick='window.open(this.href);return false;'>" . $this->modificationAuthors[$i] . "</a></span>";
                } elseif (self::OUTPUT_EMAILS && isset($this->modificationAuthorEmails[$i])) {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'><a href='mailto:" . $this->modificationAuthorEmails[$i] . "'>" . $this->modificationAuthors[$i] . "</a></span> ";
                } else {
                    $result .= ($authorCount > 1 && $i > 0 ? ", " : "") . "<span class='fieldValue'>" . $this->modificationAuthors[$i] . "</span> ";
                }
            }
        }

        if ($this->since) {
            $result .= "<li><span class='fieldTitle'>Available Since:</span> <span class='fieldValue'>" . $this->since . "</span></li>\n";
        }

        if ($this->seasonal) {
            $result .= "<li><span class='fieldTitle'>Has seasonal variants</span></li>\n";
        }

        if ($this->note !== null) {
            $result .= "<li class='note'><span class='fieldTitle'>Important Note:</span> <span class='fieldValue'>" . $this->note . "</span></li>\n";
        }

        $result .= "</ul>\n";

        if ($this->description !== null) {
            $result .= "<h2>Description</h2>\n";
            $result .= "<div class='description'>" . $this->description . "</div>\n";
        }

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

        $result .= "<p>Please note that you must download the library as a whole from the <a href='/'>OpenSceneryX home page</a>, we do not provide downloads for individual items. If you are a scenery developer and want to know why this is, and how to use the library correctly in your sceneries, <a href='/support/scenery-developers/'>start here</a>.";
        return $result;
    }

    function openGraph() {
        add_action('wpseo_add_opengraph_images', array($this, 'openGraphAddImages'));
    }

    function openGraphAddImages($object) {
        if ($this->screenshotPath !== null) {
            $object->add_image($this->screenshotPath);
        } else {
            $object->add_image("/doc/screenshot_missing.png");
        }
    }

    protected abstract function getTypeSpecificHTML();
}
