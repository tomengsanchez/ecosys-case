<?php
/**
 * Dashboard page UI.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/pages
 */

/**
 * Renders the Ecosys Profile Management dashboard.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/pages
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Page_Dashboard {

	/**
	 * Output the dashboard page.
	 *
	 * @since    1.0.0
	 */
	public function render() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Welcome to Ecosys Profile Management. This is your dashboard.', 'ecosys-profile-manager' ); ?></p>
		</div>
		<?php
	}
}
