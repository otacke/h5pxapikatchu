<?php

/**
 * Plugin Name: H5PxAPIkatchu
 * Plugin URI: https://github.com/otacke/h5pxapikatchu
 * Description: Catch and store xAPI statements of H5P
 * Version: 0.1
 * Author: Oliver Tacke
 * Author URI: https://www.olivertacke.de
 * License: WTFPL
 */

namespace H5PXAPIKATCHU;

// as suggested by the Wordpress community
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Setup the plugin.
 */
function setup () {
	wp_enqueue_script( 'H5PxAPIkatchu', plugins_url( '/js/h5pxapikatchu.js', __FILE__ ), array( 'jquery' ), '1.0', true);
	// used to pass the URL variable to JavaScript
	wp_localize_script( 'H5PxAPIkatchu', 'wpAJAXurl', admin_url( 'admin-ajax.php' ) );

	// For localization later on ...
	// load_plugin_textdomain( 'H5PxAPIkatchu', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

/**
 * Activate the plugin.
 */
function on_activation () {
	global $wpdb;

	$table_name = $wpdb->prefix . 'h5pxapikatchu';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		xapi text NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

/**
 * Deactivate the plugin.
 */
function on_deactivation () {
}

/**
 * Uninstall the plugin.
 */
function on_uninstall () {
	global $wpdb;
	$table_name = $wpdb->prefix . 'h5pxapikatchu';
	$wpdb->query( $wpdb->prepare(
		"
			DROP TABLE IF EXISTS $table_name
		"
	) );
}

/**
 * Insert an entry into the database.
 * @param {String} text - Text to be added.
 */
function insert_data () {
	$xapi = $_REQUEST['xapi'];
	// TODO: optional xAPI parts in separate database fields
	// TODO: make the parts configurable in options

	global $wpdb;

	$table_name = $wpdb->prefix . 'h5pxapikatchu';

	$wpdb->query( $wpdb->prepare(
		"
			INSERT INTO $table_name
			( time, xapi )
			VALUES ( %s, %s )
		",
		current_time( 'mysql' ),
		$xapi
	) );
	wp_die();
}

// Start setup
register_activation_hook(__FILE__, 'H5PXAPIKATCHU\on_activation');
register_deactivation_hook(__FILE__, 'H5PXAPIKATCHU\on_deactivation');
register_uninstall_hook(__FILE__, 'H5PXAPIKATCHU\on_uninstall');

add_action( 'the_post', 'H5PXAPIKATCHU\setup' );
add_action( 'wp_ajax_nopriv_insert_data', 'H5PXAPIKATCHU\insert_data' );
add_action( 'wp_ajax_insert_data', 'H5PXAPIKATCHU\insert_data' );
