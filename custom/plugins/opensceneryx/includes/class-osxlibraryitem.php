<?php

/**
 * Description of OSXLibraryItem
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

    protected $seasons = array('summer');

    /**
     * @var boolean If true, author email addresses will be output.  This should only be enabled if an email obfuscator plugin is installed
     * or if a proxy service is used (such as Cloudflare) that obfuscates emails
    */
    const OUTPUT_EMAILS = true;


    function __construct($path, $url, $itemType) {
        parent::__construct($path, $url, $itemType);

        $contents = file_get_contents($this->path . '/info.txt');
        $this->fileLines = explode(PHP_EOL, $contents);

        // Implement a presenter for the opengraph image. We only need this because there is a bug in the default implementation that means
        // that the width and height are not set correctly. Once fixed, remove this presenter and just use the wpseo_opengraph_image filter.
        // Disabled for the moment because if we include this with Yoast 14.2, we get two sets of og:image tags output to the page. We may
        // still need this once we can override the default og:image.
        //add_filter('wpseo_frontend_presenters', array($this, 'addPresenters'));

        // Intercept the yoast opengraph call
        add_filter('wpseo_opengraph_image', array($this, 'ogImage'), 10, 1);

        // Intercept the yoast twitter image call
        add_filter('wpseo_twitter_image', array($this, 'twitterImage'), 10, 1);

        $this->parse();

        foreach ($this->seasons as $season) {
            $ssFilename = "screenshot" . ($season == 'summer' ? "" : "_" . $season) . ".jpg";
            if (file_exists($this->path . "/" . $ssFilename)) {
                $this->screenshots[] = array('path' => "/" . $this->url . $ssFilename, 'caption' => $this->getHRSeason($season));
            } else {
                $this->screenshots[] = array('path' => "/doc/screenshot_missing.png", 'caption' => $this->getHRSeason($season));
            }
        }
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
                $this->virtualPaths[] = $this->addTypeExtension($matches[1]);
                continue;
            }

            if (preg_match('/^Export Deprecated v(.*):\s+(.*)/', $line, $matches) === 1) {
                $this->deprecatedVirtualPaths[] = array('version' => $matches[1], 'path' => $this->addTypeExtension($matches[2]));
                continue;
            }

            if (preg_match('/^Export External (.*):\s+(.*)/', $line, $matches) === 1) {
                $this->externalVirtualPaths[] = array('library' => $matches[1], 'path' => $this->addTypeExtension($matches[2]));
                continue;
            }

            if (preg_match('/^Export Core (.*)\s+(.*):\s+(.*)/', $line, $matches) === 1) {
                $this->coreVirtualPaths[] = array('method' => $matches[1], 'partial' => $matches[2], 'path' => $this->addTypeExtension($matches[3]));
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

        if (is_array($this->virtualPaths) && count($this->virtualPaths) > 0) {
            $result .= "<div class='virtualPath'><dfn class='tooltip tooltip-right'>ⓘ<span>Scenery developers, use these paths to insert this item into your sceneries. You will see them inside <a href='https://developer.x-plane.com/tools/worldeditor/' target='_blank'>WED</a> and <a href='https://marginal.org.uk/x-planescenery/tools.html' target='_blank'>OverlayEditor</a>.</span></dfn><h2>Paths</h2>\n";

            foreach ($this->virtualPaths as $virtualPath) {
                $result .= "<span class='notranslate'>" . $virtualPath . "</span><br />\n";
            }

            $result .= "</div>\n";
        }

        if (is_array($this->coreVirtualPaths) && count($this->coreVirtualPaths) > 0) {
            $result .= "<div class='extendedVirtualPath'><dfn class='tooltip tooltip-right'>ⓘ<span>These paths extend or override the built-in X-Plane® libraries, so are used to enhance the core simulator.<br />Those items marked as <em>'extended'</em> are mixed in with the existing X-Plane® items, while those marked as <em>'overridden'</em> completely replace them.</span></dfn><h2>Core X-Plane® Library Paths</h2>\n";

            foreach ($this->coreVirtualPaths as $coreVirtualPath) {
                $result .= "<strong>To " . $coreVirtualPath['partial'] . " (" . ($coreVirtualPath['method'] == 'Export' ? "overridden" : "extended") . ")</strong>: <span class='notranslate'>" . $coreVirtualPath['path'] . "</span><br />\n";
            }

            $result .= "</div>\n";
        }

        if (is_array($this->deprecatedVirtualPaths) && count($this->deprecatedVirtualPaths) > 0) {
            $result .= "<div class='deprecatedVirtualPath'><dfn class='tooltip tooltip-right'>ⓘ<span>These paths were published in earlier versions of OpenSceneryX but should no longer be used. They are supported for backward-compatibility.</span></dfn><h2>Deprecated Paths</h2>\n";

            foreach ($this->deprecatedVirtualPaths as $deprecatedVirtualPath) {
                $result .= "<strong>From v" . $deprecatedVirtualPath['version'] . "</strong>: <span class='notranslate'>" . $deprecatedVirtualPath['path'] . "</span><br />\n";
            }

            $result .= "</div>\n";
        }

        if (is_array($this->externalVirtualPaths) && count($this->externalVirtualPaths) > 0) {
            $result .= "<div class='externalVirtualPath'><dfn class='tooltip tooltip-right'>ⓘ<span>These paths are used where a 3rd party library has been merged into OpenSceneryX. They allow you to replace the 3rd party library with OpenSceneryX, but still allow older scenery packages to work. Scenery developers, you should use the OpenSceneryX paths instead, and if you are updating an old package then if possible please replace these paths with the new OpenSceneryX equivalents. </span></dfn><h2>3rd Party Library Paths</h2>\n";

            foreach ($this->externalVirtualPaths as $externalVirtualPath) {
                $result .= "<strong>To '" . $externalVirtualPath['library'] . "'</strong>: <span class='notranslate'>" . $externalVirtualPath['path'] . "</span><br />\n";
            }

            $result .= "</div>\n";
        }

        // Flat screenshots for Decals (and Facades, until we have got 3D previews working)
        if (get_class($this) == "OSXFacade" || get_class($this) == "OSXDecal") {
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
        } else {
            $result .= '<div class="threejs-container"></div>' . "\n";
        }

        if ($this->logo !== null) {
            $result .= "<div class='objectlogocontainer'>\n";
            $result .= "<img src='/doc/" . $this->logo . "' alt='Branding logo' />\n";
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
            $result .= "<li><span class='fieldTitle'>Conversion By:</span> ";

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
            $result .= "<li><span class='fieldTitle'>Modifications By:</span> ";

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

        if (get_class($this) == "OSXFacade" || get_class($this) == "OSXDecal") {
            if ($seasonCount > 0) {
                $result .= "<li><span class='fieldTitle'>Has seasonal variants</span> <dfn class='tooltip'>ⓘ<span>This item changes with the seasons. You can choose the method to use when installing OpenSceneryX.</span></dfn></li>\n";
            }
        } else {
            if ($seasonCount > 1) {
                $result .= "<li><span class='fieldTitle'>Seasonal variants</span> <dfn class='tooltip'>ⓘ<span>This item changes with the seasons. You can choose the method to use when installing OpenSceneryX. Click a season to see a 3D preview.</span></dfn>: \n";
                $result .= "<ul>\n";

                foreach ($this->seasons as $season) {
                    $result .= "<li><a id='" . $season . "' class='season-button' >" . $this->getHRSeason($season) . "</a></li>\n";
                }

                $result .= "</ul>\n";
                $result .= "</li>";
            }
        }

        $result .= "</ul>\n";

        if ($this->description !== null) {
            $result .= "<h2 class='description'>Description</h2>\n";
            $result .= "<div class='description'>" . $this->description . "</div>\n";
        }

        if ($this->note !== null) {
            $result .= "<h2 class='warning'>⚠︎ Important Note</h2>\n";
            $result .= "<div class='warning'>" . $this->note . "</div>\n";
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

        $result .= "<p>Please note that you must download the library as a whole from the <a href='/'>OpenSceneryX home page</a>, we do not provide downloads for individual items. If you are a scenery developer and want to know why this is, and how to use the library correctly in your sceneries, <a href='/support/scenery-developers/'>start here</a>.</p>";
        return $result;
    }

    function ogImage($img) {
        $ssCount = count($this->screenshots);
        if ($ssCount > 0) {
            return $this->screenshots[0]['path'];
        } else {
            return "/doc/screenshot_missing.png";
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

    private function getHRSeason($season) {
        switch ($season) {
            case 'summer':
                return 'Summer (default) Variant';
            case 'spring':
                return 'Spring Variant';
            case 'autumn':
                return 'Autumn Variant';
            case 'autumn_sam':
                return 'Autumn Variant, specific to SAM';
            case 'winter':
                return 'Winter (fallback for both snow and non-snow) Variant';
            case 'winter_no_snow':
                return 'Winter (non-snow) Variant';
            case 'winter_snow':
                return 'Winter (snow) Variant';
            case 'winter_sam_snow':
                return 'Winter (snow) Variant, specific to SAM';
            case 'winter_deep_snow':
                return 'Winter (deep snow) Variant';
            case 'winter_terramaxx_deep_snow':
                return 'Winter (deep snow) Variant, specific to TerraMaxx';
        }
    }

    public function getCSSClass() {
        return 'osx' . $this->itemType;
    }

    public function getMetaDescription($description) {
        // Just return the description.
        return $this->description;
    }

    /**
     * Ensure we highlight the appropriate menu items
     */
    public function menuItemClasses($classes, $item, $args) {
        // The main menu location on our theme is 'primary'
        if ('primary' !== $args->theme_location) return $classes;

        // Highlight our menu item, which is a plural version of the itemType, with first letter capitalised. 'Contents' is highlighted by the superclass.
        if (ucfirst($this->itemType . 's') == $item->title) $classes[] = 'current-menu-item';

        return parent::menuItemClasses($classes, $item, $args);
    }

    /**
     * Add the type file extension to a path if it doesn't already end with it
     */
    protected function addTypeExtension($path) {
        $extension = $this->getTypeExtension();
        if (substr_compare($path, $extension, -strlen($extension)) === 0) return $path;
        else return $path . $extension;
    }

    /**
     * Add a custom presenter for Open Graph Images. Only needed until Yoast fix the missing image width and height calculations in their class.
     */
    public function addPresenters($presenters) {
        $presenters[] = new OSXOGImagePresenter();
        return $presenters;
    }

    /**
     * Classes must override to provide class-specific description HTML
     */
    protected abstract function getTypeSpecificHTML();

    /**
     * Classes must override to provide the file extension for the type
     */
    protected abstract function getTypeExtension();
}
