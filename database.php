<?php

namespace H5PXAPIKATCHU;

/**
 * Database stuff
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class Database {

  private static $L10N_SLUG = 'H5PXAPIKATCHU';
  private static $TABLE_MAIN;
  private static $TABLE_ACTOR;
  private static $TABLE_VERB;
  private static $TABLE_OBJECT;
  private static $TABLE_RESULT;
  private static $TABLE_H5P_CONTENT_TYPES;
  private static $TABLE_H5P_LIBRARIES;

  // TODO: See below, could get rid of this public var.
  public static $COLUMN_TITLE_NAMES;

  /**
   * Build the tables of the plugin.
   */
  public static function build_tables () {
    global $wpdb;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $charset_collate = $wpdb->get_charset_collate();

    // naming a row object_id will cause trouble!
    $sql = "CREATE TABLE " . self::$TABLE_MAIN  ." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      id_actor mediumint(9),
      id_verb mediumint(9),
      id_object mediumint(9),
      id_result mediumint(9),
      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      xapi text,
      PRIMARY KEY (id)
    ) $charset_collate;";
    $ok = dbDelta( $sql );

    $sql = "CREATE TABLE " . self::$TABLE_ACTOR . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      actor_id TEXT,
      actor_name TEXT,
      actor_members TEXT,
      PRIMARY KEY (id)
    ) $charset_collate;";
    $ok = dbDelta( $sql );

    $sql = "CREATE TABLE " . self::$TABLE_VERB . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      verb_id TEXT,
      verb_display TEXT,
      PRIMARY KEY (id)
    ) $charset_collate;";
    $ok = dbDelta( $sql );

    $sql = "CREATE TABLE " . self::$TABLE_OBJECT . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      xobject_id TEXT,
      object_name TEXT,
      object_description TEXT,
      object_choices TEXT,
      object_correct_responses_pattern TEXT,
      PRIMARY KEY (id)
    ) $charset_collate;";
    $ok = dbDelta( $sql );

    $sql = "CREATE TABLE " . self::$TABLE_RESULT . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      result_response TEXT,
      result_score_raw INT,
      result_score_scaled FLOAT,
      result_completion BOOLEAN,
      result_success BOOLEAN,
      result_duration VARCHAR(20),
      PRIMARY KEY (id)
    ) $charset_collate;";
    $ok = dbDelta( $sql );
  }

  /**
   * Delete all tables of the plugin.
   */
  public static function delete_tables () {
    global $wpdb;

    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_MAIN );
    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_ACTOR );
    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_VERB );
    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_OBJECT );
    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_RESULT );
  }

  /**
   * Get column titles of all tables + additional columns.
   * This function seems weird, but we possibly want to make the data
   * structure and the retrieval process more flexible in the future.
   * @return {Array} Database column titles.
   */
  public static function get_column_titles () {
    global $wpdb;

    return array_merge(
      array_slice( $wpdb->get_col( "DESCRIBE " . self::$TABLE_ACTOR, 0 ), 1 ),
      array_slice( $wpdb->get_col( "DESCRIBE " . self::$TABLE_VERB, 0 ), 1 ),
      array_slice( $wpdb->get_col( "DESCRIBE " . self::$TABLE_OBJECT, 0 ), 1 ),
      array_slice( $wpdb->get_col( "DESCRIBE " . self::$TABLE_RESULT, 0 ), 1 ),
      array( 'time' ),
      array( 'xapi' )
    );
  }

  /**
   * Get complete overview of all stored data.
   * @return {object} Database results.
   */
  public static function get_complete_table () {
    global $wpdb;

    return $wpdb->get_results(
      "
        SELECT
        	  act.actor_id, act.actor_name, act.actor_members,
            ver.verb_id, ver.verb_display,
            obj.xobject_id, obj.object_name, obj.object_description, obj.object_choices, obj.object_correct_responses_pattern,
            res.result_response, res.result_score_raw, res.result_score_scaled, res.result_completion, res.result_success, res.result_duration,
            mst.time, mst.xapi
        FROM
        	  " . self::$TABLE_MAIN . " as mst,
            " . self::$TABLE_ACTOR . " as act,
            " . self::$TABLE_VERB . " as ver,
            " . self::$TABLE_OBJECT . " as obj,
            " . self::$TABLE_RESULT . " as res
        WHERE
        	  mst.id_actor = act.id AND
            mst.id_verb = ver.id AND
            mst.id_object = obj.id AND
            mst.id_result = res.id
        ORDER BY
            mst.time DESC
      "
    );
  }

  /**
   * Get a list of all H5P content types in the database.
   * @return {Array} Database results.
   */
  public static function get_h5p_content_types () {
    global $wpdb;

    // Stop if H5P doesn't seem to be installed, checked via two database tables.
    $ok = $wpdb->get_results(
      "SHOW TABLES LIKE '" . self::$TABLE_H5P_CONTENT_TYPES . "'"
    );
    if ( sizeof($ok) === 0 ) {
      return;
    }
    $ok = $wpdb->get_results(
      "SHOW TABLES LIKE '" . self::$TABLE_H5P_LIBRARIES . "'"
    );
    if ( sizeof($ok) === 0 ) {
      return;
    }

    // Get ID, title and library name
    $content_types = $wpdb->get_results(
      "
        SELECT
            CT.id AS ct_id, CT.title AS ct_title, LIB.name AS lib_name
        FROM
            " . self::$TABLE_H5P_CONTENT_TYPES . " AS CT,
            " . self::$TABLE_H5P_LIBRARIES . " AS LIB
        WHERE
            CT.library_id = LIB.id
      "
    );

    return json_decode( json_encode( $content_types ), true );
  }

  /**
   * Insert data into all the database tables and create lookup table.
   * @param {Array} $actor - Actor data.
   * @param {Array} $verb - Verb data.
   * @param {Array} $object - Object data.
   * @param {Array} $result - Result data.
   * @param {String} $xapi - Original xapi data.
   */
  public static function insert_data ( $actor, $verb, $object, $result, $xapi ) {
    // TODO: Error handling for not OK
    // TODO: Error handling for single tables (atomicity???)
    $ok = self::insert_main(
      self::insert_actor( $actor ),
      self::insert_verb( $verb ),
      self::insert_object( $object ),
      self::insert_result( $result ),
      $xapi
    );
  }

  /**
   * Insert data into lookup table.
   * @param {int} $actor_id - Actor ID.
   * @param {int} $verb_id - Verb ID.
   * @param {int} $object_id - Object ID.
   * @param {int} $result_id - Result ID.
   * @param {String} $xapi - Original xAPI data.
   * @param {true|null} True if ok, null else
   */
  private static function insert_main ( $actor_id, $verb_id, $object_id, $result_id, $xapi ) {
    global $wpdb;

    $ok = $wpdb->insert(
      self::$TABLE_MAIN,
      array (
        'id_actor' => $actor_id,
        'id_verb' => $verb_id,
        'id_object' => $object_id,
        'id_result' => $result_id,
        'time' => current_time( 'mysql' ),
        'xapi' => $xapi
      )
    );
    return ( $ok === true ) ? $ok : null;
  }

  /**
   * Insert actor data into database.
   * @param {Array} $actor - Actor data.
   * @return {int} database table index.
   */
  private static function insert_actor ( $actor ) {
    global $wpdb;

    // Check if entry already exists and return index accordingly.
    $actor_id = $wpdb->get_var( $wpdb->prepare(
  		"SELECT id FROM " . self::$TABLE_ACTOR . " WHERE actor_id = %s", $actor['inverseFunctionalIdentifier']
  	) );

  	if ( is_null( $actor_id ) ) {
  		$ok = $wpdb->insert(
  			self::$TABLE_ACTOR,
  			array(
  				'actor_id' => $actor['inverseFunctionalIdentifier'],
  				'actor_name' => $actor['name'],
  				'actor_members' => $actor['members']
  			)
  		);
      if ( $ok === true ) {
  		    $actor_id = $wpdb->insert_id;
      }
  	}
    return $actor_id;
  }

  /**
   * Insert verb data into database.
   * @param {Array} $verb - Verb data.
   * @return {int} database table index.
   */
  private static function insert_verb ( $verb ) {
    global $wpdb;

    // Check if entry already exists and return index accordingly.
    $verb_id = $wpdb->get_var( $wpdb->prepare(
  		"SELECT id FROM " . self::$TABLE_VERB . " WHERE verb_id = %s", $verb['id']
  	) );

  	if ( is_null( $verb_id ) ) {
  		$ok = $wpdb->insert(
  			self::$TABLE_VERB,
  			array(
  				'verb_id' => $verb['id'],
  				'verb_display' => $verb['display']
  			)
  		);
      if ( $ok === true ) {
  		    $verb_id = $wpdb->insert_id;
      }
  	}
    return $verb_id;
  }

  /**
   * Insert object data into database.
   * @param {Array} $object - Object data.
   * @return {int} database table index.
   */
  private static function insert_object ( $object ) {
    global $wpdb;

    // Check if entry already exists and return index accordingly.
  	$object_id = $wpdb->get_var( $wpdb->prepare(
  		"SELECT id FROM	" . self::$TABLE_OBJECT . " WHERE
  				xobject_id = %s AND
  				object_name = %s AND
  				object_description = %s AND
  				object_choices = %s AND
  				object_correct_responses_pattern = %s
  		",
  		$object['id'],
  		$object['name'],
  		$object['description'],
  		$object['choices'],
  		$object['correctResponsesPattern']
  	) );

  	if ( is_null( $object_id ) ) {
  		$ok = $wpdb->insert(
  			self::$TABLE_OBJECT,
  			array(
  				'xobject_id' => $object['id'],
  				'object_name' => $object['name'],
  				'object_description' => $object['description'],
  				'object_choices' => $object['choices'],
  				'object_correct_responses_pattern' => $object['correctResponsesPattern']
  			)
  		);
      if ( $ok === true ) {
  		    $object_id = $wpdb->insert_id;
      }
  	}
    return $object_id;
  }

  /**
   * Insert result data into database.
   * @param {Array} $result - Result data.
   * @return {int} database table index.
   */
  private static function insert_result ( $result ) {
    global $wpdb;

    $ok = $wpdb->insert(
      self::$TABLE_RESULT,
      array(
        'result_response' => $result['response'],
        'result_score_raw' => $result['score_raw'],
        'result_score_scaled' => $result['score_scaled'],
        'result_completion' => $result['completion'],
        'result_success' => $result['success'],
        'result_duration' => $result['duration']
      )
    );
    return ( $ok === true ) ? $wpdb->insert_id : null;
  }

  /**
   * Initialize class variables/constants
   */
  static function init() {
	  global $wpdb;
    self::$TABLE_MAIN = $wpdb->prefix . 'h5pxapikatchu';
    self::$TABLE_ACTOR = $wpdb->prefix . 'h5pxapikatchu_actor';
    self::$TABLE_VERB = $wpdb->prefix . 'h5pxapikatchu_verb';
    self::$TABLE_OBJECT = $wpdb->prefix . 'h5pxapikatchu_object';
    self::$TABLE_RESULT = $wpdb->prefix . 'h5pxapikatchu_result';
    self::$TABLE_H5P_CONTENT_TYPES = $wpdb->prefix . 'h5p_contents';
    self::$TABLE_H5P_LIBRARIES = $wpdb->prefix . 'h5p_libraries';

    // TODO: Think about ditching this since we now have a fixed set.
    self::$COLUMN_TITLE_NAMES = array(
      'id' => 'ID',
      'actor_id' => __( 'Actor Id', self::$L10N_SLUG),
      'actor_name' => __( 'Actor Name', self::$L10N_SLUG),
      'actor_members' => __( 'Actor Group Members', self::$L10N_SLUG),
      'verb_id' => __( 'Verb Id', self::$L10N_SLUG),
      'verb_display' => __( 'Verb Display', self::$L10N_SLUG),
      'xobject_id' => __( 'Object Id', self::$L10N_SLUG),
      'object_name' => __( 'Object Def. Name', self::$L10N_SLUG ),
      'object_description' => __( 'Object Def. Description', self::$L10N_SLUG ),
      'object_choices' => __( 'Object Def. Choices', self::$L10N_SLUG ),
      'object_correct_responses_pattern' => __( 'Object Def. Correct Responses', self::$L10N_SLUG ),
      'result_response' => __( 'Result Response', self::$L10N_SLUG ),
      'result_score_raw' => __( 'Result Score Raw', self::$L10N_SLUG ),
      'result_score_scaled' => __( 'Result Score Scaled', self::$L10N_SLUG ),
      'result_completion' => __( 'Result Completion', self::$L10N_SLUG ),
      'result_success' => __( 'Result Success', self::$L10N_SLUG ),
      'result_duration' => __( 'Result Duration', self::$L10N_SLUG ),
      'time' => __( 'Time', self::$L10N_SLUG ),
      'xapi' => __( 'xAPI', self::$L10N_SLUG ),
    );
  }
}
Database::init();
