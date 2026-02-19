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
	 * @param    string $to      Recipient email.
	 * @param    string $subject Subject line.
	 * @param    bool   $success Whether the send succeeded.
	 * @param    string $source  Optional. Context (e.g. 'test_email', 'new_profile').
	 * @param    string $error   Optional. Error message if failed.
	 */
	public function log( $to, $subject, $success, $source = '', $error = '' ) {
		$logs   = $this->get_logs();
		$logs[] = array(
			'time'    => current_time( 'mysql' ),
			'to'      => $to,
			'subject' => $subject,
			'success' => $success,
			'source'  => $source,
			'error'   => $error,
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
		$this->log( $to, $subject, false, 'wp_mail', $msg );
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
}
