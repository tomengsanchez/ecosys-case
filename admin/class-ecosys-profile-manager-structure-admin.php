<?php
/**
 * The structure-specific admin functionality of the plugin.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 */

/**
 * The structure admin class.
 *
 * Handles all metabox and custom field functionality for the Profile Structure post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Structure_Admin {

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
	 * Add metaboxes for structure custom fields.
	 *
	 * @since    1.0.0
	 */
	public function add_structure_metabox() {
		add_meta_box(
			'structure_fields',
			__( 'Structure Information', 'ecosys-profile-manager' ),
			array( $this, 'render_structure_metabox' ),
			'profile_structure',
			'normal',
			'high'
		);
	}

	/**
	 * Render the structure metabox.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post The post object.
	 */
	public function render_structure_metabox( $post ) {
		wp_nonce_field( 'structure_fields_nonce', 'structure_fields_nonce_field' );

		$structure_tag        = get_post_meta( $post->ID, '_structure_tag', true );
		$structure_pictures   = get_post_meta( $post->ID, '_structure_pictures', true );
		$structure_description = get_post_meta( $post->ID, '_structure_description', true );
		$structure_profile_id = get_post_meta( $post->ID, '_structure_profile_id', true );

		$picture_ids = array();
		if ( ! empty( $structure_pictures ) ) {
			$picture_ids = is_array( $structure_pictures ) ? $structure_pictures : (array) $structure_pictures;
		}

		// Pre-set profile from URL when adding from profile screen
		if ( empty( $post->ID ) && isset( $_GET['profile_id'] ) ) {
			$structure_profile_id = absint( $_GET['profile_id'] );
		}
		?>
		<div style="padding: 15px 0;">
			<div style="margin-bottom: 15px;">
				<label for="structure_profile_id" style="display: block; margin-bottom: 5px; font-weight: bold;">
					<?php _e( 'Profile', 'ecosys-profile-manager' ); ?>
				</label>
				<select 
					id="structure_profile_id" 
					name="structure_profile_id" 
					style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
				>
					<option value=""><?php _e( 'Select Profile...', 'ecosys-profile-manager' ); ?></option>
					<?php
					$profiles = get_posts( array(
						'post_type'      => 'profile',
						'posts_per_page' => -1,
						'orderby'        => 'title',
						'order'          => 'ASC',
					) );

					foreach ( $profiles as $profile ) {
						$profile_name = get_post_meta( $profile->ID, '_profile_name', true );
						$display_name = ! empty( $profile_name ) ? $profile_name : $profile->post_title;
						?>
						<option value="<?php echo esc_attr( $profile->ID ); ?>" <?php selected( $structure_profile_id, $profile->ID ); ?>>
							<?php echo esc_html( $display_name ); ?>
						</option>
						<?php
					}
					?>
				</select>
			</div>

			<div style="margin-bottom: 15px;">
				<label for="structure_tag" style="display: block; margin-bottom: 5px; font-weight: bold;">
					<?php _e( 'Structure Tag', 'ecosys-profile-manager' ); ?>
				</label>
				<input 
					type="text" 
					id="structure_tag" 
					name="structure_tag" 
					value="<?php echo esc_attr( $structure_tag ); ?>" 
					style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
				/>
			</div>

			<div style="margin-bottom: 15px;">
				<label style="display: block; margin-bottom: 5px; font-weight: bold;">
					<?php _e( 'Uploaded Images', 'ecosys-profile-manager' ); ?>
				</label>
				<input type="hidden" id="structure_pictures" name="structure_pictures" value="<?php echo esc_attr( implode( ',', $picture_ids ) ); ?>" />
				<button type="button" class="button button-primary" id="upload_structure_pictures">
					<?php _e( 'Add Pictures', 'ecosys-profile-manager' ); ?>
				</button>
				<p style="font-size: 12px; color: #666;">
					<?php _e( 'Click to upload one or more images', 'ecosys-profile-manager' ); ?>
				</p>

				<div id="structure_pictures_preview" style="margin-top: 20px;">
					<?php
					if ( ! empty( $picture_ids ) ) {
						echo '<h4>' . esc_html__( 'Uploaded Images', 'ecosys-profile-manager' ) . ':</h4>';
						echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';

						foreach ( $picture_ids as $image_id ) {
							$image_id = absint( $image_id );
							$image_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
							$full_image_url = wp_get_attachment_image_src( $image_id, 'full' );

							if ( $image_url ) {
								echo '<div style="position: relative; display: inline-block; cursor: pointer;" class="structure-image-wrapper" data-id="' . esc_attr( $image_id ) . '">';
								echo '<a href="' . esc_url( $full_image_url[0] ) . '" class="glightbox" data-gallery="structure_gallery" style="display: block;">';
								echo '<img src="' . esc_url( $image_url[0] ) . '" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; transition: transform 0.2s;" />';
								echo '</a>';
								echo '<button type="button" class="remove-picture" data-id="' . esc_attr( $image_id ) . '" style="position: absolute; top: -8px; right: -8px; background: red; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer;">×</button>';
								echo '</div>';
							}
						}

						echo '</div>';
					}
					?>
				</div>
			</div>

			<div style="margin-bottom: 15px;">
				<label for="structure_description" style="display: block; margin-bottom: 5px; font-weight: bold;">
					<?php _e( 'Structure Description', 'ecosys-profile-manager' ); ?>
				</label>
				<textarea 
					id="structure_description" 
					name="structure_description" 
					rows="5" 
					style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
				><?php echo esc_textarea( $structure_description ); ?></textarea>
			</div>
		</div>

		<script>
		jQuery(function($) {
			var mediaUploader;
			var selectedImageIds = [];

			function initLightbox() {
				if (typeof GLightbox !== 'undefined') {
					GLightbox({
						selector: '.glightbox',
						descPosition: 'bottom'
					});
				}
			}

			function updateImageIds() {
				var idString = $('#structure_pictures').val();
				selectedImageIds = idString ? idString.split(',').map(function(id) { return parseInt(id); }) : [];
			}

			updateImageIds();
			initLightbox();

			$('#upload_structure_pictures').on('click', function(e) {
				e.preventDefault();

				if (mediaUploader) {
					mediaUploader.open();
					return;
				}

				mediaUploader = wp.media.frames.file_frame = wp.media({
					title: '<?php echo esc_js( __( 'Select Images', 'ecosys-profile-manager' ) ); ?>',
					button: {
						text: '<?php echo esc_js( __( 'Select Images', 'ecosys-profile-manager' ) ); ?>'
					},
					multiple: true,
					library: {
						type: 'image'
					}
				});

				mediaUploader.on('select', function() {
					var attachments = mediaUploader.state().get('selection').toJSON();
					attachments.forEach(function(attachment) {
						if (selectedImageIds.indexOf(attachment.id) === -1) {
							selectedImageIds.push(attachment.id);
						}
					});
					$('#structure_pictures').val(selectedImageIds.join(','));
					refreshImagePreview();
				});

				mediaUploader.open();
			});

			function refreshImagePreview() {
				if (selectedImageIds.length === 0) {
					$('#structure_pictures_preview').html('');
					return;
				}

				var html = '<h4><?php echo esc_js( __( 'Uploaded Images', 'ecosys-profile-manager' ) ); ?>:</h4><div style="display: flex; flex-wrap: wrap; gap: 10px;">';

				selectedImageIds.forEach(function(id) {
					var attachment = wp.media.attachment(id);
					if (!attachment.get('url')) {
						attachment.fetch();
					}

					attachment.on('change sync', function() {
						var thumbUrl = attachment.attributes.url;
						if (attachment.attributes.sizes && attachment.attributes.sizes.thumbnail) {
							thumbUrl = attachment.attributes.sizes.thumbnail.url;
						}
						var fullUrl = attachment.attributes.url;
						var imageHtml = '<div style="position: relative; display: inline-block; cursor: pointer;" class="structure-image-wrapper" data-id="' + id + '">';
						imageHtml += '<a href="' + fullUrl + '" class="glightbox" data-gallery="structure_gallery" style="display: block;">';
						imageHtml += '<img src="' + thumbUrl + '" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; transition: transform 0.2s;" />';
						imageHtml += '</a>';
						imageHtml += '<button type="button" class="remove-picture" data-id="' + id + '" style="position: absolute; top: -8px; right: -8px; background: red; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 16px; padding: 0; line-height: 1;">×</button>';
						imageHtml += '</div>';

						if ($('.structure-image-wrapper[data-id="' + id + '"]').length === 0) {
							$('#structure_pictures_preview').append(imageHtml);
						}
					});
				});

				if ($('#structure_pictures_preview').html().indexOf('<h4>') === -1) {
					$('#structure_pictures_preview').html(html);
				}

				setTimeout(function() {
					initLightbox();
				}, 100);
			}

			$(document).on('click', '.remove-picture', function(e) {
				e.preventDefault();
				var imageId = $(this).data('id');
				selectedImageIds = selectedImageIds.filter(function(id) {
					return id !== imageId;
				});
				$('#structure_pictures').val(selectedImageIds.join(','));
				$(this).closest('div').remove();
				if (selectedImageIds.length === 0) {
					$('#structure_pictures_preview').html('');
				}
				initLightbox();
			});
		});
		</script>
		<?php
	}

	/**
	 * Save the structure metabox data.
	 *
	 * @since    1.0.0
	 * @param    int $post_id The post ID.
	 */
	public function save_structure_metabox( $post_id ) {
		if ( get_post_type( $post_id ) !== 'profile_structure' ) {
			return;
		}
		if ( ! isset( $_POST['structure_fields_nonce_field'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['structure_fields_nonce_field'], 'structure_fields_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['structure_profile_id'] ) ) {
			update_post_meta( $post_id, '_structure_profile_id', absint( $_POST['structure_profile_id'] ) );
		}

		if ( isset( $_POST['structure_tag'] ) ) {
			update_post_meta( $post_id, '_structure_tag', sanitize_text_field( $_POST['structure_tag'] ) );
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
