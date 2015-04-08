<?php
global $wptouch_tablet_list;
$wptouch_tablet_list = array(
	'iPad',											// Apple iPads
	array( 'Android', 'Tablet' ),					// Catches ALL Android devices/browsers that explicitly state they're tablets
	array( 'Nexus', '7' ),							// Nexus 7
	'Android',										// Catches ALL Android devices, not just smartphones : )
	'IEMobile/10.0',								// Windows IE 10 touch tablet devices
	'PlayBook',										// BB PlayBook
	'Xoom',											// Motorola Xoom
	'P160U',										// HP TouchPad
	'SCH-I800',										// Galaxy Tab
	'Kindle',										// Kindles
	'Silk'											// Kindles in Silk mode
);

add_action( 'wptouch_admin_page_render_wptouch-admin-theme-settings', 'foundation_tablet_settings' );
add_filter( 'wptouch_supported_device_classes', 'foundation_tablet_supported_device_classes' );

function foundation_tablet_supported_device_classes( $device_classes ) {
	global $wptouch_tablet_list;

	foreach( $wptouch_tablet_list as $tablet_user_agent ) {
		$device_classes[ 'default' ][] = $tablet_user_agent;
	}

	return $device_classes;
}

function foundation_is_tablet() {
	global $wptouch_tablet_list;
	global $wptouch_pro;

	if ( in_array( $wptouch_pro->active_device, $wptouch_tablet_list ) ) {
		return true;
	}

	return false;
}

function foundation_tablet_settings( $page_options ){

	wptouch_add_page_section(
		FOUNDATION_PAGE_HOMESCREEN_ICONS,
		__( 'iPad', 'wptouch-pro' ),
		'admin_menu_homescreen_ipad_retina',
		array(
			wptouch_add_setting(
				'image-upload',
				'ipad_icon_retina',
				sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 152, 152 ),
				'',
				WPTOUCH_SETTING_BASIC,
				'1.0'
			),
		),
		$page_options,
		FOUNDATION_SETTING_DOMAIN
	);

	return $page_options;
}