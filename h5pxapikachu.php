<?php

/**
 * Plugin Name: H5PxAPIkatchu
 * Plugin URI: https://github.com/otacke/h5pxapikatchu
 * Text Domain: H5PXAPIKATCHU
 * Domain Path: /languages
 * Description: Catch and store xAPI statements sent by H5P
 * Version: 0.2.6
 * Author: Oliver Tacke
 * Author URI: https://www.olivertacke.de
 * License: MIT
 */

namespace H5PXAPIKATCHU;

// as suggested by the Wordpress community
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !defined( 'H5PXAPIKATCHU_VERSION' ) ) {
  define( 'H5PXAPIKATCHU_VERSION', '0.3.0' );
}

// settings.php contains all functions for the settings
require_once( __DIR__ . '/class-database.php' );
require_once( __DIR__ . '/class-options.php' );
require_once( __DIR__ . '/class-table-view.php' );
require_once( __DIR__ . '/class-xapidata.php' );
require_once( __DIR__ . '/class-privacy-policy.php' );

/**
 * Setup the plugin.
 */
function setup() {
	wp_enqueue_script( 'H5PxAPIkatchu', plugins_url( '/js/h5pxapikatchu.js', __FILE__ ), array( 'jquery' ), H5PXAPIKATCHU_VERSION );

	// Pass variables to JavaScript
	wp_localize_script( 'H5PxAPIkatchu', 'wpAJAXurl', admin_url( 'admin-ajax.php' ) );
	wp_localize_script( 'H5PxAPIkatchu', 'debugEnabled', Options::is_debug_enabled() ? '1' : '0' );
	wp_localize_script( 'H5PxAPIkatchu', 'captureAllH5pContentTypes', Options::capture_all_h5p_content_types() ? '1' : '0' );
	wp_localize_script( 'H5PxAPIkatchu', 'h5pContentTypes', Options::get_h5p_content_types() );
}

/**
 * Activate the plugin.
 */
function on_activation() {
	Database::build_tables();
	Options::setDefaults();
}

/**
 * Deactivate the plugin.
 */
function on_deactivation() {
}

/**
 * Uninstall the plugin.
 */
function on_uninstall() {
	Database::delete_tables();
	Options::delete_options();
}

/**
 * Update the plugin.
 */
function update() {
	if ( H5PXAPIKATCHU_VERSION === get_option( 'h5pxapikatchu_version' ) ) {
		return;
	}

	// Update database
	Database::build_tables();

	// Update from 0.1.3 to 0.2.0
	if ( false === get_option('h5pxapikatchu_version') || '0.1.3' === get_option('h5pxapikatchu_version') ) {
    Database::complete_wp_user_id();
    Database::complete_content_id_subcontent_id();

		update_option( 'h5pxapikatchu_version', '0.2.0' );
	}

	// Update from 0.2.x to 0.3.0
  $version = explode( '.', get_option( 'h5pxapikatchu_version' ) );
  if ( $version[0] === '0' && $version[1] === '2' ) {
    // Add defaults for showing/hiding column labels
    Options::set_defaults_columns_visible();

    // From now on remember that defaults have been set already
    update_option( 'h5pxapikatchu_defaults_set', true );

		update_option( 'h5pxapikatchu_version', '0.3.0' );
  }

	update_option( 'h5pxapikatchu_version', H5PXAPIKATCHU_VERSION );
}

/**
 * Insert an entry into the database.
 *
 * @param string text Text to be added.
 */
function insert_data() {
	global $wpdb;

	$xapi = $_REQUEST['xapi'];
	$xapidata = new XAPIDATA( $xapi );

	$actor = $xapidata->get_actor();
	$actor['wpUserId'] = get_current_user_id();
	$actor['wpUserId'] = ( $actor['wpUserId'] === 0 ) ? null : $actor['wpUserId'];

	$verb = $xapidata->get_verb();

	$object = $xapidata->get_object();
	preg_match( "/[&|?]id=([0-9]+)/", $object['id'], $matches );
	$object['h5pContentId'] = ( sizeof( $matches ) > 0 ) ? $matches[1] : null;
	preg_match( "/[&|?]subContentId=([0-9a-f-]{36})/", $object['id'], $matches );
	$object['h5pSubContentId'] = ( sizeof( $matches ) > 0 ) ? $matches[1] : null;

	$result = $xapidata->get_result();

	$xapi = ( Options::store_complete_xapi() ) ? str_replace('\"', '"', $xapi) : null;

	$ok = Database::insert_data( $actor, $verb, $object, $result, $xapi );

	// We could handle database errors here using $ok.

	wp_die();
}

/**
 * Delete all data.
 */
function delete_data() {
	$response = Database::delete_data();
	exit( json_encode( $response ) );
	wp_die();
}

/**
 * Load the text domain for internationalization.
 */
function h5pxapikatchu_load_plugin_textdomain() {
	load_plugin_textdomain( 'H5PXAPIKATCHU', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// Start setup
register_activation_hook( __FILE__, 'H5PXAPIKATCHU\on_activation' );
register_deactivation_hook( __FILE__, 'H5PXAPIKATCHU\on_deactivation' );
register_uninstall_hook( __FILE__, 'H5PXAPIKATCHU\on_uninstall' );

add_action( 'the_post', 'H5PXAPIKATCHU\setup' );
add_action( 'wp_ajax_nopriv_insert_data', 'H5PXAPIKATCHU\insert_data' );
add_action( 'wp_ajax_insert_data', 'H5PXAPIKATCHU\insert_data' );
add_action( 'plugins_loaded', 'H5PXAPIKATCHU\h5pxapikatchu_load_plugin_textdomain' );
add_action( 'plugins_loaded', 'H5PXAPIKATCHU\update' );

// Include options
$h5pxapikatchu_options = new Options;

if ( is_admin() ) {
  // Data privacy hooks
  add_action( 'admin_init', 'H5PXAPIKATCHU\PrivacyPolicy::add_privacy_policy', 20 );
  add_filter( 'wp_privacy_personal_data_exporters', 'H5PXAPIKATCHU\PrivacyPolicy::register_h5pxapikatchu_exporter', 10 );
  add_filter( 'wp_privacy_personal_data_erasers', 'H5PXAPIKATCHU\PrivacyPolicy::register_h5pxapikatchu_eraser', 10 );

  // Custom style
  wp_enqueue_style( 'H5PxAPIkatchu', plugins_url( '/styles/h5pxapikatchu.css', __FILE__ ), array(), H5PXAPIKATCHU_VERSION );

	$h5pxapikatchu_table_view = new Table_View;
}
