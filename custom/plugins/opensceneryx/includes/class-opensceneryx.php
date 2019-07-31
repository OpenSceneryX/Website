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

        add_action('wp_enqueue_scripts', array($this, 'osxScripts'));
        add_action('add_link', array($this, 'osxRefreshLinkUpdated'));

        add_shortcode('osxinfo', array($this, 'osxInfoShortcode'));
        add_shortcode('osxreleasenotes', array($this, 'osxReleaseNotesShortcode'));
        add_shortcode('osxlinks', array($this, 'osxLinksShortcode'));
        add_shortcode('osxlatestitems', array($this, 'osxLatestItemsShortcode'));

        add_filter('the_posts', array($this, 'osxPosts'));
        add_filter('page_css_class', array($this, 'osxMenuClasses'), 10, 5);
        add_filter('pre_post_link', array($this, 'osxPermalink'));
        add_filter('the_content', array($this, 'osxContent'));
        add_filter('wpseo_breadcrumb_links', array($this, 'osxBreadcrumbs')); // Override Yoast breadcrumbs
        add_filter('wpseo_json_ld_search_url', array($this, 'osxJSONLDSeachUrl')); // Override Yoast JSON LD search

        wp_enqueue_style('osx', plugin_dir_url(__FILE__) . 'osx.css');
        // Required by slick carousel
        wp_enqueue_style('slick', plugin_dir_url(__FILE__) . 'slick/slick.css');
        wp_enqueue_style('slick-theme', plugin_dir_url(__FILE__) . 'slick/slick-theme.css');
    }

    /**
     * Hook for 'the_posts' filter - detect whether we are on an OSX library item or category page and manufacture
     * a post if so.
     *
     * @param array $posts The array of posts to filter
     * @return array Filtered posts array (always either the passed-in array or an array with a single OSX post in it)
     */
    function osxPosts($posts) {
        if (empty($_SERVER['REQUEST_URI'])) {
            return $posts;
        }

        $urlVars = explode('/', $_SERVER['REQUEST_URI']);

        if (count($urlVars) < 1) {
            return $posts;
        }

        switch ($urlVars[1]) {
            case 'facades':
            case 'forests':
            case 'lines':
            case 'objects':
            case 'polygons':
                break;
            default:
                return $posts;
        }

        // If we get here we are on an OSX path

        $docPath = implode(array_slice($urlVars, 1), '/');
        $osxItemPath = ABSPATH . '../' . $docPath;

        $this->osxItem = $this->osxParseFolder($osxItemPath, $docPath, $urlVars[1]);
        add_action('wp_enqueue_scripts', array($this->osxItem, 'enqueueScript'));

        if ($this->osxItem == null) {
            error_log('Library URL Not Found: ' . $_SERVER['REQUEST_URI']);
            return $posts;
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
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->filter = 'raw';

        // This solves a problem with URLs which end in a number having that number duplicated (e.g. /2/ -> /2/2/)
        remove_action('template_redirect', 'redirect_canonical');

        return array($post);
    }


    function osxScripts()
    {
        wp_enqueue_script('versionInfo', '/../doc/versionInfo.js');
        // Required by slick carousel
        wp_enqueue_script('jQuery', '//code.jquery.com/jquery-1.11.0.min.js', array(), false, true);
        wp_enqueue_script('jQuery-migrate', '//code.jquery.com/jquery-migrate-1.2.1.min.js', array(), false, true);
        // Required by three.js 3d renderer
        wp_enqueue_script('three.js', '//cdnjs.cloudflare.com/ajax/libs/three.js/107/three.min.js', array(), false, true);
        wp_enqueue_script('3ddsloader', plugin_dir_url(__FILE__) . 'three.js/DDSLoader.js', array('three.js'), false, true);
        wp_enqueue_script('3orbitcontrols', plugin_dir_url(__FILE__) . 'three.js/OrbitControls.js', array('three.js'), false, true);
        // Scripts for specific item types should be included in the class enqueueScript() method
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
            case 'developerpackdownload': return "<script type='text/javascript'>document.write('<a href=\"https://downloads.opensceneryx.com/OpenSceneryX-DeveloperPack-' + osxVersion + '.zip\">OpenSceneryX Developer Pack ' + osxVersion + '</a>');</script>";
            default: return "ERROR: 'data' parameter not recognised.  Allowed values: version, versiondate, authors, objectcount, developerpackdownload";
        }
    }

    function osxReleaseNotesShortcode($attrs)
    {
        $releaseNotesPath = ABSPATH . '../doc/ReleaseNotes.html';

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

    function osxLatestItemsShortcode($atts) {
        extract(shortcode_atts(array(
            'cssclass' => 'latest-items',
            'count' => '10',
            'title' => '<h2>Latest Additions</h2>'
            ), $atts));

        $latestItemsPath = ABSPATH . '../doc/latestitems.tsv';

        // Read TSV file containing all latest items
        if (is_file($latestItemsPath)) {
            $csvFile = file($latestItemsPath);
            $data = [];
            foreach ($csvFile as $line) {
                $data[] = str_getcsv($line, "\t");
            }
        } else {
            // No latest items found, return nothing
            return "";
        }

        // Uses http://kenwheeler.github.io/slick/ to present a carousel of latest additions
        $result = $title;
        $result .= '<div class="lazy ' . $cssclass . '">' . "\n";

        foreach ($data as $item) {
            $result .= '<div class="osx-slick-slide"><a href="/' . $item[1] . '"><img class="osx-slick-image" data-lazy="/' . $item[1] . 'screenshot.jpg"></a><div class="osx-slick-caption">' . $item[0] . '</div></div>';
        }

        $result .= '</div>' . "\n";

        $slickScript = '<script type="text/javascript">
            $(document).ready(function(){
                $(".lazy").slick({
                lazyLoad: "ondemand",
                slidesToShow: 5,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 2000,
                swipeToSlide: true,
                dots: ' . (count($data) > 14 ? 'false' : 'true') . ',
                initialSlide: ' . rand(0, count($data) - 1) . '
            });
        });
        </script>';

        wp_add_inline_script('slick', $slickScript, 'after');

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

    function osxJSONLDSeachUrl() {
        // Supply our Google CSE Search URL so that the main Google search engine can use this for its Sitelinks searchbox if it cares to
        // Without this override, the default behaviour is to use the built-in Wordpress search which only searches posts and pages.
        return 'https://cse.google.co.uk/cse?cx=partner-pub-5631233433203577:vypgar-6zdh&ie=UTF-8&q={search_term_string}&sa=Search';
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
