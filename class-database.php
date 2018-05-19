<?php

namespace H5PXAPIKATCHU;

/**
 * Database stuff
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class Database {
	private static $TABLE_MAIN;
	private static $TABLE_ACTOR;
	private static $TABLE_VERB;
	private static $TABLE_OBJECT;
	private static $TABLE_RESULT;
	private static $TABLE_H5P_CONTENT_TYPES;
	private static $TABLE_H5P_LIBRARIES;

	// Those might become handy if we make make the SELECTs flexible.
	public static $COLUMN_TITLE_NAMES;

	/**
	 * Build the tables of the plugin.
	 */
	public static function build_tables() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();

		// naming a row object_id will cause trouble!
		$sql = "CREATE TABLE " . self::$TABLE_MAIN  ." (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			id_actor MEDIUMINT(9),
			id_verb MEDIUMINT(9),
			id_object MEDIUMINT(9),
			id_result MEDIUMINT(9),
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			xapi TEXT,
			PRIMARY KEY (id)
		) $charset_collate;";
		$ok = dbDelta( $sql );

		$sql = "CREATE TABLE " . self::$TABLE_ACTOR . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			actor_id TEXT,
			actor_name TEXT,
			actor_members TEXT,
			wp_user_id BIGINT(20),
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
			h5p_content_id INT(10),
			h5p_subcontent_id VARCHAR(36)
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

		$filled = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM " . self::$TABLE_ACTOR . " WHERE id = 1", null
		) );

		if ( !isset( $filled ) ) {
			$ok = $wpdb->insert(
				self::$TABLE_RESULT,
				array(
					'id' => 1,
					'result_response' => NULL,
					'result_score_raw' => NULL,
					'result_score_scaled' => NULL,
					'result_completion' => false,
					'result_success' => false,
					'result_duration' => NULL
				)
			);
		}
	}

	/**
	 * Delete all tables of the plugin.
	 */
	public static function delete_tables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_MAIN );
		$wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_ACTOR );
		$wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_VERB );
		$wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_OBJECT );
		$wpdb->query( "DROP TABLE IF EXISTS " . self::$TABLE_RESULT );
	}

	/**
	 * Delete all irrelevant data, but leave the tables.
	 */
	public static function delete_data() {
		global $wpdb;

		$error_count = 0;
		$ok = $wpdb->query( "START TRANSACTION" );

		$ok = $wpdb->query( "TRUNCATE TABLE " . self::$TABLE_ACTOR );
		if ( false === $ok ) {
			$error_count++;
		}

		$ok = $wpdb->query( "TRUNCATE TABLE " . self::$TABLE_VERB );
		if ( false === $ok ) {
			$error_count++;
		}

		$ok = $wpdb->query( "TRUNCATE TABLE " . self::$TABLE_OBJECT );
		if ( false === $ok ) {
			$error_count++;
		}

		$ok = $wpdb->query( "TRUNCATE TABLE " . self::$TABLE_RESULT );
		if ( false === $ok ) {
			$error_count++;
		}

		$ok = $wpdb->insert(
			self::$TABLE_RESULT,
			array(
				'id' => 1,
				'result_response' => NULL,
				'result_score_raw' => NULL,
				'result_score_scaled' => NULL,
				'result_completion' => false,
				'result_success' => false,
				'result_duration' => NULL
			)
		);
		if ( false === $ok ) {
			$error_count++;
		}

		$ok = $wpdb->query( "TRUNCATE TABLE " . self::$TABLE_MAIN );
		if ( false === $ok ) {
			$error_count++;
		}

		if ( 0 !== $error_count ) {
			$ok = $wpdb->query( "ROLLBACK" );
			return 'error';
		}

		$ok = $wpdb->query( "COMMIT" );
		return 'done';
	}

	/**
	 * Get column titles of all tables + additional columns.
	 * This function seems weird, but we possibly want to make the data
	 * structure and the retrieval process more flexible in the future.
	 * @return array Database column titles.
	 */
	public static function get_column_titles() {
		global $wpdb;

		return array(
			'actor_id', 'actor_name', 'actor_members',
			'verb_id', 'verb_display',
			'xobject_id', 'object_name', 'object_description', 'object_choices', 'object_correct_responses_pattern',
			'result_response', 'result_score_raw', 'result_score_scaled', 'result_completion', 'result_success', 'result_duration',
			'time', 'xapi', 'wp_user_id', 'h5p_content_id', 'h5p_subcontent_id'
		);
	}

	/**
	 * Get all data for one user.
	 * @return object Database results.
	 */
 public static function get_user_table( $wpid ) {
	 global $wpdb;

	 return $wpdb->get_results( $wpdb->prepare(
		 "
		 SELECT
			 mst.id,
			 act.actor_id, act.actor_name, act.actor_members,
			 ver.verb_id, ver.verb_display,
			 obj.xobject_id, obj.object_name, obj.object_description, obj.object_choices, obj.object_correct_responses_pattern,
			 res.result_response, res.result_score_raw, res.result_score_scaled, res.result_completion, res.result_success, res.result_duration,
			 mst.time, mst.xapi,
			 act.wp_user_id, obj.h5p_content_id, obj.h5p_subcontent_id
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
			 mst.id_result = res.id AND
			 act.wp_user_id = %d
		 ",
		 $wpid
	 ));
 }

	/**
	 * Get complete overview of all stored data.
	 * @return object Database results.
	 */
	public static function get_complete_table() {
		global $wpdb;

		return $wpdb->get_results(
			"
			SELECT
				act.actor_id, act.actor_name, act.actor_members,
				ver.verb_id, ver.verb_display,
				obj.xobject_id, obj.object_name, obj.object_description, obj.object_choices, obj.object_correct_responses_pattern,
				res.result_response, res.result_score_raw, res.result_score_scaled, res.result_completion, res.result_success, res.result_duration,
				mst.time, mst.xapi,
				act.wp_user_id, obj.h5p_content_id, obj.h5p_subcontent_id
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
	 * @return array Database results.
	 */
	public static function get_h5p_content_types() {
		global $wpdb;

		// Stop if H5P doesn't seem to be installed, checked via two database tables.
		$ok = $wpdb->get_results(
			"SHOW TABLES LIKE '" . self::$TABLE_H5P_CONTENT_TYPES . "'"
		);
		if ( 0 === sizeof($ok) ) {
			return;
		}
		$ok = $wpdb->get_results(
			"SHOW TABLES LIKE '" . self::$TABLE_H5P_LIBRARIES . "'"
		);
		if ( 0 === sizeof($ok) ) {
			return;
		}

		// Get ID, title and library name
		$content_types = $wpdb->get_results(
			"
			SELECT
				CT.id AS ct_id, CT.title AS ct_title, LIB.title AS lib_title
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
	 * @param array $actor Actor data.
	 * @param array $verb Verb data.
	 * @param array $object Object data.
	 * @param array $result Result data.
	 * @param string $xapi Original xapi data.
	 * @return true|false False on error within database transactions.
	 */
	public static function insert_data( $actor, $verb, $object, $result, $xapi ) {
		global $wpdb;

		$error_count = 0;
		$ok = $wpdb->query( "START TRANSACTION" );

		$actor_id = self::insert_actor( $actor );
		if ( $actor_id === false ) {
			$error_count++;
		}

		$verb_id = self::insert_verb( $verb );
		if ( false === $verb_id ) {
			$error_count++;
		}

		$object_id = self::insert_object( $object );
		if ( false === $object_id ) {
			$error_count++;
		}

		$result_id = self::insert_result( $result );
		if ( false === $result_id ) {
			$error_count++;
		}

		$ok = self::insert_main(
			$actor_id,
			$verb_id,
			$object_id,
			$result_id,
			$xapi
		);
		if ( false === $ok ) {
			$error_count++;
		}

		if ( 0 !== $error_count ) {
			$ok = $wpdb->query( "ROLLBACK" );
			return false;
		}

		$ok = $wpdb->query( "COMMIT" );
		return true;
	}

	/**
	 * Insert data into lookup table.
	 * @param int $actor_id Actor ID.
	 * @param int $verb_id Verb ID.
	 * @param int $object_id Object ID.
	 * @param int $result_id Result ID.
	 * @param string $xapi Original xAPI data.
	 * @param true|null True if ok, null else
	 */
	private static function insert_main( $actor_id, $verb_id, $object_id, $result_id, $xapi ) {
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
		return ( false === $ok ) ? false : true; // {int|false}
	}

	/**
	 * Insert actor data into database.
	 * @param array $actor Actor data.
	 * @return int Database table index.
	 */
	private static function insert_actor( $actor ) {
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
					'actor_members' => $actor['members'],
					'wp_user_id' => $actor['wpUserId']
				)
			);
			$actor_id = ( 1 === $ok ) ? $wpdb->insert_id : false;
		}
		return $actor_id;
	}

	/**
	 * Insert verb data into database.
	 * @param array $verb Verb data.
	 * @return int Database table index.
	 */
	private static function insert_verb( $verb ) {
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
			$verb_id = ( 1 === $ok ) ? $wpdb->insert_id : false;
		}
		return $verb_id;
	}

	/**
	 * Insert object data into database.
	 * @param array $object Object data.
	 * @return int Database table index.
	 */
	private static function insert_object( $object ) {
		global $wpdb;

		// Check if entry already exists and return index accordingly.
		$object_id = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT
				id
			FROM
				" . self::$TABLE_OBJECT . "
			WHERE
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
					'object_correct_responses_pattern' => $object['correctResponsesPattern'],
					'h5p_content_id' => $object['h5pContentId'],
					'h5p_subcontent_id' => $object['h5pSubContentId']
				)
			);
			$object_id = ( 1 === $ok ) ? $wpdb->insert_id : false;
		}
		return $object_id;
	}

	/**
	 * Insert result data into database.
	 * @param array $result Result data.
	 * @return int Database table index.
	 */
	private static function insert_result( $result ) {
		global $wpdb;

		// Check if entry already exists and return index accordingly.
		$result_id = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT
				id
			FROM
				" . self::$TABLE_RESULT . "
			WHERE
				result_response = %s AND
				result_score_raw = %s AND
				result_score_scaled = %s AND
				result_completion = %d AND
				result_success = %d AND
				result_duration = %s
			",
			$result['response'],
			$result['score_raw'],
			$result['score_scaled'],
			$result['completion'],
			$result['success'],
			$result['duration']
		) );

		// Common type: xAPI statement without a result. Rerouted to default entry
		if ( is_null( $result['response'] ) ) {
			$result_id = 1;
		}

		if ( is_null( $result_id ) ) {
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
			$result_id = ( false !== $ok ) ? $wpdb->insert_id : false;
		}
		return $result_id;
	}

	/**
	 * Complete missing WordPress User ID for old data.
	 * Just needed for the update from 0.1.3 to 0.2.0
	 */
	public static function complete_wp_user_id() {
		global $wpdb;

		// Get actor ids that are based on email addresses
		$actor_ids = $wpdb->get_results(
			"
			SELECT
				actor_id
			FROM
				" . self::$TABLE_ACTOR . "
			WHERE
				wp_user_id IS NULL
				AND
				actor_id LIKE 'email:%'
			"
		);

		foreach( $actor_ids as $id ) {
			// Get email address
			$email = str_replace( ' ', '', substr( $id->actor_id, 6 ) );
			if ( substr( $email, 0, 7 ) === 'mailto:' ) {
				$email = substr( $email, 7 );
			}
			// Update fields if user id exists for email address
			$wp_user_id = get_user_by( 'email', $email );
			if ( $wp_user_id !== FALSE ) {
				$wpdb->query( $wpdb->prepare(
					"
					UPDATE
						" . self::$TABLE_ACTOR . "
					SET
						wp_user_id = " . $wp_user_id->ID . "
					WHERE
						actor_id = %s
					",
					$id->actor_id
				) );
			}
		}

		// Fill up with 0
		$wpdb->query(
			"
			UPDATE
				" . self::$TABLE_ACTOR . "
			SET
				wp_user_id = 0
			WHERE
				wp_user_id IS NULL
			"
		);
	}

	/**
	 * Complete missing content ids and subcontent_ids for old data.
	 * Just needed for the update from 0.1.3 to 0.2.0
	 */
	public static function complete_content_id_subcontent_id() {
		global $wpdb;

		// Get object_ids that have not been updated
		$object_ids = $wpdb->get_results(
			"
			SELECT
				xobject_id
			FROM
				" . self::$TABLE_OBJECT . "
			WHERE
				h5p_content_id IS NULL
			"
		);

		foreach( $object_ids as $id ) {
			// Extract Ids
			preg_match( "/[&|?]id=([0-9]+)/", $id->xobject_id, $matches );
			$h5p_content_id = ( sizeof( $matches ) > 0 ) ? $matches[1] : null;
			preg_match( "/[&|?]subContentId=([0-9a-f-]{36})/", $id->xobject_id, $matches );
			$h5p_subcontent_id = ( sizeof( $matches ) > 0 ) ? $matches[1] : null;

			// Update if something new was found
			if ( ( ! is_null( $h5p_content_id ) ) || ( ! is_null( $h5p_subcontent_id ) ) ) {
				$wpdb->query( $wpdb->prepare(
					"
					UPDATE
						" . self::$TABLE_OBJECT . "
					SET
						h5p_content_id = %s,
						h5p_subcontent_id = %s
					WHERE
						xobject_id = %s
					",
					$h5p_content_id,
					$h5p_subcontent_id,
					$id->xobject_id
				) );
			}
		}
	}

	/**
	 * Set the names for columns inlcuding translations.
	 */
	static function set_column_names() {
		// Those might become handy if we make make the SELECTs flexible.
		self::$COLUMN_TITLE_NAMES = array(
			'id' => 'ID',
			'actor_id' => __( 'Actor Id', 'H5PXAPIKATCHU' ),
			'actor_name' => __( 'Actor Name', 'H5PXAPIKATCHU' ),
			'actor_members' => __( 'Actor Group Members', 'H5PXAPIKATCHU' ),
			'verb_id' => __( 'Verb Id', 'H5PXAPIKATCHU' ),
			'verb_display' => __( 'Verb Display', 'H5PXAPIKATCHU' ),
			'xobject_id' => __( 'Object Id', 'H5PXAPIKATCHU' ),
			'object_name' => __( 'Object Def. Name', 'H5PXAPIKATCHU' ),
			'object_description' => __( 'Object Def. Description', 'H5PXAPIKATCHU' ),
			'object_choices' => __( 'Object Def. Choices', 'H5PXAPIKATCHU' ),
			'object_correct_responses_pattern' => __( 'Object Def. Correct Responses', 'H5PXAPIKATCHU' ),
			'result_response' => __( 'Result Response', 'H5PXAPIKATCHU' ),
			'result_score_raw' => __( 'Result Score Raw', 'H5PXAPIKATCHU' ),
			'result_score_scaled' => __( 'Result Score Scaled', 'H5PXAPIKATCHU' ),
			'result_completion' => __( 'Result Completion', 'H5PXAPIKATCHU' ),
			'result_success' => __( 'Result Success', 'H5PXAPIKATCHU' ),
			'result_duration' => __( 'Result Duration', 'H5PXAPIKATCHU' ),
			'time' => __( 'Time', 'H5PXAPIKATCHU' ),
			'xapi' => __( 'xAPI', 'H5PXAPIKATCHU' ),
			'wp_user_id' => __( 'WP User ID', 'H5PXAPIKATCHU' ),
			'h5p_content_id' => __( 'H5P Content ID', 'H5PXAPIKATCHU' ),
			'h5p_subcontent_id' => __( 'H5P Subcontent ID', 'H5PXAPIKATCHU' )
		);
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
	}
}
Database::init();
// This is neccessary for the translation to work from within an array.
add_action( 'admin_init', 'H5PXAPIKATCHU\Database::set_column_names' );
