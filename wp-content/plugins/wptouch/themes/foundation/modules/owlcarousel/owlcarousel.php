<?php

add_action( 'foundation_module_init_mobile', 'foundation_owlcarousel_init' );

function foundation_owlcarousel_init() {
	wp_enqueue_style(
		'foundation_owlcarousel',
		foundation_get_base_module_url() . '/owlcarousel/assets/owl.carousel.css',
		'',
		FOUNDATION_VERSION
	);

	wp_enqueue_script(
		'foundation_owlcarousel',
		foundation_get_base_module_url() . '/owlcarousel/owl.carousel.min.js',
		array( 'jquery' ),
		FOUNDATION_VERSION,
		true
	);
}