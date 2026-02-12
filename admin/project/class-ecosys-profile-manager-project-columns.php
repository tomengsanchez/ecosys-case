<?php
/**
 * Project columns functionality.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/project
 */

/**
 * The project columns class.
 *
 * Handles list table columns and customization for the Project post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/project
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Project_Columns {

	/**
	 * Manage project list table columns.
	 *
	 * @since    1.0.0
	 * @param    array $columns The columns array.
	 * @return   array The modified columns array.
	 */
	public function manage_project_columns( $columns ) {
		// Remove title column
		unset( $columns['title'] );
		
		// Add project name as the first column
		$new_columns = array(
			'cb' => $columns['cb'], // Keep checkbox column
		);
		$new_columns['project_name'] = __( 'Project Name', 'ecosys-profile-manager' );
		
		// Add remaining columns
		foreach ( $columns as $key => $value ) {
			if ( $key !== 'cb' && $key !== 'title' ) {
				$new_columns[ $key ] = $value;
			}
		}
		
		return $new_columns;
	}

	/**
	 * Populate custom project columns.
	 *
	 * @since    1.0.0
	 * @param    string $column The column name.
	 * @param    int    $post_id The post ID.
	 */
	public function populate_project_columns( $column, $post_id ) {
		if ( 'project_name' === $column ) {
			$project_name = get_post_meta( $post_id, '_project_name', true );
			
			if ( ! empty( $project_name ) ) {
				echo '<strong>';
				echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">';
				echo esc_html( $project_name );
				echo '</a>';
				echo '</strong>';
			} else {
				echo '—';
			}
		}
	}

}
