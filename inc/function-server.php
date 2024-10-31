<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

function WPSGDRM_new_demo($params)
{
	$post = [
		'action'       => 'newdemo',
		'fm_firstname' => $params['firstname'],
		'fm_lastname'  => $params['lastname'],
		'fm_email'     => $params['email'],
		'fm_company'   => $params['company'],
		'fm_domain'    => $params['domain'],
	];
	$url = 'https://safeguard.media/drm/webplugin.asp?action=newdemo&fm_firstname='.
		$params['firstname'].'&fm_lastname='.
		$params['lastname'].'&fm_email='.
		$params['email'].'&fm_company='.
		$params['company'].'&fm_domain='.
		$params['domain'];
	
	$args = array(
		'body'        => $post,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'cookies'     => array(),
	);

	$response = wp_remote_post( $url, $args );

	if( ! is_wp_error($response))
	{
		$parts = explode('~',$response['body']);

		return $parts;
	}

	return [];
}

function WPSGDRM_get_client_id()
{
	$av_apikey = get_option('wpsgdrm_av_apikeydrm');

	$post = [
		'action'    => 'getclient',
		'av_apikey' => $av_apikey,
		
	];
	$args = array(
		'body'        => $post,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'cookies'     => array(),
	);
	$url = 'https://safeguard.media/drm/webplugin.asp?action=getclient&av_apikey='.$av_apikey;

	$response = wp_remote_post( $url, $args );
	
	if( ! is_wp_error($response))
	{
		// execute!
		$parts = explode('~', $response['body']);

		if($parts[0] == 'success' && ! empty($parts[1])) {
			return $parts[1];
		}
	}

	return '';
}

function WPSGDRM_update_settings($params)
{
	$post = [
		'action'         => 'editsettings',
		'av_apikey'      => $params['av_apikey'],
		'fm_firstname'   => $params['fm_firstname'],
		'fm_lastname'    => $params['fm_lastname'],
		'fm_email'       => $params['fm_email'],
		'fm_company'     => $params['fm_company'],
		'fm_domain'      => $params['fm_domain'],
		'allowwindows'   => $params['av_allowwindows'],
		'allowmac'       => $params['av_allowmac'],
		'allowandroid'   => $params['av_allowandroid'],
		'allowios'       => $params['av_allowios'],
		'winversion'     => $params['av_Version_windows'],
		'macversion'     => $params['av_Version_mac'],
		'androidversion' => $params['av_Version_android'],
		'iosversion'     => $params['av_Version_ios'],
	
	];
	$url = 'https://safeguard.media/drm/webplugin.asp?action=editsettings&av_apikey='.
		$params['av_apikey'].'&fm_firstname='.
		$params['fm_firstname'].'&fm_lastname='.
		$params['fm_lastname'].'&fm_email='.
		$params['fm_email'].'&fm_company='.
		$params['fm_company'].'&fm_domain='.
		$params['fm_domain'].'&allowwindows='.
		$params['av_allowwindows'].'&allowmac='.
		$params['av_allowmac'].'&allowandroid='.
		$params['av_allowandroid'].'&allowios='.
		$params['av_allowios'].'&winversion='.
		$params['av_Version_windows'].'&macversion='.
		$params['av_Version_mac'].'&androidversion='.
		$params['av_Version_android'].'&iosversion='.
		$params['av_Version_ios'];
	
	$args = array(
		'body'        => $post,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'cookies'     => array(),
	);

	$response = wp_remote_post( $url, $args );

	if( ! is_wp_error($response))
	{
		$parts = explode('~',$response['body']);

		return $parts;
	}

	return [];
}

function WPSGDRM_fetch_tokens()
{
	$av_apikey = get_option('wpsgdrm_av_apikeydrm');
	$client_id = WPSGDRM_get_client_id();

	$token_list = array();

	if($client_id)
	{
		$post = [
			'action'   => 'gettokens',
			'av_apikey'=> $av_apikey,
		];
		$args = array(
			'body'        => $post,
			'timeout'     => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(),
			'cookies'     => array(),
		);
		$url ='https://safeguard.media/drm/webplugin.asp?action=gettokens&av_apikey='.$av_apikey;
		
		$response = wp_remote_post( $url, $args );

		if( ! is_wp_error($response))
		{
			$parts = explode('~', $response['body']);
			
			if($parts[0] == 'success' && ! empty($parts[1]))
			{
				$file_list_string = $parts[1];
				$file_list_string = trim($file_list_string,', ');

				$file_list = explode(',',$file_list_string);
				$file_list = array_reverse($file_list);
				
				foreach ($file_list as $file)
				{
					$token_data = WPSGDRM_token_to_array($file);

					$token_value = $av_apikey.'@'.$token_data['url'];

					$token_list[] = [
						'token_name'        => $file,
						'token_value'       => $token_value,
						'token_sent'        => $token_data['token_sent'],
						'keyval'            => $token_data['keyval'],
						'url'               => $token_data['url'],
						'plan'              => $token_data['plan'],
						'devices_allowed'   => $token_data['devices_allowed'],
						'number_of_devices' => $token_data['number_of_devices'],
						'user_id'           => $token_data['user_id'],
						'email'             => $token_data['email'],
					];
				}
			}
		}
	}

	return $token_list;
}

function WPSGDRM_token_to_array($token)
{
	list(
		$token_sent,
		$keyval,
		$url,
		$plan,
		$expiry2,
		$expiryval,
		$views,
		$days,
		$number_of_devices,
		$devices_allowed,
		$allowed_os,
		$user_id,
		$email) = explode('#', $token);
	
	return [
		'token_sent'        => $token_sent,
		'keyval'            => $keyval,
		'url'               => $url,
		'plan'              => $plan,
		'expiry2'           => $expiry2,
		'expiryval'         => $expiryval,
		'views'             => $views,
		'days'              => $days,
		'number_of_devices' => $number_of_devices,
		'devices_allowed'   => $devices_allowed,
		'allowed_os'        => $allowed_os,
		'user_id'           => $user_id,
		'email'             => $email,
	];
}

function WPSGDRM_add_edit_token($params, $action = 'addtoken')
{
	$post = [
		'action'       => $action,
		'av_apikey'    => $params['av_apikey'],
		'tokenid'      => $params['tokenid'],
		'webpage'      => $params['webpage'],
		'plan'         => $params['plan'],
		'expiry'       => $params['expiry'],
		'devices'      => $params['devices'],
		'allowwindows' => $params['allowwindows'],
		'allowmac'     => $params['allowmac'],
		'allowandroid' => $params['allowandroid'],
		'allowios'     => $params['allowios'],
	];

	if( ! empty($params['email'])) {
		$post['email'] = $params['email'];
	}

	$url ='https://safeguard.media/drm/webplugin.asp?';
	$url .=
		'action='.$action.
		'&av_apikey='.$params['av_apikey'].
		'&tokenid='.$params['tokenid'].
		'&webpage='.$params['webpage'].
		'&plan='.$params['plan'].
		'&expiry='.$params['expiry'].
		'&devices='.$params['devices'].
		'&allowwindows='.$params['allowwindows'].
		'&allowmac='.$params['allowmac'].
		'&allowandroid='.$params['allowandroid'].
		'&allowios='.$params['allowios'];

	if( ! empty($params['email'])) {
		$url .= '&email=' . urlencode($params['email']);
	}

	$args = array(
		'body'        => $post,
		'timeout'     => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'cookies'     => array(),
	);
	
	$response = wp_remote_post( $url, $args );

	$parts = [];
	if( ! is_wp_error($response)) {
		$parts = explode('~',$response['body']);
	}

	return $parts;
}