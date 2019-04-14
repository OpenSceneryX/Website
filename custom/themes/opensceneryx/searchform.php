<?php
/**
 * Template for displaying search forms in Twenty Eleven
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>
	<form action="https://www.google.co.uk" id="searchform" target="_blank">
    <input type="hidden" name="cx" value="partner-pub-5631233433203577:vypgar-6zdh" />
    <input type="hidden" name="ie" value="UTF-8" />
    <label for="s" class="assistive-text"><?php _e( 'Search', 'twentyeleven' ); ?></label>
    <input type="text" name="q" id="s" size="31" />
    <input type="submit" name="sa" value="Search" class="submit" id="searchsubmit" />
  </form>

  <script type="text/javascript" src="https://www.google.co.uk/coop/cse/brand?form=cse-search-box&amp;lang=en"></script>
