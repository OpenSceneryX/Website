<?php
	preg_match('/\[(.*?)\]/', wptouch_admin_get_setting_name(), $matches );
	$post_type_name = str_replace( array('[',']') , ''  , $matches[0] );
	$type_object = urlencode( serialize( get_post_type_object( $post_type_name ) ) );
?>

<input type="checkbox" class="checkbox" name="<?php wptouch_admin_the_encoded_setting_name(); ?>" id="<?php wptouch_admin_the_setting_name(); ?>" <?php if ( wptouch_admin_is_post_type_checked() ) { echo ' checked'; } ?><?php if ( wptouch_admin_is_setting_pro() ) echo ' disabled'; ?> value="<?php echo $post_type_name . '||||' . $type_object; ?>" />
<input type="hidden" name="object_<?php wptouch_admin_the_encoded_setting_name(); ?>" id="<?php wptouch_admin_the_setting_name(); ?>"<?php if ( wptouch_admin_is_post_type_checked() ) echo " checked"; ?><?php if ( wptouch_admin_is_setting_pro() ) echo ' disabled'; ?> value="<?php echo $post_type_name . '||||' . $type_object; ?>" />
<label class="checkbox" for="<?php wptouch_admin_the_setting_name(); ?>"><?php wptouch_admin_the_setting_desc(); ?></label>
<?php if ( wptouch_admin_setting_has_tooltip() ) { ?>
	<i class="wptouch-tooltip wptouch-icon-info-sign" title="<?php wptouch_admin_the_setting_tooltip(); ?>"></i>
<?php } ?>
<?php if ( wptouch_admin_get_setting_level() == WPTOUCH_SETTING_ADVANCED ) { ?> <span class="advanced"><?php _e( 'Advanced', 'wptouch-pro' ); ?></span><?php } ?>
<?php if ( wptouch_admin_is_setting_new() ) { ?> <span class="new"><?php _e( 'New', 'wptouch-pro' ); ?></span><?php } ?>
<?php include( WPTOUCH_DIR . '/include/html/pro.php' ); ?>