<?php
/**
 * Plugin settings: option keys, get/save, and notification logic.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/settings
 */

/**
 * Handles plugin options (get/save) and hooks that depend on them (e.g. new profile email).
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/settings
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Settings_Options {

	/**
	 * Email logger instance (optional).
	 *
	 * @since    1.0.0
	 * @var      Ecosys_Profile_Manager_Email_Logger|null
	 */
	private $email_logger;

	/**
	 * @since    1.0.0
	 * @param    Ecosys_Profile_Manager_Email_Logger|null $email_logger Optional. For debug logging.
	 */
	public function __construct( Ecosys_Profile_Manager_Email_Logger $email_logger = null ) {
		$this->email_logger = $email_logger;
	}

	/**
	 * Option key: notify admin when a new profile is added.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	const OPTION_NOTIFY_ON_NEW_PROFILE = 'ecosys_profile_manager_notify_on_new_profile';

	/**
	 * Get whether "Notify on new profile" is enabled.
	 *
	 * @since    1.0.0
	 * @return   bool
	 */
	public function get_notify_on_new_profile() {
		return get_option( self::OPTION_NOTIFY_ON_NEW_PROFILE, '0' ) === '1';
	}

	/**
	 * Save "Notify on new profile" setting.
	 *
	 * @since    1.0.0
	 * @param    bool $enabled Whether to enable the notification.
	 */
	public function save_notify_on_new_profile( $enabled ) {
		update_option( self::OPTION_NOTIFY_ON_NEW_PROFILE, $enabled ? '1' : '0' );
	}

	/**
	 * If a new profile was just added and notifications are enabled, send email.
	 *
	 * @since    1.0.0
	 * @param    string  $new_status New post status.
	 * @param    string  $old_status Old post status.
	 * @param    WP_Post $post       Post object.
	 */
	public function maybe_notify_new_profile( $new_status, $old_status, $post ) {
		if ( ! $post || $post->post_type !== 'profile' ) {
			return;
		}
		if ( $old_status !== 'auto-draft' && $old_status !== 'new' ) {
			return;
		}
		if ( $new_status !== 'publish' && $new_status !== 'draft' ) {
			return;
		}
		if ( ! $this->get_notify_on_new_profile() ) {
			return;
		}
		$this->send_new_profile_notification_email( $post );
	}

	/**
	 * Send notification email to admin when a new profile is added.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post The profile post.
	 */
	private function send_new_profile_notification_email( $post ) {
		$to      = get_option( 'admin_email' );
		$subject = sprintf(
			/* translators: 1: site name, 2: profile title (control number) */
			__( '[%1$s] New profile added: %2$s', 'ecosys-profile-manager' ),
			get_bloginfo( 'name' ),
			$post->post_title
		);
		$profile_name = get_post_meta( $post->ID, '_profile_name', true );
		$edit_link    = get_edit_post_link( $post->ID, 'raw' );
		$body         = __( 'A new profile has been added.', 'ecosys-profile-manager' ) . "\n\n";
		$body        .= __( 'Control Number:', 'ecosys-profile-manager' ) . ' ' . $post->post_title . "\n";
		$body        .= __( 'Name:', 'ecosys-profile-manager' ) . ' ' . ( $profile_name ? $profile_name : '—' ) . "\n";
		$body        .= __( 'Edit:', 'ecosys-profile-manager' ) . ' ' . $edit_link . "\n";
		$sent = wp_mail( $to, $subject, $body );
		if ( $this->email_logger ) {
			$this->email_logger->log( $to, $subject, $sent, 'new_profile', $sent ? '' : __( 'wp_mail returned false', 'ecosys-profile-manager' ) );
		}
	}

	/**
	 * Send a test email to the given address.
	 *
	 * @since    1.0.0
	 * @param    string $to Optional. Recipient email. Defaults to admin_email.
	 * @return   bool True if mail was sent successfully.
	 */
	public function send_test_email( $to = '' ) {
		if ( empty( $to ) || ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}
		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] Test email from Ecosys Profile Manager', 'ecosys-profile-manager' ),
			get_bloginfo( 'name' )
		);
		$body = __( 'This is a test email. Your email settings are working correctly.', 'ecosys-profile-manager' ) . "\n\n";
		$body .= sprintf(
			/* translators: %s: site URL */
			__( 'Sent from: %s', 'ecosys-profile-manager' ),
			home_url()
		);
		$sent = wp_mail( $to, $subject, $body );
		if ( $this->email_logger ) {
			$this->email_logger->log( $to, $subject, $sent, 'test_email', $sent ? '' : __( 'wp_mail returned false', 'ecosys-profile-manager' ) );
		}
		return $sent;
	}

	/**
	 * Get the email logger instance.
	 *
	 * @since    1.0.0
	 * @return   Ecosys_Profile_Manager_Email_Logger|null
	 */
	public function get_email_logger() {
		return $this->email_logger;
	}
}
