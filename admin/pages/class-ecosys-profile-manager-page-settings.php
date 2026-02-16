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
				<?php submit_button( __( 'Save Settings', 'ecosys-profile-manager' ) ); ?>
			</form>
		</div>
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
		return true;
	}
}
