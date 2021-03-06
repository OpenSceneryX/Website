<?php
/**
 * Template for displaying search forms in Twenty Eleven
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>
	<form action="/search_gcse/" id="searchform">
    <label for="s" class="assistive-text"><?php _e( 'Search', 'twentyeleven' ); ?></label>
    <input type="text" name="q" id="s" size="31" value="<?php echo get_query_var( 'q', '' ); ?>" />
    <input type="submit" value="Search" class="submit" id="searchsubmit" />
  </form>
