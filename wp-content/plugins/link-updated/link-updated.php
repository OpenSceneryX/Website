<?php
/*
Plugin Name: Link Updated
Plugin URI: 
Description: Updates the link_updated field, so you can sort on link_updated.
Version: 1.0
Author: Maykel Loomans
Author URI: http://www.maykelloomans.com/
*/

/*

Output links chronologically:
get_bookmarks("category_name=Notes&orderby=updated&order=desc"); 

*/

//add_action('edit_link', 'change_link_updated');
add_action('add_link', 'change_link_updated');

function change_link_updated($link) {
	global $wpdb, $table_prefix;

	$wpdb->query('UPDATE '.$table_prefix.'links SET link_updated = NOW() WHERE link_id = '.$link); 
}