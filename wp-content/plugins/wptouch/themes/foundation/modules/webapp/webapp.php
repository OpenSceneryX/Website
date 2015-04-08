<?php

add_action( 'wptouch_admin_page_render_wptouch-admin-theme-settings', 'foundation_webapp_settings' );



function foundation_webapp_settings( $page_options ) {
	$show_wam_settings = apply_filters( 'wptouch_allow_wam', true );

	wptouch_add_sub_page( FOUNDATION_PAGE_WEB_APP, 'foundation-page-webapp', $page_options );


	if ( !$show_wam_settings ) {
		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'Web-App Mode Unavailable', 'wptouch-pro' ),
			'foundation-web-app-settings',
			array(
				wptouch_add_pro_setting(
					'checkbox',
					'webapp_mode_unavailable',
					apply_filters( 'wptouch_allow_wam_message', 'Web-App Mode has been disabled by a theme or extension.' ),
					'',
					WPTOUCH_SETTING_BASIC,
					'3.6.6'
				)
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);
	} else {
		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'Settings', 'wptouch-pro' ),
			'foundation-web-app-settings',
			array(
				wptouch_add_pro_setting( 'checkbox', 'webapp_mode_enabled', __( 'Enable iOS Web-App Mode', 'wptouch-pro' ), '', WPTOUCH_SETTING_BASIC, '1.0' ),
				wptouch_add_pro_setting(
					'checkbox',
					'webapp_enable_persistence',
					__( 'Enable persistence', 'wptouch-pro' ),
					__( 'Loads the last visited URL for visitors on open.', 'wptouch-pro' ),
					WPTOUCH_SETTING_BASIC,
					'1.0.2'
				),
				$wam_settings[] = wptouch_add_pro_setting(
					'textarea',
					'webapp_ignore_urls',
					__( 'URLs to ignore in Web-App Mode', 'wptouch-pro' ),
					'',
					WPTOUCH_SETTING_BASIC,
					'1.0.2'
				)
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);

		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'Web App Title', 'wptouch-pro' ),
			'admin_menu_homescreen_icons_options',
			array(
				wptouch_add_pro_setting(
					'text',
					'homescreen_icon_title',
					__( 'Icon title', 'wptouch-pro' ),
					__( 'When visitors bookmark your website, this will be the title shown.', 'wptouch-pro' ),
					WPTOUCH_SETTING_BASIC,
					'1.0'
				),
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);


		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'Notice Message', 'wptouch-pro' ),
			'notice-message',
			array(
				wptouch_add_pro_setting( 'checkbox', 'webapp_show_notice', __( 'Show a notice message for iPhone, iPod touch & iPad visitors about my Web-App', 'wptouch-pro' ), __( 'WPtouch shows a notice bubble on 1st visit letting users know about your Web-App enabled website on iOS devices.', 'wptouch-pro' ), WPTOUCH_SETTING_BASIC, '1.0' ),
				wptouch_add_pro_setting( 'textarea', 'webapp_notice_message', __( 'Notice message contents', 'wptouch-pro' ), __( '[icon] is used to display the appropriate bookmark icon for your device. Do not remove it from your message.', 'wptouch-pro' ), WPTOUCH_SETTING_ADVANCED, '1.0' ),
				wptouch_add_pro_setting(
					'list',
					'webapp_notice_expiry_days',
					__( 'the notice message will be shown again for visitors', 'wptouch-pro' ),
					'',
					WPTOUCH_SETTING_ADVANCED,
					'1.0',
					array(
						'1' => __( '1 day until', 'wptouch-pro' ),
						'7' => __( '7 days until', 'wptouch-pro' ),
						'30' => __( '1 month until', 'wptouch-pro' ),
						'0' => __( 'Every time', 'wptouch-pro' )
					)
				)
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);

		/* Startup Screen Area */
		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'iPhone Startup Screen', 'wptouch-pro' ),
			'iphone-startup-screen',
			array(
				wptouch_add_pro_setting(
					'image-upload',
					'startup_screen_iphone_2g_3g',
					sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 320, 460 ),
					'',
					WPTOUCH_SETTING_BASIC,
					'1.0'
				),
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);

		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'Retina iPhone Startup Screen', 'wptouch-pro' ),
			'retina-iphone-startup-screen',
			array(
				wptouch_add_pro_setting(
					'image-upload',
					'startup_screen_iphone_4_4s',
					sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 640, 920 ),
					'',
					WPTOUCH_SETTING_BASIC,
					'1.0'
				),
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);

		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'iPhone 5 Startup Screen', 'wptouch-pro' ),
			'iphone-5-startup-screen',
			array(
				wptouch_add_pro_setting(
					'image-upload',
					'startup_screen_iphone_5',
					sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 640,1096 ),
					'',
					WPTOUCH_SETTING_BASIC,
					'1.0'
				),
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);


		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'iPhone 6 Startup Screen', 'wptouch-pro' ),
			'iphone-6-startup-screen',
			array(
				wptouch_add_pro_setting(
					'image-upload',
					'startup_screen_iphone_6',
					sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 750,1294 ),
					'',
					WPTOUCH_SETTING_BASIC,
					'1.0'
				),
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);

		wptouch_add_page_section(
			FOUNDATION_PAGE_WEB_APP,
			__( 'iPhone 6+ Startup Screen', 'wptouch-pro' ),
			'iphone-6plus-startup-screen',
			array(
				wptouch_add_pro_setting(
					'image-upload',
					'startup_screen_iphone_6plus',
					sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 1242,2148 ),
					'',
					WPTOUCH_SETTING_BASIC,
					'1.0'
				),
			),
			$page_options,
			FOUNDATION_SETTING_DOMAIN
		);

		if ( foundation_is_theme_using_module( 'tablets' ) ) {
			wptouch_add_page_section(
				FOUNDATION_PAGE_WEB_APP,
				__( 'iPad Mini and iPad Startup Screens', 'wptouch-pro' ),
				'ipad-mini-and-ipad-startup-screens',
				array(
					wptouch_add_pro_setting(
						'image-upload',
						'startup_screen_ipad_1_portrait',
						sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 768, 1004 ),
						'',
						WPTOUCH_SETTING_BASIC,
						'1.0'
					),
					wptouch_add_pro_setting(
						'image-upload',
						'startup_screen_ipad_1_landscape',
						sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 1024, 748 ),
						'',
						WPTOUCH_SETTING_BASIC,
						'1.0'
					)
				),
				$page_options,
				FOUNDATION_SETTING_DOMAIN
			);

			wptouch_add_page_section(
				FOUNDATION_PAGE_WEB_APP,
				__( 'Retina iPad Startup Screens', 'wptouch-pro' ),
				'retina-ipad-startup-screens',
				array(
					wptouch_add_pro_setting(
						'image-upload',
						'startup_screen_ipad_3_portrait',
						sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 1536, 2008 ),
						'',
						WPTOUCH_SETTING_BASIC,
						'1.0'
					),
					wptouch_add_pro_setting(
						'image-upload',
						'startup_screen_ipad_3_landscape',
						sprintf( __( '%d by %d pixels (PNG)', 'wptouch-pro' ), 2048, 1496 ),
						'',
						WPTOUCH_SETTING_BASIC,
						'1.0'
					)
				),
				$page_options,
				FOUNDATION_SETTING_DOMAIN
			);
		}
	}

	return $page_options;
}
