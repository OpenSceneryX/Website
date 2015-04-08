<?php
/**
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2012 Add This, LLC                                    |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 *
 * PHP version 5.3.6
 *
 * @category Class
 * @package  Wordpress_Plugin
 * @author   The AddThis Team <srijith@addthis.com>
 * @license  http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version  SVN: 1.0
 * @link     http://www.addthis.com/blog
 */

define('AT_API_URL', 'http://adt00:8080/live/red_lojson');

/**
 * Class for output addthis tool box
 *
 * @category Class
 * @package  Wordpress_Plugin
 * @author   The AddThis Team <srijith@addthis.com>
 * @license  http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version  Release: 1.0
 * @link     http://www.addthis.com/blog
 */
class Addthis_ToolBox
{

    const AT_ABOVE_POST_HOME = "at-above-post-homepage";
    const AT_BELOW_POST_HOME = "at-below-post-homepage";
    const AT_ABOVE_POST_PAGE = "at-above-post-page";
    const AT_BELOW_POST_PAGE = "at-below-post-page";
    const AT_ABOVE_POST = "at-above-post";
    const AT_BELOW_POST = "at-below-post";
    const AT_ABOVE_POST_CAT_PAGE = "at-above-post-cat-page";
    const AT_BELOW_POST_CAT_PAGE = "at-below-post-cat-page";
    const AT_ABOVE_POST_ARCH_PAGE = "at-above-post-arch-page";
    const AT_BELOW_POST_ARCH_PAGE = "at-below-post-arch-page";    
    const AT_CONTENT_BELOW_POST_HOME = "at-below-post-homepage-recommended";
    const AT_CONTENT_BELOW_POST_PAGE = "at-below-post-page-recommended";
    const AT_CONTENT_BELOW_POST = "at-below-post-recommended";
    const AT_CONTENT_BELOW_CAT_PAGE = "at-below-post-cat-page-recommended";
    const AT_CONTENT_BELOW_ARCH_PAGE = "at-below-post-arch-page-recommended";    
    const AT_CONTENT_ABOVE_POST_HOME = "at-above-post-homepage-recommended";
    const AT_CONTENT_ABOVE_POST_PAGE = "at-above-post-page-recommended";
    const AT_CONTENT_ABOVE_POST = "at-above-post-recommended";
    const AT_CONTENT_ABOVE_CAT_PAGE = "at-above-post-cat-page-recommended";
    const AT_CONTENT_ABOVE_ARCH_PAGE = "at-above-post-arch-page-recommended";

    //This associative array keeps relates a post ID to a Title and URL
    //  Javascript code will later use this to identify and mark up posts
    public static $postTitlesAndUrlsById;
    
    /**
     * Initializes the widget class.
     * */
    public function __construct()
    {
        add_filter('the_content', array($this, 'addWidget'));
        if ( has_excerpt()) {
            add_filter('the_excerpt', array($this, 'addWidget'));
        } else {
            add_filter('get_the_excerpt', array($this, 'addExcerptCode'));
        }
        self::$postTitlesAndUrlsById = array();
    }
    
    /**
     * Prepends a 3-letter non-printing code to a raw excerpt string.
     *   The order of the code identifies a type of post (archive, category, etc).
     *
     *   A piece of javascript later queries for this code, and inserts
     *   sharetoolbox and recommendedbox divs on either side of the excerpt.
     *
     * @param string $content Excerpt contents
     *
     * @return string
     */
    public function addExcerptCode($content)
    {
        if(!is_feed()) {
            self::$postTitlesAndUrlsById[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'url' => get_permalink(),
                'content' => $content
            );   
            
            if(preg_match('/[\+\-\*]{3}/', $content) == false) {
                //Homepage = + - *
                if (is_home() || is_front_page()) {
                    return '+' . '-' . '*' . $content;
                //Page = + * -
                } else if (is_page()) {
                    return '+' . '*' . '-' . $content;
                //Single Post = - * +
                } else if (is_single()) {
                    return '-' . '*' . '+' . $content;
                //Category = - + *
                }  else if (is_category()) {
                    return '-' . '+' . '*' . $content;
                //Archive = * + -
                }  else if (is_archive()) {
                    return '*' . '+' . '-' . $content;
                }
            }
        }
        return $content;
    }
    
