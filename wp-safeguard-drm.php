<?php
/*
Plugin Name: SafeGuard DRM Protection
Plugin URI: https://safeguard.media/wordpress-drm.asp
Description: Add DRM protection to WordPress pages and posts.
Author: ArtistScope
Version: 2.9.0
Author URI: https://safeguard.media/
License: GPLv2
Text Domain: safeguard-drm
Domain Path: /languages/
	
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// ================================================================================ //
//                                                                                  //
//  WARNING : DO NOT CHANGE ANYTHING BELOW IF YOU DONT KNOW WHAT YOU ARE DOING      //
//                                                                                  //
// ================================================================================ //

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

define('WPSGDRM_ASSET_VERSION', 1.1);

// ============================================================================================================================
# setup plugin
function WPSGDRM_setup() {
	//----add codding----

	define('WPSGDRM_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path(__FILE__))); //use for include files to other files
	define('WPSGDRM_PLUGIN_URL', plugins_url('/', __FILE__));

	require_once WPSGDRM_PLUGIN_PATH . '/inc/function-config.php';
	require_once WPSGDRM_PLUGIN_PATH . '/inc/function-common.php';
	require_once WPSGDRM_PLUGIN_PATH . '/inc/function-server.php';

	require_once WPSGDRM_PLUGIN_PATH . '/inc/ajax-page-list.php';

	require_once WPSGDRM_PLUGIN_PATH . '/inc/admin-page-settings.php';
	require_once WPSGDRM_PLUGIN_PATH . '/inc/admin-page-list.php';

	require_once WPSGDRM_PLUGIN_PATH . '/inc/frontend-shortcode.php';

	// add short code
	add_shortcode('safeguarddrm', 'WPSGDRM_shortcode');

	add_action('admin_menu', 'WPSGDRM_admin_menus');

	// load admin JS
	add_action('admin_enqueue_scripts', 'WPSGDRM_admin_load_js_all');

	// load media button
	//add_action('media_buttons_context', 'wpsafeguarddrm_media_buttons');
	add_action('media_buttons', 'WPSGDRM_media_buttons');

	add_action('wp_footer', 'WPSGDRM_cstmjsonfrontend');
}
add_action('init', 'WPSGDRM_setup');

// ============================================================================================================================
# register WordPress menus
function WPSGDRM_admin_menus() {
	$listfile =__( 'List Tokens', 'safeguard-drm' );
	$settings =__( 'Settings', 'safeguard-drm' );

	add_menu_page('SafeGuard DRM', 'SafeGuard DRM', 'publish_posts', 'wpsgdrm_list');
	add_submenu_page('wpsgdrm_list', 'SafeGuard DRM Token List', $listfile, 'publish_posts', 'wpsgdrm_list', 'WPSGDRM_admin_page_list');
	add_submenu_page('wpsgdrm_list', 'SafeGuard Media Settings', $settings, 'publish_posts', 'wpsgdrm_settings', 'WPSGDRM_admin_page_settings');
}

// ============================================================================================================================
# delete short code
function WPSGDRM_delete_shortcode() {
	// get all posts
	$posts_array = get_posts();
	foreach ($posts_array as $post) {
		// delete short code
		$post->post_content = WPSGDRM_deactivate_shortcode($post->post_content);
		// update post
		wp_update_post($post);
	}
}

// ============================================================================================================================
# deactivate short code
function WPSGDRM_deactivate_shortcode($content) {
	// delete short code
	$content = preg_replace('/\[safeguardrm name="[^"]+"\]\[\/safeguarddrm\]/s', '', $content);
	return $content;
}

// ============================================================================================================================
# delete file options
function WPSGDRM_media_buttons($context) {
	if (current_user_can('edit_posts')) {
		global $post;
		
		$id = $post->ID;

		$av_apikey  = get_option('wpsgdrm_av_apikeydrm');
		$av_domain  = get_option('wpsgdrm_fm_domain');
		$currenturl = get_permalink($id);

		// generate token for links
		$token_value=$av_apikey.'@'.$currenturl;

		echo wp_kses("<a href='#' data-value='". esc_attr($token_value) . "' class='sendtoeditor' id='wpsafeguarddrm_link' data-body='no-overflow' title='SafeGuard DRM'><img src='" . esc_attr(plugin_dir_url(__FILE__)) . "images/safeguardbutton.png'></a>", WPSGDRM_kses_allowed_options());
	}
}

// ============================================================================================================================
# admin page scripts
function WPSGDRM_admin_load_js_all() {
	// load jquery suggest plugin
	$screen = get_current_screen();

	if (current_user_can('edit_posts')) {
		wp_enqueue_script( 'plugin-script-sfdrm', plugins_url( 'js/safeguarddrm_token_uploader.js', __FILE__), ['jquery'], WPSGDRM_ASSET_VERSION, ['in_footer' => true]);
	}

	if( ! empty($screen->id) && $screen->id == 'toplevel_page_wpsgdrm_list') {
		wp_enqueue_style('jquery-datatables', plugins_url('css/jquery.dataTables.min.css', __FILE__), [], WPSGDRM_ASSET_VERSION);

		wp_enqueue_script('jquery-datatables', plugins_url('js/jquery.dataTables.min.js', __FILE__), ['jquery'], WPSGDRM_ASSET_VERSION, ['in_footer' => true]);
		wp_enqueue_script('wpsgdrm-token-list', plugins_url('js/safeguarddrm_token_list.js', __FILE__), [], WPSGDRM_ASSET_VERSION, ['in_footer' => true]);

		wp_localize_script('wpsgdrm-token-list', 'wpsgdrm_token_list_data', [
			'nonce' => wp_create_nonce('wpsgdrm_token_nonce'),
		]);
	}
}

// ============================================================================================================================
# runs when plugin activated
function WPSGDRM_activate() {
	// set plugin folder
	$upload_dir = wp_upload_dir();
	
	$target_upload_dir = $upload_dir['basedir'] . '/safeguard-drm/';

	// if this is first activation, setup plugin options
	if ( ! get_option('wpsgdrm_settings')) {

		// set default options
		$wpsafeguarddrm_options['settings'] = [
			'admin_only' => "checked",
			'mode' => "demo",
			'language' => "",
			'width' => '620',
			'height' => '400',
			'asps' => "checked",
			'ff' => "",
			'ch' => "",
		];

		update_option('wpsgdrm_settings', $wpsafeguarddrm_options);
	}

	if( ! is_dir($target_upload_dir))
	{
		wp_mkdir_p($target_upload_dir);
	}
}

// ============================================================================================================================
# runs when plugin deactivated
function WPSGDRM_deactivate() {
	// remove text editor short code
	remove_shortcode('safeguarddrm');
}

// ============================================================================================================================
# runs when plugin deleted.
function WPSGDRM_uninstall() {
	// delete all uploaded files
	
	// delete plugin options
	delete_option('wpsgdrm_settings');

	// unregister short code
	remove_shortcode('safeguarddrm');

	// delete short code from post content
	WPSGDRM_delete_shortcode();
}

// ============================================================================================================================
# register plugin hooks
register_activation_hook(__FILE__, 'WPSGDRM_activate'); // run when activated
register_deactivation_hook(__FILE__, 'WPSGDRM_deactivate'); // run when deactivated
register_uninstall_hook(__FILE__, 'WPSGDRM_uninstall'); // run when uninstalled