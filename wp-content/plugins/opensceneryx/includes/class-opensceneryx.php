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

    protected $osxItem;

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

            $this->osxItem = $this->osxParseFolder($osxItemPath, $docPath, $urlVars[1]);

            $id = -42;
            $post = new stdClass();
            $post->ID = $id;
            $post->post_category = array('uncategorized'); //Add some categories. an array()???
            $post->post_title = $this->osxItem->title;
            $post->post_content = $this->osxItem->getHTML();
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
        if (is_singular() && !is_front_page() && function_exists('yoast_breadcrumb')) {
            $content = yoast_breadcrumb('<p id="breadcrumbs">', '</p>', false) . $content;
        }

        return $content;
    }

    function osxBreadcrumbs($breadcrumbs)
    {
        global $wp_query;

        if ($wp_query->post->post_type == 'osxitem') {
            $breadcrumbs = array();
            $breadcrumbs[] = array('text' => 'Home', 'url' => '/', 'allow_html' => true);
            $breadcrumbs[] = array('text' => 'Catalogue', 'url' => '/catalogue', 'allow_html' => true);

            foreach ($this->osxItem->ancestors as $ancestor) {
                $breadcrumbs[] = array('text' => $ancestor->title, 'url' => '/' . $ancestor->url, 'allow_html' => true);
            }

            $breadcrumbs[] = array('text' => $this->osxItem->title, 'allow_html' => true);
        }

        return $breadcrumbs;
    }

    function osxParseFolder($path, $url, $itemType)
    {
        if (is_file($path . '/category.txt')) {
            return new OSXCategory($path, $url);
        } elseif (is_file($path . '/info.txt')) {
            switch ($itemType) {
                case 'facades':
                    return new OSXFacade($path, $url);
                case 'forests':
                    return new OSXForest($path, $url);
                case 'lines':
                    return new OSXLine($path, $url);
                case 'objects':
                    return new OSXObject($path, $url);
                case 'polygons':
                    return new OSXPolygon($path, $url);
            }
        }
    }
}
