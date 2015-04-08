// WPtouch Basic Client-side Ajax Routines
function WPtouchAjax( actionName, actionParams, callback ) {
	var ajaxData = {
		action: "wptouch_client_ajax",
		wptouch_action: actionName,
		wptouch_nonce: wptouchMain.security_nonce
	};

	for ( name in actionParams ) { ajaxData[name] = actionParams[name]; }

	jQuery.post( wptouchMain.ajaxurl, ajaxData, function( result ) {
		callback( result );
	});
}

jQuery( 'table' ).parent( 'p,div' ).addClass( 'table-parent' );

jQuery( '#footer .back-to-top' ).click( function( e ) {
	e.preventDefault();
	jQuery( window ).scrollTop( 0 );
});

function doWPtouchReady() {
	// Shortcode
	var shortcodeDiv = jQuery( '.wptouch-sc-content' );

	if ( shortcodeDiv.length ) {
		// We have a shortcode
		var params = {
			post_id: shortcodeDiv.attr( 'data-post-id' ),
			post_content: jQuery( '.wptouch-orig-content' ).html()
		};

		WPtouchAjax( 'handle_shortcode', params, function( response ) {
			if ( response == 'WPTOUCH_NO_SHORTCODE' ) {
				// No desktop shortcode, show the original one
				shortcodeDiv.hide();
				jQuery( '.wptouch-orig-content' ).show();
			} else {
				shortcodeDiv.html( response );
			}
		});
	}
}

jQuery( document ).ready( function() { doWPtouchReady(); });
