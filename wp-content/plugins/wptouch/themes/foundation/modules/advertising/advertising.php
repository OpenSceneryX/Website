<?php

add_action( 'wptouch_admin_page_render_wptouch-admin-theme-settings', 'foundation_ad_settings' );

function foundation_advertising_enabled() {
	return apply_filters( 'wptouch_founction_advertising_enabled', true );
}

function foundation_ad_settings( $page_options ) {
	wptouch_add_sub_page( FOUNDATION_PAGE_ADVERTISING, 'foundation-page-advertising', $page_options );

	if ( !foundation_advertising_enabled() ) {
		return $page_options;
	}	

	wptouch_add_page_section(
		FOUNDATION_PAGE_ADVERTISING,
		__( 'Service', 'wptouch-pro' ),
		'service',
		array(
			wptouch_add_pro_setting(
				'radiolist',
				'advertising_type',
				__( 'Choose a service', 'wptouch-pro' ),
				'',
				WPTOUCH_SETTING_BASIC,
				'1.0',
				array(
					'none' => __( 'None', 'wptouch-pro' ),
					'google' => __( 'Google Adsense', 'wptouch-pro' ),
					'custom' => _x( 'Custom', 'Refers to a custom advertising service', 'wptouch-pro' )
				)
			)
		),
		$page_options,
		FOUNDATION_SETTING_DOMAIN
	);

	wptouch_add_page_section(
		FOUNDATION_PAGE_ADVERTISING,
		__( 'Google AdSense', 'wptouch-pro' ),
		'google-adsense',
		array(
			wptouch_add_pro_setting( 'text', 'google_adsense_id', __( 'Publisher ID', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' ),
			wptouch_add_pro_setting( 'text', 'google_slot_id', __( 'Slot ID', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' ),
			wptouch_add_pro_setting( 
				'list', 
				'google_code_type', 
				__( 'Code Type', 'wptouch-pro'), '', 
				WPTOUCH_SETTING_ADVANCED, 
				'1.0.6',
				array( 'sync' => 'Synchronous', 'async' => 'Asynchronous' )
			)
		),
		$page_options,
		FOUNDATION_SETTING_DOMAIN
	);

	wptouch_add_page_section(
		FOUNDATION_PAGE_ADVERTISING,
		__( 'Custom Ads', 'wptouch-pro' ),
		'custom-ads',
		array(
			wptouch_add_pro_setting( 'textarea', 'custom_advertising_mobile', __( 'Mobile advertising script', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' )
		),
		$page_options,
		FOUNDATION_SETTING_DOMAIN
	);


	wptouch_add_page_section(
		FOUNDATION_PAGE_ADVERTISING,
		__( 'Ad Presentation', 'wptouch-pro' ),
		'ad-presentation',
		array(
			wptouch_add_pro_setting(
				'list',
				'advertising_location',
				__( 'Theme location', 'wptouch-pro' ),
				'',
				WPTOUCH_SETTING_BASIC,
				'1.0',
				array(
					'header' => __( 'In the header', 'wptouch-pro' ),
					'top-content' => __( 'Above the page content', 'wptouch-pro' ),
					'bottom-content' => __( 'Below the page content', 'wptouch-pro' )
				//	'footer' => __( 'In the footer', 'wptouch-pro' )
				)
			),
		),
		$page_options,
		FOUNDATION_SETTING_DOMAIN
	);
	wptouch_add_page_section(
		FOUNDATION_PAGE_ADVERTISING,
		__( 'Active Pages', 'wptouch-pro' ),
		'active-pages',
		array(
			wptouch_add_pro_setting( 'checkbox', 'advertising_blog_listings', __( 'Blog listings', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' ),
			wptouch_add_pro_setting( 'checkbox', 'advertising_single', __( 'Single posts', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' ),
			wptouch_add_pro_setting( 'checkbox', 'advertising_pages', __( 'Static pages', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' ),
			wptouch_add_pro_setting( 'checkbox', 'advertising_taxonomy', __( 'Taxonomy', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' ),
			wptouch_add_pro_setting( 'checkbox', 'advertising_search', __( 'Search results', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' )
		),
		$page_options,
		FOUNDATION_SETTING_DOMAIN
	);

	return $page_options;
	
}