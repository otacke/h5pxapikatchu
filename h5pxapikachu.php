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

// settings.php contains all functions for the settings
require_once( __DIR__ . '/settings.php' );
require_once( __DIR__ . '/display.php' );

/**
 * Setup the plugin.
 */
function setup () {
	wp_enqueue_script( 'H5PxAPIkatchu', plugins_url( '/js/h5pxapikatchu.js', __FILE__ ), array( 'jquery' ), '1.0', true);
	// used to pass the URLs variable to JavaScript
	wp_localize_script( 'H5PxAPIkatchu', 'wpAJAXurl', admin_url( 'admin-ajax.php' ) );
	$options = get_option('h5pxapikatchu_option', false);
	wp_localize_script( 'H5PxAPIkatchu', 'debug_enabled', isset( $options['debug_enabled'] ) ? '1' : '0' );
	load_plugin_textdomain( 'H5PxAPIkatchu', false, basename( dirname( __FILE__ ) ) . '/languages' );
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
		actor_object_type TEXT,
		actor_name TEXT,
		actor_mbox TEXT,
		actor_account_homepage TEXT,
		actor_account_name TEXT,
		verb_id TEXT,
		verb_display TEXT,
		object_id TEXT,
		object_definition_name TEXT,
		object_definition_description TEXT,
		object_definition_choices TEXT,
		object_definition_correctResponsesPattern TEXT,
		result_response TEXT,
		result_score_raw INT,
		result_score_scaled FLOAT,
		result_completion BOOLEAN,
		result_success BOOLEAN,
		result_duration VARCHAR(20),
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		xapi text,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
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
	// Delete database table
	global $wpdb;
	$table_name = $wpdb->prefix . 'h5pxapikatchu';
	$wpdb->query( $wpdb->prepare(
		"
			DROP TABLE IF EXISTS $table_name
		"
	) );

	// Delete options
	$option_name = 'h5pxapikatchu_option';
	delete_option( $option_name );
	delete_site_option( $option_name );
}

/**
 * Insert an entry into the database.
 * @param {String} text - Text to be added.
 */
function insert_data () {
	$xapi = $_REQUEST['xapi'];

	global $wpdb;

	$table_name = $wpdb->prefix . 'h5pxapikatchu';
	$xapi = str_replace('\"', '"', $xapi);
	$json = json_decode($xapi);

	$options = get_option('h5pxapikatchu_option', false);
	if ( !isset( $options['store_complete_xapi'] ) ) {
		$xapi = null;
	}

	$wpdb->query( $wpdb->prepare(
		"
			INSERT INTO $table_name
			( actor_object_type,
				actor_name,
				actor_mbox,
				actor_account_homepage,
				actor_account_name,
				verb_id,
				verb_display,
				object_id,
				object_definition_name,
				object_definition_description,
				object_definition_choices,
				object_definition_correctResponsesPattern,
				result_response,
				result_score_raw,
				result_score_scaled,
				result_completion,
				result_success,
				result_duration,
				time,
				xapi )
			VALUES ( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %f, %d, %d, %s, %s, %s )
		",
		$json->actor->objectType,
		$json->actor->name,
		$json->actor->mbox,
		$json->actor->account->homepage,
		$json->actor->account->name,
		$json->verb->id,
		json_encode($json->verb->display) !== 'null' ? json_encode($json->verb->display) : '',
		$json->object->id,
		json_encode($json->object->definition->name) !== 'null' ? json_encode($json->object->definition->name) : '',
		json_encode($json->object->definition->description) !== 'null' ? json_encode($json->object->definition->description) : '',
		json_encode($json->object->definition->choices) !== 'null' ? json_encode($json->object->definition->choices) : '',
		json_encode($json->object->definition->correctResponsesPattern) !== 'null' ? json_encode($json->object->definition->correctResponsesPattern) : '',
		json_encode($json->result->response) !== 'null' ? json_encode($json->result->response) : '',
		$json->result->score->raw,
		$json->result->score->scaled,
		$json->result->completion,
		$json->result->success,
		$json->result->duration,
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

// Include settings
if ( is_admin() ) {
	$settings = new Settings;
	$display = new Display;
}
