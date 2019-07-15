<?php

/**
 * Description of OSXObject
 */
abstract class OSXLibraryItem extends OSXItem {

    protected $textures = array();

    protected $filePath = null;

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

    protected $screenshots = array();

    protected $seasons = array();

    /**
     * @var boolean If true, author email addresses will be output.  This should only be enabled if an email obfuscator plugin is installed
     * or if a proxy service is used (such as Cloudflare) that obfuscates emails
    */
    const OUTPUT_EMAILS = true;


    function __construct($path, $url) {
        parent::__construct($path, $url);

        $contents = file_get_contents($this->path . '/info.txt');
        $this->fileLines = explode(PHP_EOL, $contents);

        foreach (glob($this->path . "/*.jpg") as $filename) {
            $basename = basename($filename);
            switch ($basename) {
                case 'screenshot.jpg':
                    $this->screenshots[] = array('path' => "/" . $this->url . basename($filename), 'caption' => 'Summer (default) Variant');
                    break;
                case 'screenshot_spring.jpg':
                    $this->screenshots[] = array('path' => "/" . $this->url . basename($filename), 'caption' => 'Spring Variant');
                    break;
                case 'screenshot_autumn.jpg':
                    $this->screenshots[] = array('path' => "/" . $this->url . basename($filename), 'caption' => 'Autumn Variant');
                    break;
                case 'screenshot_winter.jpg':
                    $this->screenshots[] = array('path' => "/" . $this->url . basename($filename), 'caption' => 'Winter (both snow and non-snow) Variant');
                    break;
                case 'screenshot_winter_no_snow.jpg':
                    $this->screenshots[] = array('path' => "/" . $this->url . basename($filename), 'caption' => 'Winter (non-snow) Variant');
                    break;
                case 'screenshot_winter_snow.jpg':
                    $this->screenshots[] = array('path' => "/" . $this->url . basename($filename), 'caption' => 'Winter (snow) Variant');
                    break;
                case 'screenshot_winter_deep_snow.jpg':
                    $this->screenshots[] = array('path' => "/" . $this->url . basename($filename), 'caption' => 'Winter (deep snow) Variant');
                    break;
                case 'screenshot_winter_terramaxx_deep_snow.jpg':
                    $this->screenshots[] = array('path' => "/" . $this->url . basename($filename), 'caption' => 'Winter (deep snow TerraMaxx) Variant');
                    break;
            }
        }

        // Intercept the yoast opengraph call
        add_action('wpseo_opengraph', array($this, 'openGraph'));
        // Intercept the yoast twitter image call
        add_filter('wpseo_twitter_image', array($this, 'twitterImage'), 10, 1);

        $this->parse();
    }

