<?php
/**
 * Plugin menu and submenu registration only.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 */

/**
 * Registers the admin menu and submenus. Page content is rendered by separate page classes.
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
	 * Menu slug (used for main menu and dashboard).
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $menu_slug
	 */
	private $menu_slug = 'ecosys-profile-management';

	/**
	 * GRM menu slug.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $grm_menu_slug
	 */
	private $grm_menu_slug = 'ecosys-grm';

	/**
	 * Settings options instance (for Settings page).
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Ecosys_Profile_Manager_Settings_Options|null
	 */
	private $settings_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string                                            $plugin_name      The name of the plugin.
	 * @param    string                                            $version          The version.
	 * @param    Ecosys_Profile_Manager_Settings_Options|null $settings_options Optional. For Settings page save/load.
	 */
	public function __construct( $plugin_name, $version, $settings_options = null ) {
		$this->plugin_name      = $plugin_name;
		$this->version          = $version;
		$this->settings_options = $settings_options;
	}

	/**
	 * Register the admin menu and submenus.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		$capability = 'manage_ecosys_profile_manager';

		add_menu_page(
			__( 'Ecosys Profile Management', 'ecosys-profile-manager' ),
			__( 'Ecosys Profile Management', 'ecosys-profile-manager' ),
			$capability,
			$this->menu_slug,
			array( $this, 'render_dashboard_page' ),
			'dashicons-groups',
			30
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Dashboard', 'ecosys-profile-manager' ),
			__( 'Dashboard', 'ecosys-profile-manager' ),
			$capability,
			$this->menu_slug,
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Settings', 'ecosys-profile-manager' ),
			__( 'Settings', 'ecosys-profile-manager' ),
			$capability,
			$this->menu_slug . '-settings',
			array( $this, 'render_settings_page' )
		);

		// GRM menu
		add_menu_page(
			__( 'GRM', 'ecosys-profile-manager' ),
			__( 'GRM', 'ecosys-profile-manager' ),
			$capability,
			$this->grm_menu_slug,
			array( $this, 'render_grm_dashboard_page' ),
			'dashicons-portfolio',
			31
		);

		add_submenu_page(
			$this->grm_menu_slug,
			__( 'Dashboard', 'ecosys-profile-manager' ),
			__( 'Dashboard', 'ecosys-profile-manager' ),
			$capability,
			$this->grm_menu_slug,
			array( $this, 'render_grm_dashboard_page' )
		);

		add_submenu_page(
			$this->grm_menu_slug,
			__( 'Grievances', 'ecosys-profile-manager' ),
			__( 'Grievances', 'ecosys-profile-manager' ),
			$capability,
			$this->grm_menu_slug . '-grievances',
			array( $this, 'render_grm_grievances_page' )
		);

		add_submenu_page(
			$this->grm_menu_slug,
			__( 'Library', 'ecosys-profile-manager' ),
			__( 'Library', 'ecosys-profile-manager' ),
			$capability,
			$this->grm_menu_slug . '-library',
			array( $this, 'render_grm_library_page' )
		);
	}

	/**
	 * Render the Dashboard page (delegates to page class).
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_page() {
		$page = new Ecosys_Profile_Manager_Page_Dashboard();
		$page->render();
	}

	/**
	 * Render the Settings page (delegates to page class).
	 *
	 * @since    1.0.0
	 */
	public function render_settings_page() {
		$options = $this->settings_options ? $this->settings_options : new Ecosys_Profile_Manager_Settings_Options();
		$page    = new Ecosys_Profile_Manager_Page_Settings( $options );
		$page->render();
	}

	/**
	 * Render GRM Dashboard page (delegates to page class).
	 *
	 * @since    1.0.0
	 */
	public function render_grm_dashboard_page() {
		$page = new Ecosys_Profile_Manager_Page_GRM_Dashboard();
		$page->render();
	}

	/**
	 * Render GRM Grievances page (delegates to page class).
	 *
	 * @since    1.0.0
	 */
	public function render_grm_grievances_page() {
		$page = new Ecosys_Profile_Manager_Page_GRM_Grievances();
		$page->render();
	}

	/**
	 * Render GRM Library page (delegates to page class).
	 *
	 * @since    1.0.0
	 */
	public function render_grm_library_page() {
		$page = new Ecosys_Profile_Manager_Page_GRM_Library();
		$page->render();
	}
}
