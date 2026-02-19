<?php
/**
 * Settings page UI.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/pages
 */

/**
 * Renders the plugin Settings page and handles form save (delegates to options).
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/pages
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Page_Settings {

	/**
	 * Settings options instance (get/save).
	 *
	 * @since    1.0.0
	 * @var      Ecosys_Profile_Manager_Settings_Options
	 */
	private $options;

	/**
	 * @since    1.0.0
	 * @param    Ecosys_Profile_Manager_Settings_Options $options Options instance.
	 */
	public function __construct( Ecosys_Profile_Manager_Settings_Options $options ) {
		$this->options = $options;
	}

	/**
	 * Output the settings page and save on POST.
	 *
	 * @since    1.0.0
	 */
	public function render() {
		$updated = false;
		if ( $this->maybe_save() ) {
			$updated = true;
		}

		$notify_on_new_profile = $this->options->get_notify_on_new_profile();
		$smtp = $this->options->get_smtp_options();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php if ( $updated ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'ecosys-profile-manager' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="">
				<?php wp_nonce_field( 'ecosys_settings_save', 'ecosys_settings_nonce' ); ?>

				<h2 class="title"><?php esc_html_e( 'Email Settings', 'ecosys-profile-manager' ); ?></h2>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Notifications', 'ecosys-profile-manager' ); ?></th>
							<td>
								<fieldset>
									<label for="ecosys_notify_on_new_profile">
										<input name="ecosys_notify_on_new_profile" type="checkbox" id="ecosys_notify_on_new_profile" value="1" <?php checked( $notify_on_new_profile ); ?> />
										<?php esc_html_e( 'Notify on adding new Profile', 'ecosys-profile-manager' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Send an email to the site admin when a new profile is added.', 'ecosys-profile-manager' ); ?></p>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>

				<h2 class="title" style="margin-top: 24px;"><?php esc_html_e( 'SMTP Settings', 'ecosys-profile-manager' ); ?></h2>
				<p class="description" style="margin-bottom: 12px;"><?php esc_html_e( 'Configure SMTP to send mail via a mail server. You can use WordPress default mail or SMTP. Test mail always uses SMTP (when configured) to verify your settings.', 'ecosys-profile-manager' ); ?></p>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Use SMTP', 'ecosys-profile-manager' ); ?></th>
							<td>
								<fieldset>
									<label for="ecosys_smtp_use_smtp">
										<input name="ecosys_smtp_use_smtp" type="checkbox" id="ecosys_smtp_use_smtp" value="1" <?php checked( ! empty( $smtp['use_smtp'] ) ); ?> />
										<?php esc_html_e( 'Use SMTP for sending mail', 'ecosys-profile-manager' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'If unchecked, WordPress default mail (PHP mail()) is used for plugin and site emails. If checked, the SMTP settings below are used. Test mail always uses SMTP regardless.', 'ecosys-profile-manager' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="ecosys_smtp_host"><?php esc_html_e( 'SMTP Host', 'ecosys-profile-manager' ); ?></label></th>
							<td>
								<input name="ecosys_smtp_host" type="text" id="ecosys_smtp_host" value="<?php echo esc_attr( $smtp['host'] ); ?>" class="regular-text" placeholder="smtp.example.com" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="ecosys_smtp_port"><?php esc_html_e( 'Port', 'ecosys-profile-manager' ); ?></label></th>
							<td>
								<input name="ecosys_smtp_port" type="number" id="ecosys_smtp_port" value="<?php echo esc_attr( $smtp['port'] ); ?>" class="small-text" min="1" max="65535" />
								<p class="description"><?php esc_html_e( 'Common: 587 (TLS), 465 (SSL), 25 (none).', 'ecosys-profile-manager' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="ecosys_smtp_encryption"><?php esc_html_e( 'Encryption', 'ecosys-profile-manager' ); ?></label></th>
							<td>
								<select name="ecosys_smtp_encryption" id="ecosys_smtp_encryption">
									<option value="none" <?php selected( $smtp['encryption'], 'none' ); ?>><?php esc_html_e( 'None', 'ecosys-profile-manager' ); ?></option>
									<option value="tls" <?php selected( $smtp['encryption'], 'tls' ); ?>><?php esc_html_e( 'TLS', 'ecosys-profile-manager' ); ?></option>
									<option value="ssl" <?php selected( $smtp['encryption'], 'ssl' ); ?>><?php esc_html_e( 'SSL', 'ecosys-profile-manager' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="ecosys_smtp_username"><?php esc_html_e( 'Username', 'ecosys-profile-manager' ); ?></label></th>
							<td>
								<input name="ecosys_smtp_username" type="text" id="ecosys_smtp_username" value="<?php echo esc_attr( $smtp['username'] ); ?>" class="regular-text" autocomplete="off" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="ecosys_smtp_password"><?php esc_html_e( 'Password', 'ecosys-profile-manager' ); ?></label></th>
							<td>
								<input name="ecosys_smtp_password" type="password" id="ecosys_smtp_password" value="<?php echo esc_attr( $smtp['password'] ); ?>" class="regular-text" autocomplete="new-password" />
								<p class="description"><?php esc_html_e( 'Leave blank to keep existing password.', 'ecosys-profile-manager' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="ecosys_smtp_from_email"><?php esc_html_e( 'From Email', 'ecosys-profile-manager' ); ?></label></th>
							<td>
								<input name="ecosys_smtp_from_email" type="email" id="ecosys_smtp_from_email" value="<?php echo esc_attr( $smtp['from_email'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="ecosys_smtp_from_name"><?php esc_html_e( 'From Name', 'ecosys-profile-manager' ); ?></label></th>
							<td>
								<input name="ecosys_smtp_from_name" type="text" id="ecosys_smtp_from_name" value="<?php echo esc_attr( $smtp['from_name'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Use for entire site', 'ecosys-profile-manager' ); ?></th>
							<td>
								<fieldset>
									<label for="ecosys_smtp_use_for_entire_site">
										<input name="ecosys_smtp_use_for_entire_site" type="checkbox" id="ecosys_smtp_use_for_entire_site" value="1" <?php checked( ! empty( $smtp['use_for_entire_site'] ) ); ?> />
										<?php esc_html_e( 'Use this SMTP for the entire WordPress site', 'ecosys-profile-manager' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'If checked, all outgoing mail (password resets, other plugins, etc.) will use this SMTP. If unchecked, only this plugin\'s emails use SMTP. Requires "Use SMTP" above.', 'ecosys-profile-manager' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Test mail', 'ecosys-profile-manager' ); ?></th>
							<td>
								<p class="description" style="margin-bottom: 8px;"><?php esc_html_e( 'Save settings first, then send a test email to verify SMTP.', 'ecosys-profile-manager' ); ?></p>
								<input type="email" id="ecosys_test_mail_to" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" style="max-width: 280px;" />
								<button type="button" class="button" id="ecosys-send-test-mail"><?php esc_html_e( 'Send test email', 'ecosys-profile-manager' ); ?></button>
								<span id="ecosys-test-mail-result" style="margin-left: 8px;"></span>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Email debug log', 'ecosys-profile-manager' ); ?></th>
							<td>
								<button type="button" class="button" id="ecosys-view-email-debug-log"><?php esc_html_e( 'View email debug log', 'ecosys-profile-manager' ); ?></button>
								<p class="description"><?php esc_html_e( 'Shows SMTP conversation and errors from the last email sends.', 'ecosys-profile-manager' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button( __( 'Save Settings', 'ecosys-profile-manager' ) ); ?>
			</form>
		</div>

		<div id="ecosys-email-debug-log-dialog" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 100050; align-items: center; justify-content: center;">
			<div style="background: #fff; padding: 0; max-width: 700px; width: 90%; max-height: 85vh; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
				<div style="padding: 16px 20px; border-bottom: 1px solid #c3c4c7; display: flex; justify-content: space-between; align-items: center;">
					<h2 style="margin: 0; font-size: 18px;"><?php esc_html_e( 'Email debug log', 'ecosys-profile-manager' ); ?></h2>
					<button type="button" class="button" id="ecosys-debug-log-close" style="margin: 0;"><?php esc_html_e( 'Close', 'ecosys-profile-manager' ); ?></button>
				</div>
				<div style="padding: 12px 20px; border-bottom: 1px solid #c3c4c7; display: flex; gap: 8px;">
					<button type="button" class="button" id="ecosys-debug-log-clear"><?php esc_html_e( 'Clear log', 'ecosys-profile-manager' ); ?></button>
				</div>
				<pre id="ecosys-debug-log-content" style="margin: 0; padding: 16px 20px; overflow: auto; flex: 1; font-size: 12px; line-height: 1.5; background: #f6f7f7; white-space: pre-wrap; word-break: break-all;"><?php esc_html_e( 'Loading…', 'ecosys-profile-manager' ); ?></pre>
			</div>
		</div>

		<script>
		(function($) {
			var debugLogNonce = '<?php echo esc_js( wp_create_nonce( 'ecosys_get_email_debug_log' ) ); ?>';
			var clearLogNonce = '<?php echo esc_js( wp_create_nonce( 'ecosys_clear_email_debug_log' ) ); ?>';
			var $dialog = $('#ecosys-email-debug-log-dialog');
			var $content = $('#ecosys-debug-log-content');

			function loadDebugLog() {
				$content.text('<?php echo esc_js( __( 'Loading…', 'ecosys-profile-manager' ) ); ?>');
				$.post(ajaxurl, { action: 'ecosys_get_email_debug_log', nonce: debugLogNonce }).done(function(res) {
					if (res.success && res.data && res.data.log !== undefined) {
						$content.text(res.data.log || '<?php echo esc_js( __( 'No log entries yet. Send a test email to capture SMTP debug output.', 'ecosys-profile-manager' ) ); ?>');
					} else {
						$content.text('<?php echo esc_js( __( 'Could not load log.', 'ecosys-profile-manager' ) ); ?>');
					}
				}).fail(function() {
					$content.text('<?php echo esc_js( __( 'Request failed.', 'ecosys-profile-manager' ) ); ?>');
				});
			}

			$('#ecosys-view-email-debug-log').on('click', function() {
				$dialog.css('display', 'flex');
				loadDebugLog();
			});

			$('#ecosys-debug-log-close').on('click', function() {
				$dialog.hide();
			});

			$dialog.on('click', function(e) {
				if (e.target === $dialog[0]) $dialog.hide();
			});

			$('#ecosys-debug-log-clear').on('click', function() {
				var $btn = $(this);
				if (!confirm('<?php echo esc_js( __( 'Clear the entire debug log?', 'ecosys-profile-manager' ) ); ?>')) return;
				$btn.prop('disabled', true);
				$.post(ajaxurl, { action: 'ecosys_clear_email_debug_log', nonce: clearLogNonce }).done(function(res) {
					if (res.success) {
						$content.text('<?php echo esc_js( __( 'Log cleared.', 'ecosys-profile-manager' ) ); ?>');
					}
				}).always(function() {
					$btn.prop('disabled', false);
				});
			});

			$('#ecosys-send-test-mail').on('click', function() {
				var $btn = $(this);
				var $result = $('#ecosys-test-mail-result');
				var to = ($('#ecosys_test_mail_to').val() || '').trim();
				if (!to) {
					$result.css('color', '#b32d2e').text('<?php echo esc_js( __( 'Enter an email address.', 'ecosys-profile-manager' ) ); ?>');
					return;
				}
				$btn.prop('disabled', true);
				$result.removeClass('notice-success').css('color', '');
				$.post(ajaxurl, {
					action: 'ecosys_send_test_mail',
					nonce: '<?php echo esc_js( wp_create_nonce( 'ecosys_send_test_mail' ) ); ?>',
					to: to
				}).done(function(res) {
					if (res.success) {
						$result.css('color', '#00a32a').text(res.data && res.data.message ? res.data.message : '<?php echo esc_js( __( 'Test email sent.', 'ecosys-profile-manager' ) ); ?>');
					} else {
						$result.css('color', '#b32d2e').text(res.data && res.data.message ? res.data.message : '<?php echo esc_js( __( 'Failed to send.', 'ecosys-profile-manager' ) ); ?>');
					}
				}).fail(function() {
					$result.css('color', '#b32d2e').text('<?php echo esc_js( __( 'Request failed.', 'ecosys-profile-manager' ) ); ?>');
				}).always(function() {
					$btn.prop('disabled', false);
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Save settings if nonce and capability are valid.
	 *
	 * @since    1.0.0
	 * @return   bool True if save was performed.
	 */
	private function maybe_save() {
		if ( ! isset( $_POST['ecosys_settings_nonce'] ) ) {
			return false;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ecosys_settings_nonce'] ) ), 'ecosys_settings_save' ) ) {
			return false;
		}
		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) ) {
			return false;
		}

		$notify = isset( $_POST['ecosys_notify_on_new_profile'] );
		$this->options->save_notify_on_new_profile( $notify );

		$smtp = $this->options->get_smtp_options();
		if ( isset( $_POST['ecosys_smtp_host'] ) ) {
			$smtp['host'] = sanitize_text_field( wp_unslash( $_POST['ecosys_smtp_host'] ) );
		}
		if ( isset( $_POST['ecosys_smtp_port'] ) ) {
			$smtp['port'] = absint( $_POST['ecosys_smtp_port'] );
			if ( $smtp['port'] < 1 ) {
				$smtp['port'] = 587;
			}
		}
		if ( isset( $_POST['ecosys_smtp_encryption'] ) ) {
			$smtp['encryption'] = in_array( $_POST['ecosys_smtp_encryption'], array( 'none', 'tls', 'ssl' ), true ) ? $_POST['ecosys_smtp_encryption'] : 'tls';
		}
		if ( isset( $_POST['ecosys_smtp_username'] ) ) {
			$smtp['username'] = sanitize_text_field( wp_unslash( $_POST['ecosys_smtp_username'] ) );
		}
		if ( isset( $_POST['ecosys_smtp_password'] ) ) {
			$pass = $_POST['ecosys_smtp_password'];
			if ( (string) $pass !== '' ) {
				$smtp['password'] = $pass;
			}
		}
		if ( isset( $_POST['ecosys_smtp_from_email'] ) ) {
			$smtp['from_email'] = sanitize_email( wp_unslash( $_POST['ecosys_smtp_from_email'] ) );
		}
		if ( isset( $_POST['ecosys_smtp_from_name'] ) ) {
			$smtp['from_name'] = sanitize_text_field( wp_unslash( $_POST['ecosys_smtp_from_name'] ) );
		}
		$smtp['use_smtp']            = isset( $_POST['ecosys_smtp_use_smtp'] );
		$smtp['use_for_entire_site'] = isset( $_POST['ecosys_smtp_use_for_entire_site'] );
		$this->options->save_smtp_options( $smtp );

		return true;
	}
}
