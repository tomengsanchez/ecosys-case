<?php
/**
 * Profile Structure columns functionality.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile-structure
 */

/**
 * The profile structure columns class.
 *
 * Handles list table columns and customization for the Profile Structure post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile-structure
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Profile_Structure_Columns {

	/**
	 * Manage structure list table columns.
	 *
	 * @since    1.0.0
	 * @param    array $columns The columns array.
	 * @return   array The modified columns array.
	 */
	public function manage_structure_columns( $columns ) {
		$new_columns = array(
			'cb' => $columns['cb'],
			'structure_tag' => __( 'Structure Tag', 'ecosys-profile-manager' ),
			'structure_profile' => __( 'Profile', 'ecosys-profile-manager' ),
			'date' => $columns['date'],
		);
		return $new_columns;
	}

	/**
	 * Populate custom structure columns.
	 *
	 * @since    1.0.0
	 * @param    string $column The column name.
	 * @param    int    $post_id The post ID.
	 */
	public function populate_structure_columns( $column, $post_id ) {
		if ( 'structure_tag' === $column ) {
			$tag = get_post_meta( $post_id, '_structure_tag', true );
			echo ! empty( $tag ) ? esc_html( $tag ) : '—';
		} elseif ( 'structure_profile' === $column ) {
			$profile_id = get_post_meta( $post_id, '_structure_profile_id', true );
			if ( ! empty( $profile_id ) ) {
				$profile_name = get_post_meta( $profile_id, '_profile_name', true );
				$profile = get_post( $profile_id );
				$display_name = ! empty( $profile_name ) ? $profile_name : ( $profile ? $profile->post_title : '' );
				echo '<a href="' . esc_url( get_edit_post_link( $profile_id ) ) . '">' . esc_html( $display_name ) . '</a>';
			} else {
				echo '—';
			}
		}
	}

}
