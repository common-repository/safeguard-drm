<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

function WPSGDRM_help_icon($message)
{
	$help_icon =
		'<img src="' . esc_attr(WPSGDRM_PLUGIN_URL) . 'images/help-24-30.png" '.
			'alt="' . esc_attr($message) . '" border="0">';
	
	return $help_icon;
}

function WPSGDRM_upload_dir($upload) {
	$upload['subdir'] = '/safeguard-media';
	$upload['path'] = $upload['basedir'] . $upload['subdir'];
	$upload['url'] = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}

function WPSGDRM_get_upload_url()
{
	$upload_dir = wp_upload_dir();
	
	return $upload_dir['baseurl'] . '/safeguard-media/';
}

function WPSGDRM_get_upload_dir()
{
	$upload_dir = wp_upload_dir();
	
	return $upload_dir['basedir'] . '/safeguard-media/';
}

function WPSGDRM_sanitize_option($key, $option, $source)
{
	$default = isset($option['default']) ? $option['default'] : '';

	$option_value = isset($source[$key]) ? $source[$key] : $default;

	if( ! empty($option['type']))
	{
		if($option['type'] == '1_0') {
			$option_value = $option_value == '1' ? '1' : '0';
		} else if($option['type'] == 'checked') {
			$option_value = $option_value ? 'checked' : '';
		} else if($option['type'] == 'int') {
			$option_value = (int)$option_value;
		} else if($option['type'] == 'float') {
			$option_value = (float)$option_value;
		} else if($option['type'] == 'hex_color') {
			$option_value = str_replace('#','', sanitize_text_field($option_value));
		} else {
			$option_value = sanitize_text_field($option_value);
		}
	}
	else
	{
		$option_value = sanitize_text_field($option_value);
	}

	return $option_value;
}

function WPSGDRM_get_first_class_settings() {

	$settings = [
		'width' => '600',
		'height' => '600',
		//'prints_allowed' => 0,
		//'print_anywhere' => 0,
		//'allow_capture' => 0,
		'remote' => 0,
		//'background' => 'CCCCCC',
	];

	return $settings;
}

function WPSGDRM_encrypt_decrypt($action, $string) {
	$output = false;

	$encrypt_method = "AES-256-CBC";
	$av_apikey  = get_option('wpsgdrm_av_apikeydrm');
	$secret_key = $av_apikey;
	$secret_iv = 'This is my secret iv';

	// hash
	$key = hash('sha256', $secret_key);

	// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	$iv = substr(hash('sha256', $secret_iv), 0, 16);

	if ( $action == 'encrypt' ) {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
	} else if( $action == 'decrypt' ) {
		$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	}

	return $output;
}

function WPSGDRM_get_browser_info()
{
	$u_agent    = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] );
	$bname      = 'Unknown';
	$platform   = 'Unknown';
	$version    = "";
	$iospattern = '^(?:(?:(?:Mozilla/\d\.\d\s*\()+|Mobile\s*Safari\s*\d+\.\d+(\.\d+)?\s*)(?:iPhone(?:\s+Simulator)?|iPad|iPod);\s*(?:U;\s*)?(?:[a-z]+(?:-[a-z]+)?;\s*)?CPU\s*(?:iPhone\s*)?(?:OS\s*\d+_\d+(?:_\d+)?\s*)?(?:like|comme)\s*Mac\s*O?S?\s*X(?:;\s*[a-z]+(?:-[a-z]+)?)?\)\s*)?(?:AppleWebKit/\d+(?:\.\d+(?:\.\d+)?|\s*\+)?\s*)?(?:\(KHTML,\s*(?:like|comme)\s*Gecko\s*\)\s*)?(?:Version/\d+\.\d+(?:\.\d+)?\s*)?(?:Mobile/\w+\s*)?(?:Safari/\d+\.\d+(\.\d+)?.*)?$';

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	}
	else if (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	}
	else if (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}
	else if (preg_match('/android/i',$u_agent)) { 
		$platform = 'android';
	}
	else if (preg_match($iospattern,$u_agent)) { 
		$platform = 'ios';
	}
	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/Firefox/i',$u_agent) && !preg_match('/ArtisReader/i',$u_agent)){
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	elseif(preg_match('/Firefox/i',$u_agent) && preg_match('/ArtisReader/i',$u_agent)){
		$bname = 'ArtisBrowser';
		$ub = "ArtisBrowser";
	}
	else if(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}

	// finally get the correct version number
	$known = array('Version', @$ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}
	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($u_agent,"Version") < strripos($u_agent,@$ub)){
		$version= $matches['version'][0];
		}
		else {
		$version = $matches['version'][1];
		}
	}
	else {
		$version = $matches['version'][0];
	}

	// check if we have a number
	if( $version == null || $version == "" ){ 
		$version = "?";
	}

	return array(
		'userAgent' => $u_agent,
		'name'      => $ub,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'   => $pattern
	);
}

