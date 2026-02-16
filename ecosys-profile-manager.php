<?php
/**
 * Plugin Name: Ecosys Profile Manager
 * Plugin URI: https://ecosys.io/plugins/profile-manager
 * Description: A comprehensive profile management plugin for WordPress users and advanced user profiles.
 * Version: 1.0.1
 * Author: Ecosys
 * Author URI: https://ecosys.io
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ecosys-profile-manager
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'ECOSYS_PROFILE_MANAGER_VERSION', '1.0.1' );

/**
 * Plugin base path.
 */
define( 'ECOSYS_PROFILE_MANAGER_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin base URL.
 */
define( 'ECOSYS_PROFILE_MANAGER_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_ecosys_profile_manager() {
	require_once ECOSYS_PROFILE_MANAGER_PATH . 'includes/class-ecosys-profile-manager-activator.php';
	Ecosys_Profile_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ecosys_profile_manager() {
	require_once ECOSYS_PROFILE_MANAGER_PATH . 'includes/class-ecosys-profile-manager-deactivator.php';
	Ecosys_Profile_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ecosys_profile_manager' );
register_deactivation_hook( __FILE__, 'deactivate_ecosys_profile_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require ECOSYS_PROFILE_MANAGER_PATH . 'includes/class-ecosys-profile-manager.php';

/**
 * Begins execution of the plugin.
 */
function run_ecosys_profile_manager() {
	$plugin = new Ecosys_Profile_Manager();
	$plugin->run();
}

run_ecosys_profile_manager();
