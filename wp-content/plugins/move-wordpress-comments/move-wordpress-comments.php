<?php
/**
 * @author Nicolas Kuttler <wp@nkuttler.de>
 * @package nkmovecomments
 */

/*
Plugin Name: Move WordPress Comments
Plugin URI: http://www.nkuttler.de/wordpress/nkmovecomments/
Author: Nicolas Kuttler
Author URI: http://www.nkuttler.de/
Description: Move comments between posts and fix comment threading.
Version: 0.2.1.1
*/

/**
 * Install hook
 *
 * @since unknown
 * @package nkmovecomment
 */
function nkmovecomments_install() {
    if (!get_option('nkmovecomments_active')) {
        update_option('nkmovecomments_active', 'on');
    }
}
register_activation_hook(__FILE__,'nkmovecomments_install');

/**
 * Main function. Move comments and catch widget output.
 *
 * @since unknown
 * @package nkmovecomment
 */
function nkmovecomments() {
	nkmovecomments_load_translation_file();
	if (current_user_can('moderate_comments')) {
		add_action( 'comment_text', 'nkmovecomments_form', 50 );
		if ($_POST['action'] == 'nkmovecomments') {
			global $wpdb;
	
			$postID = (int) $_POST['postID'];
			$parentID = (int) $_POST['parentID'];
			$commentID = (int) $_POST['commentID'];
			$oldpostID= (int) $_POST['oldpostID'];

			// Do some sanity checks
			if ( get_post( $postID ) == null )
				return;

			if ( get_post( $oldpostID ) == null )
				return;

			if ( get_comment( $parentID ) == null && $parentID != 0 )
				return;

			if ( get_comment( $commentID ) == null )
				return;

			if ( !nkmovecomments_valid_parent( $postID, $parentID ) )
				return;

			// move to different post
			if ( $postID != $oldpostID ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->comments SET comment_post_ID=$postID WHERE comment_ID=$commentID;") );

				// Change post count
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET comment_count=comment_count+1 WHERE ID=$postID" ) );
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET comment_count=comment_count-1 WHERE ID=$oldpostID" ) );
			}

			// move to different parent
			if ( $parentID != $commentID ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->comments SET comment_parent=$parentID WHERE comment_ID=$commentID;" ) );
			}

		}
		/* catch widget */
		if ( $_POST['nkmovecomments_active'] ) {
			update_option('nkmovecomments_active', $_POST['nkmovecomments_active']);
		}
	}
}
add_action('init', 'nkmovecomments');

/**
 * Return the move comments form
 *
 * @since unknown
 * @package nkmovecomment
 *
 * @param string $content a comment
 * @return string the comment with the form
 */
function nkmovecomments_form($content) {
	if (get_option('nkmovecomments_active') === 'on') {
		global $comment;
	
		$post   = __( 'Post:', 'nkmovecomments' );
		$parent = __( 'Parent comment:', 'nkmovecomments' );
		$thisis = sprintf( __( '(This is #%s)', 'nkmovecomments' ), $comment->comment_ID );
		$move   = __( 'Move', 'nkmovecomments' );

		$form=<<<EOF
		<div class="move-wordpress-comments" >
		<form action="#comment-$comment->comment_ID" method="post">
		$post
		<input type="text" size="5" name="postID" value="$comment->comment_post_ID" onblur="if(this.value == '') { this.value='$comment->comment_post_ID'}" onfocus="if (this.value == '$comment->comment_post_ID') {this.value=''}" />
		$parent
		<input type="text" size="5" name="parentID" value="$comment->comment_parent" onblur="if(this.value == '') { this.value='$comment->comment_parent'}" onfocus="if (this.value == '$comment->comment_parent') {this.value=''}" />
		$thisis
		<input type="hidden" name="commentID" value="$comment->comment_ID" />
		<input type="hidden" name="action" value="nkmovecomments" />
		<input type="hidden" name="oldpostID" value="$comment->comment_post_ID" />
		<input type="submit" value="$move" />
		</form >
		</div >
EOF;
	}
	return $content . $form;
}

/**
 * Check if the new parent is valid
 *
 * @since 0.2.0
 *
 * @param $postID the new post's id
 * @param $parentID the new parent'sid
 *
 * @return bool Is the parent comment a valid parent?
 */
function nkmovecomments_valid_parent( $postID, $parentID ) {
	if ( $parentID == 0 )
		return true;

	$args = array(
		'status'  => 'approve',
		'post_id' => $postID
	);
	$validParents = get_comments( $args );

	foreach( $validParents as $validParent ) {
		if ( $parentID == $validParent->comment_ID ) {
			return true;
		}
	}
	return false;
}

/**
 * Initialize the widget
 *
 * @since unknown
 * @package nkmovecomment
 */
function nkmovecomments_init() {
	register_sidebar_widget(__('Move Comments'), 'widget_nkmovecomments');
}
add_action('plugins_loaded', 'nkmovecomments_init');

/**
 * The widget
 *
 * @since unknown
 * @package nkmovecomment
 */
function widget_nkmovecomments($args) {
	if ( current_user_can('moderate_comments') ) {

		extract($args);
		echo $before_widget;
		echo $before_title;
		_e( 'Move Comments', 'nkmovecomments' );
		echo $after_title;
		?>
		<ul>
			<li>
				<form action="" method="post" style="display: inline;">
					<input type="hidden" name="nkmovecomments_active" value="on" />
 					<input type="submit" value="<?php _e( 'on', 'nkmovecomments' ) ?>" />
				</form>
				<form action="" method="post" style="display: inline;">
					<input type="hidden" name="nkmovecomments_active" value="off" />
 					<input type="submit" value="<?php _e( 'off', 'nkmovecomments' ) ?>" />
				</form> <?php /*
				<select name="nkmovecomments_active" >
					<option <?php if (get_option('nkmovecomments_active') === 'on') { echo 'selected="selected"'; } ?>>on</option>
					<option <?php if (get_option('nkmovecomments_active') === 'off') { echo 'selected="selected"'; } ?>>off</option>
				</select>
				<input type="submit" value="Submit">

				<input type="submit" name="nkmovecomments_active" value="on" <?php if (get_option('nkmovecomments_active') === 'on') { echo ' disabled="disabled"'; } ?>>
				<input type="submit" name="nkmovecomments_active" value="off"<?php if (get_option('nkmovecomments_active') === 'off') { echo ' disabled="disabled"'; } ?>>
				</form> */ ?>
			</li>
		</ul> <?php
		echo $after_widget;
	}
}

/**
 * Load Translations
 *
 * @since 0.2.0
 */
function nkmovecomments_load_translation_file() {
	$plugin_path = plugin_basename( dirname( __FILE__ ) .'/translations' );
	load_plugin_textdomain( 'nkmovecomments', '', $plugin_path );
}
