<?php

/*  Copyright 2015  Austin Goudge  (email : austin@opensceneryx.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Main plugin class
 *
 * @author austin
 */
class OpenSceneryX {
    protected $pluginDirPath;

    public function run($pluginDirPath)
    {
        $this->pluginDirPath = $pluginDirPath;

        // We want to intercept all requests to parse the URL
        add_action('template_redirect', array($this, 'osxURLHandler'));
    }

    /**
    * Parse the URL to check for an osx path (facades, forests, lines, objects, polygons).  If we find one, use a special
    * template that includes the docs for the specific item
    */
    function osxURLHandler()
    {
        global $wp_query, $osxItemPath;

        if (!empty($_SERVER['REQUEST_URI'])) {
            $urlVars = explode('/', $_SERVER['REQUEST_URI']);

            if (count($urlVars) < 2) {
                return;
            }

            if (!$wp_query->is_404) {
                return;
            }

            $wp_query->is_404 = false;

            switch ($urlVars[1]) {
                case 'facades':
                case 'forests':
                case 'lines':
                case 'objects':
                case 'polygons':
                    break;
                default:
                    return;
            }

            $docPath = implode(array_slice($urlVars, 1), '/');
            $osxItemPath = ABSPATH . $docPath;

            $template = $this->pluginDirPath . 'item.php';
            require($template);
            die;
        }
    }
}
