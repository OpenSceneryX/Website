<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<title><?php wp_title( ' | ', true, 'right' ); ?></title>
		<?php wptouch_head(); ?>
		<?php
			if ( isset( $_REQUEST[ 'wptouch_preview_theme' ] ) ) {
				$query_vars = $_REQUEST;
				if ( isset( $query_vars[ 'wptouch_preview_theme' ] ) ) {
					unset( $query_vars[ 'wptouch_preview_theme' ] );
				}
				echo '<link rel="canonical" href="' . substr( $_SERVER[ 'REQUEST_URI' ], 0, strpos( $_SERVER[ 'REQUEST_URI' ], '?' ) );
				if ( count( $query_vars ) > 0 ) {
					echo '?' . http_build_query( $query_vars );
				}
				echo '" />';
			}
		?>
	</head>

	<body <?php body_class( wptouch_get_body_classes() ); ?>>

		<?php do_action( 'wptouch_preview' ); ?>

		<?php do_action( 'wptouch_body_top' ); ?>

		<?php get_template_part( 'header-bottom' ); ?>

		<?php do_action( 'wptouch_body_top_second' ); ?>
