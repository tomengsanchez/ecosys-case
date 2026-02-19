<?php
/**
 * Email debug logger: stores mail send attempts for debugging.
 *
 * @link       https://ecosys.io
 * @since      1.0.0
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/settings
 */

/**
 * Logs plugin email sends and wp_mail failures for debugging.
 *
 * @package    Ecosys_Profile_Manager
 * @subpackage Ecosys_Profile_Manager/admin/settings
 * @author     Ecosys <info@ecosys.io>
 */
class Ecosys_Profile_Manager_Email_Logger {

	/**
	 * Buffer for captured SMTP debug output (per send).
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	private static $smtp_debug_buffer = '';

	/**
	 * Option key for log storage.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	const OPTION_EMAIL_LOGS = 'ecosys_profile_manager_email_logs';

	/**
	 * Maximum number of log entries to keep.
	 *
	 * @since    1.0.0
	 * @var      int
	 */
	const MAX_LOGS = 50;

	/**
	 * Log an email send attempt.
	 *
	 * @since    1.0.0
	 * @param    string $to       Recipient email.
	 * @param    string $subject  Subject line.
	 * @param    bool   $success  Whether the send succeeded.
	 * @param    string $source   Optional. Context (e.g. 'test_email', 'new_profile').
	 * @param    string $error    Optional. Error message if failed.
	 * @param    string $response Optional. SMTP/server response text from the email attempt.
	 */
	public function log( $to, $subject, $success, $source = '', $error = '', $response = '' ) {
		$sections = array();
		if ( $error !== '' || $response !== '' ) {
			$err_block = trim( ( $error !== '' ? $error . "\n\n" : '' ) . $response );
			if ( $err_block !== '' ) {
				$sections[] = "--- Error Information ---\n" . $err_block;
			}
		}
		$captured = self::get_and_clear_smtp_debug();
		if ( $captured !== '' ) {
			$sections[] = "--- SMTP Debug ---\n" . $captured;
		}
		$response = implode( "\n\n", $sections );
		$logs   = $this->get_logs();
		$logs[] = array(
			'time'     => current_time( 'mysql' ),
			'to'       => $to,
			'subject'  => $subject,
			'success'  => $success,
			'source'   => $source,
			'error'    => $error,
			'response' => $response,
		);
		$logs = array_slice( array_reverse( $logs ), 0, self::MAX_LOGS );
		update_option( self::OPTION_EMAIL_LOGS, array_reverse( $logs ), false );
	}

	/**
	 * Get stored log entries.
	 *
	 * @since    1.0.0
	 * @return   array Log entries (newest first).
	 */
	public function get_logs() {
		$logs = get_option( self::OPTION_EMAIL_LOGS, array() );
		if ( ! is_array( $logs ) ) {
			return array();
		}
		return array_reverse( $logs );
	}

