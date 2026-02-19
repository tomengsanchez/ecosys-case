/**
 * Ecosys Profile Manager - Settings page (email debug logs modal)
 *
 * @package Ecosys_Profile_Manager
 * @since 1.0.0
 */

(function ($) {
	'use strict';

	function initEmailLogsModal() {
		var $btn = $('#ecosys-view-email-logs');
		var $modal = $('#ecosys-email-logs-modal');
		var $content = $('#ecosys-email-logs-content');
		var $overlay = $modal.find('.ecosys-modal-overlay');
		var $close = $modal.find('.ecosys-modal-close');
		var i18n = (typeof ecosysSettings !== 'undefined' && ecosysSettings.i18n) ? ecosysSettings.i18n : {};

		function showModal() {
			$content.html('<p>' + (i18n.loading || 'Loading…') + '</p>');
			$modal.attr('aria-hidden', 'false').show();

			$.ajax({
				url: (typeof ecosysSettings !== 'undefined') ? ecosysSettings.ajaxUrl : '',
				type: 'POST',
				data: {
					action: 'ecosys_get_email_logs',
					nonce: (typeof ecosysSettings !== 'undefined') ? ecosysSettings.nonce : ''
				},
				success: function (res) {
					if (res.success && res.data && res.data.logs) {
						$content.html(buildLogsTable(res.data.logs, i18n));
					} else {
						$content.html('<p>' + (i18n.noLogs || 'No email logs yet.') + '</p>');
					}
				},
				error: function () {
					$content.html('<p class="ecosys-log-failed">' + (i18n.error || 'Error') + ': Failed to load logs.</p>');
				}
			});
		}

		function buildLogsTable(logs, i18n) {
			if (!logs || logs.length === 0) {
				return '<p>' + (i18n.noLogs || 'No email logs yet.') + '</p>';
			}
			var html = '<table class="ecosys-email-logs-table"><thead><tr>';
			html += '<th>' + (i18n.time || 'Time') + '</th>';
			html += '<th>' + (i18n.to || 'To') + '</th>';
			html += '<th>' + (i18n.subject || 'Subject') + '</th>';
			html += '<th>' + (i18n.status || 'Status') + '</th>';
			html += '<th>' + (i18n.source || 'Source') + '</th>';
			html += '<th>' + (i18n.error || 'Error') + '</th>';
			html += '<th>' + (i18n.response || 'Response') + '</th>';
			html += '</tr></thead><tbody>';
			logs.forEach(function (log) {
				var statusClass = log.success ? 'ecosys-log-success' : 'ecosys-log-failed';
				var statusText = log.success ? (i18n.success || 'Success') : (i18n.failed || 'Failed');
				html += '<tr>';
				html += '<td>' + escapeHtml(log.time || '') + '</td>';
				html += '<td>' + escapeHtml(log.to || '') + '</td>';
				html += '<td>' + escapeHtml(log.subject || '') + '</td>';
				html += '<td class="' + statusClass + '">' + escapeHtml(statusText) + '</td>';
				html += '<td>' + escapeHtml(log.source || '') + '</td>';
				html += '<td class="ecosys-log-error">' + escapeHtml(log.error || '') + '</td>';
				html += '<td class="ecosys-log-response">' + escapeHtml(log.response || '') + '</td>';
				html += '</tr>';
			});
			html += '</tbody></table>';
			return html;
		}

		function escapeHtml(s) {
			if (s == null) return '';
			var div = document.createElement('div');
			div.textContent = s;
			return div.innerHTML;
		}

		function hideModal() {
			$modal.attr('aria-hidden', 'true').hide();
		}

		function clearLogs() {
			var $clearBtn = $('#ecosys-clear-email-logs');
			$clearBtn.prop('disabled', true);
			$.ajax({
				url: (typeof ecosysSettings !== 'undefined') ? ecosysSettings.ajaxUrl : '',
				type: 'POST',
				data: {
					action: 'ecosys_clear_email_logs',
					nonce: (typeof ecosysSettings !== 'undefined') ? ecosysSettings.nonce : ''
				},
				success: function (res) {
					if (res.success) {
						$content.html('<p>' + (i18n.noLogs || 'No email logs yet.') + '</p>');
					}
				},
				complete: function () {
					$clearBtn.prop('disabled', false);
				}
			});
		}

		$btn.on('click', showModal);
		$close.on('click', hideModal);
		$overlay.on('click', hideModal);
		$(document).on('click', '#ecosys-clear-email-logs', clearLogs);

		$(document).on('keydown.ecosysModal', function (e) {
			if (e.key === 'Escape' && $modal.is(':visible')) {
				hideModal();
			}
		});
	}

	function initSmtpToggle() {
		var $cb = $('#ecosys_use_custom_smtp');
		var $wrap = $('#ecosys-smtp-config-wrap');
		var $row = $('#ecosys-smtp-config-row');

		function toggleSmtpFields() {
			var enabled = $cb.is(':checked');
			$wrap.find('input, select').prop('disabled', !enabled);
			$row.toggleClass('ecosys-smtp-disabled', !enabled);
		}

		$cb.on('change', toggleSmtpFields);
		toggleSmtpFields();
	}

	$(function () {
		if ($('#ecosys-view-email-logs').length) {
			initEmailLogsModal();
		}
		if ($('#ecosys_use_custom_smtp').length) {
			initSmtpToggle();
		}
	});
})(jQuery);
