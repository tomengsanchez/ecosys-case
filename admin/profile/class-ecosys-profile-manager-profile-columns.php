<?php
/**
 * Profile columns functionality.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile
 */

/**
 * The profile columns class.
 *
 * Handles list table columns and customization for the Profile post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Profile_Columns {

	/**
	 * Manage profile list table columns.
	 *
	 * @since    1.0.0
	 * @param    array $columns The columns array.
	 * @return   array The modified columns array.
	 */
	public function manage_profile_columns( $columns ) {
		// Remove title column
		unset( $columns['title'] );
		
		// Keep checkbox column and reorder
		$new_columns = array(
			'cb' => $columns['cb'],
		);
		$new_columns['profile_name'] = __( 'Name', 'ecosys-profile-manager' );
		$new_columns['profile_project'] = __( 'Project', 'ecosys-profile-manager' );
		
		// Add remaining columns except title
		foreach ( $columns as $key => $value ) {
			if ( $key !== 'cb' && $key !== 'title' ) {
				$new_columns[ $key ] = $value;
			}
		}
		
		return $new_columns;
	}

	/**
	 * Populate custom profile columns.
	 *
	 * @since    1.0.0
	 * @param    string $column The column name.
	 * @param    int    $post_id The post ID.
	 */
	public function populate_profile_columns( $column, $post_id ) {
		if ( 'profile_name' === $column ) {
			$name = get_post_meta( $post_id, '_profile_name', true );
			
			if ( ! empty( $name ) ) {
				echo '<strong>';
				echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">';
				echo esc_html( $name );
				echo '</a>';
				echo '</strong>';
			} else {
				echo '—';
			}
		} elseif ( 'profile_project' === $column ) {
			$project_id = get_post_meta( $post_id, '_profile_project_id', true );
			
			if ( ! empty( $project_id ) ) {
				$project_id = absint( $project_id );
				$project_name = get_post_meta( $project_id, '_project_name', true );
				
				if ( ! empty( $project_name ) ) {
					echo '<a href="' . esc_url( add_query_arg( 'project_filter', $project_id, admin_url( 'edit.php?post_type=profile' ) ) ) . '">';
					echo esc_html( $project_name );
					echo '</a>';
				} else {
					$project = get_post( $project_id );
					if ( $project ) {
						echo '<a href="' . esc_url( add_query_arg( 'project_filter', $project_id, admin_url( 'edit.php?post_type=profile' ) ) ) . '">';
						echo esc_html( $project->post_title );
						echo '</a>';
					}
				}
			} else {
				echo '—';
			}
		}
	}

}
