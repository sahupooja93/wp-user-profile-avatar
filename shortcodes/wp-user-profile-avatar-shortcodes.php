
<?php
class WP_User_Profile_Avatar_Shortcodes {

	/**
	 * Constructor - get the plugin hooked in and ready
	 */
	public function __construct() 
	{
		add_shortcode( 'user_profile_avatar', array( $this, 'user_profile_avatar' ) );
		add_shortcode( 'user_profile_avatar_upload', array( $this, 'user_profile_avatar_upload' ) );		

		add_action( 'wp_ajax_nopriv_update_user_avatar', array( $this, 'update_user_avatar' ) );
		add_action( 'wp_ajax_update_user_avatar', array( $this, 'update_user_avatar' ) );

		add_action( 'wp_ajax_nopriv_remove_user_avatar', array( $this, 'remove_user_avatar' ) );
		add_action( 'wp_ajax_remove_user_avatar', array( $this, 'remove_user_avatar' ) );

		add_action( 'wp_ajax_nopriv_undo_user_avatar', array( $this, 'undo_user_avatar' ) );
		add_action( 'wp_ajax_undo_user_avatar', array( $this, 'undo_user_avatar' ) );
	}

    /**
     * user_profile_avatar function.
     *
     * @access public
     * @param $atts, $content
     * @return 
     * @since 1.0
     */
	public function user_profile_avatar($atts = [], $content = null) 
	{
		global $blog_id, $post, $wpdb;

		$current_user_id = get_current_user_id();

		extract( shortcode_atts( array(
			'user_id' 	=> '',
			'size' 		=> 'thumbnail',
			'align' 	=> 'alignnone',
			'link' 		=> '#',
			'target'	=> '_self',

		), $atts ) );

		ob_start();

		$image_url = get_wp_user_profile_avatar_url( $user_id, ['size' => $size ] );

		if($link == 'image') {
	        // Get image src
	        $link = get_wp_user_profile_avatar_url( $user_id, ['size' => 'original' ] );
	    } 
	    elseif($link == 'attachment') 
	    {
	        // Get attachment URL
	        $link = get_attachment_link(get_the_author_meta($wpdb->get_blog_prefix($blog_id).'user_avatar', $user_id));
	    }
		?>

		<div class="wp-user-profile-avatar">
			<a href="<?php echo $link; ?>" target="<?php echo $target; ?>" class="wp-user-profile-avatar-link"><img src="<?php echo $image_url; ?>" class="size-<?php echo $size; ?> <?php echo $align; ?>" ></a>
			<p class="caption-text"><?php echo $content; ?></p>
		</div>

		<?php

		return ob_get_clean();

	}

