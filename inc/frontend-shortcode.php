<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

function WPSGDRM_shortcode($atts)
{
	WPSGDRM_check_artis_browser_version();
	$browserinfo = WPSGDRM_get_browser_info();
	
	$computer_id = '';
	
	if(isset($_SERVER['HTTP_ARTISDRM'])) {
		$computer_id = WPSGDRM_encrypt_decrypt('encrypt', $_SERVER['HTTP_ARTISDRM']);
	}

	$atts["token"] .= '@' . $computer_id;

	$token = $atts["token"];

	$settings = WPSGDRM_get_first_class_settings();

	// get plugin options
	$wpsafeguarddrm_options = get_option('wpsgdrm_settings');
	if ($wpsafeguarddrm_options["settings"]) {
		$settings = wp_parse_args($wpsafeguarddrm_options["settings"], $settings);
	}

	$settings = wp_parse_args($atts, $settings);

	extract($settings);

	$asps = ($asps) ? '1' : '0';
	$firefox = ($ff) ? '1' : '0';
	$chrome = ($ch) ? '1' : '0';

	$plugin_url  = WPSGDRM_PLUGIN_URL;

	$av_allowwindows = "";
	$av_allowmac = "";
	$av_allowandroid = "";
	$av_allowios = "";
	$av_allowremote = ""; 

	$av_allowwindows = get_option('wpsgdrm_av_allowwindows');
	$av_allowmac = get_option('wpsgdrm_av_allowmac');
	$av_allowandroid = get_option('wpsgdrm_av_allowandroid');
	$av_allowios = get_option('wpsgdrm_av_allowios');
	$av_allowremote = get_option('wpsgdrm_av_allowremote');
	$av_Version_windows = get_option('wpsgdrm_av_Version_windows');
	$av_Version_mac = get_option('wpsgdrm_av_Version_mac');
	$av_Version_ios = get_option('wpsgdrm_av_Version_ios');
	$av_Version_android = get_option('wpsgdrm_av_Version_android');

	$browsername=$browserinfo['name'];
	$browserversion=$browserinfo['version'];
	$errormessage = esc_html( __( 'Device type not authorised by this website!', 'safeguard-drm' ));
	$rand = wp_rand(9999,99999);

	$script_tag = 'script';

	ob_start();
	?>
<div id="safeguarddrm-media-outer"></div>
<style>
#safeguarddrm media{
	width: 100%;
	position: absolute;
	height: 100%;
	left: 0;
	top: 0;
}
</style>
<script type="text/javascript">
	var browser = '<?php echo esc_js($browsername); ?>';
	var version = '<?php echo esc_js($browserversion); ?>';
	var m_token = '<?php echo esc_js($token); ?>';
	
	var av_allowwindows = "<?php echo esc_js($av_allowwindows); ?>";
	var av_allowmac = "<?php echo esc_js($av_allowmac); ?>";
	var av_allowandroid = "<?php echo esc_js($av_allowandroid); ?>";
	var av_allowios = "<?php echo esc_js($av_allowios); ?>";
	var av_allowremote = "<?php echo esc_js($av_allowremote); ?>";
	var av_Version_windows = "<?php echo esc_js($av_Version_windows); ?>";
	var av_Version_mac = "<?php echo esc_js($av_Version_mac); ?>";
	var av_Version_ios = "<?php echo esc_js($av_Version_ios); ?>";
	var av_Version_android = "<?php echo esc_js($av_Version_android); ?>";

	// for watermark end
	var m_bpDebugging = false;
	var m_bpASPS = "<?php echo esc_js($asps); ?>";
	var m_bpChrome = "<?php echo esc_js($chrome); ?>";
	var m_bpFx = "<?php echo esc_js($firefox); ?>"; // all firefox browsers from version 5 and later
	if (typeof m_szMode != 'undefined' && m_szMode == "debug") {
		m_bpDebugging = true;
	}
		
	var m_allowmac = "<?php echo esc_js($av_allowmac); ?>";
	var m_allowwindows = "<?php echo esc_js($av_allowwindows); ?>";
	var m_allowandroid = "<?php echo esc_js($av_allowandroid); ?>";
	var m_allowios = "<?php echo esc_js($av_allowios); ?>";
	var m_allowremote = "<?php echo esc_js($av_allowremote); ?>";
	var errormessage = "<?php echo esc_js($errormessage); ?>";
</script>
<<?php echo esc_html($script_tag); ?> src="<?php echo esc_url($plugin_url . 'js/wp-safeguarddrm.js?v=' . $rand); ?>" type="text/javascript"></<?php echo esc_html($script_tag); ?>>
<script type="text/javascript">insertSafeGuarddrm();</script>

<?php
	$output = ob_get_clean();

	if(current_user_can('edit_posts'))
	{
		$output = '<p>Please use a non-admin account for testing protected pages.</p>';
	}

	return $output;
}