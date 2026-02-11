<?php
/**
 * Custom Post Type Registration
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/includes
 */

/**
 * Register custom post types for the plugin.
 *
 * @since      1.0.0
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/includes
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_CPT {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name
	 */
	private $plugin_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of the plugin.
	 */
	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Register custom post types.
	 *
	 * @since    1.0.0
	 */
	public function register_custom_post_types() {
		$this->register_profile_post_type();
		$this->register_project_post_type();
		$this->register_profile_structure_post_type();
	}

	/**
	 * Register the Profile custom post type.
	 *
	 * @since    1.0.0
	 */
	private function register_profile_post_type() {

		$labels = array(
			'name'               => _x( 'Profiles', 'post type general name', 'ecosys-profile-manager' ),
			'singular_name'      => _x( 'Profile', 'post type singular name', 'ecosys-profile-manager' ),
			'menu_name'          => _x( 'Profiles', 'admin menu', 'ecosys-profile-manager' ),
			'name_admin_bar'     => _x( 'Profile', 'add new on admin bar', 'ecosys-profile-manager' ),
			'add_new'            => _x( 'Add New', 'profile', 'ecosys-profile-manager' ),
			'add_new_item'       => __( 'Add New Profile', 'ecosys-profile-manager' ),
			'new_item'           => __( 'New Profile', 'ecosys-profile-manager' ),
			'edit_item'          => __( 'Edit Profile', 'ecosys-profile-manager' ),
			'view_item'          => __( 'View Profile', 'ecosys-profile-manager' ),
			'all_items'          => __( 'All Profiles', 'ecosys-profile-manager' ),
			'search_items'       => __( 'Search Profiles', 'ecosys-profile-manager' ),
			'parent_item_colon'  => __( 'Parent Profiles:', 'ecosys-profile-manager' ),
			'not_found'          => __( 'No profiles found.', 'ecosys-profile-manager' ),
			'not_found_in_trash' => __( 'No profiles found in Trash.', 'ecosys-profile-manager' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Custom profile post type', 'ecosys-profile-manager' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'profile' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-businessman',
			'supports'           => array('custom-fields'),
		);

		register_post_type( 'profile', $args );

	}

	/**
	 * Register the Project custom post type.
	 *
	 * @since    1.0.0
	 */
	private function register_project_post_type() {

		$labels = array(
			'name'               => _x( 'Projects', 'post type general name', 'ecosys-profile-manager' ),
			'singular_name'      => _x( 'Project', 'post type singular name', 'ecosys-profile-manager' ),
			'menu_name'          => _x( 'Projects', 'admin menu', 'ecosys-profile-manager' ),
			'name_admin_bar'     => _x( 'Project', 'add new on admin bar', 'ecosys-profile-manager' ),
			'add_new'            => _x( 'Add New', 'project', 'ecosys-profile-manager' ),
			'add_new_item'       => __( 'Add New Project', 'ecosys-profile-manager' ),
			'new_item'           => __( 'New Project', 'ecosys-profile-manager' ),
			'edit_item'          => __( 'Edit Project', 'ecosys-profile-manager' ),
			'view_item'          => __( 'View Project', 'ecosys-profile-manager' ),
			'all_items'          => __( 'All Projects', 'ecosys-profile-manager' ),
			'search_items'       => __( 'Search Projects', 'ecosys-profile-manager' ),
			'parent_item_colon'  => __( 'Parent Projects:', 'ecosys-profile-manager' ),
			'not_found'          => __( 'No projects found.', 'ecosys-profile-manager' ),
			'not_found_in_trash' => __( 'No projects found in Trash.', 'ecosys-profile-manager' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Custom project post type', 'ecosys-profile-manager' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'project' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 6,
			'menu_icon'          => 'dashicons-briefcase',
			'supports'           => array( 'custom-fields', 'author' ),
		);

		register_post_type( 'project', $args );

	}

	/**
	 * Register the Profile Structure custom post type.
	 *
	 * @since    1.0.0
	 */
	private function register_profile_structure_post_type() {

		$labels = array(
			'name'               => _x( 'Structures', 'post type general name', 'ecosys-profile-manager' ),
			'singular_name'      => _x( 'Structure', 'post type singular name', 'ecosys-profile-manager' ),
			'menu_name'          => _x( 'Structures', 'admin menu', 'ecosys-profile-manager' ),
			'name_admin_bar'     => _x( 'Structure', 'add new on admin bar', 'ecosys-profile-manager' ),
			'add_new'            => _x( 'Add New', 'structure', 'ecosys-profile-manager' ),
			'add_new_item'       => __( 'Add New Structure', 'ecosys-profile-manager' ),
			'new_item'           => __( 'New Structure', 'ecosys-profile-manager' ),
			'edit_item'          => __( 'Edit Structure', 'ecosys-profile-manager' ),
			'view_item'          => __( 'View Structure', 'ecosys-profile-manager' ),
			'all_items'          => __( 'All Structures', 'ecosys-profile-manager' ),
			'search_items'       => __( 'Search Structures', 'ecosys-profile-manager' ),
			'parent_item_colon'  => __( 'Parent Profile:', 'ecosys-profile-manager' ),
			'not_found'          => __( 'No structures found.', 'ecosys-profile-manager' ),
			'not_found_in_trash' => __( 'No structures found in Trash.', 'ecosys-profile-manager' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Structure information linked to profiles', 'ecosys-profile-manager' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=profile',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'profile-structure' ),
			'capability_type'    => 'post',
			'has_archive'       => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'custom-fields' ),
		);

		register_post_type( 'profile_structure', $args );

	}

}
