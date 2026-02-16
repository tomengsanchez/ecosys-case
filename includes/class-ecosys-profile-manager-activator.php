<?php
/**
 * Fired during plugin activation
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/includes
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Activator {

	/**
	 * Run tasks needed on plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Create or update the custom Ecosys Admin role.
		self::add_roles();

		// Ensure required capabilities are granted to the appropriate roles.
		self::add_capabilities();

	}

	/**
	 * Add or update custom roles used by the plugin.
	 *
	 * @since 1.0.0
	 */
	private static function add_roles() {

		// Cap that gates access to all Ecosys Profile Manager admin functionality.
		$capability = 'manage_ecosys_profile_manager';

		$caps = array(
			'read'                           => true,
			$capability                      => true,
			// Full access to media and file uploads (documents, pictures).
			'upload_files'                   => true,
			'edit_posts'                     => true,
			'edit_others_posts'              => true,
			'delete_posts'                   => true,
			'delete_others_posts'            => true,
		);

		$role = get_role( 'ecosys_admin' );

		if ( null === $role ) {
			// Create the Ecosys Admin role if it does not yet exist.
			add_role(
				'ecosys_admin',
				__( 'Ecosys Admin', 'ecosys-profile-manager' ),
				$caps
			);
		} else {
			// Make sure the role has all required capabilities.
			foreach ( $caps as $cap => $grant ) {
				if ( $grant && ! $role->has_cap( $cap ) ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	/**
	 * Ensure core WordPress roles have the capabilities needed for this plugin.
	 *
	 * Currently this grants full Ecosys Profile Manager access to administrators
	 * as well as the custom Ecosys Admin role.
	 *
	 * @since 1.0.0
	 */
	private static function add_capabilities() {

		$capability = 'manage_ecosys_profile_manager';

		$roles = array(
			'administrator',
			'ecosys_admin',
		);

		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );

			if ( ! $role ) {
				continue;
			}

			if ( ! $role->has_cap( $capability ) ) {
				$role->add_cap( $capability );
			}
		}
	}

}
