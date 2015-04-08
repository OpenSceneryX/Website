<?php
/**
 * @package OpenSceneryX Scenery Packages
 * @author Austin Goudge	
 * @version 1.0
 */
/*
Plugin Name: OpenSceneryX Scenery Packages
Plugin URI: http://www.opensceneryx.com
Description: Insert various lists of links into a post.
Author: Austin Goudge
Version: 1.0
Author URI: http://www.opensceneryx.com
*/

/*  Copyright 2011  Austin Goudge (email : austin at opensceneryx.com)
*/

function getOpenSceneryXLinks($atts, $content = null) {
	// Extract values from $attrs with defaults.  The values will be extracted directly into variables
	// in the current symbol table
	extract(shortcode_atts(array(
	        'linkcatid' => '0',
	        'cssclass' => 'multiple-airports',
	        ), $atts));
	
	$bookmarks = get_bookmarks(array('category' => $linkcatid, 'show_description' => true, 'show_updated' => true));
	
	$result = '<h2>' . $content . ' (' . count($bookmarks) . (count($bookmarks) == 1 ? ' site' : ' sites') . ')</h2>' . "\n";
	$result .= '<ul class="xoxo blogroll">' . "\n";

	foreach ($bookmarks as $bookmark) {
		$name = esc_attr(sanitize_bookmark_field('link_name', $bookmark->link_name, $bookmark->link_id, 'display'));
		$desc = esc_attr(sanitize_bookmark_field('link_description', $bookmark->link_description, $bookmark->link_id, 'display'));

		$result .= '<li class="' . $cssclass . '"><a href="' . $bookmark->link_url . '"' . ($bookmark->link_target != '' ? ' target="' . $bookmark->link_target . '"' : '') . '>' . $name . '</a>' . ($desc ? ' - ' . $desc : '') . ($bookmark->recently_updated == '1' ? ' - <span>' . sprintf(__('NEW! %s'), date(get_option('links_updated_date_format'), $bookmark->link_updated_f + (get_option('gmt_offset') * 3600))) . '</span>' : '') . '</li>' . "\n";
	}
	
	$result .= '</ul>' . "\n";
	
	return $result;
}

// Allow us to add the pull quote using Wordpress shortcode, "[osxlinks][/osxlinks]" 
add_shortcode('osx_links', 'getOpenSceneryXLinks');
