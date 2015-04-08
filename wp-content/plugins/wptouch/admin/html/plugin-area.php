<?php if ( function_exists( 'wptouch_pro_check_for_update' ) ) { ?>
	<?php if ( ( $version = wptouch_pro_check_for_update() ) !== false ) { ?>
		<?php if ( !wptouch_has_license() ) { ?>
		</tr>
		<tr class="plugin-update-tr">
			<td colspan="3" class="plugin-update colspanchange">
				<div class="update-message">
					<?php if ( !wptouch_has_license() ) { ?>
						<?php if ( wptouch_show_renewal_notice() ) { ?>
							<?php echo sprintf( __( 'A new product version (%s) is available. %sRenew your license%s to download this update and receive additional product support.', 'wptouch-pro' ), $version, '<a href="http://www.wptouch.com/renew/?utm_campaign=renew-plugins-page&utm_source=wptouch&utm_medium=web">', '</a>' ); ?>
						<?php } else if ( wptouch_should_show_license_nag() ) { ?>
							<?php echo sprintf( __( 'A new product version (%s) is available. Please %sactivate your license%s, or %spurchase a new license%s to enable updates and full product support.', 'wptouch-pro' ), $version, '<a href="' . wptouch_get_license_activation_url() . '">', '</a>', '<a href="http://www.wptouch.com/?utm_source=license_nag&utm_medium=web&utm_campaign=wptouch3_upgrades">', '</a>' ); ?>
						<?php } ?>
					<?php } ?>
				</div>
			</td>
		</tr>
		<?php } ?>
	<?php } ?>
<?php } ?>
