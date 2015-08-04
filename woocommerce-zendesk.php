<?php
/*
Plugin Name: WooCommerce Zendesk Connect
Plugin URI: https://wpfortune.com/shop/plugins/woocommerce-zendesk-connect/
Description: This plugin connects WooCommerce with Zendesk. - Free version
Version: 1.0.0
Requires at least: 3.5
Author: WP Fortune
Author URI: http://wpfortune.com
License: GPL
*/
/*  Copyright 2013  WP Fortune  (email : info@wpfortune.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Load required functions 
* @since 0.0.5
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once('inc/wzn-funct.php');

/**
* Check if WooCommerce is active 
* @since 0.0.5
*/
if (is_woocommerce_active()) {
	load_plugin_textdomain('woo-wzn', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
	require_once('admin/wzn-settings.php');
	add_action('admin_menu', 'woocommerce_wzn_admin_menu');
	add_action('wp_footer', 'woo_wzn_scripts');
	// If frontend styling is on, load styles in footer
	if(get_option( 'woo_wzn_use_style')=='on') {
		add_action('wp_footer','woo_wzn_styles');
	}
	
} else {
	// if WooCommerce is not active show admin message
	add_action('admin_notices', 'wznAdminMessages');   
}
/**
* Check if settings are saved for new version 
* @since 0.0.5
*/
if(get_option( 'woo_wzn_version' ) != "0.0.5")  {
  function wzn_version_message() {wznMessage(sprintf(__( 'Please update your WooCommerce Zendesk Connect settings before using this new release. %s', 'woo-wzn'),'&nbsp;<a class=button href="admin.php?page=woocommerce_wzn">'.__('Settings','woo-wzn').'</a>'), true);}
  add_action('admin_notices', 'wzn_version_message');
}

/**
* Settings link on plugin page 
* @since 0.0.5
*/
function wzn_plugin_links($links) { 
  $settings_link = '<a href="admin.php?page=woocommerce_wzn" title="'.__('Settings','woo-wzn').'">'.__('Settings','woo-wzn').'</a>'; 
  $premium_link = '<a href="https://wpfortune.com/shop/plugins/woocommerce-zendesk-connect/" title="Buy Pro" target=_blank>Buy Pro</a>';
  array_unshift($links, $settings_link,$premium_link);
  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'wzn_plugin_links' );
?>