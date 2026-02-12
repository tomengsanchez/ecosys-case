<?php
/**
 * Profile database integrations and filtering.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile
 */

/**
 * The profile database class.
 *
 * Handles database operations, filtering, and search functionality for the Profile post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Profile_Database {

	/**
	 * Add project filter dropdown.
	 *
	 * @since    1.0.0
	 */
	public function add_project_filter() {
		global $typenow;
		
		if ( $typenow !== 'profile' ) {
			return;
		}

		$selected = isset( $_GET['project_filter'] ) ? absint( $_GET['project_filter'] ) : '';
		
		// Get all projects
		$projects = get_posts( array(
			'post_type'      => 'project',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		if ( empty( $projects ) ) {
			return;
		}

		echo '<select name="project_filter" id="project_filter">';
		echo '<option value="">' . esc_html__( 'All Projects', 'ecosys-profile-manager' ) . '</option>';
		
		foreach ( $projects as $project ) {
			$project_name = get_post_meta( $project->ID, '_project_name', true );
			$display_name = ! empty( $project_name ) ? $project_name : $project->post_title;
			
			echo '<option value="' . esc_attr( $project->ID ) . '" ' . selected( $selected, $project->ID ) . '>';
			echo esc_html( $display_name );
			echo '</option>';
		}
		
		echo '</select>';
	}

	/**
	 * Filter profiles by project.
	 *
	 * @since    1.0.0
	 * @param    WP_Query $query The query object.
	 */
	public function filter_by_project( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		global $typenow;
		
		if ( $typenow !== 'profile' ) {
			return;
		}

		if ( ! isset( $_GET['project_filter'] ) || empty( $_GET['project_filter'] ) ) {
			return;
		}

		$project_id = absint( $_GET['project_filter'] );
		
		$query->set( 'meta_key', '_profile_project_id' );
		$query->set( 'meta_value', $project_id );
	}

	/**
	 * Make profile name searchable.
	 *
	 * @since    1.0.0
	 * @param    string   $search The search query.
	 * @param    WP_Query $query The query object.
	 * @return   string The modified search query.
	 */
	public function search_profile_by_name( $search, $query ) {
		if ( ! is_admin() || ! $query->is_search() ) {
			return $search;
		}

		if ( ! isset( $query->query_vars['post_type'] ) || 'profile' !== $query->query_vars['post_type'] ) {
			return $search;
		}

		$search_term = $query->get( 's' );
		
		if ( empty( $search_term ) ) {
			return $search;
		}

		global $wpdb;
		
		// Delete the old search query
		$search = '';
		
		// Search in both post title and profile name meta
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';
		
		$search = $wpdb->prepare(
			"AND (({$wpdb->posts}.post_title LIKE %s) OR (SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID AND {$wpdb->postmeta}.meta_key = '_profile_name' AND {$wpdb->postmeta}.meta_value LIKE %s))",
			$search_term,
			$search_term
		);

		return $search;
	}

}
