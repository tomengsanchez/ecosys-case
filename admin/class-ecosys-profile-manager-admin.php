<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 */

/**
 * The admin base class for the plugin.
 *
 * Handles admin-wide functionality like enqueuing styles and scripts.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Admin {

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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ecosys_Profile_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ecosys_Profile_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, ECOSYS_PROFILE_MANAGER_URL . 'assets/css/ecosys-profile-manager-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ecosys_Profile_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ecosys_Profile_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, ECOSYS_PROFILE_MANAGER_URL . 'assets/js/ecosys-profile-manager-admin.js', array( 'jquery' ), $this->version, false );
		
		// Enqueue media uploader
		wp_enqueue_media();

		// Enqueue GLightbox for lightbox functionality
		wp_enqueue_script( 'glightbox', 'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js', array(), '3.2.0', true );
		wp_enqueue_style( 'glightbox', 'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css', array(), '3.2.0' );

	}

	/**
	 * Remove the default WordPress Dashboard menu for Ecosys-specific roles.
	 *
	 * This targets users who can manage Ecosys Profile Manager content but
	 * do not have the full `manage_options` capability (i.e. not full site admins).
	 *
	 * @since 1.0.0
	 */
	public function maybe_remove_default_dashboard() {

		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) || current_user_can( 'manage_options' ) ) {
			return;
		}

		remove_menu_page( 'index.php' );
	}

	/**
	 * Redirect Ecosys-specific roles away from the default Dashboard to the plugin dashboard.
	 *
	 * @since 1.0.0
	 */
	public function maybe_redirect_dashboard() {

		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) || current_user_can( 'manage_options' ) ) {
			return;
		}

		// This slug matches the one registered in Ecosys_Profile_Manager_Menu.
		$dashboard_url = admin_url( 'admin.php?page=ecosys-profile-management' );

		wp_safe_redirect( $dashboard_url );
		exit;
	}

}

