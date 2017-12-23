<?php

/**
 * Plugin Name: H5PxAPIkatchu
 * Plugin URI: https://github.com/otacke/h5pxapikatchu
 * Description: Catch and store xAPI statements of H5P
 * Version: 0.1
 * Author: Oliver Tacke
 * Author URI: https://www.olivertacke.de
 * License: MIT
 */

namespace H5PXAPIKATCHU;

// as suggested by the Wordpress community
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// settings.php contains all functions for the settings
require_once( __DIR__ . '/database.php' );
require_once( __DIR__ . '/options.php' );
require_once( __DIR__ . '/display.php' );
require_once( __DIR__ . '/xapidata.php' );

/**
 * Setup the plugin.
 */
function setup () {
	wp_enqueue_script( 'H5PxAPIkatchu', plugins_url( '/js/h5pxapikatchu.js', __FILE__ ), array( 'jquery' ), '1.0', true);

	// Pass variables to JavaScript
	wp_localize_script( 'H5PxAPIkatchu', 'wpAJAXurl', admin_url( 'admin-ajax.php' ) );
	wp_localize_script( 'H5PxAPIkatchu', 'debug_enabled', OPTIONS::is_debug_enabled() ? '1' : '0' );
	wp_localize_script( 'H5PxAPIkatchu', 'captureAllH5pContentTypes', OPTIONS::capture_all_h5p_content_types() ? '1' : '0' );
	wp_localize_script( 'H5PxAPIkatchu', 'h5pContentTypes', OPTIONS::get_h5p_content_types() );
	load_plugin_textdomain( 'H5PxAPIkatchu', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

/**
 * Activate the plugin.
 */
function on_activation () {
	Database::build_tables();
	Options::setDefaults();
}

/**
 * Deactivate the plugin.
 */
function on_deactivation () {
	// TODO: Remove after testing is done
	on_uninstall();
}

/**
 * Uninstall the plugin.
 */
function on_uninstall () {
	Database::delete_tables();
	Options::delete_options();
}

/**
 * Insert an entry into the database.
 *
 * @param {String} text - Text to be added.
 */
function insert_data () {
	global $wpdb;

	$xapi = $_REQUEST['xapi'];
	$xapidata = new XAPIDATA( $xapi );

	$actor = $xapidata->get_actor();
	$verb = $xapidata->get_verb();
	$object = $xapidata->get_object();
	$result = $xapidata->get_result();

	$xapi = ( Options::store_complete_xapi() ) ? str_replace('\"', '"', $xapi) : null;

	DATABASE::insert_data( $actor, $verb, $object, $result, $xapi );

	wp_die();
}

// Start setup
register_activation_hook( __FILE__, 'H5PXAPIKATCHU\on_activation' );
register_deactivation_hook( __FILE__, 'H5PXAPIKATCHU\on_deactivation' );
register_uninstall_hook( __FILE__, 'H5PXAPIKATCHU\on_uninstall' );

add_action( 'the_post', 'H5PXAPIKATCHU\setup' );
add_action( 'wp_ajax_nopriv_insert_data', 'H5PXAPIKATCHU\insert_data' );
add_action( 'wp_ajax_insert_data', 'H5PXAPIKATCHU\insert_data' );

// Include options
$h5pxapikatchu_options = new Options;

if ( is_admin() ) {
	$h5pxapikatchu_display = new Display;
}
