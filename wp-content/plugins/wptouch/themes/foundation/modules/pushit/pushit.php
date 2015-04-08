<?php

add_action( 'foundation_module_init_mobile', 'foundation_pushit_init' );

function foundation_pushit_init() {

	// PushIt CSS
	wp_register_style( 'foundation_pushit', foundation_get_base_module_url() . '/pushit/pushit.css' );

	wp_enqueue_style(
		'foundation_pushit',
		foundation_get_base_module_url() . '/pushit/pushit.css',
		'',
		FOUNDATION_VERSION
	);

	// PushIt JS
	wp_enqueue_script(
		'foundation_pushit',
		foundation_get_base_module_url() . '/pushit/pushit.js',
		array( 'jquery' ),
		FOUNDATION_VERSION,
		true
	);
}