	/**
	 * Hook: log wp_mail failures (any plugin/theme).
	 *
	 * WordPress passes only the WP_Error; mail data is in error->get_error_data().
	 * Extracts SMTP response from PHPMailer when available.
	 *
	 * @since    1.0.0
	 * @param    WP_Error $error     Error object.
	 * @param    array    $mail_data Optional. Mail data (WP may not pass this; we use error data).
	 */
	public function on_wp_mail_failed( $error, $mail_data = array() ) {
		if ( empty( $mail_data ) && $error instanceof WP_Error ) {
			$data      = $error->get_error_data();
			$mail_data = is_array( $data ) ? $data : array();
		}
		$to      = isset( $mail_data['to'] ) ? ( is_array( $mail_data['to'] ) ? implode( ', ', $mail_data['to'] ) : $mail_data['to'] ) : '';
		$subject = isset( $mail_data['subject'] ) ? $mail_data['subject'] : '';
		$msg     = $error instanceof WP_Error ? $error->get_error_message() : (string) $error;

		$debug_parts = array();
		if ( ! empty( $msg ) ) {
			$debug_parts[] = $msg;
		}
		if ( $error instanceof WP_Error ) {
			$all_messages = $error->get_error_messages();
			foreach ( $all_messages as $m ) {
				if ( $m !== $msg && ! empty( $m ) ) {
					$debug_parts[] = $m;
				}
			}
		}
		if ( ! empty( $mail_data['phpmailer'] ) && is_object( $mail_data['phpmailer'] ) ) {
			$phpmailer = $mail_data['phpmailer'];
			if ( ! empty( $phpmailer->ErrorInfo ) && ! in_array( $phpmailer->ErrorInfo, $debug_parts, true ) ) {
				$debug_parts[] = $phpmailer->ErrorInfo;
			}
			if ( method_exists( $phpmailer, 'getSMTPInstance' ) ) {
				$smtp = $phpmailer->getSMTPInstance();
				if ( $smtp ) {
					if ( method_exists( $smtp, 'getLastResponse' ) ) {
						$last = $smtp->getLastResponse();
						if ( ! empty( $last ) && ! in_array( $last, $debug_parts, true ) ) {
							$debug_parts[] = $last;
						}
					}
					foreach ( array( 'last_reply', 'last_response' ) as $prop ) {
						if ( isset( $smtp->{$prop} ) ) {
							$val = is_array( $smtp->{$prop} ) ? implode( "\n", $smtp->{$prop} ) : (string) $smtp->{$prop};
							if ( $val !== '' && ! in_array( $val, $debug_parts, true ) ) {
								$debug_parts[] = $val;
							}
							break;
						}
					}
					if ( method_exists( $smtp, 'getError' ) ) {
						$smtp_err = $smtp->getError();
						if ( ! empty( $smtp_err ) && ! in_array( $smtp_err, $debug_parts, true ) ) {
							$debug_parts[] = $smtp_err;
						}
					}
					if ( ! empty( $smtp->error ) && ! in_array( $smtp->error, $debug_parts, true ) ) {
						$debug_parts[] = $smtp->error;
					}
				}
			}
		}

		$response = implode( "\n\n", array_unique( array_filter( $debug_parts ) ) );
		if ( empty( $response ) ) {
			$response = $msg;
		}

		$source = 'wp_mail';
		if ( ! empty( $subject ) ) {
			if ( strpos( $subject, 'Test email from Ecosys Profile Manager' ) !== false ) {
				$source = 'test_email';
			} elseif ( strpos( $subject, 'New profile added:' ) !== false ) {
				$source = 'new_profile';
			}
		}

		$this->log( $to, $subject, false, $source, $msg, $response );
	}

	/**
	 * Clear the SMTP debug buffer (call at start of each send).
	 *
	 * @since    1.0.0
	 */
	public static function clear_smtp_debug() {
		self::$smtp_debug_buffer = '';
	}

	/**
	 * Get and clear the captured SMTP debug buffer.
	 *
	 * @since    1.0.0
	 * @return   string Captured debug output.
	 */
	public static function get_and_clear_smtp_debug() {
		$buf       = self::$smtp_debug_buffer;
		self::$smtp_debug_buffer = '';
		return $buf;
	}

	/**
	 * Return a callable for PHPMailer Debugoutput to capture SMTP conversation.
	 *
	 * @since    1.0.0
	 * @return   callable
	 */
	public static function get_smtp_debug_callback() {
		return function ( $str, $level ) {
			Ecosys_Profile_Manager_Email_Logger::$smtp_debug_buffer .= $str;
		};
	}

	/**
	 * AJAX handler: return logs as JSON.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_logs() {
		check_ajax_referer( 'ecosys_email_logs', 'nonce' );
		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) ) {
			wp_send_json_error( array( 'message' => __( 'Forbidden.', 'ecosys-profile-manager' ) ), 403 );
		}
		wp_send_json_success( array( 'logs' => $this->get_logs() ) );
	}

	/**
	 * AJAX handler: clear all email logs.
	 *
	 * @since    1.0.0
	 */
	public function ajax_clear_logs() {
		check_ajax_referer( 'ecosys_email_logs', 'nonce' );
		if ( ! current_user_can( 'manage_ecosys_profile_manager' ) ) {
			wp_send_json_error( array( 'message' => __( 'Forbidden.', 'ecosys-profile-manager' ) ), 403 );
		}
		update_option( self::OPTION_EMAIL_LOGS, array(), false );
		wp_send_json_success( array( 'logs' => array() ) );
	}
}
