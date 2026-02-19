<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/includes
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ecosys_Profile_Manager_Loader $loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'ecosys-profile-manager';
		$this->version     = ECOSYS_PROFILE_MANAGER_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_cpt_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'includes/class-ecosys-profile-manager-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'includes/class-ecosys-profile-manager-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/class-ecosys-profile-manager-admin.php';

		/**
		 * The class responsible for the plugin admin menu.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/class-ecosys-profile-manager-menu.php';

		/**
		 * Settings: email logger for debug.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/settings/class-ecosys-profile-manager-email-logger.php';

		/**
		 * Settings: options (get/save) and notification logic.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/settings/class-ecosys-profile-manager-settings-options.php';

		/**
		 * Admin page UIs (Dashboard, Settings, GRM).
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/pages/class-ecosys-profile-manager-page-dashboard.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/pages/class-ecosys-profile-manager-page-settings.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/pages/class-ecosys-profile-manager-page-grm-dashboard.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/pages/class-ecosys-profile-manager-page-grm-grievances.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/pages/class-ecosys-profile-manager-page-grm-library.php';

		/**
		 * Profile admin classes.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/profile/class-ecosys-profile-manager-profile-metabox.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/profile/class-ecosys-profile-manager-profile-columns.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/profile/class-ecosys-profile-manager-profile-database.php';

		/**
		 * Project admin classes.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/project/class-ecosys-profile-manager-project-metabox.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/project/class-ecosys-profile-manager-project-columns.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/project/class-ecosys-profile-manager-project-database.php';

		/**
		 * Profile Structure admin classes.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/profile-structure/class-ecosys-profile-manager-profile-structure-metabox.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/profile-structure/class-ecosys-profile-manager-profile-structure-columns.php';
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'admin/profile-structure/class-ecosys-profile-manager-profile-structure-database.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'includes/class-ecosys-profile-manager-public.php';

		/**
		 * The class responsible for registering custom post types.
		 */
		require_once ECOSYS_PROFILE_MANAGER_PATH . 'includes/class-ecosys-profile-manager-cpt.php';

		$this->loader = new Ecosys_Profile_Manager_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ecosys_Profile_Manager_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to custom post types
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_cpt_hooks() {

		$plugin_cpt = new Ecosys_Profile_Manager_CPT( $this->get_plugin_name() );

		$this->loader->add_action( 'init', $plugin_cpt, 'register_custom_post_types' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ecosys_Profile_Manager_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'maybe_remove_default_dashboard', 999 );
		$this->loader->add_action( 'load-index.php', $plugin_admin, 'maybe_redirect_dashboard' );

		// Email logger and settings options
		$email_logger     = new Ecosys_Profile_Manager_Email_Logger();
		$settings_options = new Ecosys_Profile_Manager_Settings_Options( $email_logger );
		$this->loader->add_action( 'wp_mail_failed', $email_logger, 'on_wp_mail_failed', 10, 2 );
		$this->loader->add_action( 'wp_ajax_ecosys_get_email_logs', $email_logger, 'ajax_get_logs' );
		$this->loader->add_action( 'wp_ajax_ecosys_clear_email_logs', $email_logger, 'ajax_clear_logs' );
		$this->loader->add_action( 'phpmailer_init', $settings_options, 'phpmailer_init_smtp', 10, 1 );
		$this->loader->add_action( 'transition_post_status', $settings_options, 'maybe_notify_new_profile', 10, 3 );

		// Plugin menu (submenu registration only; page UIs are in admin/pages)
		$plugin_menu = new Ecosys_Profile_Manager_Menu( $this->get_plugin_name(), $this->get_version(), $settings_options );
		$this->loader->add_action( 'admin_menu', $plugin_menu, 'add_admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_menu, 'enqueue_settings_scripts' );

		// Profile metabox hooks
		$profile_metabox = new Ecosys_Profile_Manager_Profile_MetaBox( $this->get_plugin_name() );
		$this->loader->add_action( 'add_meta_boxes', $profile_metabox, 'add_profile_metabox' );
		$this->loader->add_action( 'add_meta_boxes', $profile_metabox, 'add_structure_information_metabox' );
		$this->loader->add_action( 'wp_ajax_ecosys_add_structure', $profile_metabox, 'ajax_add_structure' );
		$this->loader->add_action( 'wp_ajax_ecosys_get_structure', $profile_metabox, 'ajax_get_structure' );
		$this->loader->add_action( 'wp_ajax_ecosys_update_structure', $profile_metabox, 'ajax_update_structure' );
		$this->loader->add_action( 'wp_ajax_ecosys_delete_structure', $profile_metabox, 'ajax_delete_structure' );

		// Profile columns hooks
		$profile_columns = new Ecosys_Profile_Manager_Profile_Columns();
		$this->loader->add_filter( 'manage_profile_posts_columns', $profile_columns, 'manage_profile_columns' );
		$this->loader->add_action( 'manage_profile_posts_custom_column', $profile_columns, 'populate_profile_columns', 10, 2 );

		// Profile database hooks
		$profile_database = new Ecosys_Profile_Manager_Profile_Database();
		$this->loader->add_action( 'restrict_manage_posts', $profile_database, 'add_project_filter' );
		$this->loader->add_filter( 'posts_search', $profile_database, 'search_profile_by_name', 10, 2 );
		$this->loader->add_action( 'pre_get_posts', $profile_database, 'filter_by_project' );
		$this->loader->add_action( 'save_post', $profile_database, 'save_profile_meta' );
		$this->loader->add_action( 'admin_notices', $profile_database, 'maybe_show_duplicate_title_notice' );

		// Project metabox hooks
		$project_metabox = new Ecosys_Profile_Manager_Project_MetaBox( $this->get_plugin_name() );
		$this->loader->add_action( 'add_meta_boxes', $project_metabox, 'add_project_metabox' );

		// Project columns hooks
		$project_columns = new Ecosys_Profile_Manager_Project_Columns();
		$this->loader->add_filter( 'manage_project_posts_columns', $project_columns, 'manage_project_columns' );
		$this->loader->add_action( 'manage_project_posts_custom_column', $project_columns, 'populate_project_columns', 10, 2 );

		// Project database hooks
		$project_database = new Ecosys_Profile_Manager_Project_Database();
		$this->loader->add_filter( 'posts_search', $project_database, 'search_project_by_name', 10, 2 );
		$this->loader->add_action( 'save_post', $project_database, 'save_project_meta' );

		// Profile Structure metabox hooks
		$structure_metabox = new Ecosys_Profile_Manager_Profile_Structure_MetaBox( $this->get_plugin_name() );
		$this->loader->add_action( 'add_meta_boxes', $structure_metabox, 'add_structure_metabox' );

		// Profile Structure database hooks
		$structure_database = new Ecosys_Profile_Manager_Profile_Structure_Database();
		$this->loader->add_action( 'save_post', $structure_database, 'save_structure_meta' );

		// Profile Structure columns hooks
		$structure_columns = new Ecosys_Profile_Manager_Profile_Structure_Columns();
		$this->loader->add_filter( 'manage_profile_structure_posts_columns', $structure_columns, 'manage_structure_columns' );
		$this->loader->add_action( 'manage_profile_structure_posts_custom_column', $structure_columns, 'populate_structure_columns', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ecosys_Profile_Manager_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ecosys_Profile_Manager_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
