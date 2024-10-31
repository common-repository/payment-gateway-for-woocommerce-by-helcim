<?php
/*
Plugin Name: Helcim Payment Gateway
Plugin URI: https://www.helcim.com/
Description: Helcim Payment Gateway
Version: 1.0.7
Author: Helcim Inc.
Author URI: https://www.helcim.com/
*/

add_action('plugins_loaded', 'woocommerce_helcim_init', 0);

function woocommerce_helcim_init() {
	if ( !class_exists( 'WC_Payment_Gateway' ) ) { return; }

	require_once plugin_dir_path(__FILE__) . 'class-wc-gateway-helcim.php';

	function add_helcim_gateway($methods) {
		$methods[] = 'WC_Gateway_Helcim';
		return $methods;
	}

	add_filter('woocommerce_payment_gateways', 'add_helcim_gateway');
}
