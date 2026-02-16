<?php
/**
 * Profile Structure database integrations.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile-structure
 */

/**
 * The profile structure database class.
 *
 * Handles database operations and integrations for the Profile Structure post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile-structure
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Profile_Structure_Database {

	/**
	 * Save structure meta on save_post (Profile Structure CPT).
	 *
	 * @since    1.0.0
	 * @param    int $post_id The post ID.
	 */
	public function save_structure_meta( $post_id ) {
		if ( get_post_type( $post_id ) !== 'profile_structure' ) {
			return;
		}
		if ( ! isset( $_POST['structure_fields_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['structure_fields_nonce_field'] ) ), 'structure_fields_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['structure_profile_id'] ) ) {
			update_post_meta( $post_id, '_structure_profile_id', absint( $_POST['structure_profile_id'] ) );
		}
		if ( isset( $_POST['structure_tag'] ) ) {
			update_post_meta( $post_id, '_structure_tag', sanitize_text_field( wp_unslash( $_POST['structure_tag'] ) ) );
		}
		if ( isset( $_POST['structure_pictures'] ) ) {
			$picture_ids = array_map( 'absint', array_filter( explode( ',', sanitize_text_field( wp_unslash( $_POST['structure_pictures'] ) ) ) ) );
			update_post_meta( $post_id, '_structure_pictures', $picture_ids );
		} else {
			delete_post_meta( $post_id, '_structure_pictures' );
		}
		if ( isset( $_POST['structure_description'] ) ) {
			update_post_meta( $post_id, '_structure_description', sanitize_textarea_field( wp_unslash( $_POST['structure_description'] ) ) );
		}
	}

}
