<?php

// Exit If Accessed Directly
if( ! defined( 'ABSPATH' ) ) { exit; }

// Setings Class
class wpbme_settings {

	// Renders WP Settings API Forms
	static function page_settings() {

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
				isset( $_POST['wpbme_api_2025'] )
				|| isset( $_POST['wpbme_tracking_disable'] )
				|| isset( $_POST['wpbme_debug'] )
				|| isset( $_POST['wpbme_username'] )
				|| isset( $_POST['wpbme_password'] )
				|| isset( $_POST['wpbme_key'] )
				|| isset( $_POST['wpbme_base_url'] )
				|| isset( $_POST['wpbme_logout'] )
				|| isset( $_POST['wpbme_signup_form_ids'] )
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

		// Handle Log Out
		if( isset( $_POST[ 'wpbme_logout' ] ) && $_POST[ 'wpbme_logout' ] === 'yes' ) {
			delete_option( 'wpbme_api_2025' );
			delete_option( 'wpbme_ap_token' );
			delete_option( 'wpbme_key' );
			delete_option( 'wpbme_temp_token' );
			delete_option( 'wpbme_temp_token_ttl' );
			$updated = true;
		}

		// Maybe 2025 Authentication
		else if(
			! empty( $_POST[ 'wpbme_api_2025' ] ) && $_POST[ 'wpbme_api_2025' ] === 'yes'
			&& ! empty( $_POST[ 'wpbme_key' ] )
			&& ! empty( $_POST[ 'wpbme_base_url' ] )
		) {
			update_option( 'wpbme_api_2025', 'yes' );
			update_option( 'wpbme_key', sanitize_text_field( $_POST['wpbme_key'] ) );
			update_option( 'wpbme_base_url', sanitize_url( $_POST['wpbme_base_url'] ) );
			$updated = true;
		}

		// Maybe 2019 Authentication
		else if( ! empty( $_POST['wpbme_username'] ) && ! empty( $_POST['wpbme_password'] ) ) {

			$response = wpbme_api::authenticate(
				sanitize_text_field( $_POST['wpbme_username'] ),
				sanitize_text_field( $_POST['wpbme_password'] )
			);

			if( ! $response || empty( $response['wpbme_key'] ) ) {

				?>
				<div class="notice notice-error is-dismissible">
					<p>
						<?php _e( 'The credential failed to authenticate.', 'benchmark-email-lite' ); ?>
						<?php echo isset( $response['error'] ) ? $response['error'] : ''; ?>
					</p>
				</div>
				<?php

			} else {

				update_option( 'wpbme_ap_token', $response['wpbme_ap_token'] );
				update_option( 'wpbme_key', $response['wpbme_key'] );
				update_option( 'wpbme_temp_token', $response['wpbme_temp_token'] );
				$updated = true;

				?>
				<div class="notice notice-success is-dismissible">
					<p><?php _e( 'Your login was successful and access keys have been saved.', 'benchmark-email-lite' ); ?></p>
				</div>
				<?php

			}

		}

		// Save Fields
		if( isset( $_POST[ 'wpbme_tracking_disable' ] ) ) {
			update_option(
				'wpbme_tracking_disable',
				sanitize_text_field( $_POST[ 'wpbme_tracking_disable' ] )
			);
			$updated = true;
		}
		if( isset( $_POST[ 'wpbme_debug' ] ) ) {
			update_option(
				'wpbme_debug',
				sanitize_text_field( $_POST[ 'wpbme_debug' ] )
			);
			$updated = true;
		}
		if( isset( $_POST[ 'wpbme_signup_form_ids' ] ) ) {
			update_option(
				'wpbme_signup_form_ids',
				sanitize_text_field( $_POST[ 'wpbme_signup_form_ids' ] )
			);
			$updated = true;
		}

		// Update Feedback
		if( $updated ) {
			wpbme_api::update_partner();
			?>
			<div class="updated">
				<p><?php _e( 'Settings saved.', 'benchmark-email-lite' ); ?></p>
			</div>
			<?php
		}

		// Load Settings
		$wpbme_api_2025 = get_option( 'wpbme_api_2025' );
		$wpbme_key = get_option( 'wpbme_key' );
		$wpbme_debug = get_option( 'wpbme_debug' );
		$wpbme_tracking_disable = get_option( 'wpbme_tracking_disable' );
		$wpbme_signup_form_ids = get_option( 'wpbme_signup_form_ids' );
		?>

		<style type="text/css">
			div.benchmark-email-lite fieldset {
				border: 0.33em outset;
				padding: 1em;
			}
		</style>

		<div class="wrap benchmark-email-lite">

			<h1><?php _e( 'Benchmark settings', 'benchmark-email-lite' ); ?></h1>
			<br />

			<form name="wbme_settings_form" method="post" action="">

				<?php wp_nonce_field( 'wbme_settings_form' ); ?>

				<?php if( $wpbme_key ) /* AUTHENTICATED FIELDS */ { ?>

				<fieldset>

					<legend>
						<h2>
							Benchmark
							<?php echo $wpbme_api_2025 === 'yes' ? 'New Generation' : 'Classic';  ?>
						</h2>
					</legend>

					<p>
						<strong><?php _e( 'You are currently logged in.', 'benchmark-email-lite' ); ?></strong>
					</p>

					<p>
						<label>
							<input type="checkbox" id="wpbme_logout" name="wpbme_logout" value="yes" />
							<?php _e( 'Log out?', 'benchmark-email-lite' ); ?>
						</label>
					</p>

					<?php if( $wpbme_api_2025 === 'yes' ) { /* NEW BENCHMARK FIELDS */ ?>
					<p>
						<label style="display: block;">
							<?php _e( 'Signup Form IDs', 'benchmark-email-lite' ); ?><br />
							<textarea name="wpbme_signup_form_ids"><?php echo $wpbme_signup_form_ids; ?></textarea><br>
							<em>
								<?php _e( 'Enter one per line.', 'benchmark-email-lite' ); ?>
								<br>
								<?php
								printf(
									__(
										'Obtain these from <a target="Benchmark" href="%s">here</a>.',
										'benchmark-email-lite'
									),
									'https://app.benchmarkemail.io/forms/all'
								);
								?>
								<br>
								<?php _e( 'Click the Get Code button.', 'benchmark-email-lite' ); ?>
								<br>
								<?php
								_e(
									'Copy just the value that follows data-bme-form-id (within quotes).',
									'benchmark-email-lite'
								);
								?>
							</em>
						</label>
					</p>

					<?php } else { /* CLASSIC BENCHMARK FIELDS */ ?>
					<p>
						<label>
							<?php $wpbme_tracking_disable = $wpbme_tracking_disable == 'yes' ? 'checked="checked"' : ''; ?>
							<input type="checkbox" id="wpbme_tracking_disable" name="wpbme_tracking_disable" value="yes" <?php echo $wpbme_tracking_disable; ?> />
							<?php _e( 'Disable visitor tracking?', 'benchmark-email-lite' ); ?><br />
							<em>
								<?php
								_e(
									'Optionally disable the front-end visitor tracker used by Automation Pro conversion tracking.',
									'benchmark-email-lite'
								);
								?>
							</em>
						</label>
					</p>
					<?php } /* END CLASSIC BENCHMARK FIELDS */ ?>

					<?php if( class_exists( 'WooCommerce' ) ) { /* WOOCOMMERCE FIELDS */ ?>
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
					<?php } /* END WOOCOMMERCE FIELDS */ ?>

				</fieldset>

				<?php } else { /* NOT AUTHENTICATED FIELDS */ ?>

				<!--
				<fieldset>
					<legend><h2>Benchmark New Generation Accounts (October 2025+)</h2></legend>

					<p>
						<label>
							<input type="checkbox" id="wpbme_api_2025" name="wpbme_api_2025" value="yes" />
							<?php _e( 'Use the latest Benchmark system?', 'benchmark-email-lite' ); ?><br>
							<em><?php printf(
								__(
									'Please log in as account Owner and obtain your API credentials on the <a target="Benchmark" href="%s">New Benchmark API Page</a>.',
									'benchmark-email-lite'
								),
								'https://app.benchmarkemail.io/settings/api-keys'
							); ?></em>
						</label>

					</p>

					<p>
						<label style="display: block;">
							<?php _e( 'Your API Key', 'benchmark-email-lite' ); ?><br />
							<input type="password" name="wpbme_key" />
						</label>
					</p>

					<p>
						<label style="display: block;">
							<?php _e( 'Your API Base URL', 'benchmark-email-lite' ); ?><br />
							<input type="url" name="wpbme_base_url" />
						</label>
					</p>

				</fieldset>
				<br>
				-->

				<fieldset>
					<legend><h2>Benchmark Classic Accounts</h2></legend>

					<p>
						<label style="display: block;">
							<?php _e( 'Benchmark Username', 'benchmark-email-lite' ); ?><br />
							<input type="text" name="wpbme_username" />
						</label>
					</p>

					<p>
						<label style="display: block;">
							<?php _e( 'Benchmark Password', 'benchmark-email-lite' ); ?><br />
							<input type="password" name="wpbme_password" />
						</label>
					</p>

					<p>
						<?php _e( 'Don\'t have an account?', 'benchmark-email-lite' ); ?>
						<a href="https://ui.benchmarkemail.com/classic-register?p=68907" target="_blank">
							<?php _e( 'Sign up Free!', 'benchmark-email-lite' ); ?>
						</a>
					</p>

				</fieldset>

				<?php } /* END NOT AUTHENTICATED FIELDS */ ?>

				<p class="submit">
					<input type="submit" name="Submit" class="button-primary"
						value="<?php echo $wpbme_key
							? __( 'Save Changes', 'benchmark-email-lite' )
							: __( 'Sign In', 'benchmark-email-lite' ); ?>" />
				</p>

			</form>

		</div>

		<?php
	}
}