    public static function getPostTitlesAndUrls()
    {
        return self::$postTitlesAndUrlsById;
    }

    /**
     * Adds toolbox to wp pages
     *
     * @param string $content Page contents
     *
     * @return string
     */
    public function addWidget($content)
    {
        if (Addthis_Wordpress::getPubid() && !is_404() && !is_feed()) {
            global $post;
            $postid = $post->ID;
            $at_flag = get_post_meta( $postid, '_at_widget', TRUE );
            if (is_home() || is_front_page()) {
                if($at_flag == '' || $at_flag == '1'){
                    $content  = self::_buildDiv(self::AT_ABOVE_POST_HOME) . 
                                self::_buildDiv(self::AT_CONTENT_ABOVE_POST_HOME) . 
                                $content;
                    $content .= self::_buildDiv(self::AT_BELOW_POST_HOME);
                    $content .= self::_buildDiv(self::AT_CONTENT_BELOW_POST_HOME);
                }
            } else if (is_page()) {
                if($at_flag == '' || $at_flag == '1'){
                    $content  = self::_buildDiv(self::AT_ABOVE_POST_PAGE) . 
                                self::_buildDiv(self::AT_CONTENT_ABOVE_POST_PAGE) . 
                                $content;
                    $content .= self::_buildDiv(self::AT_BELOW_POST_PAGE);
                    $content .= self::_buildDiv(self::AT_CONTENT_BELOW_POST_PAGE);
                }
            } else if (is_single()) {
                if($at_flag == '' || $at_flag == '1'){
                    $content  = self::_buildDiv(self::AT_ABOVE_POST) . 
                                self::_buildDiv(self::AT_CONTENT_ABOVE_POST, false) . 
                                $content;
                    $content .= self::_buildDiv(self::AT_BELOW_POST);
                    $content .= self::_buildDiv(self::AT_CONTENT_BELOW_POST, false);
                }
            }  else if (is_category()) {
                if($at_flag == '' || $at_flag == '1'){
                    $content  = self::_buildDiv(self::AT_ABOVE_POST_CAT_PAGE) . 
                                self::_buildDiv(self::AT_CONTENT_ABOVE_CAT_PAGE) . 
                                $content;
                    $content .= self::_buildDiv(self::AT_BELOW_POST_CAT_PAGE);
                    $content .= self::_buildDiv(self::AT_CONTENT_BELOW_CAT_PAGE);
                }
            }  else if (is_archive()) {
                if($at_flag == '' || $at_flag == '1'){
                    $content  = self::_buildDiv(self::AT_ABOVE_POST_ARCH_PAGE) . 
                                self::_buildDiv(self::AT_CONTENT_ABOVE_ARCH_PAGE) . 
                                $content;
                    $content .= self::_buildDiv(self::AT_BELOW_POST_ARCH_PAGE);
                    $content .= self::_buildDiv(self::AT_CONTENT_BELOW_ARCH_PAGE);
                }
            }     
        }

        return $content;
    }

    /**
     * Build toolbox div
     *
     * @param string $class Class name
     *
     * @return string
     */
    private static function _buildDiv($class, $inline_data = true)
    {
        $title = get_the_title();
        $url   = get_permalink();
        if($inline_data == true){
            return "<div class='".$class." addthis-toolbox at-wordpress-hide'".
                       " data-title='".$title."' data-url='".$url."'>".
                    "</div>";
        } else {
             return "<div class='".$class." addthis-toolbox at-wordpress-hide'></div>";
        }
    }

    /**
     * Get user's activated tools in addthis
     *
     * @return array
     */
    public static function getUserTools()
    {
        $curl = curl_init();
        $url  = AT_API_URL . '?pub='. Addthis_Wordpress::getPubid();
        $url .= '&dp=' . Addthis_Wordpress::getDomain();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);
        $activatedTools = null;

        if ($response) {
            foreach ($response as $key => $value) {
                if ($key == 'pc') {
                    $activatedTools = $value;
                    break;
                }
            }
        }
        return $activatedTools ? explode(',', $activatedTools) : array();
    }
}
