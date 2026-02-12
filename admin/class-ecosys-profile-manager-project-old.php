<?php
/**
 * The project-specific admin functionality of the plugin.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 */

/**
 * The project admin class.
 *
 * Handles all metabox and custom field functionality for the Project post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Project_Admin {

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
	 * Add metabox for project custom fields.
	 *
	 * @since    1.0.0
	 */
	public function add_project_metabox() {
		add_meta_box(
			'project_custom_fields',
			__( 'Project Information', 'ecosys-profile-manager' ),
			array( $this, 'render_project_metabox' ),
			'project',
			'normal',
			'high'
		);
	}

	/**
	 * Render the project metabox.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post The post object.
	 */
	public function render_project_metabox( $post ) {
		wp_nonce_field( 'project_metabox_nonce', 'project_metabox_nonce_field' );

		// Get existing value
		$project_name = get_post_meta( $post->ID, '_project_name', true );
		?>
		<div style="padding: 15px 0;">
			<div style="margin-bottom: 15px;">
			<label for="project_name" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">
				<?php _e( 'Project Name', 'ecosys-profile-manager' ); ?>
			</label>
			<input 
				type="text" 
				id="project_name" 
				name="project_name" 
				value="<?php echo esc_attr( $project_name ); ?>" 
				style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;"
				/>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the project metabox data.
	 *
	 * @since    1.0.0
	 * @param    int $post_id The post ID.
	 */
	public function save_project_metabox( $post_id ) {
		// Verify nonce
		if ( ! isset( $_POST['project_metabox_nonce_field'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['project_metabox_nonce_field'], 'project_metabox_nonce' ) ) {
			return;
		}

		// Check user capabilities
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Project Name
		if ( isset( $_POST['project_name'] ) ) {
			update_post_meta( $post_id, '_project_name', sanitize_text_field( $_POST['project_name'] ) );
		}
	}

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

}