    /**
     * user_profile_avatar_upload function.
     *
     * @access public
     * @param $atts, $content
     * @return 
     * @since 1.0
     */
	public function user_profile_avatar_upload($atts = [], $content = null) 
	{
		extract( shortcode_atts( array(
			
		), $atts ) );

		if(!is_user_logged_in())
		{
			echo '<h5><strong style="color:red;">' . __( 'ERROR: ', 'wp-event-manager-zoom' ) . '</strong>' . __( 'You do not have enough priviledge to access this page. Please login to continue.', 'wp-user-profile-avatar' ) . '</h5>';

			return false;
		}

		$wp_user_profile_avatar_allow_upload = get_option('wp_user_profile_avatar_allow_upload');

		$user_id = get_current_user_id();

		$user_data = get_userdata($user_id);

		if(in_array('contributor', $user_data->roles))
		{	
			if(empty($wp_user_profile_avatar_allow_upload))
			{
				echo '<h5><strong style="color:red;">' . __( 'ERROR: ', 'wp-event-manager-zoom' ) . '</strong>' . __( 'You do not have enough priviledge to access this page. Please login to continue.', 'wp-user-profile-avatar' ) . '</h5>';

				return false;
			}
		}

		if(in_array('subscriber', $user_data->roles))
		{	
			if(empty($wp_user_profile_avatar_allow_upload))
			{
				echo '<h5><strong style="color:red;">' . __( 'ERROR: ', 'wp-event-manager-zoom' ) . '</strong>' . __( 'You do not have enough priviledge to access this page. Please login to continue.', 'wp-user-profile-avatar' ) . '</h5>';

				return false;
			}
		}

		

		ob_start();

		wp_enqueue_script( 'wp-user-profile-avatar-frontend-avatar' );

		$wp_user_profile_avatar_original = get_wp_user_profile_avatar_url($user_id, ['size' => 'original']);
		$wp_user_profile_avatar_thumbnail = get_wp_user_profile_avatar_url($user_id, ['size' => 'thumbnail']);

		$wp_user_profile_avatar_attachment_id = get_user_meta($user_id, 'wp_user_profile_avatar_attachment_id', true);
		$wp_user_profile_avatar_url = get_user_meta($user_id, 'wp_user_profile_avatar_url', true);
		?>

		<div class="wp-user-profile-avatar-upload">
			<form method="post" name="update-user-profile-avatar" class="update-user-profile-avatar" enctype="multipart/form-data">
				<table class="form-table">
					<tr>
						<td>
							<p>
							<input type="text" name="wp_user_profile_avatar_url" class="regular-text code" value="<?php echo $wp_user_profile_avatar_url; ?>" placeholder="Enter Image URL">
							</p>

							<p><?php _e('OR Upload Image', 'wp-user-profile-avatar'); ?></p>

							<p id="wp-user-profile-avatar-add-button-existing">
								<?php /* <button type="button" class="button" id="wp-user-profile-avatar-add"><?php _e('Choose Image'); ?></button> */ ?>
								<input type="file" name="wp_user_profile_avatar_upload" class="input-text wp-user-profile-avatar-image" accept="image/jpg, image/jpeg, image/gif, image/png" >

								<input type="hidden" name="wp_user_profile_avatar_attachment_id" id="wp_user_profile_avatar_attachment_id" value="<?php echo $wp_user_profile_avatar_attachment_id; ?>">
								<input type="hidden" name="user_id" id="wp_user_id" value="<?php echo $user_id; ?>">
							</p>
						</td>
					</tr>

					<?php
	              	$class_hide = 'wp-user-profile-avatar-hide';
	              	if(!empty($wp_user_profile_avatar_attachment_id))
	              	{
	              		$class_hide = '';
	              	}
	              	?>
					<tr id="wp-user-profile-avatar-images-existing">
						<td>
					      	<p id="wp-user-profile-avatar-preview">
					        	<img src="<?php echo $wp_user_profile_avatar_original; ?>" alt="">
					        	<span class="description"><?php _e('Original Size', 'wp-user-profile-avatar'); ?></span>
					      	</p>
					      	<p id="wp-user-profile-avatar-thumbnail">
					        	<img src="<?php echo $wp_user_profile_avatar_thumbnail; ?>" alt="">
					        	<span class="description"><?php _e('Thumbnail', 'wp-user-profile-avatar'); ?></span>
					      	</p>
					      	<p id="wp-user-profile-avatar-remove-button" class="<?php echo $class_hide; ?>">
						        <button type="button" class="button" id="wp-user-profile-avatar-remove"><?php _e('Remove Image', 'wp-user-profile-avatar'); ?></button>
					        </p>
					      	<p id="wp-user-profile-avatar-undo-button">
					      		<button type="button" class="button" id="wp-user-profile-avatar-undo"><?php _e('Undo', 'wp-user-profile-avatar'); ?></button>
					      	</p>
				      	</td>
					</tr>

					<tr>
						<td>
							<button type="button" class="button" id="wp-user-profile-avatar-update-profile"><?php _e('Update Profile', 'wp-user-profile-avatar'); ?></button>
						</td>
					</tr>

				</table>
			</form>

			<div id="upload_avatar_responce"></div>

		</div>

		<?php

		return ob_get_clean();
	}