    function enqueueScript() {
        // Inject the slick slider code. Can't do this in the getHTML function below because that's too late
        $slickScript = '<script type="text/javascript">
            $(document).ready(function(){
                $(".slick-screenshots").slick({
                    autoplay: true,
                    autoplaySpeed: 2000,
                    swipeToSlide: true,
                    dots: true
                });
            });
            </script>';

        wp_enqueue_script('slick', plugin_dir_url(__FILE__) . 'slick/slick.min.js', array(), false, true);
        wp_add_inline_script('slick', $slickScript, 'after');
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
                end($this->textures);
                $texture = &$this->textures[key($this->textures)]; // Reference to (not value of) last texture
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

            if (preg_match('/^Season (.*):\s+True/', $line, $matches) === 1) {
                $this->seasons[] = $matches[1];
                continue;
            }

            if (preg_match('/^File Path:\s+(.*)/', $line, $matches) === 1) {
                $this->filePath = $matches[1];
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
            $result .= "<div class='virtualPath'><dfn class='tooltip tooltip-right'>ⓘ<span>Scenery developers, use these paths to insert this item into your sceneries. You will see them inside <a href='https://developer.x-plane.com/tools/worldeditor/' target='_blank'>WED</a> and <a href='https://marginal.org.uk/x-planescenery/tools.html' target='_blank'>OverlayEditor</a>.</span></dfn><h2>Paths</h2>\n";

            foreach ($this->virtualPaths as $virtualPath) {
                $result .= $virtualPath . "<br />\n";
            }

            $result .= "</div>\n";
        }

        if (count($this->extendedVirtualPaths) > 0) {
            $result .= "<div class='extendedVirtualPath'><dfn class='tooltip tooltip-right'>ⓘ<span>These paths extend the built-in X-Plane® libraries, so are used to enhance the core simulator.</span></dfn><h2>Extended Library Paths</h2>\n";

            foreach ($this->extendedVirtualPaths as $extendedVirtualPath) {
                $result .= $extendedVirtualPath . "<br />\n";
            }

            $result .= "</div>\n";
        }

        if (count($this->deprecatedVirtualPaths) > 0) {
            $result .= "<div class='deprecatedVirtualPath'><dfn class='tooltip tooltip-right'>ⓘ<span>These paths were published in earlier versions of OpenSceneryX but should no longer be used. They are supported for backward-compatibility.</span></dfn><h2>Deprecated Paths</h2>\n";

            foreach ($this->deprecatedVirtualPaths as $deprecatedVirtualPath) {
                $result .= "<strong>From v" . $deprecatedVirtualPath['version'] . "</strong>: " . $deprecatedVirtualPath['path'] . "<br />\n";
            }

            $result .= "</div>\n";
        }

        if (count($this->externalVirtualPaths) > 0) {
            $result .= "<div class='externalVirtualPath'><dfn class='tooltip tooltip-right'>ⓘ<span>These paths are used where a 3rd party library has been merged into OpenSceneryX. They allow you to replace the 3rd party library with OpenSceneryX, but still allow older scenery packages to work. Scenery developers, you should use the OpenSceneryX paths instead, and if you are updating an old package then if possible please replace these paths with the new OpenSceneryX equivalents. </span></dfn><h2>3rd Party Library Paths</h2>\n";

            foreach ($this->externalVirtualPaths as $externalVirtualPath) {
                $result .= "<strong>To '" . $externalVirtualPath['library'] . "'</strong>: " . $externalVirtualPath['path'] . "<br />\n";
            }

            $result .= "</div>\n";
        }

        $result .= '<div class="threejs-container"></div>' . "\n";

        $ssCount = count($this->screenshots);
        if ($ssCount == 0) {
            $result .= "<img class='screenshot' src='/doc/screenshot_missing.png' alt='No Screenshot Available' />\n";
        } elseif ($ssCount == 1) {
            $result .= "<img class='screenshot' src='" . $this->screenshots[0]['path'] . "' alt='Screenshot of " . \str_replace("'", "&apos;", $this->title) . "' />\n";
        } else {
            // Uses http://kenwheeler.github.io/slick/ to present a carousel of screenshots
            $result .= '<div class="slick-screenshots">' . "\n";

            foreach ($this->screenshots as $screenshot) {
                $result .= '<div class="osx-slick-slide"><img class="osx-slick-image-screenshot" src="' . $screenshot['path'] . '"><div class="osx-slick-caption-screenshot">' . $screenshot['caption'] . '</div></div>';
            }

            $result .= '</div>' . "\n";
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
            $result .= "<li><span class='fieldTitle'>Available Since</span> <dfn class='tooltip'>ⓘ<span>This item was added in OpenSceneryX version " . $this->since . "</span></dfn>: <span class='fieldValue'>" . $this->since . "</span></li>\n";
        }

        $seasonCount = count($this->seasons);
        if ($seasonCount > 0) {
            $result .= "<li><span class='fieldTitle'>Has seasonal variants</span> <dfn class='tooltip'>ⓘ<span>This item changes with the seasons. You can choose the method to use when installing OpenSceneryX.</span></dfn></li>\n";
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
                $result .= "<li><span class='fieldTitle'>Texture&nbsp;'" . $texture['name'] . "'&nbsp;shared with <dfn class='tooltip'>ⓘ<span>This item uses textures that are shared with other items for efficiency.</span></dfn>:\n";
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
        $ssCount = count($this->screenshots);
        if ($ssCount > 0) {
            $object->add_image($this->screenshots[0]['path']);
        } else {
            $object->add_image("/doc/screenshot_missing.png");
        }
    }

    function twitterImage($img) {
        $ssCount = count($this->screenshots);
        if ($ssCount > 0) {
            return $this->screenshots[0]['path'];
        } else {
            return "/doc/screenshot_missing.png";
        }
    }

    protected abstract function getTypeSpecificHTML();
}
