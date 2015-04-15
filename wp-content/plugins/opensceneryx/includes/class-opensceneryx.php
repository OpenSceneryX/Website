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

        add_action('wp', array($this, 'osxLibraryPage'));
        add_filter('page_css_class', array($this, 'osxMenuClasses'), 10, 5);
        add_filter('pre_post_link', array($this, 'osxPermalink'));
        add_filter('the_content', array($this, 'osxContent'));
        add_filter('wpseo_breadcrumb_links', array($this, 'osxBreadcrumbs'));
    }

    function osxLibraryPage() {
        global $wp_query;

        if (!empty($_SERVER['REQUEST_URI'])) {
            $urlVars = explode('/', $_SERVER['REQUEST_URI']);

            if (count($urlVars) < 1) {
                return;
            }

            if (!$wp_query->is_404) {
                return;
            }

		    switch ($urlVars[1]) {
                case 'doc':
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

            $details = $this->osxParseFolder($osxItemPath);

            $id = -42;
            $post = new stdClass();
            $post->ID = $id;
            $post->post_category = array('uncategorized'); //Add some categories. an array()???
            $post->post_title = $details['title'];
            $post->post_content = $details['content'];
            $post->post_excerpt = '';
            $post->post_status = 'publish';
            $post->post_type = 'osxitem';
            $post->post_author = 1;
            $post->post_parent = 753;
            $post->guid = $docPath;

            $wp_query->queried_object = $post;
            $wp_query->post = $post;
            $wp_query->found_posts = 1;
            $wp_query->post_count = 1;
            $wp_query->max_num_pages = 1;
            $wp_query->posts = array($post);

            $wp_query->is_single = true;
            $wp_query->is_singular = true;
            $wp_query->is_404 = false;
            $wp_query->is_posts_page = false;
            $wp_query->is_page = true;
            $wp_query->is_post = false;
        }
    }

    function osxMenuClasses($classes, $page, $depth, $args, $currentPage)
    {
        global $wp_query;

        if ($wp_query->post->post_type == 'osxitem') {
            $pageItemClass = 'page-item-' . $wp_query->post->post_parent;
            $classes = str_replace('current_page_item', '', $classes);
            $classes = str_replace($pageItemClass, $pageItemClass . ' current_page_item', $classes);
        }
        return $classes;
    }

    function osxPermalink($url)
    {
        global $wp_query;

        if ($wp_query->post->post_type == 'osxitem') {
            return $wp_query->post->guid;
        }

        return $url;
    }

    function osxContent($content)
    {
        if (function_exists('yoast_breadcrumb')) {
            $content = yoast_breadcrumb('<p id="breadcrumbs">', '</p>', false) . $content;
        }

        return $content;
    }

    function osxBreadcrumbs($breadcrumbs)
    {
        global $wp_query;
//print_r($breadcrumbs);
        if ($wp_query->post->post_type == 'osxitem') {

        }

        return $breadcrumbs;
    }

    function osxParseFolder($path)
    {
        if (is_file($path . '/category.txt')) {
            return $this->osxParseCategory($path . '/category.txt');
        } elseif (is_file($path . '/info.txt')) {
            return file_get_contents($path . '/info.txt');
        }

        return $result;
    }
    
    function osxParseCategory($path)
    {
        $result = array('title' => 'Not Found', 'content' => '');
        $contents = file_get_contents($path);
        $lines = explode(PHP_EOL, $contents);
        $matches = array();
        $foundSubcat = false;
        $foundItem = false;

        foreach ($lines as $line) {
            if (preg_match('/Title:\s+(.*)/', $line, $matches) === 1) {
                $result['title'] = $matches[1];
                continue;
            }

            if (preg_match('/Sub-category:\s+"(.*?)"\s+"(.*?)"/', $line, $matches) === 1) {
                if (!$foundSubcat) {
                    $foundSubcat = true;
                    $result['content'] .= '<h2>Sub-categories</h2>';
                }
                $result['content'] .= $this->osxGetSubcatHTML($path, $matches[1], $matches[2]);
                continue;
            }
                
            if (preg_match('/Item:\s+"(.*?)"\s+"(.*?)"/', $line, $matches) === 1) {
                if (!$foundItem) {
                    $foundItem = true;
                    $result['content'] .= '<h2>Objects</h2>';
                }
                $result['content'] .= $this->osxGetItemHTML($path, $matches[1], $matches[2]);
                continue;
            }
		}
		
		return $result; 
    }
    
    function osxGetSubcatHTML($parentPath, $subCategoryTitle, $subCategoryPath)
    {
        return "<h3 class='inline'><a href='" . $subCategoryPath . "'>" . $subCategoryTitle . "</a></h3>\n";
    }

    function osxGetItemHTML($parentPath, $itemTitle, $itemPath)
    {
        $result = "<div class='thumbnailcontainer'>\n";
		$result .= "<h4><a href='/" . $itemPath . "'>" . $itemTitle . "</a></h4><a href='/" . $itemPath . "' class='nounderline'>";
	//			if (sceneryObject.screenshotFilePath != ""):
		$result .= "<img src='/" . $itemPath . "/screenshot.jpg' alt='Screenshot of " . str_replace("'", "&apos;", $itemTitle) . "' />";
	//			else:
	//				htmlFileContent += "<img src='/doc/screenshot_missing.png' alt='No Screenshot Available' />"
		$result .=  "</a>\n";
		$result .=  "</div>\n";

        return $result;
    }
}