    /**
     * update_user_avatar function.
     *
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
	public function update_user_avatar() 
	{
		check_ajax_referer( '_nonce_user_profile_avatar_security', 'security' );

		$form_data = $_POST['formData'];
        parse_str($form_data, $form_data);

        $user_id = $form_data['user_id'];

		if (!empty($_FILES['user-avatar']))
        {
            $file = $_FILES['user-avatar'];            

            $post_id = 0;

            // Upload file
            $overrides     = array('test_form' => false);
            $uploaded_file = $this->handle_upload($file, $overrides);

            $attachment = array(
                'post_title'     => $file['name'],
                'post_content'   => '',
                'post_type'      => 'attachment',
                'post_parent'    => null, // populated after inserting post
                'post_mime_type' => $file['type'],
                'guid'           => $uploaded_file['url']
            );

            $attachment['post_parent'] = $post_id;

            $attach_id = wp_insert_attachment($attachment, $uploaded_file['file'], $post_id);

            $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file['file']);

            $result = wp_update_attachment_metadata($attach_id, $attach_data);

            update_user_meta($user_id, 'wp_user_profile_avatar_attachment_id', $attach_id);
        }
        else
        {
        	update_user_meta($user_id, 'wp_user_profile_avatar_attachment_id', $form_data['wp_user_profile_avatar_attachment_id']);
        }

        update_user_meta($user_id, 'wp_user_profile_avatar_url', $form_data['wp_user_profile_avatar_url']);

        if(!empty($form_data['wp_user_profile_avatar_attachment_id']) || $form_data['wp_user_profile_avatar_url'])
		{
			update_user_meta( $user_id, 'wp_user_profile_avatar_default', 'wp_user_profile_avatar' );
		}
		else
		{
			update_user_meta( $user_id, 'wp_user_profile_avatar_default', '' );
		}


        $wp_user_profile_avatar_attachment_id = get_user_meta($user_id, 'wp_user_profile_avatar_attachment_id', true);
		$wp_user_profile_avatar_url = get_user_meta($user_id, 'wp_user_profile_avatar_url', true);

		if( empty($wp_user_profile_avatar_attachment_id) && empty($wp_user_profile_avatar_url))
		{
			$wp_user_profile_avatar_original = '';
			$wp_user_profile_avatar_thumbnail = '';
			$message = __( 'Error! Select Image', 'wp-user-profile-avatar');
			$class = 'wp-user-profile-avatar-error';
		}
		else
		{
			$wp_user_profile_avatar_original = get_wp_user_profile_avatar_url($user_id, ['size' => 'original']);
			$wp_user_profile_avatar_thumbnail = get_wp_user_profile_avatar_url($user_id, ['size' => 'thumbnail']);
			$message = __( 'Successfully Updated Avatar', 'wp-user-profile-avatar');
			$class = 'wp-user-profile-avatar-success';
		}

		echo json_encode(['avatar_original' => $wp_user_profile_avatar_original, 'avatar_thumbnail' => $wp_user_profile_avatar_thumbnail, 'message' => $message, 'class' => $class]);

        wp_die();
	}

    /**
     * remove_user_avatar function.
     *
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
	function remove_user_avatar()
    {
        check_ajax_referer( '_nonce_user_profile_avatar_security', 'security' );

		$form_data = $_POST['formData'];
        parse_str($form_data, $form_data);

        $user_id = $form_data['user_id'];

        update_user_meta($user_id, 'wp_user_profile_avatar_attachment_id', '');
        update_user_meta($user_id, 'wp_user_profile_avatar_url', '');
        update_user_meta( $user_id, 'wp_user_profile_avatar_default', '' );

        $wp_user_profile_avatar_original = get_wp_user_profile_avatar_url($user_id, ['size' => 'original']);
		$wp_user_profile_avatar_thumbnail = get_wp_user_profile_avatar_url($user_id, ['size' => 'thumbnail']);

		$message = __( 'Successfully Removed Avatar', 'wp-user-profile-avatar');
			$class = 'wp-user-profile-avatar-success';

		echo json_encode(['avatar_original' => $wp_user_profile_avatar_original, 'avatar_thumbnail' => $wp_user_profile_avatar_thumbnail, 'message' => $message, 'class' => $class]);

        wp_die();
    }

    /**
     * undo_user_avatar function.
     *
     * @access public
     * @param 
     * @return 
     * @since 1.0
     */
    function undo_user_avatar()
    {
        check_ajax_referer( '_nonce_user_profile_avatar_security', 'security' );

		$form_data = $_POST['formData'];
        parse_str($form_data, $form_data);

        $user_id = $form_data['user_id'];

        update_user_meta($user_id, 'wp_user_profile_avatar_attachment_id', $form_data['wp_user_profile_avatar_attachment_id']);
        update_user_meta($user_id, 'wp_user_profile_avatar_url', $form_data['wp_user_profile_avatar_url']);

        if(!empty($form_data['wp_user_profile_avatar_attachment_id']) || $form_data['wp_user_profile_avatar_url'])
		{
			update_user_meta( $user_id, 'wp_user_profile_avatar_default', 'wp_user_profile_avatar' );
		}
		else
		{
			update_user_meta( $user_id, 'wp_user_profile_avatar_default', '' );
		}

        $wp_user_profile_avatar_original = get_wp_user_profile_avatar_url($user_id, ['size' => 'original']);
		$wp_user_profile_avatar_thumbnail = get_wp_user_profile_avatar_url($user_id, ['size' => 'thumbnail']);

		$message = __( 'Successfully Undo Avatar', 'wp-user-profile-avatar');
			$class = 'wp-user-profile-avatar-success';

		echo json_encode(['avatar_original' => $wp_user_profile_avatar_original, 'avatar_thumbnail' => $wp_user_profile_avatar_thumbnail, 'message' => $message, 'class' => $class]);

        wp_die();
    }

    /**
     * handle_upload function.
     *
     * @access public
     * @param $file_handler, $overrides
     * @return 
     * @since 1.0
     */
	function handle_upload($file_handler, $overrides)
    {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $upload = wp_handle_upload($file_handler, $overrides);

        return $upload;
    }

}

new WP_User_Profile_Avatar_Shortcodes();