<?php if ( ! defined('ABSPATH')) exit;

function WPSGDRM_admin_page_settings()
{
	$msg              = '';
	$option_structure = WPSGDRM_option_structure();

	$wpsgdrm_wpnonce = isset($_POST['wpsgdrm_wpnonce']) ? sanitize_text_field($_POST['wpsgdrm_wpnonce']) : '';

	if( ! empty($_POST) && wp_verify_nonce($wpsgdrm_wpnonce, 'wpsgdrm_settings'))
	{
		if(isset($_POST['action_two']) && sanitize_text_field($_POST['action_two'])=='newdemo')
		{
			$firstname = (isset($_POST['fm_firstname'])) ? sanitize_text_field($_POST['fm_firstname']) : "";
			$lastname  = (isset($_POST['fm_lastname'])) ? sanitize_text_field($_POST['fm_lastname']) : "";
			$email     = (isset($_POST['fm_email'])) ? sanitize_email($_POST['fm_email']) : "";
			$company   = (isset($_POST['fm_company'])) ? sanitize_text_field($_POST['fm_company']) : "";
			$domain    = (isset($_POST['fm_domain'])) ? sanitize_text_field($_POST['fm_domain']) : "";

			update_option('wpsgdrm_fm_firstname', $firstname);
			update_option('wpsgdrm_fm_lastname', $lastname);
			update_option('wpsgdrm_fm_email', $email);
			update_option('wpsgdrm_fm_company', $company);
			update_option('wpsgdrm_fm_domain', $domain);
			
			$parts = WPSGDRM_new_demo([
				'firstname' => $firstname,
				'lastname'  => $lastname,
				'email'     => $email,
				'company'   => $company,
				'domain'    => $domain,
			]);

			if($parts[0]=='success' && isset($parts[1]))
			{
				$av_apikey = $parts[1];
				
				if($av_apikey != 'exists')
				{
					update_option('wpsgdrm_av_apikeydrm', $av_apikey);
				}
			}
		}
		elseif(isset($_POST['action_three']) && sanitize_text_field($_POST['action_three']) == 'editsettings')
		{
			/***********************
				* Get posted data
				**********************/
			$option_data = [];
			
			foreach($option_structure as $option_key => $option)
			{
				$option_data[$option_key] = WPSGDRM_sanitize_option($option_key, $option, $_POST);
			}

			foreach($option_data as $option_key => $option_value)
			{
				update_option($option_key, $option_value);
			}

			$firstname = (isset($_POST['fm_firstname'])) ? sanitize_text_field($_POST['fm_firstname']) : "";
			$lastname  = (isset($_POST['fm_lastname'])) ? sanitize_text_field($_POST['fm_lastname']) : "";
			$email     = (isset($_POST['fm_email'])) ? sanitize_email($_POST['fm_email']) : "";
			$company   = (isset($_POST['fm_company'])) ? sanitize_text_field($_POST['fm_company']) : "";
			$domain    = (isset($_POST['fm_domain'])) ? sanitize_text_field($_POST['fm_domain']) : "";

			update_option('wpsgdrm_fm_firstname', $firstname);
			update_option('wpsgdrm_fm_lastname', $lastname);
			update_option('wpsgdrm_fm_email', $email);
			update_option('wpsgdrm_fm_company', $company);
			update_option('wpsgdrm_fm_domain', $domain);

			WPSGDRM_update_settings([
				'av_apikey'          => $option_data['wpsgdrm_av_apikeydrm'],
				'fm_firstname'       => $firstname,
				'fm_lastname'        => $lastname,
				'fm_email'           => $email,
				'fm_company'         => $company,
				'fm_domain'          => $domain,
				'av_allowwindows'    => $option_data['wpsgdrm_av_allowwindows'],
				'av_allowmac'        => $option_data['wpsgdrm_av_allowmac'],
				'av_allowandroid'    => $option_data['wpsgdrm_av_allowandroid'],
				'av_allowios'        => $option_data['wpsgdrm_av_allowios'],
				'av_Version_windows' => $option_data['wpsgdrm_av_Version_windows'],
				'av_Version_mac'     => $option_data['wpsgdrm_av_Version_mac'],
				'av_Version_android' => $option_data['wpsgdrm_av_Version_android'],
				'av_Version_ios'     => $option_data['wpsgdrm_av_Version_ios'],
			]);
		}
		else
		{
			$av_apikey = isset($_POST['wpsgdrm_av_apikeydrm']) ? sanitize_text_field($_POST['wpsgdrm_av_apikeydrm']) : '';

			update_option('wpsgdrm_av_apikeydrm', $av_apikey);
		}
	}

	$av_apikey   = get_option('wpsgdrm_av_apikeydrm');
	$validapikey = false;
	
	if( ! empty($av_apikey))
	{
		$client_id = WPSGDRM_get_client_id();

		if($client_id)
		{
			$validapikey = true;
		}
	}

	$fm_firstname       = get_option('wpsgdrm_fm_firstname');
	$fm_lastname        = get_option('wpsgdrm_fm_lastname');
	$fm_company         = get_option('wpsgdrm_fm_company');
	$fm_domain          = get_option('wpsgdrm_fm_domain');
	$fm_email           = get_option('wpsgdrm_fm_email');

	$av_allowwindows    = get_option('wpsgdrm_av_allowwindows');
	$av_allowmac        = get_option('wpsgdrm_av_allowmac');
	$av_allowandroid    = get_option('wpsgdrm_av_allowandroid');
	$av_allowios        = get_option('wpsgdrm_av_allowios');
	$av_allowremote     = get_option('wpsgdrm_av_allowremote');
	$av_Version_windows = get_option('wpsgdrm_av_Version_windows');
	$av_Version_mac     = get_option('wpsgdrm_av_Version_mac');
	$av_Version_android = get_option('wpsgdrm_av_Version_android');
	$av_Version_ios     = get_option('wpsgdrm_av_Version_ios');
?>
<style type="text/css">
	.wpsafeguarddrm_page_setting img {
		cursor: pointer;
	}
</style>
<div class="wrap">
	<div class="icon32" id="icon-settings"><br/></div>

	<?php echo wp_kses($msg, WPSGDRM_kses_allowed_options()); ?>
	
	<?php if( ! $validapikey) { ?>
	<!--start sign up form-->
	<div>
		<form action="" method="post" id="Register" name="Register_account">
			<?php echo wp_kses(wp_nonce_field('wpsgdrm_settings', 'wpsgdrm_wpnonce'), WPSGDRM_kses_allowed_options()); ?>
			<input type="hidden" name="action_two" value="newdemo" >

			<h2><?php echo esc_html( __( 'Register Demo Account', 'safeguard-drm' )); ?></h2>
			
			<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguarddrm_page_setting'>
				<tbody>
					<tr style="display:none;">
						<td width="50" align="left">&nbsp;</td>
						<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Register demo account', 'safeguard-drm' ))); ?></td>
						<td align="left"><?php echo esc_html( __( 'Action', 'safeguard-drm' )); ?>:</td>
						<td align="left"> <?php echo esc_html( __( 'Register demo account', 'safeguard-drm' )); ?></td>
					</tr>
					<tr>
						<td width="50" align="left">&nbsp;</td>
						<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your First Name', 'safeguard-drm' ))); ?></td>
						<td align="left"><?php echo esc_html( __( 'First Name:', 'safeguard-drm') ); ?></td>
						<td align="left">
							<input type="text" name="fm_firstname" value="<?php echo esc_attr($fm_firstname); ?>" style="width:300px;" />
						</td>
					</tr>
					<tr>
						<td width="50" align="left">&nbsp;</td>
						<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your Surname Name', 'safeguard-drm' ))); ?></td>
						<td align="left"><?php echo esc_html( __( 'Last Name:', 'safeguard-drm' )); ?></td>
						<td align="left">
							<input type="text" name="fm_lastname" value="<?php echo esc_attr($fm_lastname); ?>" style="width:300px;" />
						</td>
					</tr>
					<tr>
						<td width="50" align="left">&nbsp;</td>
						<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your Email Address', 'safeguard-drm' ))); ?></td>
						<td align="left"><?php echo esc_html( __( 'Email:', 'safeguard-drm' )); ?></td>
						<td align="left">
							<input type="text" name="fm_email" value="<?php echo esc_attr($fm_email); ?>" style="width:300px;" />
						</td>
					</tr>
					<tr>
						<td width="50" align="left">&nbsp;</td>
						<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your Company Name', 'safeguard-drm' ))); ?></td>
						<td align="left"><?php echo esc_html( __( 'Company:', 'safeguard-drm')); ?></td>
						<td align="left">
							<input type="text" name="fm_company" value="<?php echo esc_attr($fm_company); ?>" style="width:300px;" />
						</td>
					</tr>
					<tr>
						<td width="50" align="left">&nbsp;</td>
						<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your Website Url', 'safeguard-drm' ))); ?></td>
						<td align="left"><?php echo esc_html( __( 'Domain:', 'safeguard-drm' )); ?></td>
						<td align="left">
							<input type="text" name="fm_domain" value="<?php echo esc_attr($fm_domain); ?>" style="width:300px;" />
						</td>
					</tr>
					<tr>
					<td width="50" align="left">&nbsp;</td>
					<td width="30" align="left"><input type="submit" class="button-primary" value="<?php echo esc_attr( __( 'Submit', 'safeguard-drm' )); ?>"/></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<!--end sing up form-->
	<?php } ?>
	
	<h2><?php echo esc_html( __( 'Default Settings', 'safeguard-drm' )); ?></h2>
	<p><u>NOTE</u>: When claiming or testing DRM tokens it is strongly recommended to use a different computer to avoid exemption as Admin.</p>
	<?php if( ! $validapikey) { ?>
	<form action="" method="post">
		<?php echo wp_kses(wp_nonce_field('wpsgdrm_settings', 'wpsgdrm_wpnonce'), WPSGDRM_kses_allowed_options()); ?>
		<input type="hidden" name="action_three" value="getclient" >

		<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguarddrm_page_setting'>
			<p><strong><?php echo esc_html( __( 'Default settings applied to DRM:', 'safeguard-drm' )); ?></strong></p>
			<tbody>
			<tr>
				<td align='left' width='50'>&nbsp;</td>
				<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'DRM API Key', 'safeguard-drm' ))); ?></td>
				<td align="left"><?php echo esc_html( __( 'DRM API Key:', 'safeguard-drm' )); ?></td>
				<td align="left">
					<input type="text" name="wpsgdrm_av_apikeydrm" value="<?php echo esc_attr($av_apikey); ?>" size="45" style="width:300px;">
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" value="<?php echo esc_attr( __( 'Save Settings', 'safeguard-drm' )); ?>"
				class="button-primary" id="submit" name="submit">
		</p>
	</form>
	<?php } ?>
	
	<?php if($av_apikey && $validapikey) { ?>
	<form action="" method="post">
		<?php echo wp_kses(wp_nonce_field('wpsgdrm_settings', 'wpsgdrm_wpnonce'), WPSGDRM_kses_allowed_options()); ?>
		<input type="hidden" name="action_four" value="getclient" >
		<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguarddrm_page_setting'>
			<p><strong><?php echo esc_html( __( 'Default settings applied to DRM:', 'safeguard-drm' )); ?></strong></p>
			<tbody>
				<tr style="display:none;">
					<td width="50" align="left">&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Edit account', 'safeguard-drm' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Action', 'safeguard-drm' )); ?>:</td>
					<td align="left"> <?php echo esc_html( __( 'Edit account', 'safeguard-drm' )); ?>
						<input type="hidden" name="action_three" value="editsettings" >
					</td>
				</tr>
				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'DRM API Key', 'safeguard-drm' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'DRM API Key:', 'safeguard-drm' )); ?>&nbsp;&nbsp;</td>
					<td align="left">
						<?php echo esc_html($av_apikey); ?>
						<input type="hidden" name="wpsgdrm_av_apikeydrm" value="<?php echo esc_attr($av_apikey); ?>" size="45">
					</td>
				</tr>
				<tr>
					<td width="50" align="left">&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your First Name', 'safeguard-drm' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'First Name:', 'safeguard-drm') ); ?></td>
					<td align="left">
						<input type="text" name="fm_firstname" value="<?php echo esc_attr($fm_firstname); ?>" style="width:200px;" />
					</td>
				</tr>
				<tr>
					<td width="50" align="left">&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your Surname Name', 'safeguard-drm' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Last Name:', 'safeguard-drm' )); ?></td>
					<td align="left">
					<input type="text" name="fm_lastname" value="<?php echo esc_attr($fm_lastname); ?>" style="width:200px;" />
					</td>
				</tr>
				<tr>
					<td width="50" align="left">&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Email', 'safeguard-drm' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Email:', 'safeguard-drm' )); ?></td>
					<td align="left">
					<input type="text" name="fm_email" value="<?php echo esc_attr($fm_email); ?>" style="width:300px;" />
					</td>
				</tr>
				<tr>
					<td width="50" align="left">&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your Company Name', 'safeguard-drm' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Company:', 'safeguard-drm')); ?></td>
					<td align="left">
						<input type="text" name="fm_company" value="<?php echo esc_attr($fm_company); ?>" style="width:300px;" />
					</td>
				</tr>
				<tr>
					<td width="50" align="left">&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Your Website Url', 'safeguard-drm' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Domain:', 'safeguard-drm' )); ?></td>
					<td align="left">
					<input type="text" name="fm_domain" value="<?php echo esc_attr($fm_domain); ?>" style="width:300px;" />
					</td>
				</tr>
			</tbody>
		</table>
		
		<hr>
		<label ><b>ArtisBrowser Versions to Allow:</b></label>
		<table border="0" cellspacing="0" cellpadding="1" class='wpsafeguarddrm_page_setting'>
			<tr>
				<td align='left' width='50'>&nbsp;</td>
				<td><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'To allow access to Windows computers or not.', 'safeguard-drm' ))); ?></td>
				<td><?php esc_html_e( 'Allow Windows:&nbsp;', 'safeguard-drm' ); ?></td>
				<td><input type="checkbox" name="wpsgdrm_av_allowwindows"  value="1" <?php echo $av_allowwindows ? "checked" : ''; ?> ></td>
				<td><?php
					if(empty($av_Version_windows))
					{
						$av_Version_windows = "34.11";
					}
					?>
					<input type="text" name="wpsgdrm_av_Version_windows" value="<?php echo esc_attr($av_Version_windows); ?>" style="width:80px;"/>
				</td>
				<td><?php esc_html_e( '&nbsp;Min.Version', 'safeguard-drm' ); ?></td>
			</tr>
			<tr>
				<td align='left' width='50'>&nbsp;</td>
				<td><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'To allow access to Mac OSX computers or not.', 'safeguard-drm' ))); ?></td>
				<td><?php esc_html_e( 'Allow Mac OSX:&nbsp;', 'safeguard-drm' ); ?></td>
				<td><input type="checkbox" name="wpsgdrm_av_allowmac"  value="1" <?php if($av_allowmac=="1"){echo "checked";}?> ></td>
				<td><?php
					if(empty($av_Version_mac))
					{
						$av_Version_mac = "32.1";
					}
					?>
					<input type="text" name="wpsgdrm_av_Version_mac" value="<?php echo esc_attr($av_Version_mac); ?>" style="width:80px;"/>
				</td>
				<td><?php esc_html_e( '&nbsp;Min.Version', 'safeguard-drm' ); ?></td>
			</tr>
			<tr>
				<td align='left' width='50'>&nbsp;</td>
				<td><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'To allow access to Android devices or not.', 'safeguard-drm' ))); ?></td>
				<td><?php esc_attr_e( 'Allow Android:', 'safeguard-drm' ); ?></td>
				<td><input type="checkbox" name="wpsgdrm_av_allowandroid"  value="1" <?php echo $av_allowandroid ? "checked" :''; ?> ></td>
				<td><?php
					if(empty($av_Version_android))
					{
						$av_Version_android = "34.0";
					}
					?>
					<input type="text" name="wpsgdrm_av_Version_android" value="<?php echo esc_attr($av_Version_android); ?>" style="width:80px;"/>
				</td>
				<td><?php esc_html_e( '&nbsp;Min.Version', 'safeguard-drm' ); ?></td>
			</tr>
			<tr>
				<td align='left' width='50'>&nbsp;</td>
				<td><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'To allow access to iOS (iPad/iPhone) devices or not.', 'safeguard-drm' ))); ?></td>
				<td><?php esc_html_e( 'Allow IOS:', 'safeguard-drm' ); ?></td>
				<td><input type="checkbox" name="wpsgdrm_av_allowios"  value="1" <?php echo $av_allowios ? "checked" : ''; ?>></td>
				<td><?php
					if(empty($av_Version_ios))
					{
						$av_Version_ios = "34.0";
					}
					?>
					<input type="text" name="wpsgdrm_av_Version_ios" value="<?php echo esc_attr($av_Version_ios); ?>" style="width:80px;"/>
				</td>
				<td><?php esc_html_e( '&nbsp;Min.Version', 'safeguard-drm' ); ?></td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" value="<?php esc_attr_e( 'Save Settings', 'safeguard-drm' ); ?>"
				class="button-primary" id="submit" name="submit">
		</p>
	</form>
	<?php } ?>

	<div class="clear"></div>
</div>

<div class="clear"></div>
<script type='text/javascript'>
jQuery(document).ready(function ($) {
	$('.wpsafeguarddrm_page_setting img').click(function () {
		alert($(this).attr("alt"));
	});
});
</script>
<?php
}