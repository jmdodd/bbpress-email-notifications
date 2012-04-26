<?php
/*
Plugin Name: bbPress Email Notifications
Description: Provide notification emails and controls for bbPress subscriptions, merge, and split functions. 
Version: 0.1
Author: Jennifer M. Dodd
Author URI: http://uncommoncontent.com/
Text Domain: bbpress-email-notifications
*/ 

/*
	Copyright 2012  Jennifer M. Dodd  <jmdodd@gmail.com>

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, see <http://www.gnu.org/licenses/>. 
*/


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'ucc_ben_init' ) ) {
function ucc_ben_init() {
	if ( in_array( 'bbpress/bbpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		load_plugin_textdomain( 'bbpress-email-notifications', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		include( plugin_dir_path( __FILE__ ) . '/includes/ucc-ben-loader.php' ); 
	}
} }
add_action( 'init', 'ucc_ben_init' );
