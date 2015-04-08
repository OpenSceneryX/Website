<?php

add_action( 'foundation_module_init_mobile', 'foundation_wptouch_icons_init' );

function foundation_wptouch_icons_init() {
	wp_enqueue_style(
		'foundation_wptouch_icons_css',
		foundation_get_base_module_url() . '/wptouch-icons/css/wptouch-icons.css',
		'',
		FOUNDATION_VERSION,
		'screen'
	);
}