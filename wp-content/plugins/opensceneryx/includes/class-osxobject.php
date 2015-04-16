<?php

/**
 * Description of OSXObject
 */
class OSXObject extends OSXItem {

    public $virtualPaths = array();
    public $deprecatedVirtualPaths = array();

    public $author = null;
    public $authorEmail = null;
    public $authorURL = null;
    public $textureAuthor = null;
    public $textureAuthorEmail = null;
    public $textureAuthorURL = null;
    public $conversionAuthor = null;
    public $conversionAuthorEmail = null;
    public $conversionAuthorURL = null;

    public $width = null;
    public $height = null;
    public $depth = null;

    public $description = null;

    public $animated = false;

    public $logo = null;

    public $note = null;


    function __construct($path, $url) {
        parent::__construct($path, $url);
        $this->parse();
    }

    function parse() {
        $contents = file_get_contents($this->path . '/info.txt');
        $lines = explode(PHP_EOL, $contents);
        $matches = array();

        foreach ($lines as $line) {
            if (preg_match('/^Title:\s+(.*)/', $line, $matches) === 1) {
                $this->title = $matches[1];
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

            if (preg_match('/^Author:\s+(.*?)/', $line, $matches) === 1) {
                $this->author = $matches[1];
                continue;
            }

            if (preg_match('/^Author, texture:\s+(.*?)/', $line, $matches) === 1) {
                $this->textureAuthor = $matches[1];
                continue;
            }

            if (preg_match('/^Author, conversion:\s+(.*?)/', $line, $matches) === 1) {
                $this->conversionAuthor = $matches[1];
                continue;
            }
		}
    }

    function getHTML() {
               /*




<li><span class='fieldTitle'>Original Author:</span> <span class='fieldValue'><a href='http://forums.x-plane.org/index.php?showuser=6024' onclick='window.open(this.href);return false;'>Daveduck</a></span></li>
<li><span class='fieldTitle'>Description:</span> <span class='fieldValue'>A drive-in movie theatre screen showing an image from "The Aviator".<br/><br/>There are no restrictions of any kind placed on this object.  You are free to do as you please with it, including directly distributing it in your scenery package independent of OpenScenery X.  Crediting Daveduck at X-Plane.org would be polite.<br/><br/>
You must include this license with any distribution.<br/><br/>
Image from "The Aviator" Copyright &copy; Warner Bros. Pictures 2004 all rights reserved.</span></li>
<li><span class='fieldTitle'>Dimensions:</span>
<ul class='dimensions'>
<li id='width'><span class='fieldTitle'>w:</span> 36.2m</li>
<li id='height'><span class='fieldTitle'>h:</span> 29.3m</li>
<li id='depth'><span class='fieldTitle'>d:</span> 6.2m</li>
</ul>
</li>
</ul>
</div>
         */
        $result = '';


        if (count($this->virtualPaths) > 0) {
            $result .= "<div class='virtualPath'><h3>Virtual Paths</h3>";

            foreach ($this->virtualPaths as $virtualPath) {
                $result .= $virtualPath . "<br />";
            }

            $result .= "</div>";
        }

        if (count($this->deprecatedVirtualPaths) > 0) {
            $result .= "<div class='deprecatedVirtualPath'><h3>Deprecated Paths</h3>";

            foreach ($this->deprecatedVirtualPaths as $deprecatedVirtualPath) {
                $result .= "<strong>From v" . $deprecatedVirtualPath['version'] . "</strong>: " . $deprecatedVirtualPath['path'] . "<br />";
            }

            $result .= "</div>";
        }

        if (is_file($this->path . "/screenshot.jpg")) {
            $result .= "<img class='screenshot' src='/" . $this->url . "/screenshot.jpg' alt='Screenshot of " . \str_replace("'", "&apos;", $this->title) . "' />";
        } else {
            $result .= "<img class='screenshot' src='/doc/screenshot_missing.png' alt='No Screenshot Available' />";
        }

        $result .= "<ul class='mainItemDetails'>";

        //if ($this->author !== null) {
        //    $result .= "<li><span class='fieldTitle'>Original Author:</span> <span class='fieldValue'><a href='";
        //    if ($this->authorURL. $this->authorhttp://forums.x-plane.org/index.php?showuser=6024' onclick='window.open(this.href);return false;'>Daveduck</a></span></li>
        //
        //}

        $result .= "</ul>";

        return $result;
    }
}
