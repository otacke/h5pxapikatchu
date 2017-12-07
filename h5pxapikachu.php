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
	// TODO: Put all the DB stuff in separate file/class, easier for porting
	global $wpdb;

	$table_name = $wpdb->prefix . 'h5pxapikatchu';
	$charset_collate = $wpdb->get_charset_collate();

	// naming a row object_id will cause trouble!
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		actor_object_type TEXT,
		actor_name TEXT,
		actor_mbox TEXT,
		actor_account_homepage TEXT,
		actor_account_name TEXT,
		verb_id TEXT,
		verb_display TEXT,
		xobject_id TEXT,
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

  $result = $wpdb->insert(
		$table_name,
		array (
		 	'actor_object_type' => shape_xAPI_field( $json->actor->objectType ),
		 	'actor_name' => shape_xAPI_field( $json->actor->name ),
		 	'actor_mbox' => shape_xAPI_field( $json->actor->mbox ),
		 	'actor_account_homepage' => shape_xAPI_field( $json->actor->account->homepage ),
		 	'actor_account_name' => shape_xAPI_field( $json->actor->account->name ),
		 	'verb_id' => shape_xAPI_field( $json->verb->id ),
		 	'verb_display' => shape_xAPI_field( $json->verb->display, true),
		 	'xobject_id' => shape_xAPI_field( $json->object->id ),
		 	'object_definition_name' => shape_xAPI_field( $json->object->definition->name, true),
		 	'object_definition_description' => shape_xAPI_field( $json->object->definition->description, true ),
		 	'object_definition_choices' => shape_xAPI_field( $json->object->definition->choices),
		 	'object_definition_correctResponsesPattern' => shape_xAPI_field( $json->object->definition->correctResponsesPattern ),
		 	'result_response' => shape_xAPI_field( $json->result->response ),
		 	'result_score_raw' => shape_xAPI_field( $json->result->score->raw ),
		 	'result_score_scaled' => shape_xAPI_field( $json->result->score->scaled ),
		 	'result_completion' => shape_xAPI_field( $json->result->completion ),
		 	'result_success' => shape_xAPI_field( $json->result->success ),
		 	'result_duration' => shape_xAPI_field( $json->result->duration ),
		 	'time' => current_time( 'mysql' ),
		 	'xapi' => $xapi
	  )
	);

	wp_die();
}

function shape_xAPI_field ( $field, $hasLanguage = false ) {
	if ( ! isset( $field ) ) {
		return NULL;
	}
	if ( is_string ( $field ) || is_int( $field ) || is_float( $field ) || is_bool( $field )) {
		return $field;
	}
	if ( is_array ( $field ) ) {
		return json_encode( $field );
	}
	if ( ! isset( $hasLanguage ) || $hasLanguage === false ) {
		return json_encode( $field ) !== 'null' ? json_encode ( $field ) : '';
	}
	if ( isset( $hasLanguage) ) {
		$locale = str_replace( '_', '-', get_locale() );
		$localeEnUs = 'en-US';
		return sizeof($field->$locale) === 0 ? $field->$localeEnUs : $field->$locale;
	}
	return 'BUG';
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
