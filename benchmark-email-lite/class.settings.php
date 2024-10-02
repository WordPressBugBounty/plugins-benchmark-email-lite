<?php

// Exit If Accessed Directly
if( ! defined( 'ABSPATH' ) ) { exit; }

// Setings Class
class wpbme_settings {

	// Renders WP Settings API Forms
	static function page_settings() {

		wpbme_api::tracker( 'Settings' );

		// Permissions Check
		if( ! current_user_can( 'manage_options' ) ) {
			?>
			<div class="error">
				<p><?php
				_e(
					'You do not have sufficient permissions to access this page.',
					'benchmark-email-lite'
				);
				?></p>
			</div>
			<?php
			return;
		}

		// Nonce Check For Submitted Fields
		if(
			(
				isset( $_POST[ 'wpbme_key' ] )
				|| isset( $_POST[ 'wpbme_temp_token' ] )
				|| isset( $_POST[ 'wpbme_ap_token' ] )
				|| isset( $_POST[ 'wpbme_tracking_disable' ] )
				|| isset( $_POST[ 'wpbme_usage_disable' ] )
				|| isset( $_POST[ 'wpbme_debug' ] )
				|| isset( $_POST[ 'BME_USERNAME' ] )
				|| isset( $_POST[ 'BME_PASSWORD' ] )
			)
			&& ! wp_verify_nonce( $_POST['_wpnonce'], 'wbme_settings_form' )
		) {
			?>
			<div class="error">
				<p><?php
				_e(
					'You do not have sufficient permissions to access this page.',
					'benchmark-email-lite'
				);
				?></p>
			</div>
			<?php
			return;
		}

		// Track Updates
		$updated = false;

		// Maybe Run Authentication
		if( ! empty( $_POST['BME_USERNAME'] ) && ! empty( $_POST['BME_PASSWORD'] ) ) {

			wpbme_api::update_partner();

			$response = wpbme_api::authenticate(
				sanitize_text_field( $_POST['BME_USERNAME'] ),
				sanitize_text_field( $_POST['BME_PASSWORD'] )
			);

			if( $response && isset( $response['wpbme_key'] ) ) {

				$_POST['wpbme_key'] = $response['wpbme_key'];
				$_POST[ 'wpbme_temp_token'] = $response['wpbme_temp_token'];
				$_POST[ 'wpbme_ap_token'] = $response['wpbme_ap_token'];

				?>
				<div class="notice notice-success is-dismissible">
					<p><?php _e( 'Your login was successful and access keys have been saved.', 'benchmark-email-lite' ); ?></p>
				</div>
				<?php

			} else {

				?>
				<div class="notice notice-error is-dismissible">
					<p>
						<?php _e( 'The credential failed to authenticate.', 'benchmark-email-lite' ); ?>
						<?php echo $response['error']; ?>
					</p>
				</div>
				<?php

			}

		}

		// Auth Keys Update
		if( isset( $_POST[ 'wpbme_ap_token' ] ) ) {
			update_option(
				'wpbme_ap_token',
				sanitize_text_field( $_POST[ 'wpbme_ap_token' ] )
			);
			$updated = true;
		}
		if( isset( $_POST[ 'wpbme_key' ] ) ) {
			update_option(
				'wpbme_key',
				sanitize_text_field( $_POST[ 'wpbme_key' ] ) );
			$updated = true;
		}
		if( isset( $_POST[ 'wpbme_temp_token' ] ) ) {
			update_option(
				'wpbme_temp_token',
				sanitize_text_field( $_POST[ 'wpbme_temp_token' ] )
			);
			$updated = true;
		}

		// Tracker Disablement Update
		if(
			isset( $_POST[ 'wpbme_tracking_disable' ] )
			&& $_POST[ 'wpbme_tracking_disable' ] == 'yes'
		) {
			update_option( 'wpbme_tracking_disable', 'yes' );
			$updated = true;
		} elseif( isset( $_POST[ 'wpbme_key' ] ) ) {
			delete_option( 'wpbme_tracking_disable' );
			$updated = true;
		}

		// Usage Disablement Update
		if(
			isset( $_POST[ 'wpbme_usage_disable' ] )
			&& $_POST[ 'wpbme_usage_disable' ] == 'yes'
		) {
			update_option( 'wpbme_usage_disable', 'yes' );
			$updated = true;
		} elseif( isset( $_POST[ 'wpbme_key' ] ) ) {
			delete_option( 'wpbme_usage_disable' );
			$updated = true;
		}

		// Debug Update
		if(
			isset( $_POST[ 'wpbme_debug' ] )
			&& $_POST[ 'wpbme_debug' ] == 'yes'
		) {
			update_option( 'wpbme_debug', 'yes' );
			$updated = true;
		} elseif( isset( $_POST[ 'wpbme_key' ] ) ) {
			delete_option( 'wpbme_debug' );
			$updated = true;
		}

		// Display Update Made
		if( $updated ) {
			wpbme_api::update_partner();
			?>
			<div class="updated">
				<p><?php _e( 'Settings saved.', 'benchmark-email-lite' ); ?></p>
			</div>
			<?php
		}

		// Load Settings
		$wpbme_ap_token = get_option( 'wpbme_ap_token' );
		$wpbme_debug = get_option( 'wpbme_debug' );
		$wpbme_key = get_option( 'wpbme_key' );
		$wpbme_temp_token = get_option( 'wpbme_temp_token' );
		$wpbme_tracking_disable = get_option( 'wpbme_tracking_disable' );
		$wpbme_usage_disable = get_option( 'wpbme_usage_disable' );

		?>

		<div class="wrap">

			<h1><?php _e( 'Benchmark settings', 'benchmark-email-lite' ); ?></h1>
			<br />

			<form name="wbme_settings_form" method="post" action="">

				<?php wp_nonce_field( 'wbme_settings_form' ); ?>

				<fieldset style="border: 1px solid; padding: 1em; display: inline-block; margin-bottom: 2em;">

					<legend>
						<h2><?php _e( 'Benchmark connection', 'benchmark-email-lite' ); ?></h2>
					</legend>

					<p>
						<a href="https://ui.benchmarkemail.com/register?p=68907" target="_blank">
							<?php _e( 'Get a FREE Benchmark Email account!', 'benchmark-email-lite' ); ?>
						</a>
					</p>

					<p>
						<label style="display: block;">
							<?php _e( 'Benchmark Username', 'benchmark-email-lite' ); ?><br />
							<input type="text" name="BME_USERNAME" />
						</label>
					</p>

					<p>
						<label style="display: block;">
							<?php _e( 'Benchmark Password', 'benchmark-email-lite' ); ?><br />
							<input type="password" name="BME_PASSWORD" />
						</label>
					</p>

					<fieldset style="border: 1px solid; background-color: rgba( 0, 0, 0, 0.2 ); padding: 1em;">

						<legend>
							<h3><?php _e( 'Login details' , 'benchmark-email-lite' ); ?></h3>
						</legend>

						<p>
							<label style="display: block;">
								<?php _e( 'API Key', 'benchmark-email-lite' ); ?><br />
								<input type="text" size="36" id="wpbme_key" name="wpbme_key" value="<?php echo $wpbme_key; ?>" /><br />
								<em><?php _e( 'Authenticates communications with the Benchmark REST API.', 'benchmark-email-lite' ); ?></em>
							</label>
						</p>

						<p>
							<label style="display: block;">
								<?php _e( 'Authentication Token', 'benchmark-email-lite' ); ?><br />
								<input type="text" size="36" id="wpbme_temp_token" name="wpbme_temp_token" value="<?php echo $wpbme_temp_token; ?>" /><br />
								<em><?php _e( 'Authenticates your Benchmark Interface browser session.', 'benchmark-email-lite' ); ?></em>
							</label>
						</p>

						<p>
							<label style="display: block;">
								<?php _e( 'Automation Pro Token', 'benchmark-email-lite' ); ?><br />
								<input type="text" size="36" id="wpbme_ap_token" name="wpbme_ap_token" value="<?php echo $wpbme_ap_token; ?>" /><br />
								<em><?php _e( 'Authenticates front-end visitor tracker used by Automation Pro.', 'benchmark-email-lite' ); ?></em>
							</label>
						</p>

					</fieldset>

				</fieldset>

				<div style="clear: both;"></div>

				<fieldset style="border: 1px solid; padding: 1em; display: inline-block;">

					<legend>
						<h3><?php _e( 'Less common settings', 'benchmark-email-lite' ); ?></h3>
					</legend>

					<p>
						<label>
							<?php $wpbme_tracking_disable = $wpbme_tracking_disable == 'yes' ? 'checked="checked"' : ''; ?>
							<input type="checkbox" id="wpbme_tracking_disable" name="wpbme_tracking_disable" value="yes" <?php echo $wpbme_tracking_disable; ?> />
							<?php _e( 'Disable visitor tracking?', 'benchmark-email-lite' ); ?><br />
							<em><?php _e( 'Optionally disable the front-end visitor tracker used by Automation Pro conversion tracking.', 'benchmark-email-lite' ); ?></em>
						</label>
					</p>

					<p>
						<label>
							<?php $wpbme_usage_disable = $wpbme_usage_disable == 'yes' ? 'checked="checked"' : ''; ?>
							<input type="checkbox" id="wpbme_usage_disable" name="wpbme_usage_disable" value="yes" <?php echo $wpbme_usage_disable; ?> />
							<?php _e( 'Disable admin usage tracking?', 'benchmark-email-lite' ); ?><br />
							<em><?php _e( 'Optionally disable aggregate usage statistics for the developer of this plugin.', 'benchmark-email-lite' ); ?></em>
						</label>
					</p>

					<?php if( class_exists( 'WooCommerce' ) ) { ?>
					<p>
						<label>
							<?php $wpbme_debug = $wpbme_debug == 'yes' ? 'checked="checked"' : ''; ?>
							<input type="checkbox" id="wpbme_debug" name="wpbme_debug" value="yes" <?php echo $wpbme_debug; ?> />
							<?php _e( 'Enable debugging?', 'benchmark-email-lite' ); ?><br />
							<em><?php _e( 'Optionally enable logging of all API communications into WooCommerce, as available.', 'benchmark-email-lite' ); ?></em>
							<p>
								<a href="<?php echo admin_url( 'admin.php?page=wc-status&tab=logs' ); ?>">
									<?php _e( 'Logs are stored in WooCommerce', 'benchmark-email-lite' ); ?>
								</a>
							</p>
						</label>
					</p>
					<?php } ?>

				</fieldset>

				<p class="submit">
					<input type="submit" name="Submit" class="button-primary"
						value="<?php esc_attr_e( 'Save Changes', 'benchmark-email-lite' ) ?>" />
				</p>

			</form>

		</div>

		<?php
	}
}
