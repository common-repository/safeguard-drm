<?php if ( ! defined('ABSPATH')) exit;

function WPSGDRM_ajax_resend_token()
{
	if( ! current_user_can('manage_options')) {
		wp_send_json_error();
	}

	try {
		$email = isset($_REQUEST['email']) ? sanitize_email($_REQUEST['email']) : '';
		$token = isset($_REQUEST['token']) ? sanitize_text_field($_REQUEST['token']) : '';
		$nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';

		if ( ! wp_verify_nonce($nonce, 'wpsgdrm_token_nonce')) {
			throw new Exception(__('Form has expired, please reload the page.', 'safeguard-drm'));
		}

		if( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new Exception(__('Invalid email address.', 'safeguard-drm'));
		}

		if(empty($token)) {
			throw new Exception(__('Empty token.', 'safeguard-drm'));
		}

		$av_apikey = get_option('wpsgdrm_av_apikeydrm');

		if(empty($av_apikey)) {
			throw new Exception(__('Invalid api key.', 'safeguard-drm'));
		}

		$token_parts = explode('@', $token);
		$url = isset($token_parts[1]) ? $token_parts[1] : '';

		$token = WPSGDRM_encrypt_decrypt('encrypt', $token);

		$client_id = WPSGDRM_get_client_id();

		if(empty($client_id)) {
			throw new Exception(__('Invalid client id.', 'safeguard-drm'));
		}
		
		$token = $client_id.'@'.$token;

		WPSGDRM_send_email($token, $email, $url);

		wp_send_json_success([
			'message' => __('Successfully sent.', 'safeguard-drm'),
		]);
	} catch(Exception $e) {
		wp_send_json_error([
			'error' => $e->getMessage(),
		]);
	}
}
add_action('wp_ajax_wpsgdrm_resend_token', 'WPSGDRM_ajax_resend_token');