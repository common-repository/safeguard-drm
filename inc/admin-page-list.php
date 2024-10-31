<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

function WPSGDRM_admin_page_list()
{
	$msg      = '';
	$table    = '';
	$hideform = false;

	$plan         = '';
	$expiry       = '';
	$devices      = '';
	$allowwindows = null;
	$allowmac     = null;
	$allowandroid = null;
	$allowios     = null;

	if( ! empty($_POST) && wp_verify_nonce(isset($_POST['wpsgdrm_wpnonce']) ? $_POST['wpsgdrm_wpnonce'] : '', 'wpsgdrm_settings'))
	{
		$post_action = isset($_POST["action"]) ? (string)$_POST["action"] : "";
		$av_apikey   = get_option('wpsgdrm_av_apikeydrm');

		if($post_action == 'emailtoken')
		{
			try {
				$token       = isset($_POST['tokenid']) ? sanitize_text_field($_POST['tokenid']) : "";
				$token_parts = explode('@', $token);
				$unencrypted = 'wpsgdrm_' . $token;
				$token       = WPSGDRM_encrypt_decrypt('encrypt', $token);
				$email       = '';
				$url         = isset($token_parts[1]) ? $token_parts[1] : '';
				
				if( ! empty( $_POST['user_email'] ) )
				{
					$email = sanitize_email($_POST['user_email']);
				}
				else if( ! empty($_POST['email']))
				{
					$email = sanitize_email($_POST['email']);
				}

				if($av_apikey)
				{
					$client_id = WPSGDRM_get_client_id();
					$token = $client_id.'@'.$token;
				}

				if( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
					throw new Exception(__('Invalid email address.', 'safeguard-drm'));
				}
				
				WPSGDRM_send_email($token, $email, $url);
				update_option($unencrypted, $email);
				
				$msg = '<div class="updated"><p><strong>' . esc_html(__( 'Email sent successfully', 'safeguard-drm' )) . '</strong></p></div>';
				$hideform = true;
			} catch(Exception $e) {
				$msg = '<div class="error"><p><strong>' . esc_html($e->getMessage()) . '</strong></p></div>';
			}
		}

		if(isset($_POST["av_apikey"]) && isset($_POST["action"]) && $_POST['action'] != 'emailtoken')
		{
			$av_apikey = isset($_POST['av_apikey']) ? sanitize_text_field($_POST['av_apikey']) : "";
			
			if( ! empty($av_apikey))
			{
				$client_id = WPSGDRM_get_client_id();
				
				if($client_id)
				{
					$tokenid      = isset($_POST['tokenid']) ? sanitize_text_field($_POST['tokenid']) : "";
					$webpage      = isset($_POST['webpage']) ? sanitize_url($_POST['webpage']) : "";
					$plan         = isset($_POST['plan']) ? sanitize_text_field($_POST['plan']) : "";
					$expiry       = isset($_POST['expiry']) ? sanitize_text_field($_POST['expiry']) : "";
					$devices      = isset($_POST['devices']) ? sanitize_text_field($_POST['devices']) : "";
					$allowwindows = isset($_POST['allowwindows']) ? sanitize_text_field($_POST['allowwindows']) : "";
					$allowmac     = isset($_POST['allowmac']) ? sanitize_text_field($_POST['allowmac']) : "";
					$allowandroid = isset($_POST['allowandroid']) ? sanitize_text_field($_POST['allowandroid']) : "";
					$allowios     = isset($_POST['allowios']) ? sanitize_text_field($_POST['allowios']) : "";
					$page_id      = isset($_POST['page_id']) ? sanitize_text_field($_POST['page_id']) : "";
					$email       = '';
				
					if( ! empty( $_POST['user_email'] ) )
					{
						$email = sanitize_email($_POST['user_email']);
					}
					else if( ! empty($_POST['email']))
					{
						$email = sanitize_email($_POST['email']);
					}

					if( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$email = '';
					}
					
					$expiry = str_replace('-','',$expiry);
					
					$webpagesel='';
					
					if((int)$page_id > 0) {
						$webpagesel = get_permalink($page_id);
					}
					if($webpage!='') {
						$webpagesel = $webpage;
					}
					
					$action = 'addtoken';
					if (isset($_POST["action"]) && sanitize_text_field($_POST['action']) == 'edittoken')
					{
						$action = 'edittoken';
					}

					$parts = WPSGDRM_add_edit_token([
							'av_apikey'    => $av_apikey,
							'tokenid'      => $tokenid,
							'webpage'      => $webpagesel,
							'plan'         => $plan,
							'expiry'       => $expiry,
							'devices'      => $devices,
							'allowwindows' => $allowwindows,
							'allowmac'     => $allowmac,
							'allowandroid' => $allowandroid,
							'allowios'     => $allowios,
							'email'        => $email,
						],
						$action
					);

					if($parts[0]=='success')
					{
						/**************************************************
							* Lets send the token immediately if email exists
							**************************************************/
						if($email)
						{
							$file_list = WPSGDRM_fetch_tokens();

							foreach($file_list as $row)
							{
								if($row['keyval'] == $parts[1])
								{
									$token       = $row['keyval'] . '@' . $row['url'];
									$unencrypted = 'wpsgdrm_' . $token;

									$token = WPSGDRM_encrypt_decrypt('encrypt', $token);
									$token = $client_id.'@'.$token;

									WPSGDRM_send_email($token, $email, $row['url']);
									update_option($unencrypted, $email);

									break;
								}
							}
						}

						$success_message = sprintf(
							/* translators: %1s: Token ID */
							__( 'Token: %1$s added successfully', 'safeguard-drm' ),
							$tokenid
						);

						if($action == 'edittoken')
						{
							$success_message = sprintf(
								/* translators: %1s: Token ID */
								__( 'Token: %1$s updated successfully', 'safeguard-drm' ),
								$tokenid
							);
						}
						
						$msg = '<div class="error"><p><strong>' . esc_html($success_message) . '</strong></p></div>';

						$uploadOk = true;
					}
				}
				else
				{
					$error_message =__( 'Sorry, Invalid client id', 'safeguard-drm' );
					$msg = '<div class="error"><p><strong>' . esc_html($error_message) . '</strong></p></div>';
				}
			}
			else
			{
				$error_message =__( 'Sorry, Invalid Key', 'safeguard-drm' );
				$msg = '<div class="error"><p><strong>' . esc_html($error_message) . '</strong></p></div>';

				$uploadOk = false;
			}
		}
	}

	$av_apikey  = get_option('wpsgdrm_av_apikeydrm');

	if($av_apikey)
	{
		if( ! isset($file_list)) {
			$file_list = WPSGDRM_fetch_tokens();
		}

		if(count($file_list) > 0)
		{
			$i = count($file_list);
			foreach ($file_list as $token)
			{
				$key = $token['keyval'].'@'.$token['url'];
				
				$email_action   = '';
				$email_assigned = get_option('wpsgdrm_' . $key);

				if(empty($email_assigned))
				{
					$email_assigned = $token['email'];
				}
				
				if($email_assigned == '')
				{
					$url = admin_url('admin.php?page=wpsgdrm_list&view=emailtoken&token='. urlencode($token['token_name']));

					$email_action = "<a href='" . esc_url($url) . "' class='button'>Email</a>";
				}
				else {
					$email_action =
						'<div><button type="button" class="button button-primary btn-resend" data-email="'.esc_attr($email_assigned).'" data-token="'.esc_attr($token['keyval'] . '@' . $token['url']).'">Resend</button></div>';
				}

				$url = admin_url('admin.php?page=wpsgdrm_list&token=' . urlencode($token['token_name']));

				$html_settings =
					'<div class="tooltip">' .
						$token['token_sent'] .
						'<div class="tooltip-text">
							<div class="row-info">
								<div class="row-info__label">' . esc_html(__('Allow Windows', 'safeguard-drm')) . ':</div>
								<div class="row-info__value">' . (strpos($token['devices_allowed'], 'W') !== false ? 'Yes' : 'No') . '</div>
							</div>
							<div class="row-info">
								<div class="row-info__label">' . esc_html(__('Allow Mac', 'safeguard-drm')) . ':</div>
								<div class="row-info__value">' . (strpos($token['devices_allowed'], 'M') !== false ? 'Yes' : 'No') . '</div>
							</div>
							<div class="row-info">
								<div class="row-info__label">' . esc_html(__('Allow Android', 'safeguard-drm')) . ':</div>
								<div class="row-info__value">' . (strpos($token['devices_allowed'], 'A') !== false ? 'Yes' : 'No') . '</div>
							</div>
							<div class="row-info">
								<div class="row-info__label">' . esc_html(__('Allow IOS', 'safeguard-drm')) . ':</div>
								<div class="row-info__value">' . (strpos($token['devices_allowed'], 'I') !== false ? 'Yes' : 'No') . '</div>
							</div>
							<div class="row-info">
								<div class="row-info__label">' . esc_html(__('Devices', 'safeguard-drm')) . ':</div>
								<div class="row-info__value">' . (strpos($token['number_of_devices'], 'I') !== false ? 'Yes' : 'No') . '</div>
							</div>
						</div>
					</div>';

				$table .=
					"<tr>
						<td>{$i}</td>
						<td>" . wp_kses($html_settings, WPSGDRM_kses_allowed_options()) . "</td>
						<td>" . esc_html($token['keyval']) . "</td>
						<td>" . esc_html($token['url'])  . "</td>
						<td>" . wp_kses($email_assigned, WPSGDRM_kses_allowed_options()) . " </td>
						<td>" . esc_html($token['user_id'] ? __('Yes', 'safeguard-drm') : __('No', 'safeguard-drm')) . "</td>
						<td>" . wp_kses($email_action, WPSGDRM_kses_allowed_options()) . "</td>
					</tr>";
				$i--;
			}
		}
	}
?>
	<div class="wrap">
		<div class="icon32" id="icon-file"><br/></div>

		<div id="wpsgdrm-notifications">
			<?php echo wp_kses($msg, WPSGDRM_kses_allowed_options()); ?>
		</div>
	
		<?php
		if($av_apikey)
		{
			?>
			<br>
			<?php
			if(isset($_REQUEST['view']) && sanitize_text_field($_REQUEST['view'])=='edittoken')
			{
				$token      = sanitize_text_field($_REQUEST['token']);
				$token_data = WPSGDRM_token_to_array($token);
			?>
			<form action="" method="post" id="edittoken" name="edittoken">
				<?php echo wp_kses(wp_nonce_field('wpsgdrm_settings', 'wpsgdrm_wpnonce'), WPSGDRM_kses_allowed_options()); ?>
				<input type="hidden" name="action"value="edittoken" >

				<h2><?php echo esc_html(__( 'Edit Token', 'safeguard-drm' )); ?></h2>
				<br>
				<a href="<?php echo esc_url(admin_url('admin.php?page=wpsgdrm_list')); ?>" class="button"><?php echo esc_html(__( '&lt;&lt; Add New Token', 'safeguard-drm' )); ?></a>
				<hr />
		
				<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguarddrm_page_setting'>
					<tbody>
						<tr style="display:none;">
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Edit demo key', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'ApiKey', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<input type="text" name="av_apikey" value="<?php echo esc_attr($av_apikey); ?>" />
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Token ID Autogenerated', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'TokenID', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<input type="text" name="tokenid" value="<?php echo esc_attr($token_data['keyval']); ?>" />
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'WebPage Url', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'WebPage Url', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<?php wp_dropdown_pages(array('option_none_value'=>'')); ?> OR<br>
								<input type="text" style="width: 250px;" name="webpage" value="<?php echo esc_attr($token_data['url']); ?>" />
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow Type', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Allow Type', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<select name="plan" size="1"
									onchange="if(this.value=='expiry') {document.getElementById('expiry').type='date';} else {document.getElementById('expiry').type='text';}">
									<option value="noexpiry" <?php if($plan=='noexpiry') echo 'selected'; ?>><?php echo esc_html(__( 'No expiry', 'safeguard-drm' )); ?></option>
									<option value="expiry" <?php if($plan=='expiry') echo 'selected'; ?> ><?php echo esc_html(__( 'Expiry date', 'safeguard-drm' )); ?></option>
									<option value="days" <?php if($plan=='days') echo 'selected'; ?>><?php echo esc_html(__( 'Expire by days', 'safeguard-drm' )); ?></option>
									<option value="views" <?php if($plan=='views') echo 'selected'; ?>><?php echo esc_html(__( 'Expiry by views', 'safeguard-drm' )); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Expiry', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Expiry', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<input type="text" style="width: 150px;" id="expiry" name="expiry" value="<?php echo esc_attr($token_data['expiryval']); ?>" >
								<i><?php echo esc_html(__( 'YYYYMMDD or like 10', 'safeguard-drm' )); ?></i>
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Devices', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Devices', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<input type="text" style="width: 100px;" id="devices" name="devices" value="<?php echo esc_attr($token_data['number_of_devices']); ?>">
								<i><?php echo esc_html(__( 'number of computers allowed', 'safeguard-drm' )); ?></i>
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow Windows', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Allow Windows', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<select name="allowwindows" size="1">
									<option value="1" <?php if(stristr($token_data['devices_allowed'],'W')) echo 'selected'; ?> ><?php echo esc_html(__( 'Yes', 'safeguard-drm' )); ?></option>
									<option value="0" <?php if(!stristr($token_data['devices_allowed'],'W')) echo 'selected'; ?>><?php echo esc_html(__( 'No', 'safeguard-drm' )); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow Mac', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Allow Mac', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<select name="allowmac" size="1">
									<option value="1" <?php if(stristr($token_data['devices_allowed'],'M')) echo 'selected'; ?>><?php echo esc_html(__( 'Yes', 'safeguard-drm' )); ?></option>
									<option value="0" <?php if(!stristr($token_data['devices_allowed'],'M')) echo 'selected'; ?>><?php echo esc_html(__( 'No', 'safeguard-drm' )); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow Android', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Allow Android', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<select name="allowandroid" size="1">
									<option value="1" <?php if(stristr($token_data['devices_allowed'],'A')) echo 'selected'; ?>><?php echo esc_html(__( 'Yes', 'safeguard-drm' )); ?></option>
									<option value="0" <?php if(!stristr($token_data['devices_allowed'],'A')) echo 'selected'; ?>><?php echo esc_html(__( 'No', 'safeguard-drm' )); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow IOS', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Allow IOS', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<select name="allowios" size="1">
									<option value="1" <?php if(stristr($token_data['devices_allowed'],'I')) echo 'selected'; ?>><?php echo esc_html(__( 'Yes', 'safeguard-drm' )); ?></option>
									<option value="0" <?php if(!stristr($token_data['devices_allowed'],'I')) echo 'selected'; ?>><?php echo esc_html(__( 'No', 'safeguard-drm' )); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><input type="submit" class="button-primary" name="submit" value="submit"/></td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php
			}
			elseif(isset($_REQUEST['view']) && sanitize_text_field($_REQUEST['view'])=='emailtoken')
			{
				$token      = isset($_REQUEST['token']) ? sanitize_text_field($_REQUEST['token']) : '';
				$token_data = WPSGDRM_token_to_array($token);
			?>
			<form action="" method="post" id="emailtoken" name="emailtoken">
				<?php echo wp_kses(wp_nonce_field('wpsgdrm_settings', 'wpsgdrm_wpnonce'), WPSGDRM_kses_allowed_options()); ?>
				<input type="hidden" name="action" value="emailtoken" ></td>

				<h2><?php echo esc_html(__( 'Email Token', 'safeguard-drm' )); ?></h2>
				<br>
				<a href="<?php echo esc_url(admin_url('admin.php?page=wpsgdrm_list')); ?>" class="button"><?php echo esc_html(__( '&lt;&lt; Add New Token', 'safeguard-drm' )); ?></a>
				<hr />
		
				<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguarddrm_page_setting' <?php if($hideform) echo 'style="display:none;"'; ?>>
					<tbody>
						<tr style="display:none;">
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'ApiKey', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'ApiKey', 'safeguard-drm' )); ?>:</td>
							<td align="left"> <input type="text" name="av_apikey" value="<?php echo esc_attr($av_apikey); ?>" />
						</tr>
						<tr style="display:none;">
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Token ID', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'TokenID', 'safeguard-drm' )); ?>:</td>
							<td align="left">
							<input type="hidden" name="tokenid" value="<?php echo esc_attr($token_data['keyval'].'@'.$token_data['url']); ?>" />
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Email Ids', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Email Ids', 'safeguard-drm' )); ?>:</td>
							<td align="left">
								<?php
									$blogusers = get_users(array('orderby'=>'nicename'));
								?>
								<select name="user_email" id="emails">
									<option value="">Select</option>
									<?php
									// Array of WP_User objects.
									if(is_array($blogusers) && count($blogusers)>0)
									{
										foreach ( $blogusers as $user )
										{
											?>
											<option value="<?php echo esc_attr($user->data->user_email); ?>"><?php echo  esc_html( $user->data->user_nicename ) . '('.esc_html( $user->data->display_name ).')'; ?></option>
											<?php
										}
									}
								?>
								</select> OR
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Email address', 'safeguard-drm' ))); ?></td>
							<td align="left"><?php echo esc_html(__( 'Email address', 'safeguard-drm' )); ?>:</td>
							<td align="left"><input type="email" name="email" value="" style="width:300px;" /></td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><input type="submit" class="button-primary" name="submit" value="submit"/></td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php
			}
			else
			{
				if( ! isset($_REQUEST['view']))
				{
				?>
				<form action="" method="post" id="addnewtoken" name="addnewtoken">
					<?php echo wp_kses(wp_nonce_field('wpsgdrm_settings', 'wpsgdrm_wpnonce'), WPSGDRM_kses_allowed_options()); ?>
					<input type="hidden" name="action"value="addtoken" >

					<h2><?php echo esc_html(__( 'Add New Token', 'safeguard-drm' )); ?></h2>

					<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguarddrm_page_setting'>
						<tbody>
							<tr style="display:none;">
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Register demo key', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'ApiKey', 'safeguard-drm' )); ?>:</td>
								<td align="left"> <input type="text" name="av_apikey" value="<?php echo esc_attr($av_apikey); ?>" /></td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Token ID Autogenerated', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'TokenID', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<input type="text" name="tokenid" value="<?php echo esc_html(gmdate('U')); ?>" />
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'WebPage Url:', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'WebPage Url', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<?php wp_dropdown_pages(array('option_none_value'=>'')); ?> OR <br>
									<input type="text" style="width: 250px;" name="webpage" value="" />
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow Type', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Allow Type', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<select name="plan" size="1" onchange="if(this.value=='expiry') {document.getElementById('expiry').type='date';} else {document.getElementById('expiry').type='text';}">
										<option value="noexpiry"<?php echo $plan == 'noexpiry' ? ' selected' : ''; ?>><?php echo esc_html(__( 'No expiry', 'safeguard-drm' )); ?></option>
										<option value="expiry"<?php echo $plan == 'expiry' ? ' selected' : ''; ?>><?php echo esc_html(__( 'Expiry date', 'safeguard-drm' )); ?></option>
										<option value="days"<?php echo $plan == 'days' ? ' selected' : ''; ?>><?php echo esc_html(__( 'Expire by days', 'safeguard-drm' )); ?></option>
										<option value="views"<?php echo $plan == 'views' ? ' selected' : ''; ?>><?php echo esc_html(__( 'Expiry by views', 'safeguard-drm' )); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Expiry', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Expiry', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<input type="text" style="width: 150px;" id="expiry" name="expiry" value="<?php echo $expiry ? esc_attr($expiry) : esc_attr(gmdate('Ymd')); ?>">
									<i><?php echo esc_html(__( 'YYYYMMDD or like 10', 'safeguard-drm' )); ?></i>
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Devices', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Devices', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<input type="text" style="width: 100px;" id="devices" name="devices" value="<?php echo $devices ? esc_attr($devices) : 1; ?>">
									<i><?php echo esc_html(__( 'number of computers allowed', 'safeguard-drm' )); ?></i>
								</td>
							</tr>
						
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow Windows', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Allow Windows', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<select name="allowwindows" size="1">
										<option value="1"<?php echo ($allowwindows !== null && $allowwindows) ? ' selected' : ''; ?>><?php echo esc_html(__( 'Yes', 'safeguard-drm' )); ?></option>
										<option value="0"<?php echo ($allowwindows !== null && ! $allowwindows) ? ' selected' : ''; ?>><?php echo esc_html(__( 'No', 'safeguard-drm' )); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow Mac', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Allow Mac', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<select name="allowmac" size="1">
										<option value="1"<?php echo ($allowmac !== null && $allowmac) ? ' selected' : ''; ?>><?php echo esc_html(__( 'Yes', 'safeguard-drm' )); ?></option>
										<option value="0"<?php echo ($allowmac !== null && ! $allowmac) ? ' selected' : ''; ?>><?php echo esc_html(__( 'No', 'safeguard-drm' )); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow Android', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Allow Android', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<select name="allowandroid" size="1">
										<option value="1"<?php echo ($allowandroid !== null && $allowandroid) ? ' selected' : ''; ?>><?php echo esc_html(__( 'Yes', 'safeguard-drm' )); ?></option>
										<option value="0"<?php echo ($allowandroid !== null && ! $allowandroid) ? ' selected' : ''; ?>><?php echo esc_html(__( 'No', 'safeguard-drm' )); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Allow IOS', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Allow IOS', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<select name="allowios" size="1">
										<option value="1"<?php echo ($allowios !== null && $allowios) ? ' selected' : ''; ?>><?php echo esc_html(__( 'Yes', 'safeguard-drm' )); ?></option>
										<option value="0"<?php echo ($allowios !== null && ! $allowios) ? ' selected' : ''; ?>><?php echo esc_html(__( 'No', 'safeguard-drm' )); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Email Ids', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Email Ids', 'safeguard-drm' )); ?>:</td>
								<td align="left">
									<?php
										$blogusers = get_users(array('orderby'=>'nicename'));
									?>
									<select name="user_email" id="emails">
										<option value="">Select</option>
										<?php
										// Array of WP_User objects.
										if(is_array($blogusers) && count($blogusers)>0)
										{
											foreach ( $blogusers as $user )
											{
												?>
												<option value="<?php echo esc_attr($user->data->user_email); ?>"><?php echo  esc_html( $user->data->user_nicename ) . '('.esc_html( $user->data->display_name ).')'; ?></option>
												<?php
											}
										}
									?>
									</select> OR
								</td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><?php echo wp_kses_post(WPSGDRM_help_icon(__( 'Email address', 'safeguard-drm' ))); ?></td>
								<td align="left"><?php echo esc_html(__( 'Email address', 'safeguard-drm' )); ?>:</td>
								<td align="left"><input type="email" name="email" value="" style="width:300px;" /></td>
							</tr>
							<tr>
								<td width="50" align="left">&nbsp;</td>
								<td width="30" align="left"><input type="submit" class="button-primary" name="submit" value="submit"/></td>
							</tr>
						</tbody>
					</table>
				</form>
				<?php
				}
			}
			?>
		<hr>
		<?php
		}
		?>
		<div id="col-container">
			<div class="col-wrap">
				<h2><?php echo esc_html(__( 'Registered Token List', 'safeguard-drm' )); ?></h2>
				<table class="wp-list-table widefat hover" id="wpsgdrm-token-list" style="width:100%">
					<thead>
						<tr>
							<th width="5px">&nbsp;<?php echo esc_html(__( 'SNo.', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Token', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Token Value', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'URL', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Email', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Claimed', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Action', 'safeguard-drm' )); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php echo wp_kses($table, WPSGDRM_kses_allowed_options()); ?>
					</tbody>
					<tfoot>
						<tr>
							<th>&nbsp;<?php echo esc_html(__( 'SNo.', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Token', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Token Value', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'URL', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Email', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Claimed', 'safeguard-drm' )); ?></th>
							<th><?php echo esc_html(__( 'Action', 'safeguard-drm' )); ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<div class="clear"></div>
	</div>
<script type='text/javascript'>
jQuery(document).ready(function ($) {
	$('.wpsafeguarddrm_page_setting img').click(function () {
		alert($(this).attr("alt"));
	});

	let table = new DataTable('#wpsgdrm-token-list', {
		order: [[0, 'desc']]
	});
});
</script>
<style type="text/css">
#wpsgdrm-token-list_wrapper select[name="wpsgdrm-token-list_length"] {
	width: 70px;
}
.row-info {
	display: flex;
}
.row-info__label {
	min-width: 110px;
}
.tooltip {
	cursor: help;
	position: relative;
	text-decoration: underline;
	color: #135e96;
	width: 140px;
	display: inline-block;
}
.tooltip .tooltip-text {
	background: #1496bb;
	bottom: 100%;
	color: #fff;
	display: block;
	left: -20px;
	margin-bottom: 15px;
	display: none;
	padding: 20px;
	pointer-events: none;
	position: absolute;
	width: 220px;
	
	-webkit-transform: translateY(10px);
	-moz-transform: translateY(10px);
	-ms-transform: translateY(10px);
	-o-transform: translateY(10px);
	transform: translateY(10px);

	-webkit-transition: all .25s ease-out;
	-moz-transition: all .25s ease-out;
	-ms-transition: all .25s ease-out;
	-o-transition: all .25s ease-out;
	transition: all .25s ease-out;

	-webkit-box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.28);
	-moz-box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.28);
	-ms-box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.28);
	-o-box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.28);
	box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.28);
}
.tooltip .tooltip-text:before {
	bottom: -20px;
	content: " ";
	display: block;
	height: 20px;
	left: 0;
	position: absolute;
	width: 100%;
}

/* CSS Triangles - see Trevor's post */
.tooltip .tooltip-text:after {
	border-left: solid transparent 10px;
	border-right: solid transparent 10px;
	border-top: solid #1496bb 10px;
	bottom: -10px;
	content: " ";
	height: 0;
	left: 50%;
	margin-left: -13px;
	position: absolute;
	width: 0;
}
.tooltip:hover .tooltip-text {
	display: block;
	pointer-events: auto;
	-webkit-transform: translateY(0px);
	-moz-transform: translateY(0px);
	-ms-transform: translateY(0px);
	-o-transform: translateY(0px);
	transform: translateY(0px);
}
</style>
<?php
}