<?php
/**
 * Profile metabox functionality.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile
 */

/**
 * The profile metabox class.
 *
 * Handles metabox and custom field functionality for the Profile post type.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/profile
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Profile_MetaBox {

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
	 * Add metabox for profile custom fields.
	 *
	 * @since    1.0.0
	 */
	public function add_profile_metabox() {
		add_meta_box(
			'profile_custom_fields',
			__( 'Profile Information', 'ecosys-profile-manager' ),
			array( $this, 'render_profile_metabox' ),
			'profile',
			'normal',
			'high'
		);
	}

	/**
	 * Render the profile metabox.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post The post object.
	 */
	public function render_profile_metabox( $post ) {
		wp_nonce_field( 'profile_metabox_nonce', 'profile_metabox_nonce_field' );

		// Get existing values
		$name = get_post_meta( $post->ID, '_profile_name', true );
		$contact_number = get_post_meta( $post->ID, '_profile_contact_number', true );
		$age = get_post_meta( $post->ID, '_profile_age', true );
		$sex = get_post_meta( $post->ID, '_profile_sex', true );
		$project_id = get_post_meta( $post->ID, '_profile_project_id', true );
		?>
		<div style="padding: 15px 0;">
			<div style="margin-bottom: 15px;">
				<label for="profile_name" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">
					<?php _e( 'Name', 'ecosys-profile-manager' ); ?>
				</label>
				<input 
					type="text" 
					id="profile_name" 
					name="profile_name" 
					value="<?php echo esc_attr( $name ); ?>" 
					style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;"
				/>
			</div>

			<div style="margin-bottom: 15px;">
				<label for="profile_contact_number" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">
					<?php _e( 'Contact Number', 'ecosys-profile-manager' ); ?>
				</label>
				<input 
					type="number" 
					id="profile_contact_number" 
					name="profile_contact_number" 
					value="<?php echo esc_attr( $contact_number ); ?>" 
					style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;"
				/>
			</div>

			<div style="margin-bottom: 15px;">
				<label for="profile_age" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">
					<?php _e( 'Age', 'ecosys-profile-manager' ); ?>
				</label>
				<input 
					type="number" 
					id="profile_age" 
					name="profile_age" 
					value="<?php echo esc_attr( $age ); ?>" 
					min="0"
					max="150"
					style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;"
				/>
			</div>

			<div style="margin-bottom: 15px;">
				<label for="profile_sex" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">
					<?php _e( 'Sex', 'ecosys-profile-manager' ); ?>
				</label>
				<select 
					id="profile_sex" 
					name="profile_sex" 
					style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;"
				>
					<option value=""><?php _e( 'Select...', 'ecosys-profile-manager' ); ?></option>
					<option value="male" <?php selected( $sex, 'male' ); ?>>
						<?php _e( 'Male', 'ecosys-profile-manager' ); ?>
					</option>
					<option value="female" <?php selected( $sex, 'female' ); ?>>
						<?php _e( 'Female', 'ecosys-profile-manager' ); ?>
					</option>
				</select>
			</div>

			<div style="margin-bottom: 15px;">
				<label for="profile_project_id" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;">
					<?php _e( 'Project', 'ecosys-profile-manager' ); ?>
				</label>
				<select 
					id="profile_project_id" 
					name="profile_project_id" 
					style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;"
				>
					<option value=""><?php _e( 'Select Project...', 'ecosys-profile-manager' ); ?></option>
					<?php
					// Get all projects
					$projects = get_posts( array(
						'post_type'      => 'project',
						'posts_per_page' => -1,
						'orderby'        => 'title',
						'order'          => 'ASC',
					) );

					foreach ( $projects as $project ) {
						$project_name = get_post_meta( $project->ID, '_project_name', true );
						$display_name = ! empty( $project_name ) ? $project_name : $project->post_title;
						?>
						<option value="<?php echo esc_attr( $project->ID ); ?>" <?php selected( $project_id, $project->ID ); ?>>
							<?php echo esc_html( $display_name ); ?>
						</option>
						<?php
					}
					?>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the profile metabox data.
	 *
	 * @since    1.0.0
	 * @param    int $post_id The post ID.
	 */
	public function save_profile_metabox( $post_id ) {
		// Verify nonce
		if ( ! isset( $_POST['profile_metabox_nonce_field'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['profile_metabox_nonce_field'], 'profile_metabox_nonce' ) ) {
			return;
		}

		// Check user capabilities
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Name
		if ( isset( $_POST['profile_name'] ) ) {
			update_post_meta( $post_id, '_profile_name', sanitize_text_field( $_POST['profile_name'] ) );
		}

		// Save Contact Number
		if ( isset( $_POST['profile_contact_number'] ) ) {
			update_post_meta( $post_id, '_profile_contact_number', sanitize_text_field( $_POST['profile_contact_number'] ) );
		}

		// Save Age
		if ( isset( $_POST['profile_age'] ) ) {
			update_post_meta( $post_id, '_profile_age', sanitize_text_field( $_POST['profile_age'] ) );
		}

		// Save Sex
		if ( isset( $_POST['profile_sex'] ) ) {
			update_post_meta( $post_id, '_profile_sex', sanitize_text_field( $_POST['profile_sex'] ) );
		}

		// Save Project ID
		if ( isset( $_POST['profile_project_id'] ) ) {
			update_post_meta( $post_id, '_profile_project_id', absint( $_POST['profile_project_id'] ) );
		}
	}

	/**
	 * Add metabox for structure information - lists structures linked to this profile.
	 *
	 * @since    1.0.0
	 */
	public function add_structure_information_metabox() {
		add_meta_box(
			'structure_information',
			__( 'Structure Information', 'ecosys-profile-manager' ),
			array( $this, 'render_structure_information_metabox' ),
			'profile',
			'normal',
			'high'
		);
	}

	/**
	 * Render the structure information metabox - lists structures and Add Structure dialog.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post The post object.
	 */
	public function render_structure_information_metabox( $post ) {
		$profile_id = $post->ID;
		$is_new_profile = empty( $profile_id ) || 'auto-draft' === $post->post_status;

		$structures = array();
		if ( ! $is_new_profile ) {
			$structures = get_posts( array(
				'post_type'      => 'profile_structure',
				'posts_per_page' => -1,
				'meta_key'       => '_structure_profile_id',
				'meta_value'     => $profile_id,
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );
		}
		?>
		<div class="ecosys-structure-metabox" data-profile-id="<?php echo esc_attr( $profile_id ); ?>">
			<?php if ( $is_new_profile ) : ?>
				<p style="color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px;">
					<?php _e( 'Please save or publish the profile first to add structures.', 'ecosys-profile-manager' ); ?>
				</p>
			<?php else : ?>
				<p>
					<button type="button" class="button button-primary" id="ecosys-open-add-structure-dialog">
						<?php _e( 'Add Structure', 'ecosys-profile-manager' ); ?>
					</button>
				</p>

				<div id="ecosys-structure-list-wrap">
					<?php if ( ! empty( $structures ) ) : ?>
						<table class="widefat striped" style="margin-top: 15px;" id="ecosys-structures-table">
							<thead>
								<tr>
									<th><?php _e( 'Structure Tag', 'ecosys-profile-manager' ); ?></th>
									<th><?php _e( 'Images', 'ecosys-profile-manager' ); ?></th>
									<th><?php _e( 'Description', 'ecosys-profile-manager' ); ?></th>
									<th><?php _e( 'Actions', 'ecosys-profile-manager' ); ?></th>
								</tr>
							</thead>
							<tbody id="ecosys-structures-tbody">
								<?php foreach ( $structures as $structure ) : ?>
									<?php echo $this->get_structure_row_html( $structure ); ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p class="ecosys-no-structures-msg" style="color: #666; margin-top: 10px;"><?php _e( 'No structures added yet. Click "Add Structure" to add one.', 'ecosys-profile-manager' ); ?></p>
					<?php endif; ?>
				</div>

				<!-- Add/Edit Structure Modal -->
				<div id="ecosys-add-structure-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 100050; align-items: center; justify-content: center;">
					<div style="background: #fff; padding: 24px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
						<h3 id="ecosys-modal-title" style="margin-top: 0;"><?php _e( 'Add Structure', 'ecosys-profile-manager' ); ?></h3>
						<input type="hidden" id="ecosys-modal-structure-id" value="" />

						<div style="margin-bottom: 15px;">
							<label for="ecosys-modal-structure-tag" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;"><?php _e( 'Structure Tag', 'ecosys-profile-manager' ); ?></label>
							<input type="text" id="ecosys-modal-structure-tag" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;" />
						</div>

						<div style="margin-bottom: 15px;">
							<label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;"><?php _e( 'Uploaded Images', 'ecosys-profile-manager' ); ?></label>
							<input type="hidden" id="ecosys-modal-structure-pictures" value="" />
							<button type="button" class="button" id="ecosys-modal-upload-pictures"><?php _e( 'Add Pictures', 'ecosys-profile-manager' ); ?></button>
							<div id="ecosys-modal-pictures-preview" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;"></div>
						</div>

						<div style="margin-bottom: 20px;">
							<label for="ecosys-modal-structure-description" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 12px;"><?php _e( 'Structure Description', 'ecosys-profile-manager' ); ?></label>
							<textarea id="ecosys-modal-structure-description" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;"></textarea>
						</div>

						<div style="display: flex; gap: 8px; justify-content: flex-end;">
							<button type="button" class="button" id="ecosys-modal-cancel"><?php _e( 'Cancel', 'ecosys-profile-manager' ); ?></button>
							<button type="button" class="button button-primary" id="ecosys-modal-save"><?php _e( 'Add Structure', 'ecosys-profile-manager' ); ?></button>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php
		if ( ! $is_new_profile ) {
			$this->render_structure_dialog_script();
		}
		?>
		<?php
	}

	/**
	 * Output a single structure row HTML.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $structure The structure post object.
	 * @return   string HTML for the row.
	 */
	public function get_structure_row_html( $structure ) {
		$tag         = get_post_meta( $structure->ID, '_structure_tag', true );
		$description = get_post_meta( $structure->ID, '_structure_description', true );
		$pictures    = get_post_meta( $structure->ID, '_structure_pictures', true );
		$edit_url    = get_edit_post_link( $structure->ID );

		$picture_ids = array();
		if ( ! empty( $pictures ) ) {
			$picture_ids = is_array( $pictures ) ? $pictures : (array) $pictures;
		}

		$images_html = '';
		if ( ! empty( $picture_ids ) ) {
			$gallery_id  = 'structure-gallery-' . $structure->ID;
			$desc_attr   = ! empty( $description ) ? ' description: ' . esc_attr( $description ) . '; descPosition: bottom' : '';
			$images_html = '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
			foreach ( $picture_ids as $image_id ) {
				$image_id = absint( $image_id );
				$thumb    = wp_get_attachment_image_src( $image_id, 'thumbnail' );
				$full     = wp_get_attachment_image_src( $image_id, 'full' );
				if ( $thumb && $full ) {
					$glightbox_attr = 'data-gallery="' . esc_attr( $gallery_id ) . '"';
					if ( $desc_attr ) {
						$glightbox_attr .= ' data-glightbox="' . trim( $desc_attr ) . '"';
					}
					$images_html .= '<a href="' . esc_url( $full[0] ) . '" class="ecosys-structure-glightbox" ' . $glightbox_attr . '>';
					$images_html .= '<img src="' . esc_url( $thumb[0] ) . '" style="max-width: 60px; max-height: 60px; border: 1px solid #ddd; border-radius: 4px; object-fit: cover; cursor: pointer;" alt="" />';
					$images_html .= '</a>';
				}
			}
			$images_html .= '</div>';
		} else {
			$images_html = '—';
		}

		ob_start();
		?>
		<tr data-structure-id="<?php echo esc_attr( $structure->ID ); ?>">
			<td><?php echo esc_html( $tag ?: '—' ); ?></td>
			<td><?php echo $images_html; ?></td>
			<td><?php echo esc_html( wp_trim_words( $description, 10 ) ?: '—' ); ?></td>
			<td><button type="button" class="button button-small ecosys-edit-structure" data-structure-id="<?php echo esc_attr( $structure->ID ); ?>"><?php _e( 'Edit', 'ecosys-profile-manager' ); ?></button></td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render JavaScript for the Add Structure dialog.
	 *
	 * @since    1.0.0
	 */
	private function render_structure_dialog_script() {
		$ajax_nonce  = wp_create_nonce( 'ecosys_add_structure' );
		$edit_nonce  = wp_create_nonce( 'ecosys_edit_structure' );
		?>
		<script>
		(function($) {
			function initStructureLightbox() {
				if (typeof GLightbox !== 'undefined') {
					new GLightbox({ selector: '.ecosys-structure-glightbox', openEffect: 'zoom', closeEffect: 'fade', descPosition: 'bottom' });
				}
			}
			$(window).on('load', function() { initStructureLightbox(); });

			var $modal = $('#ecosys-add-structure-modal');
			var $tbody = $('#ecosys-structures-tbody');
			var $noStructuresMsg = $('.ecosys-no-structures-msg');
			var profileId = $('.ecosys-structure-metabox').data('profile-id');
			var mediaUploader;
			var modalPictureIds = [];

			function openModal(structureId) {
				$modal.css('display', 'flex');
				$('#ecosys-modal-structure-id').val(structureId || '');
				$('#ecosys-modal-structure-tag').val('');
				$('#ecosys-modal-structure-pictures').val('');
				$('#ecosys-modal-structure-description').val('');
				modalPictureIds = [];
				$('#ecosys-modal-pictures-preview').html('');
				if (structureId) {
					$('#ecosys-modal-title').text('<?php echo esc_js( __( 'Edit Structure', 'ecosys-profile-manager' ) ); ?>');
					$('#ecosys-modal-save').text('<?php echo esc_js( __( 'Update Structure', 'ecosys-profile-manager' ) ); ?>');
					$.post(ajaxurl, { action: 'ecosys_get_structure', nonce: '<?php echo esc_js( $edit_nonce ); ?>', structure_id: structureId }).done(function(res) {
						if (res.success && res.data) {
							var d = res.data;
							$('#ecosys-modal-structure-tag').val(d.structure_tag || '');
							$('#ecosys-modal-structure-description').val(d.structure_description || '');
							modalPictureIds = d.structure_pictures || [];
							updateModalPreview(d.pictures_data || []);
						}
					});
				} else {
					$('#ecosys-modal-title').text('<?php echo esc_js( __( 'Add Structure', 'ecosys-profile-manager' ) ); ?>');
					$('#ecosys-modal-save').text('<?php echo esc_js( __( 'Add Structure', 'ecosys-profile-manager' ) ); ?>');
				}
			}

			function closeModal() {
				$modal.hide();
			}

			$('#ecosys-open-add-structure-dialog').on('click', function() { openModal(); });
			$(document).on('click', '.ecosys-edit-structure', function() {
				openModal($(this).data('structure-id'));
			});
			$('#ecosys-modal-cancel').on('click', closeModal);
			$modal.on('click', function(e) {
				if (e.target === $modal[0]) closeModal();
			});

			$('#ecosys-modal-upload-pictures').on('click', function() {
				if (mediaUploader) {
					mediaUploader.open();
					return;
				}
				mediaUploader = wp.media({
					title: '<?php echo esc_js( __( 'Select Images', 'ecosys-profile-manager' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Select Images', 'ecosys-profile-manager' ) ); ?>' },
					multiple: true,
					library: { type: 'image' }
				});
				mediaUploader.on('select', function() {
					var attachments = mediaUploader.state().get('selection').toJSON();
					attachments.forEach(function(a) {
						if (modalPictureIds.indexOf(a.id) === -1) modalPictureIds.push(a.id);
					});
					updateModalPreview(attachments);
				});
				mediaUploader.open();
			});

			function updateModalPreview(attachmentsData) {
				var $preview = $('#ecosys-modal-pictures-preview');
				$preview.empty();
				attachmentsData = attachmentsData || [];
				var dataMap = {};
				attachmentsData.forEach(function(a) {
					dataMap[a.id] = (a.sizes && a.sizes.thumbnail && a.sizes.thumbnail.url) ? a.sizes.thumbnail.url : (a.url || '');
				});
				modalPictureIds.forEach(function(id) {
					var thumb = dataMap[id];
					if (!thumb && wp.media && wp.media.attachment) {
						var att = wp.media.attachment(id);
						if (att.attributes) {
							thumb = (att.attributes.sizes && att.attributes.sizes.thumbnail) ? att.attributes.sizes.thumbnail.url : att.attributes.url || '';
						}
					}
					thumb = thumb || '';
					var $wrap = $('<div style="position:relative;display:inline-block;">');
					$wrap.append($('<img>').attr('src', thumb).css({maxWidth:'80px',maxHeight:'80px',border:'1px solid #ddd',borderRadius:'4px'}));
					$wrap.append($('<button type="button" class="remove-modal-pic" data-id="'+id+'">×</button>').css({position:'absolute',top:'-6px',right:'-6px',background:'red',color:'#fff',border:'none',width:'20px',height:'20px',borderRadius:'50%',cursor:'pointer',fontSize:'14px',lineHeight:1,padding:0}));
					$preview.append($wrap);
				});
			}

			$(document).on('click', '.remove-modal-pic', function() {
				var id = parseInt($(this).data('id'), 10);
				modalPictureIds = modalPictureIds.filter(function(x) { return x !== id; });
				updateModalPreview();
			});

			$('#ecosys-modal-save').on('click', function() {
				var $btn = $(this);
				var structureId = $('#ecosys-modal-structure-id').val();
				var isEdit = structureId && structureId !== '';
				$btn.prop('disabled', true);
				var payload = {
					action: isEdit ? 'ecosys_update_structure' : 'ecosys_add_structure',
					nonce: isEdit ? '<?php echo esc_js( $edit_nonce ); ?>' : '<?php echo esc_js( $ajax_nonce ); ?>',
					profile_id: profileId,
					structure_tag: $('#ecosys-modal-structure-tag').val(),
					structure_pictures: modalPictureIds.join(','),
					structure_description: $('#ecosys-modal-structure-description').val()
				};
				if (isEdit) payload.structure_id = structureId;
				$.post(ajaxurl, payload).done(function(res) {
					if (res.success && res.data && res.data.row_html) {
						if (isEdit) {
							$('tr[data-structure-id="' + structureId + '"]').replaceWith(res.data.row_html);
						} else {
							var $existingTbody = $('#ecosys-structures-tbody');
							if ($existingTbody.length) {
								$existingTbody.prepend(res.data.row_html);
							} else {
								var tableHtml = '<table class="widefat striped" style="margin-top:15px;" id="ecosys-structures-table"><thead><tr><th><?php echo esc_js( __( 'Structure Tag', 'ecosys-profile-manager' ) ); ?></th><th><?php echo esc_js( __( 'Images', 'ecosys-profile-manager' ) ); ?></th><th><?php echo esc_js( __( 'Description', 'ecosys-profile-manager' ) ); ?></th><th><?php echo esc_js( __( 'Actions', 'ecosys-profile-manager' ) ); ?></th></tr></thead><tbody id="ecosys-structures-tbody">' + res.data.row_html + '</tbody></table>';
								$('#ecosys-structure-list-wrap').html(tableHtml);
							}
						}
						initStructureLightbox();
						closeModal();
					} else {
						alert(res.data && res.data.message ? res.data.message : '<?php echo esc_js( __( 'An error occurred.', 'ecosys-profile-manager' ) ); ?>');
					}
				}).fail(function() {
					alert('<?php echo esc_js( __( 'An error occurred.', 'ecosys-profile-manager' ) ); ?>');
				}).always(function() {
					$btn.prop('disabled', false);
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * AJAX handler to add a structure from the profile edit screen.
	 *
	 * @since    1.0.0
	 */
	public function ajax_add_structure() {
		check_ajax_referer( 'ecosys_add_structure', 'nonce' );

		$profile_id = isset( $_POST['profile_id'] ) ? absint( $_POST['profile_id'] ) : 0;
		if ( ! $profile_id || get_post_type( $profile_id ) !== 'profile' ) {
			wp_send_json_error( array( 'message' => __( 'Invalid profile.', 'ecosys-profile-manager' ) ) );
		}

		if ( ! current_user_can( 'edit_post', $profile_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ecosys-profile-manager' ) ) );
		}

		$structure_tag        = isset( $_POST['structure_tag'] ) ? sanitize_text_field( wp_unslash( $_POST['structure_tag'] ) ) : '';
		$structure_pictures   = isset( $_POST['structure_pictures'] ) ? sanitize_text_field( wp_unslash( $_POST['structure_pictures'] ) ) : '';
		$structure_description = isset( $_POST['structure_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['structure_description'] ) ) : '';

		$picture_ids = array_filter( array_map( 'absint', explode( ',', $structure_pictures ) ) );

		$structure_id = wp_insert_post( array(
			'post_type'   => 'profile_structure',
			'post_status' => 'publish',
			'post_title'  => $structure_tag ?: __( 'Structure', 'ecosys-profile-manager' ),
		) );

		if ( is_wp_error( $structure_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create structure.', 'ecosys-profile-manager' ) ) );
		}

		update_post_meta( $structure_id, '_structure_profile_id', $profile_id );
		update_post_meta( $structure_id, '_structure_tag', $structure_tag );
		update_post_meta( $structure_id, '_structure_pictures', $picture_ids );
		update_post_meta( $structure_id, '_structure_description', $structure_description );

		$structure = get_post( $structure_id );
		$row_html = $this->get_structure_row_html( $structure );

		wp_send_json_success( array( 'row_html' => $row_html, 'structure_id' => $structure_id ) );
	}

	/**
	 * AJAX handler to get structure data for editing.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_structure() {
		check_ajax_referer( 'ecosys_edit_structure', 'nonce' );

		$structure_id = isset( $_POST['structure_id'] ) ? absint( $_POST['structure_id'] ) : 0;
		if ( ! $structure_id || get_post_type( $structure_id ) !== 'profile_structure' ) {
			wp_send_json_error( array( 'message' => __( 'Invalid structure.', 'ecosys-profile-manager' ) ) );
		}

		if ( ! current_user_can( 'edit_post', $structure_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ecosys-profile-manager' ) ) );
		}

		$tag         = get_post_meta( $structure_id, '_structure_tag', true );
		$description = get_post_meta( $structure_id, '_structure_description', true );
		$pictures    = get_post_meta( $structure_id, '_structure_pictures', true );
		$picture_ids = is_array( $pictures ) ? $pictures : ( ! empty( $pictures ) ? (array) $pictures : array() );

		$pictures_data = array();
		foreach ( $picture_ids as $pid ) {
			$pid = absint( $pid );
			$thumb = wp_get_attachment_image_src( $pid, 'thumbnail' );
			$full  = wp_get_attachment_image_src( $pid, 'full' );
			if ( $thumb && $full ) {
				$pictures_data[] = array(
					'id'    => $pid,
					'url'   => $full[0],
					'sizes' => array( 'thumbnail' => array( 'url' => $thumb[0] ) ),
				);
			}
		}

		wp_send_json_success( array(
			'structure_tag'        => $tag,
			'structure_description' => $description,
			'structure_pictures'   => $picture_ids,
			'pictures_data'        => $pictures_data,
		) );
	}

	/**
	 * AJAX handler to update a structure.
	 *
	 * @since    1.0.0
	 */
	public function ajax_update_structure() {
		check_ajax_referer( 'ecosys_edit_structure', 'nonce' );

		$structure_id = isset( $_POST['structure_id'] ) ? absint( $_POST['structure_id'] ) : 0;
		if ( ! $structure_id || get_post_type( $structure_id ) !== 'profile_structure' ) {
			wp_send_json_error( array( 'message' => __( 'Invalid structure.', 'ecosys-profile-manager' ) ) );
		}

		if ( ! current_user_can( 'edit_post', $structure_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ecosys-profile-manager' ) ) );
		}

		$structure_tag         = isset( $_POST['structure_tag'] ) ? sanitize_text_field( wp_unslash( $_POST['structure_tag'] ) ) : '';
		$structure_pictures    = isset( $_POST['structure_pictures'] ) ? sanitize_text_field( wp_unslash( $_POST['structure_pictures'] ) ) : '';
		$structure_description = isset( $_POST['structure_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['structure_description'] ) ) : '';

		$picture_ids = array_filter( array_map( 'absint', explode( ',', $structure_pictures ) ) );

		wp_update_post( array(
			'ID'         => $structure_id,
			'post_title' => $structure_tag ?: __( 'Structure', 'ecosys-profile-manager' ),
		) );

		update_post_meta( $structure_id, '_structure_tag', $structure_tag );
		update_post_meta( $structure_id, '_structure_pictures', $picture_ids );
		update_post_meta( $structure_id, '_structure_description', $structure_description );

		$structure = get_post( $structure_id );
		$row_html  = $this->get_structure_row_html( $structure );

		wp_send_json_success( array( 'row_html' => $row_html, 'structure_id' => $structure_id ) );
	}

}
