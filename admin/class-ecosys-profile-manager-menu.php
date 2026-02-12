<?php
/**
 * The plugin menu registration.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 */

/**
 * Registers the plugin admin menu and submenus.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Menu {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version
	 */
	private $version;

	/**
	 * The menu slug.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $menu_slug
	 */
	private $menu_slug = 'ecosys-profile-management';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of the plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Ecosys Profile Management', 'ecosys-profile-manager' ),
			__( 'Ecosys Profile Management', 'ecosys-profile-manager' ),
			'manage_options',
			$this->menu_slug,
			array( $this, 'render_dashboard_page' ),
			'dashicons-groups',
			30
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Dashboard', 'ecosys-profile-manager' ),
			__( 'Dashboard', 'ecosys-profile-manager' ),
			'manage_options',
			$this->menu_slug,
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Settings', 'ecosys-profile-manager' ),
			__( 'Settings', 'ecosys-profile-manager' ),
			'manage_options',
			$this->menu_slug . '-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render the Dashboard page.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Welcome to Ecosys Profile Management. This is your dashboard.', 'ecosys-profile-manager' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render the Settings page.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Plugin settings will appear here.', 'ecosys-profile-manager' ); ?></p>
		</div>
		<?php
	}

}
