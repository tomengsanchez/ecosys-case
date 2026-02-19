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
 * Handles plugin options (get/save) and hooks that depend on them (e.g. new profile email, SMTP).
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/settings
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Settings_Options {

	/**
	 * Option key: notify admin when a new profile is added.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	const OPTION_NOTIFY_ON_NEW_PROFILE = 'ecosys_profile_manager_notify_on_new_profile';

	/**
	 * Option key: SMTP settings (host, port, encryption, username, password, from_email, from_name, use_for_entire_site).
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	const OPTION_SMTP = 'ecosys_profile_manager_smtp';

	/**
	 * Option key: email debug log (last N sends).
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	const OPTION_EMAIL_DEBUG_LOG = 'ecosys_profile_manager_email_debug_log';

	/**
	 * Max size in bytes for the stored debug log (trimmed from start).
	 *
	 * @since    1.0.0
	 * @var      int
	 */
	const DEBUG_LOG_MAX_BYTES = 100000;

	/**
	 * Flag set to true when the plugin is sending mail (so SMTP is used when use_smtp is on).
	 *
	 * @since    1.0.0
	 * @var      bool
	 */
	public static $sending_plugin_mail = false;

	/**
	 * Flag set to true when sending a test mail (always uses SMTP when configured, regardless of use_smtp).
	 *
	 * @since    1.0.0
	 * @var      bool
	 */
	public static $sending_test_mail = false;

	/**
	 * Buffer for current request's PHPMailer debug output.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	public static $debug_log_buffer = '';

	/**
	 * Context description for the current send (e.g. "Test email to x@y.com").
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	public static $debug_log_context = '';

	/**
	 * Whether shutdown has been registered for this request (to flush debug log).
	 *
	 * @since    1.0.0
	 * @var      bool
	 */
	private static $debug_shutdown_registered = false;

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

		self::$sending_plugin_mail = true;
		self::$debug_log_context   = __( 'New profile notification', 'ecosys-profile-manager' ) . ' → ' . $to;
		wp_mail( $to, $subject, $body );
		self::$sending_plugin_mail = false;
		self::$debug_log_context   = '';
	}

	/**
	 * Default SMTP options.
	 *
	 * @since    1.0.0
	 * @return   array
	 */
	public static function get_smtp_defaults() {
		return array(
			'use_smtp'            => false,
			'host'                => '',
			'port'                => '587',
			'encryption'          => 'tls',
			'username'            => '',
			'password'            => '',
			'from_email'          => '',
			'from_name'            => '',
			'use_for_entire_site' => false,
		);
	}

	/**
	 * Get SMTP options (merged with defaults).
	 *
	 * @since    1.0.0
	 * @return   array
	 */
	public function get_smtp_options() {
		$saved = get_option( self::OPTION_SMTP, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_merge( self::get_smtp_defaults(), $saved );
	}

	/**
	 * Save SMTP options (only allowed keys).
	 *
	 * @since    1.0.0
	 * @param    array $options Options array (host, port, encryption, username, password, from_email, from_name, use_for_entire_site).
	 */
	public function save_smtp_options( $options ) {
		$defaults = self::get_smtp_defaults();
		$allowed  = array_keys( $defaults );
		$out      = array();
		foreach ( $allowed as $key ) {
			if ( ! isset( $options[ $key ] ) ) {
				$out[ $key ] = $defaults[ $key ];
				continue;
			}
			$val = $options[ $key ];
			if ( $key === 'use_for_entire_site' || $key === 'use_smtp' ) {
				$out[ $key ] = (bool) $val;
			} elseif ( $key === 'port' ) {
				$out[ $key ] = absint( $val );
				if ( $out[ $key ] < 1 ) {
					$out[ $key ] = 587;
				}
			} elseif ( $key === 'encryption' ) {
				$out[ $key ] = in_array( $val, array( 'none', 'tls', 'ssl' ), true ) ? $val : 'tls';
			} elseif ( $key === 'password' ) {
				$out[ $key ] = is_string( $val ) ? $val : '';
			} else {
				$out[ $key ] = sanitize_text_field( $val );
			}
		}
		update_option( self::OPTION_SMTP, $out );
	}

	/**
	 * Whether SMTP is configured (has host).
	 *
	 * @since    1.0.0
	 * @return   bool
	 */
	public function is_smtp_configured() {
		$opts = $this->get_smtp_options();
		return ! empty( $opts['host'] );
	}

	/**
	 * Configure PHPMailer to use SMTP when plugin SMTP is enabled.
	 * Runs on phpmailer_init. Use SMTP for entire site if option is set, otherwise only for plugin-originated mail.
	 *
	 * @since    1.0.0
	 * @param    \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance.
	 */
	public function configure_phpmailer_smtp( $phpmailer ) {
		if ( ! $this->is_smtp_configured() ) {
			return;
		}

		$opts = $this->get_smtp_options();

		$use_smtp      = ! empty( $opts['use_smtp'] );
		$use_globally  = ! empty( $opts['use_for_entire_site'] );
		$plugin_mail   = self::$sending_plugin_mail;
		$test_mail     = self::$sending_test_mail;

		$should_use_smtp = $test_mail || ( $use_smtp && ( $use_globally || $plugin_mail ) );
		if ( ! $should_use_smtp ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host       = $opts['host'];
		$phpmailer->Port       = (int) $opts['port'];
		$phpmailer->SMTPAuth   = ! empty( $opts['username'] );
		$phpmailer->Username   = $opts['username'];
		$phpmailer->Password   = $opts['password'];
		$phpmailer->SMTPSecure = $opts['encryption'] === 'none' ? '' : $opts['encryption'];

		if ( ! empty( $opts['from_email'] ) ) {
			$phpmailer->From = $opts['from_email'];
		}
		if ( ! empty( $opts['from_name'] ) ) {
			$phpmailer->FromName = $opts['from_name'];
		}

		// Capture SMTP debug output for the email debug log.
		self::$debug_log_buffer = '';
		if ( ! self::$debug_shutdown_registered ) {
			self::$debug_shutdown_registered = true;
			register_shutdown_function( array( __CLASS__, 'flush_debug_log_to_option' ) );
		}
		$phpmailer->SMTPDebug   = 2;
		$phpmailer->Debugoutput = array( __CLASS__, 'append_debug_log' );
	}

	/**
	 * Callback for PHPMailer Debugoutput: append to buffer.
	 *
	 * @since    1.0.0
	 * @param    string $str  Debug line.
	 * @param    int    $level Debug level (unused).
	 */
	public static function append_debug_log( $str, $level = 0 ) {
		self::$debug_log_buffer .= $str;
	}

	/**
	 * Shutdown: save current request's debug buffer to option (with timestamp and context).
	 *
	 * @since    1.0.0
	 */
	public static function flush_debug_log_to_option() {
		if ( self::$debug_log_buffer === '' ) {
			return;
		}
		$context = self::$debug_log_context ? self::$debug_log_context : __( 'Email send', 'ecosys-profile-manager' );
		$header  = '=== ' . gmdate( 'Y-m-d H:i:s' ) . ' (' . $context . ") ===\n";
		$entry   = $header . self::$debug_log_buffer . "\n";

		$log   = get_option( self::OPTION_EMAIL_DEBUG_LOG, '' );
		$log   = $entry . $log;
		$bytes = strlen( $log );
		if ( $bytes > self::DEBUG_LOG_MAX_BYTES ) {
			$log = substr( $log, 0, self::DEBUG_LOG_MAX_BYTES );
			$p   = strpos( $log, "\n===", 100 );
			if ( $p !== false ) {
				$log = substr( $log, $p + 1 );
			}
		}
		update_option( self::OPTION_EMAIL_DEBUG_LOG, $log );
	}

	/**
	 * Get the stored email debug log content.
	 *
	 * @since    1.0.0
	 * @return   string
	 */
	public function get_email_debug_log() {
		$log = get_option( self::OPTION_EMAIL_DEBUG_LOG, '' );
		return is_string( $log ) ? $log : '';
	}

	/**
	 * Clear the stored email debug log.
	 *
	 * @since    1.0.0
	 */
	public function clear_email_debug_log() {
		delete_option( self::OPTION_EMAIL_DEBUG_LOG );
	}

	/**
	 * AJAX handler: send a test email using the saved SMTP settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_send_test_mail() {
		check_ajax_referer( 'ecosys_send_test_mail', 'nonce' );

		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ecosys-profile-manager' ) ) );
		}

		if ( ! $this->is_smtp_configured() ) {
			wp_send_json_error( array( 'message' => __( 'SMTP is not configured. Save host and other settings first.', 'ecosys-profile-manager' ) ) );
		}

		$to = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
		if ( ! is_email( $to ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address.', 'ecosys-profile-manager' ) ) );
		}

		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] SMTP test email', 'ecosys-profile-manager' ),
			get_bloginfo( 'name' )
		);
		$body = __( 'This is a test email from your Ecosys Profile Manager SMTP settings.', 'ecosys-profile-manager' ) . "\n\n";
		$body .= __( 'If you received this, SMTP is working correctly.', 'ecosys-profile-manager' );

		self::$sending_test_mail  = true;
		self::$debug_log_context  = __( 'Test email', 'ecosys-profile-manager' ) . ' → ' . $to;
		$sent = wp_mail( $to, $subject, $body );
		self::$sending_test_mail  = false;
		self::$debug_log_context  = '';

		if ( $sent ) {
			wp_send_json_success( array( 'message' => __( 'Test email sent. Check the inbox (and spam) for the address you entered.', 'ecosys-profile-manager' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'wp_mail() returned false. Check SMTP credentials and server logs.', 'ecosys-profile-manager' ) ) );
	}

	/**
	 * AJAX handler: return the email debug log content for the dialog.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_email_debug_log() {
		check_ajax_referer( 'ecosys_get_email_debug_log', 'nonce' );

		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ecosys-profile-manager' ) ) );
		}

		$log = $this->get_email_debug_log();
		wp_send_json_success( array( 'log' => $log ) );
	}

	/**
	 * AJAX handler: clear the email debug log.
	 *
	 * @since    1.0.0
	 */
	public function ajax_clear_email_debug_log() {
		check_ajax_referer( 'ecosys_clear_email_debug_log', 'nonce' );

		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ecosys-profile-manager' ) ) );
		}

		$this->clear_email_debug_log();
		wp_send_json_success( array( 'message' => __( 'Debug log cleared.', 'ecosys-profile-manager' ) ) );
	}
}
