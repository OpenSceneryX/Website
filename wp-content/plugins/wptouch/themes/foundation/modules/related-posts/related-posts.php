<?php

add_action( 'wptouch_admin_page_render_wptouch-admin-theme-settings', 'foundation_related_posts_settings' );


function foundation_related_posts_settings( $page_options ){
	wptouch_add_page_section(
		FOUNDATION_PAGE_GENERAL,
		__( 'Related Posts', 'wptouch-pro' ),
		'foundation-related-posts-settings',
		array(
			wptouch_add_pro_setting(
				'checkbox',
				'related_posts_enabled',
				__( 'Enable display of related content on single posts', 'wptouch-pro' ),
				'',
				WPTOUCH_SETTING_BASIC,
				'1.0.7'
			),
			wptouch_add_pro_setting(
				'checkbox',
				'related_posts_skip_tags',
				__( 'Ignore tags when identifying related posts', 'wptouch-pro' ),
				'',
				WPTOUCH_SETTING_BASIC,
				'1.0.7'
			),
			wptouch_add_pro_setting(
				'radiolist',
				'related_posts_max',
				__( 'Maximum number of related posts to show', 'wptouch-pro' ),
				'',
				WPTOUCH_SETTING_BASIC,
				'1.0.7',
				array(
					'2' => '2',
					'3' => '3',
					'4' => '4'
				)
			)
		),
		$page_options,
		FOUNDATION_SETTING_DOMAIN
	);

	return $page_options;

}