<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

function WPSGDRM_option_structure()
{
	$option_structure = [
		'wpsgdrm_av_apikeydrm' => [
			'default' => '',
		],
		'wpsgdrm_av_allowwindows' => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsgdrm_av_allowmac' => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsgdrm_av_allowandroid' => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsgdrm_av_allowios' => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsgdrm_av_allowremote' => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsgdrm_av_Version' => [
			'default' => '1.0',
			'type'    => 'float',
		],
		'wpsgdrm_av_Version_windows' => [
			'default' => '34.11',
			'type'    => 'float',
		],
		'wpsgdrm_av_Version_mac' => [
			'default' => '32.1',
			'type'    => 'float',
		],
		'wpsgdrm_av_Version_android' => [
			'default' => '34.0',
			'type'    => 'float',
		],
		'wpsgdrm_av_Version_ios' => [
			'default' => '34.0',
			'type'    => 'float',
		],
	];

	return $option_structure;
}

function WPSGDRM_kses_allowed_options()
{
	$default = wp_kses_allowed_html('post');

	$default['input'] = [
		'type' => 1,
		'name' => 1,
		'value' => 1,
		'class' => 1,
		'id' => 1,
	];

	$default['form'] = [
		'type' => 1,
		'name' => 1,
		'value' => 1,
		'class' => 1,
		'id' => 1,
	];

	return $default;
}