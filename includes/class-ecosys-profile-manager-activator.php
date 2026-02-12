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
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	/**
	 * Capability for accessing Ecosys plugin (dashboard, grievance, profiles, projects).
	 *
	 * @since 1.0.0
	 */
	const CAP_MANAGE = 'ecosys_manage';

	/**
	 * Capability for accessing Ecosys plugin settings (Ecosys Admin only).
	 *
	 * @since 1.0.0
	 */
	const CAP_MANAGE_SETTINGS = 'ecosys_manage_settings';

	public static function activate() {

		// Create Ecosys Admin and Ecosys Officer roles.
		self::add_ecosys_roles();

	}

	/**
	 * Add Ecosys Admin and Ecosys Officer roles with appropriate capabilities.
	 *
	 * @since 1.0.0
	 */
	private static function add_ecosys_roles() {

		// Capabilities needed to manage CPTs (profile, project, profile_structure use capability_type 'post').
		$post_caps = array(
			'read',
			'edit_posts',
			'edit_others_posts',
			'publish_posts',
			'delete_posts',
			'delete_others_posts',
			'read_private_posts',
			'delete_private_posts',
			'delete_published_posts',
		);

		// Ecosys Admin: full plugin access including settings.
		add_role(
			'ecosys_admin',
			__( 'Ecosys Admin', 'ecosys-profile-manager' ),
			array_merge(
				array(
					self::CAP_MANAGE          => true,
					self::CAP_MANAGE_SETTINGS => true,
				),
				array_fill_keys( $post_caps, true )
			)
		);

		// Ecosys Officer: full plugin access except settings.
		add_role(
			'ecosys_officer',
			__( 'Ecosys Officer', 'ecosys-profile-manager' ),
			array_merge(
				array(
					self::CAP_MANAGE => true,
				),
				array_fill_keys( $post_caps, true )
			)
		);

		// Grant Ecosys capabilities to Administrator so they retain full access.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( self::CAP_MANAGE );
			$admin->add_cap( self::CAP_MANAGE_SETTINGS );
		}

	}

}
