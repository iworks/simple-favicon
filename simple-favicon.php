<?php
/*
Plugin Name: Simple Favicon
Plugin URI: http://iworks.pl/
Description: 
Text Domain: simple-favicon
Version: PLUGIN_VERSION
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GNU GPL
 */

require_once dirname( __FILE__ ) .'/vendor/iworks/favicon.php';
new Iworks_Favicon();

include_once dirname( __FILE__ ) .'/vendor/iworks/rate/rate.php';
do_action(
	'iworks-register-plugin',
	plugin_basename( __FILE__ ),
	__( 'Simple Favicon', 'simple-favicon' ),
	'simple-favicon'
);
