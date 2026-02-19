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
	 * Option key: use custom SMTP instead of WordPress default mail.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	const OPTION_USE_CUSTOM_SMTP = 'ecosys_profile_manager_use_custom_smtp';

	/**
	 * Option key prefix for SMTP settings.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	const OPTION_SMTP_PREFIX = 'ecosys_profile_manager_smtp_';

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
	 * Get whether custom SMTP is enabled.
	 *
	 * @since    1.0.0
	 * @return   bool
	 */
	public function get_use_custom_smtp() {
		return get_option( self::OPTION_USE_CUSTOM_SMTP, '0' ) === '1';
	}

	/**
	 * Save "Use custom SMTP" setting.
	 *
	 * @since    1.0.0
	 * @param    bool $enabled Whether to use custom SMTP.
	 */
	public function save_use_custom_smtp( $enabled ) {
		update_option( self::OPTION_USE_CUSTOM_SMTP, $enabled ? '1' : '0' );
	}

	/**
	 * Get SMTP setting value.
	 *
	 * @since    1.0.0
	 * @param    string $key     Setting key (host, port, encryption, username, password, from_email, from_name).
	 * @param    string $default Default value.
	 * @return   string
	 */
	public function get_smtp( $key, $default = '' ) {
		return get_option( self::OPTION_SMTP_PREFIX . $key, $default );
	}

	/**
	 * Save SMTP settings from POST-like array.
	 *
	 * @since    1.0.0
	 * @param    array $values Associative array of key => value.
	 */
	public function save_smtp( $values ) {
		$keys = array( 'host', 'port', 'encryption', 'username', 'password', 'from_email', 'from_name', 'insecure_ssl' );
		foreach ( $keys as $key ) {
			if ( $key === 'insecure_ssl' ) {
				$enabled = isset( $values[ $key ] ) && $values[ $key ];
				update_option( self::OPTION_SMTP_PREFIX . $key, $enabled ? '1' : '0' );
				continue;
			}
			if ( ! isset( $values[ $key ] ) ) {
				continue;
			}
			if ( $key === 'password' ) {
				$value = is_string( $values[ $key ] ) ? $values[ $key ] : '';
				if ( $value !== '' ) {
					update_option( self::OPTION_SMTP_PREFIX . $key, $value );
				}
				continue;
			}
			$value = sanitize_text_field( $values[ $key ] );
			update_option( self::OPTION_SMTP_PREFIX . $key, $value );
		}
	}

	/**
	 * Configure PHPMailer for custom SMTP when use_custom_smtp is enabled.
	 *
	 * @since    1.0.0
	 * @param    object $phpmailer The PHPMailer instance.
	 */
	public function phpmailer_init_smtp( $phpmailer ) {
		if ( ! $this->get_use_custom_smtp() ) {
			return;
		}
		$host = $this->get_smtp( 'host' );
		if ( empty( $host ) ) {
			return;
		}
		$phpmailer->isSMTP();
		$phpmailer->Host       = $host;
		$port                  = absint( $this->get_smtp( 'port', '587' ) ) ?: 587;
		$phpmailer->Port       = $port;
		$encryption            = $this->get_smtp( 'encryption', 'tls' );
		$phpmailer->SMTPSecure = $encryption;
		$phpmailer->SMTPAutoTLS = ! empty( $encryption );
		$phpmailer->Timeout    = 30;

		$username = $this->get_smtp( 'username' );
		$password = $this->get_smtp( 'password' );
		if ( ! empty( $username ) && $password !== '' ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = $username;
			$phpmailer->Password = $password;
		} else {
			$phpmailer->SMTPAuth = false;
		}

		if ( $this->get_smtp( 'insecure_ssl', '0' ) === '1' ) {
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				),
			);
		}

		$from_email = $this->get_smtp( 'from_email' );
		if ( ! empty( $from_email ) && is_email( $from_email ) ) {
			$phpmailer->setFrom( $from_email, $this->get_smtp( 'from_name', '' ), false );
		}

		if ( $this->email_logger ) {
			Ecosys_Profile_Manager_Email_Logger::clear_smtp_debug();
			$phpmailer->SMTPDebug   = 2;
			$phpmailer->Debugoutput = Ecosys_Profile_Manager_Email_Logger::get_smtp_debug_callback();
		}
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
			if ( $sent ) {
				$this->email_logger->log( $to, $subject, true, 'new_profile', '', '' );
			}
			// On failure, wp_mail_failed will log with full error and SMTP response.
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
			if ( $sent ) {
				$this->email_logger->log( $to, $subject, true, 'test_email', '', '' );
			}
			// On failure, wp_mail_failed will log with full error and SMTP response.
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
