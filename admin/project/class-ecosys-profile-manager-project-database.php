<?php
/**
 * Project database integrations and filtering.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/project
 */

/**
 * The project database class.
 *
 * Handles database operations, filtering, and search functionality for the Project post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/project
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Project_Database {

	/**
	 * Make project name searchable.
	 *
	 * @since    1.0.0
	 * @param    string   $search The search query.
	 * @param    WP_Query $query The query object.
	 * @return   string The modified search query.
	 */
	public function search_project_by_name( $search, $query ) {
		if ( ! is_admin() || ! $query->is_search() ) {
			return $search;
		}

		if ( ! isset( $query->query_vars['post_type'] ) || 'project' !== $query->query_vars['post_type'] ) {
			return $search;
		}

		$search_term = $query->get( 's' );
		
		if ( empty( $search_term ) ) {
			return $search;
		}

		global $wpdb;
		
		// Delete the old search query
		$search = '';
		
		// Search in both post title and project name meta
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';
		
		$search = $wpdb->prepare(
			"AND (({$wpdb->posts}.post_title LIKE %s) OR (SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID AND {$wpdb->postmeta}.meta_key = '_project_name' AND {$wpdb->postmeta}.meta_value LIKE %s))",
			$search_term,
			$search_term
		);

		return $search;
	}

	/**
	 * Save project meta on save_post (Project CPT).
	 *
	 * @since    1.0.0
	 * @param    int $post_id The post ID.
	 */
	public function save_project_meta( $post_id ) {
		if ( get_post_type( $post_id ) !== 'project' ) {
			return;
		}
		if ( ! isset( $_POST['project_metabox_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['project_metabox_nonce_field'] ) ), 'project_metabox_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['project_name'] ) ) {
			update_post_meta( $post_id, '_project_name', sanitize_text_field( wp_unslash( $_POST['project_name'] ) ) );
		}
	}

}
