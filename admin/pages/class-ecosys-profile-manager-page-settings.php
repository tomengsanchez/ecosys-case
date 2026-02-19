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
		$updated  = false;
		$test_msg = $this->maybe_send_test_mail();
		// Only save when not sending test mail (test mail button submit would overwrite checkbox state).
		if ( ! isset( $_POST['ecosys_send_test_mail'] ) && $this->maybe_save() ) {
			$updated = true;
		}

		$notify_on_new_profile = $this->options->get_notify_on_new_profile();
		$use_custom_smtp      = $this->options->get_use_custom_smtp();
		$smtp_host            = $this->options->get_smtp( 'host' );
		$smtp_port            = $this->options->get_smtp( 'port', '587' );
		$smtp_encryption      = $this->options->get_smtp( 'encryption', 'tls' );
		$smtp_username        = $this->options->get_smtp( 'username' );
		$smtp_from_email      = $this->options->get_smtp( 'from_email', get_option( 'admin_email' ) );
		$smtp_from_name       = $this->options->get_smtp( 'from_name', get_bloginfo( 'name' ) );
		$smtp_insecure_ssl    = $this->options->get_smtp( 'insecure_ssl', '0' ) === '1';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php if ( $updated ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'ecosys-profile-manager' ); ?></p></div>
			<?php endif; ?>
			<?php if ( $test_msg ) : ?>
				<div class="notice notice-<?php echo $test_msg['success'] ? 'success' : 'error'; ?> is-dismissible"><p><?php echo esc_html( $test_msg['message'] ); ?></p></div>
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
						<tr>
							<th scope="row"><?php esc_html_e( 'Email Method', 'ecosys-profile-manager' ); ?></th>
							<td>
								<fieldset>
									<label for="ecosys_use_custom_smtp">
										<input name="ecosys_use_custom_smtp" type="checkbox" id="ecosys_use_custom_smtp" value="1" <?php checked( $use_custom_smtp ); ?> />
										<?php esc_html_e( 'Use custom SMTP', 'ecosys-profile-manager' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'When unchecked, the default WordPress email (wp_mail) is used. When checked, emails are sent via your custom SMTP configuration below.', 'ecosys-profile-manager' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr id="ecosys-smtp-config-row" class="<?php echo $use_custom_smtp ? '' : 'ecosys-smtp-disabled'; ?>">
							<th scope="row"><?php esc_html_e( 'SMTP Configuration', 'ecosys-profile-manager' ); ?></th>
							<td id="ecosys-smtp-config-wrap">
								<table class="form-table ecosys-smtp-fields" role="presentation">
									<tr>
										<th scope="row"><label for="ecosys_smtp_host"><?php esc_html_e( 'SMTP Host', 'ecosys-profile-manager' ); ?></label></th>
										<td><input name="ecosys_smtp_host" type="text" id="ecosys_smtp_host" value="<?php echo esc_attr( $smtp_host ); ?>" class="regular-text" placeholder="smtp.example.com" <?php disabled( ! $use_custom_smtp ); ?> /></td>
									</tr>
									<tr>
										<th scope="row"><label for="ecosys_smtp_port"><?php esc_html_e( 'Port', 'ecosys-profile-manager' ); ?></label></th>
										<td><input name="ecosys_smtp_port" type="number" id="ecosys_smtp_port" value="<?php echo esc_attr( $smtp_port ); ?>" class="small-text" placeholder="587" min="1" max="65535" <?php disabled( ! $use_custom_smtp ); ?> /></td>
									</tr>
									<tr>
										<th scope="row"><label for="ecosys_smtp_encryption"><?php esc_html_e( 'Encryption', 'ecosys-profile-manager' ); ?></label></th>
										<td>
											<select name="ecosys_smtp_encryption" id="ecosys_smtp_encryption" <?php disabled( ! $use_custom_smtp ); ?>>
												<option value="" <?php selected( $smtp_encryption, '' ); ?>><?php esc_html_e( 'None', 'ecosys-profile-manager' ); ?></option>
												<option value="tls" <?php selected( $smtp_encryption, 'tls' ); ?>>TLS</option>
												<option value="ssl" <?php selected( $smtp_encryption, 'ssl' ); ?>>SSL</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="ecosys_smtp_username"><?php esc_html_e( 'Username', 'ecosys-profile-manager' ); ?></label></th>
										<td><input name="ecosys_smtp_username" type="text" id="ecosys_smtp_username" value="<?php echo esc_attr( $smtp_username ); ?>" class="regular-text" autocomplete="off" <?php disabled( ! $use_custom_smtp ); ?> /></td>
									</tr>
									<tr>
										<th scope="row"><label for="ecosys_smtp_password"><?php esc_html_e( 'Password', 'ecosys-profile-manager' ); ?></label></th>
										<td><input name="ecosys_smtp_password" type="password" id="ecosys_smtp_password" value="" class="regular-text" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Leave blank to keep existing', 'ecosys-profile-manager' ); ?>" <?php disabled( ! $use_custom_smtp ); ?> /></td>
									</tr>
									<tr>
										<th scope="row"><label for="ecosys_smtp_from_email"><?php esc_html_e( 'From Email', 'ecosys-profile-manager' ); ?></label></th>
										<td><input name="ecosys_smtp_from_email" type="email" id="ecosys_smtp_from_email" value="<?php echo esc_attr( $smtp_from_email ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" <?php disabled( ! $use_custom_smtp ); ?> /></td>
									</tr>
									<tr>
										<th scope="row"><label for="ecosys_smtp_from_name"><?php esc_html_e( 'From Name', 'ecosys-profile-manager' ); ?></label></th>
										<td><input name="ecosys_smtp_from_name" type="text" id="ecosys_smtp_from_name" value="<?php echo esc_attr( $smtp_from_name ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" <?php disabled( ! $use_custom_smtp ); ?> /></td>
									</tr>
									<tr>
										<th scope="row"></th>
										<td>
											<label for="ecosys_smtp_insecure_ssl">
												<input name="ecosys_smtp_insecure_ssl" type="checkbox" id="ecosys_smtp_insecure_ssl" value="1" <?php checked( $smtp_insecure_ssl ); ?> <?php disabled( ! $use_custom_smtp ); ?> />
												<?php esc_html_e( 'Allow insecure SSL (self-signed certificates)', 'ecosys-profile-manager' ); ?>
											</label>
											<p class="description"><?php esc_html_e( 'Enable if your SMTP server uses a self-signed certificate or SSL verification fails. Disable for production when possible.', 'ecosys-profile-manager' ); ?></p>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Test Email', 'ecosys-profile-manager' ); ?></th>
							<td>
								<?php wp_nonce_field( 'ecosys_test_mail', 'ecosys_test_mail_nonce' ); ?>
								<input type="email" name="ecosys_test_email_to" id="ecosys_test_email_to" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'email@example.com', 'ecosys-profile-manager' ); ?>" />
								<button type="submit" name="ecosys_send_test_mail" value="1" class="button button-secondary">
									<?php esc_html_e( 'Send Test Email', 'ecosys-profile-manager' ); ?>
								</button>
								<p class="description"><?php esc_html_e( 'Enter an email address and click to send a test email.', 'ecosys-profile-manager' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Email Debug Logs', 'ecosys-profile-manager' ); ?></th>
							<td>
								<button type="button" id="ecosys-view-email-logs" class="button button-secondary">
									<?php esc_html_e( 'View Email Debug Logs', 'ecosys-profile-manager' ); ?>
								</button>
								<p class="description"><?php esc_html_e( 'View recent email send attempts for debugging.', 'ecosys-profile-manager' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button( __( 'Save Settings', 'ecosys-profile-manager' ) ); ?>
			</form>

			<div id="ecosys-email-logs-modal" class="ecosys-modal" aria-hidden="true" style="display:none;">
				<div class="ecosys-modal-overlay"></div>
				<div class="ecosys-modal-content">
					<div class="ecosys-modal-header">
						<h2><?php esc_html_e( 'Email Debug Logs', 'ecosys-profile-manager' ); ?></h2>
						<div class="ecosys-modal-header-actions">
							<button type="button" id="ecosys-clear-email-logs" class="button button-secondary">
								<?php esc_html_e( 'Clear Logs', 'ecosys-profile-manager' ); ?>
							</button>
							<button type="button" class="ecosys-modal-close" aria-label="<?php esc_attr_e( 'Close', 'ecosys-profile-manager' ); ?>">&times;</button>
						</div>
					</div>
					<div class="ecosys-modal-body">
						<div id="ecosys-email-logs-content"><p><?php esc_html_e( 'Loading…', 'ecosys-profile-manager' ); ?></p></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Send test email if requested and nonce/capability are valid.
	 *
	 * @since    1.0.0
	 * @return   array|null Message with 'success' and 'message' keys, or null if not requested.
	 */
	private function maybe_send_test_mail() {
		if ( ! isset( $_POST['ecosys_send_test_mail'], $_POST['ecosys_test_mail_nonce'] ) ) {
			return null;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ecosys_test_mail_nonce'] ) ), 'ecosys_test_mail' ) ) {
			return null;
		}
		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) ) {
			return null;
		}
		$to = isset( $_POST['ecosys_test_email_to'] ) ? sanitize_email( wp_unslash( $_POST['ecosys_test_email_to'] ) ) : '';
		if ( empty( $to ) ) {
			$to = get_option( 'admin_email' );
		} elseif ( ! is_email( $to ) ) {
			return array(
				'success' => false,
				'message' => __( 'Please enter a valid email address.', 'ecosys-profile-manager' ),
			);
		}
		$sent = $this->options->send_test_email( $to );
		return array(
			'success' => $sent,
			'message' => $sent
				? __( 'Test email sent successfully. Check your inbox.', 'ecosys-profile-manager' )
				: __( 'Failed to send test email. Check your email configuration.', 'ecosys-profile-manager' ),
		);
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

		$use_smtp = isset( $_POST['ecosys_use_custom_smtp'] );
		$this->options->save_use_custom_smtp( $use_smtp );

		if ( $use_smtp ) {
			$smtp_values = array(
				'host'        => isset( $_POST['ecosys_smtp_host'] ) ? sanitize_text_field( wp_unslash( $_POST['ecosys_smtp_host'] ) ) : '',
				'port'        => isset( $_POST['ecosys_smtp_port'] ) ? sanitize_text_field( wp_unslash( $_POST['ecosys_smtp_port'] ) ) : '587',
				'encryption'  => isset( $_POST['ecosys_smtp_encryption'] ) ? sanitize_text_field( wp_unslash( $_POST['ecosys_smtp_encryption'] ) ) : 'tls',
				'username'    => isset( $_POST['ecosys_smtp_username'] ) ? sanitize_text_field( wp_unslash( $_POST['ecosys_smtp_username'] ) ) : '',
				'password'    => isset( $_POST['ecosys_smtp_password'] ) ? wp_unslash( $_POST['ecosys_smtp_password'] ) : '',
				'from_email'  => isset( $_POST['ecosys_smtp_from_email'] ) ? sanitize_email( wp_unslash( $_POST['ecosys_smtp_from_email'] ) ) : '',
				'from_name'   => isset( $_POST['ecosys_smtp_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ecosys_smtp_from_name'] ) ) : '',
				'insecure_ssl' => isset( $_POST['ecosys_smtp_insecure_ssl'] ),
			);
			$this->options->save_smtp( $smtp_values );
		}

		return true;
	}
}
