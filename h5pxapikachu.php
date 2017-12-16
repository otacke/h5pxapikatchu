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
	$options = Options::$OPTIONS;

	wp_enqueue_script( 'H5PxAPIkatchu', plugins_url( '/js/h5pxapikatchu.js', __FILE__ ), array( 'jquery' ), '1.0', true);

	// used to pass the URLs variable to JavaScript
	wp_localize_script( 'H5PxAPIkatchu', 'wpAJAXurl', admin_url( 'admin-ajax.php' ) );
	wp_localize_script( 'H5PxAPIkatchu', 'debug_enabled', isset( $options['debug_enabled'] ) ? '1' : '0' );
	load_plugin_textdomain( 'H5PxAPIkatchu', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

/**
 * Activate the plugin.
 */
function on_activation () {
	Database::build_table();
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
	Database::delete_table();
	Options::delete_options();
}

/**
 * Insert an entry into the database.
 *
 * TODO: Move this to database.php
 *
 * @param {String} text - Text to be added.
 */
function insert_data () {
	global $wpdb;

	$xapi = $_REQUEST['xapi'];

	$xapidata = new XAPIDATA($xapi);
	$actor = $xapidata->get_actor();
	$verb = $xapidata->get_verb();
	$object = $xapidata->get_object();
	$result = $xapidata->get_result();

	$table_name = DATABASE::$TABLE_NAME;
	$table_actor = DATABASE::$TABLE_ACTOR;
	$table_verb = DATABASE::$TABLE_VERB;
	$table_object = DATABASE::$TABLE_OBJECT;
	$table_result = DATABASE::$TABLE_RESULT;

	// TODO: Refactor & error handling
	$actor_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT id FROM $table_actor WHERE actor_id = %s", $actor['inverseFunctionalIdentifier']
	) );

	if ( is_null( $actor_id ) ) {
		$wpdb->insert(
			$table_actor,
			array(
				'actor_id' => $actor['inverseFunctionalIdentifier'],
				'actor_name' => $actor['name'],
				'actor_members' => $actor['members']
			)
		);
		$actor_id = $wpdb->insert_id;
	}

	// TODO: Refactor & error handling
	$verb_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT id FROM $table_verb WHERE verb_id = %s", $verb['id']
	) );

	if ( is_null( $verb_id ) ) {
		$wpdb->insert(
			$table_verb,
			array(
				'verb_id' => $verb['id'],
				'verb_display' => $verb['display']
			)
		);
		$verb_id = $wpdb->insert_id;
	}

	// TODO: Refactor & error handling
	$object_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT id FROM $table_object WHERE xobject_id = %s", $object['id']
	) );

	if ( is_null( $object_id ) ) {
		$wpdb->insert(
			$table_object,
			array(
				'xobject_id' => $object['id'],
				'object_name' => $object['name'],
				'object_description' => $object['description'],
				'object_choices' => $object['choices'],
				'object_correct_responses_pattern' => $object['correctResponsesPattern']
			)
		);
		$object_id = $wpdb->insert_id;
	}

	// TODO: Refactor & error handling
	$wpdb->insert(
		$table_result,
		array(
			'result_response' => $result['response'],
      'result_score_raw' => $result['score_raw'],
      'result_score_scaled' => $result['score_scaled'],
      'result_completion' => $result['completion'],
      'result_success' => $result['success'],
      'result_duration' => $result['duration']
		)
	);
	$result_id = $wpdb->insert_id;

	$xapi = str_replace('\"', '"', $xapi);
	$json = json_decode($xapi);

	$options = get_option('h5pxapikatchu_option', false);
	if ( !isset( $options['store_complete_xapi'] ) ) {
		$xapi = null;
	}

	// TODO: Remove obsolete entries and update display.php

	// There must be a smarter way to do this ...
  $wpdb->insert(
		$table_name,
		array (
			'id_actor' => $actor_id,
			'id_verb' => $verb_id,
			'id_object' => $object_id,
			'id_result' => $result_id,
			'actor_object_type' => isset ( $json->actor->objectType ) ? shape_xAPI_field( $json->actor->objectType ) : NULL,
			'actor_name' => isset( $json->actor->name ) ? shape_xAPI_field( $json->actor->name ) : NULL,
			'actor_mbox' => isset( $json->actor->mbox ) ? shape_xAPI_field( $json->actor->mbox ) : NULL,
			'actor_account_homepage' => isset( $json->actor->account->homepage ) ? shape_xAPI_field( $json->actor->account->homepage ) : NULL,
			'actor_account_name' => isset( $json->actor->account->name ) ? shape_xAPI_field( $json->actor->account->name ) : NULL,
			'verb_id' => isset( $json->verb->id ) ? shape_xAPI_field( $json->verb->id ) : NULL,
			'verb_display' => isset( $json->verb->display ) ? shape_xAPI_field( $json->verb->display, true) : NULL,
			'xobject_id' => isset( $json->object->id ) ? shape_xAPI_field( $json->object->id ) : NULL,
			'object_definition_name' => isset( $json->object->definition->name ) ? shape_xAPI_field( $json->object->definition->name, true) : NULL,
			'object_definition_description' => isset($json->object->definition->description) ? shape_xAPI_field( $json->object->definition->description, true ) : NULL,
			'object_definition_choices' => isset( $json->object->definition->choices ) ? shape_xAPI_field( $json->object->definition->choices ) : NULL,
			'object_definition_correctResponsesPattern' => isset ( $json->object->definition->correctResponsesPattern ) ? shape_xAPI_field( $json->object->definition->correctResponsesPattern ) : NULL,
			'result_response' => isset( $json->result->response) ? shape_xAPI_field( $json->result->response ) : NULL,
			'result_score_raw' => isset( $json->result->score->raw) ? shape_xAPI_field( $json->result->score->raw ) : NULL,
			'result_score_scaled' => isset( $json->result->score->scaled ) ? shape_xAPI_field( $json->result->score->scaled ) : NULL,
			'result_completion' => isset( $json->result->completion ) ? shape_xAPI_field( $json->result->completion ) : NULL,
			'result_success' => isset( $json->result->success ) ? shape_xAPI_field( $json->result->success ) : NULL,
			'result_duration' => isset( $json->result->duration ) ? shape_xAPI_field( $json->result->duration ) : NULL,
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
	$options = new Options;
	$display = new Display;
}
