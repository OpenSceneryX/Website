/* namespace all admin functions with fdnAdmin */

function fdnAdminCheckFeatured() {
	var featuredSelect = jQuery( '#featured_enabled' );
	jQuery( '#featured_category, #featured_tag, #featured_type, #featured_post_ids, #featured_post_type' ).parent().hide();
	// We're on the proper admin page
	if ( featuredSelect.attr( 'checked' ) ) {
		var featuredType = jQuery( '#featured_type' ).val();
		if ( featuredType == 'latest' ) {
			jQuery( '#featured_type' ).parent().animate( { height: 'toggle', opacity: 'toggle' }, 280 );
		} else if ( featuredType == 'category' ) {
			jQuery( '#featured_category, #featured_type' ).parent().animate( { height: 'toggle', opacity: 'toggle' }, 280 );
		} else if ( featuredType == 'tag' ) {
			jQuery( '#featured_tag, #featured_type' ).parent().animate( { height: 'toggle', opacity: 'toggle' }, 280 );
		} else if ( featuredType == 'posts' ) {
			jQuery( '#featured_post_ids, #featured_type' ).parent().animate( { height: 'toggle', opacity: 'toggle' }, 280 );
		} else if ( featuredType == 'post_type' ) {
			jQuery( '#featured_post_type, #featured_type' ).parent().animate( { height: 'toggle', opacity: 'toggle' }, 280 );
		}
	}
}

function fdnAdminAdsPlacement(){
	var presentationSelect = jQuery( '#advertising_location' );
	presentationSelect.change( function(){
		var selectedOption = presentationSelect.val();
		if ( selectedOption != 'header' ) {
			jQuery( '.wptouch-not-free #advertising_blog_listings, .wptouch-not-free #advertising_search' ).prop( 'disabled', 'disabled' )
		} else {
			jQuery( '.wptouch-not-free #advertising_blog_listings, .wptouch-not-free #advertising_search' ).prop( 'disabled', '' )
		}
	}).change();
}

function fdnCheckAddonsInfinityCDN() {
	var cdnState = jQuery( '#setting-cache_optimize_cdn input:checked' ).attr( 'value' );
	if ( cdnState == 'maxcdn' ) {
		jQuery( '#setting-media_optimize_cdn_prefix_1, #setting-media_optimize_cdn_prefix_2, #setting-media_optimize_cdn_prefix_3, #setting-media_optimize_cdn_prefix_4' ).show();
	} else {
		jQuery( '#setting-media_optimize_cdn_prefix_1, #setting-media_optimize_cdn_prefix_2, #setting-media_optimize_cdn_prefix_3, #setting-media_optimize_cdn_prefix_4' ).hide();
	}
}

function fdnCheckAddonsInfinity() {
	var infinityCheck = jQuery( '#cache_enable' );
	if ( infinityCheck.length ) {
		var checked = infinityCheck.attr( 'checked' );
		var allItems = jQuery( '#section-addons-infinity-cache li' ).not( 'li#setting-cache_enable' );
		if ( !checked ) {
			fdnCheckAddonsInfinityCDN();

			allItems.hide();
		} else {
			allItems.show();

			fdnCheckAddonsInfinityCDN();
		}
	}
}

function fdnCheckAddons() {
	fdnCheckAddonsInfinity();
	var infinityCheck = jQuery( '#cache_enable' );
	if ( infinityCheck.length ) {
		infinityCheck.change( function() {
			fdnCheckAddonsInfinity();
		});

		jQuery( '#setting-cache_optimize_cdn input[type=radio]' ).change( function() {
			fdnCheckAddonsInfinityCDN();
		});
	}

	var advertisingAddonLocation = jQuery( '#wptouch-addon-multi-ads' );
	if ( advertisingAddonLocation.length ) {
		jQuery( '#setting-advertising_header_ab_enabled, #setting-advertising_footer_ab_enabled, #setting-advertising_pre_content_ab_enabled, #setting-advertising_post_content_ab_enabled, #setting-advertising_mid_content_ab_enabled' ).change( function() {

				var thisId = jQuery( this ).prop( 'id' );
				var newId = thisId.replace( '_ab_enabled', '_code_2' );
				newId = newId.replace( 'setting-', '' );

				if ( !jQuery( this ).find( 'input' ).is( ":checked" ) ) {
					jQuery( '#' + newId ).parent().hide();
				} else {
					jQuery( '#' + newId ).parent().show();
				}
		}).change();

		jQuery( '#setting-advertising_header_enabled, #setting-advertising_footer_enabled, #setting-advertising_pre_content_enabled, #setting-advertising_post_content_enabled, #setting-advertising_mid_content_enabled' ).change( function() {
			if ( jQuery( this ).find( '.checkbox' ).is( ":checked" ) ) {
				jQuery( this ).parent().find( "input, textarea" ).parent().show();

				jQuery( '#setting-advertising_header_ab_enabled, #setting-advertising_footer_ab_enabled, #setting-advertising_pre_content_ab_enabled, #setting-advertising_post_content_ab_enabled' ).change();
			} else {
				jQuery( this ).parent().find( "input, textarea" ).parent().hide();
				jQuery( this ).show();
			}
		}).change();
	}
}

function fdnAdminReady() {
	var isFree = jQuery( '.wptouch-free' );

	webAppNotice = jQuery( '#webapp_notice_message' );
	if ( webAppNotice.is( 'textarea' ) ) {
		if ( webAppNotice.val().indexOf( '[device]' ) > -1 ) {
			notice_parts = jQuery( '#webapp_notice_message' ).val().split( '[device]' );
			webAppNotice.val( notice_parts[ 0 ] + 'home screen' + notice_parts[ 1 ] );
		}
	}

	fdnAdminAdsPlacement();

	// Featured Slider Show/Hide
	wptouchCheckToggle( '#featured_enabled', '#setting-featured_continuous, #setting-featured_grayscale, #setting-featured_autoslide, #setting-featured_speed, #setting-featured_filter_posts, #setting-featured_max_number_of_posts, #setting-featured_title_date' );

	// Login Options
	wptouchCheckToggle( '#show_login_box', '#setting-show_login_links' );

	// Related Posts Show/Hide
	if ( !isFree.length ) {
		wptouchCheckToggle( '#related_posts_enabled', '#setting-related_posts_max' );
	}

	// Custom Post Types Show/Hide
	wptouchCheckToggle( '#enable_custom_post_types', '#section-foundation-web-custom-post-types' );

	fdnAdminCheckFeatured();

	jQuery( '#featured_enabled' ).click( function() { fdnAdminCheckFeatured(); });
	jQuery( '#featured_type' ).change( function() { fdnAdminCheckFeatured(); });

	fdnCheckAddons();
}

jQuery( document ).ready( function() { fdnAdminReady(); });