<?php
/**
 * Helcim Inc Payment Gateway
 *
 * Uninstalls Helcim Gateway.
 *
 * @version 	1.0.7
 * @author 		Helcim Inc.
 */

if ( !defined('WP_UNINSTALL_PLUGIN') ) exit;

delete_option( 'woocommerce_helcim_settings' );