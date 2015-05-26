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
 * Additional credits:
 * Maykel Loomans http://www.maykelloomans.com/ for the 'link_updated' code.
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
        add_action('wp_enqueue_scripts', array($this, 'osxScripts'));
        add_action('add_link', array($this, 'osxRefreshLinkUpdated'));

        add_shortcode('osxinfo', array($this, 'osxInfoShortcode'));
        add_shortcode('osxreleasenotes', array($this, 'osxReleaseNotesShortcode'));
        add_shortcode('osxlinks', array($this, 'osxLinksShortcode'));

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
            if ($this->osxItem == null) {
                $wp_query->is_404 = true;
                error_log('Library URL Not Found: ' . $_SERVER['REQUEST_URI']);
                return;
            }

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

    function osxScripts()
    {
        wp_enqueue_script('versionInfo', '/doc/versionInfo.js');
    }

    /**
     * Updates the link_updated field when a link is initially created
     *
     * @global $wpdb Wordpress DB object
     * @global string $table_prefix Wordpress table prefix
     * @param int $link The ID of the link being updated
     */
    function osxRefreshLinkUpdated($link)
    {
        global $wpdb, $table_prefix;
        $wpdb->query('UPDATE ' . $table_prefix . 'links SET link_updated = NOW() WHERE link_id = ' . $link);
    }

    function osxInfoShortcode($attrs)
    {
        if (!array_key_exists('data', $attrs)) {
            return "ERROR: No 'data' parameter specified.  Allowed values: version, versiondate, authors, objectcount, developerpackdownload";
        }

        switch ($attrs['data']) {
            case 'version': return "<script type='text/javascript'>document.write(osxVersion);</script>";
            case 'versiondate': return "<script type='text/javascript'>document.write(osxVersionDate);</script>";
            case 'authors': return "<script type='text/javascript'>document.write(osxAuthors);</script>";
            case 'objectcount': return "<script type='text/javascript'>document.write(osxObjectCount);</script>";
            case 'developerpackdownload': return "<script type='text/javascript'>document.write('<a href=\"/downloads/OpenSceneryX-DeveloperPack-' + osxVersion + '.zip\">OpenSceneryX Developer Pack ' + osxVersion + '</a>');</script>";
            default: return "ERROR: 'data' parameter not recognised.  Allowed values: version, versiondate, authors, objectcount, developerpackdownload";
        }
    }

    function osxReleaseNotesShortcode($attrs)
    {
        $releaseNotesPath = ABSPATH . 'doc/ReleaseNotes.html';

        if (is_file($releaseNotesPath)) {
            return file_get_contents($releaseNotesPath);
        } else {
            return "ERROR: No release notes found";
        }
    }

    function osxLinksShortcode($atts, $content = null) {
        // Extract values from $attrs with defaults.  The values will be extracted directly into variables
        // in the current symbol table
        extract(shortcode_atts(array(
                'linkcatid' => '0',
                'cssclass' => 'multiple-airports',
                ), $atts));

        $bookmarks = get_bookmarks(array('category' => $linkcatid, 'show_description' => true, 'show_updated' => true));

        $result = '<h2>' . $content . ' (' . count($bookmarks) . (count($bookmarks) == 1 ? ' site' : ' sites') . ')</h2>' . "\n";
        $result .= '<ul class="xoxo blogroll">' . "\n";

        $newLinkAge = 90 * 24 * 60 * 60;

        foreach ($bookmarks as $bookmark) {
            $name = esc_attr(sanitize_bookmark_field('link_name', $bookmark->link_name, $bookmark->link_id, 'display'));
            $desc = esc_attr(sanitize_bookmark_field('link_description', $bookmark->link_description, $bookmark->link_id, 'display'));

            $result .= '<li class="' . $cssclass . '"><a href="' . $bookmark->link_url . '"'
                    . ($bookmark->link_target != '' ? ' target="' . $bookmark->link_target . '"' : '') . '>' . $name . '</a>'
                    . ($desc ? ' - ' . $desc : '')
                    . (time() - $bookmark->link_updated_f < $newLinkAge ? ' - <span>' . sprintf(__('NEW! %s'), date(get_option('links_updated_date_format'), $bookmark->link_updated_f + (get_option('gmt_offset') * 3600))) . '</span>' : '')
                    . '</li>' . "\n";
        }

        $result .= '</ul>' . "\n";

        return $result;
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
            $breadcrumbs[] = array('text' => 'Contents', 'url' => '/contents', 'allow_html' => true);

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

        return null;
    }
}