function WPSGDRM_check_artis_browser_version()
{
	$wpsafeguard_current_browser = WPSGDRM_get_browser_info();
	$browsername    = $wpsafeguard_current_browser['name'];
	$browserversion = $wpsafeguard_current_browser['version'];
	$platform       = $wpsafeguard_current_browser['platform'];

	// print_r($wpsafeguard_current_browser);

	$wpsafeguard_current_browser_data = $wpsafeguard_current_browser['userAgent'];

	if( $wpsafeguard_current_browser_data != "" && ! current_user_can('edit_posts'))
	{
		$wpsafeguard_browser_data = explode("/", $wpsafeguard_current_browser_data);

		if (strpos($browsername, 'ArtisBrowser') !== false) {

			$current_version = end($wpsafeguard_browser_data);
			
			$av_allowwindows = get_option('wpsgdrm_av_allowwindows');
			$av_allowmac = get_option('wpsgdrm_av_allowmac');
			$av_allowandroid = get_option('wpsgdrm_av_allowandroid');
			$av_allowios = get_option('wpsgdrm_av_allowios');
			$av_allowremote = get_option('wpsgdrm_av_allowremote');
			$av_Version_windows = get_option('wpsgdrm_av_Version_windows');
			$av_Version_mac = get_option('wpsgdrm_av_Version_mac');
			$av_Version_ios = get_option('wpsgdrm_av_Version_ios');
			$av_Version_android = get_option('wpsgdrm_av_Version_android');
			if($platform=='windows') $minimum_version=$av_Version_windows;
			if($platform=='mac') $minimum_version=$av_Version_mac;
			if($platform=='ios') $minimum_version=$av_Version_ios;
			if($platform=='android') $minimum_version=$av_Version_android;
			
			if( $current_version < $minimum_version )
			{
				$ref_url = get_permalink(get_the_ID());
				?>
				<script>
				document.location = '<?php echo esc_js(WPSGDRM_PLUGIN_URL."download-update.html?ref=".urlencode($ref_url)); ?>';
				</script>
				<?php
				exit;
			}
		}
		else
		{
			$ref_url = get_permalink(get_the_ID());
			?>
			<script>
				document.location = '<?php echo esc_js(WPSGDRM_PLUGIN_URL."download.html?ref=".urlencode($ref_url)); ?>';
			</script>
			<?php
			exit;
		}
	}
}

function WPSGDRM_send_email($token, $email, $url = '')
{
	$from_email = get_bloginfo('admin_email');
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	$headers .= "From:".get_bloginfo('name')." <".$from_email. ">\r\n";

	$subject = 'New DRM Token';

	$mail_message = "ArtisBrowser is required to claim your token. If you don't already have ArtisBrowser installed, you can download it from ";
	$mail_message .= "<a href='https://artisbrowser.com/artisbrowser-download.asp'>artisbrowser.com</a>. <br><br>";
	$mail_message .= "When ArtisBrowser is installed, click to <a href='https://safeguard.media/c/?id=" . $token . "'>Claim Your Token</a>. "; 
	$mail_message .= "Or you can claim your token by copying this link into the ArtisBrowser address bar:<br>";
	$mail_message .= "<p style = 'word-break: normal; border-collapse : collapse !important;'>https://safeguard.media/drm/token.asp?id=".$token."</p>";

	if($url)
	{
		$mail_message .= "After claiming your token, you can then use this link in ArtisBrowser: <br />"; 
		$mail_message .= $url . "<br /><br />";
		$mail_message .= "When you arrive at that page, bookmark it for future use.";
	}
	
	$mail_message .= "<br><br>Thanks<br><br>".get_bloginfo('name');
	$mail_message .= "</body></html>";
	
	wp_mail($email, $subject, $mail_message, $headers);
}

function WPSGDRM_cstmjsonfrontend()
{
?>
<style>
	#media-controls_outer{ display:none; }
	#media-container #media-controls_outer {
		display: none;
	}
	#media-container:hover #media-controls_outer {
		display: block;
	}
</style>
<?php
}