
<?php require_once( dirname( __FILE__ ) . '/../include/theme-browser.php' ); ?>

<?php wptouch_rewind_themes(); ?>

<?php $cloud_themes = wptouch_get_available_cloud_themes(); ?>

<?php while ( wptouch_has_themes( true ) ) { ?>
	<?php wptouch_the_theme(); ?>
	<?php if ( wptouch_is_theme_in_cloud() ) { ?>
		<?php include( 'theme-browser-item.php' ); ?>
	<?php } ?>
<?php } ?>