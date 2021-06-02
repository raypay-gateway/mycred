<?php
/*
Plugin Name: RayPay myCRED
Version: 1.0
Description: RayPay payment gateway for myCRED
Author: Saminray
Author URI: https://saminray.com
Text Domain: mycred-raypay-gateway
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function raypay_mycred_load_textdomain() {
    load_plugin_textdomain( 'mycred-raypay-gateway', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'raypay_mycred_load_textdomain' );

require_once( plugin_dir_path( __FILE__ ) . 'class-mycred-gateway-raypay.php' );
