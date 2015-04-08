<div class="check-group">

<label class="checklist" for="<?php wptouch_admin_the_setting_name(); ?>">
	<?php if ( wptouch_get_locale() == 'fr_FR' ) { ?>
	<?php wptouch_admin_the_setting_desc(); ?> :
	<?php } else { ?>
	<?php wptouch_admin_the_setting_desc(); ?>:
	<?php } ?>
</label>
<?php if ( wptouch_admin_setting_has_tooltip() ) { ?>
	<i class="wptouch-tooltip wptouch-icon-info-sign" title="<?php wptouch_admin_the_setting_tooltip(); ?>"></i>
<?php } ?>
<?php if ( wptouch_admin_get_setting_level() == WPTOUCH_SETTING_ADVANCED ) { ?> <span class="advanced"><?php _e( 'Advanced', 'wptouch-pro' ); ?></span><?php } ?>
<?php if ( wptouch_admin_is_setting_new() ) { ?>
	<span class="new"><?php _e( 'New', 'wptouch-pro' ); ?></span>
<?php } ?>
<?php include( WPTOUCH_DIR . '/include/html/pro.php' ); ?>

<?php while ( wptouch_admin_has_list_options() ) { ?>
	<?php wptouch_admin_the_list_option(); ?>

	<p><input type="checkbox" id="<?php wptouch_admin_the_setting_name(); ?>-<?php wptouch_admin_the_list_option_key(); ?>" name="<?php wptouch_admin_the_encoded_setting_name(); ?>[]" value="<?php wptouch_admin_the_list_option_key(); ?>"<?php if ( wptouch_admin_is_checklist_option_selected() ) echo " checked"; ?>> <label for="<?php wptouch_admin_the_setting_name(); ?>-<?php wptouch_admin_the_list_option_key(); ?>"><?php wptouch_admin_the_list_option_desc(); ?></label></p>
<?php } ?>
	<input type="hidden" name="checklist-<?php wptouch_admin_the_setting_name(); ?>" value="1">
</div>
