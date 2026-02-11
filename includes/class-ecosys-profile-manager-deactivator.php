<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/includes
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// Clear any scheduled cron jobs
		// Clean up temporary data if needed
		// etc.

	}

}
