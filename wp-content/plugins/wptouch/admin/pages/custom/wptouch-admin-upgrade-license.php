<?php
	function wptouch_create_anonymous_nonce( $action ) {
		// Creates a valid WordPress nonce for anonymous requests.
		$uid = 0;
		$token = '';
		$i = wp_nonce_tick();
		$nonce = substr(wp_hash($i . '|' . $action . '|' . $uid . '|' . $token  , 'nonce'), -12, 10);
		return $nonce;
	}

	$nonce = wptouch_create_anonymous_nonce( 'wptouch_ajax_callback' );
	$callback = urlencode( admin_url( 'admin-ajax.php' ) );
	$iframe_source = 'https://www.wptouch.com/pricing/?utm_campaign=upgrade-license-from-admin&utm_medium=web&utm_source=wptouch&wptouchpro_callback_url=' . $callback . '&wptouchpro_callback_nonce=' . $nonce
?>
<iframe seamless style="width: 100%; height: 100%; min-height: 500px" src="<?php echo $iframe_source; ?>" id="upgrade-license-area" data-callback-url="<?php echo $callback; ?>" data-callback-nonce="<?php echo $nonce; ?>">

</iframe>