<?php

namespace H5PXAPIKATCHU;

/**
 * Database stuff
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class Database {

  public static $COLUMN_TITLES;
  public static $TABLE_MAIN;
  public static $TABLE_ACTOR;
  public static $TABLE_VERB;
  public static $TABLE_OBJECT;
  public static $TABLE_RESULT;
  public static $TABLE_H5P_CONTENT_TYPES;
  public static $TABLE_H5P_LIBRARIES;

  public static function build_table() {
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

  public static function delete_table () {
    global $wpdb;

    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_MAIN );
    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_ACTOR );
    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_VERB );
    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_OBJECT );
    $wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_RESULT );
  }

  public static function get_column_titles () {
    global $wpdb;

    return array_merge(
      array_slice($wpdb->get_col("DESCRIBE " . self::$TABLE_ACTOR, 0), 1),
      array_slice($wpdb->get_col("DESCRIBE " . self::$TABLE_VERB, 0), 1),
      array_slice($wpdb->get_col("DESCRIBE " . self::$TABLE_OBJECT, 0), 1),
      array_slice($wpdb->get_col("DESCRIBE " . self::$TABLE_RESULT, 0), 1),
      array('time'),
      array('xapi')
    );
  }

  public static function get_complete_table () {
    global $wpdb;

    // TODO: build SELECT part dynamically based on future options for columns
    // TODO: Unuglify :-)

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

  public static function get_h5p_content_types () {
    global $wpdb;

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

    $content_types = $wpdb->get_results(
      "
        SELECT CT.id AS ct_id, CT.title AS ct_title, LIB.name AS lib_name
        FROM " . self::$TABLE_H5P_CONTENT_TYPES . " AS CT, " . self::$TABLE_H5P_LIBRARIES . " AS LIB
        WHERE CT.library_id = LIB.id
      "
    );
    return json_decode( json_encode( $content_types ), true );
  }

  public static function insert_data ( $actor, $verb, $object, $result, $xapi ) {
    self::insert_main(
      self::insert_actor( $actor ),
      self::insert_verb( $verb ),
      self::insert_object( $object ),
      self::insert_result( $result ),
      $xapi
    );
  }

  // TODO: Error handling
  private static function insert_main ( $actor_id, $verb_id, $object_id, $result_id, $xapi ) {
    global $wpdb;

    $wpdb->insert(
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
  }

  // TODO: Refactor & error handling
  private static function insert_actor ( $actor ) {
    global $wpdb;

    $actor_id = $wpdb->get_var( $wpdb->prepare(
  		"SELECT id FROM " . self::$TABLE_ACTOR . " WHERE actor_id = %s", $actor['inverseFunctionalIdentifier']
  	) );

  	if ( is_null( $actor_id ) ) {
  		$wpdb->insert(
  			self::$TABLE_ACTOR,
  			array(
  				'actor_id' => $actor['inverseFunctionalIdentifier'],
  				'actor_name' => $actor['name'],
  				'actor_members' => $actor['members']
  			)
  		);
  		$actor_id = $wpdb->insert_id;
  	}
    return $actor_id;
  }

  // TODO: Refactor & error handling
  private static function insert_verb ( $verb ) {
    global $wpdb;

    $verb_id = $wpdb->get_var( $wpdb->prepare(
  		"SELECT id FROM " . self::$TABLE_VERB . " WHERE verb_id = %s", $verb['id']
  	) );

  	if ( is_null( $verb_id ) ) {
  		$wpdb->insert(
  			self::$TABLE_VERB,
  			array(
  				'verb_id' => $verb['id'],
  				'verb_display' => $verb['display']
  			)
  		);
  		$verb_id = $wpdb->insert_id;
  	}
    return $verb_id;
  }

  // TODO: Refactor & error handling
  private static function insert_object ( $object ) {
    global $wpdb;

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
  		$wpdb->insert(
  			self::$TABLE_OBJECT,
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
    return $object_id;
  }

  // TODO: Refactor & error handling
  private static function insert_result ( $result ) {
    global $wpdb;

    $wpdb->insert(
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
    return $wpdb->insert_id;
  }


  static function init() {
	  global $wpdb;
    self::$TABLE_MAIN = $wpdb->prefix . 'h5pxapikatchu';
    self::$TABLE_ACTOR = $wpdb->prefix . 'h5pxapikatchu_actor';
    self::$TABLE_VERB = $wpdb->prefix . 'h5pxapikatchu_verb';
    self::$TABLE_OBJECT = $wpdb->prefix . 'h5pxapikatchu_object';
    self::$TABLE_RESULT = $wpdb->prefix . 'h5pxapikatchu_result';
    self::$TABLE_H5P_CONTENT_TYPES = $wpdb->prefix . 'h5p_contents';
    self::$TABLE_H5P_LIBRARIES = $wpdb->prefix . 'h5p_libraries';


    self::$COLUMN_TITLES = array(
      'id' => 'ID',
      'actor_id' => __( 'Actor Id', 'H5PxAPIkatchu'),
      'actor_name' => __( 'Actor Name', 'H5PxAPIkatchu'),
      'actor_members' => __( 'Actor Group Members', 'H5PxAPIkatchu'),
      'verb_id' => __( 'Verb Id', 'H5PxAPIkatchu'),
      'verb_display' => __( 'Verb Display', 'H5PxAPIkatchu'),
      'xobject_id' => __( 'Object Id', 'H5PxAPIkatchu'),
      'object_name' => __( 'Object Def. Name', 'H5PxAPIkatchu' ),
      'object_description' => __( 'Object Def. Description', 'H5PxAPIkatchu' ),
      'object_choices' => __( 'Object Def. Choices', 'H5PxAPIkatchu' ),
      'object_correct_responses_pattern' => __( 'Object Def. Correct Responses', 'H5PxAPIkatchu' ),
      'result_response' => __( 'Result Response', 'H5PxAPIkatchu' ),
      'result_score_raw' => __( 'Result Score Raw', 'H5PxAPIkatchu' ),
      'result_score_scaled' => __( 'Result Score Scaled', 'H5PxAPIkatchu' ),
      'result_completion' => __( 'Result Completion', 'H5PxAPIkatchu' ),
      'result_success' => __( 'Result Success', 'H5PxAPIkatchu' ),
      'result_duration' => __( 'Result Duration', 'H5PxAPIkatchu' ),
      'time' => __( 'Time', 'H5PxAPIkatchu' ),
      'xapi' => __( 'xAPI', 'H5PxAPIkatchu' )
    );
  }
}
Database::init();
