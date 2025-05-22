<?php

/**
 * Plugin Name: SNORDIAN's H5PxAPIkatchu
 * Plugin URI: https://github.com/otacke/h5pxapikatchu
 * Text Domain: H5PXAPIKATCHU
 * Domain Path: /languages
 * Description: Catch and store xAPI statements sent by H5P
 * Version: 0.4.16
 * Author: Oliver Tacke
 * Author URI: https://www.olivertacke.de
 * License: MIT
 */

namespace H5PXAPIKATCHU;

// as suggested by the WordPress community
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! defined( 'H5PXAPIKATCHU_VERSION' ) ) {
	define( 'H5PXAPIKATCHU_VERSION', '0.4.16' );
}

// settings.php contains all functions for the settings
require_once( __DIR__ . '/class-database.php' );
require_once( __DIR__ . '/class-options.php' );
require_once( __DIR__ . '/class-table-view.php' );
require_once( __DIR__ . '/class-xapidata.php' );
require_once( __DIR__ . '/class-privacypolicy.php' );

/**
 * Initialize
 */
function init() {
	// Include options
	$h5pxapikatchu_options = new Options;

	// Try to make sure that the configuration is set
	$path = plugin_dir_path( __FILE__ ) . 'js' . '/' . 'h5pxapikatchu-config.js';
	if ( ! file_exists( $path ) ) {
		$config_data = get_option( 'h5pxapikatchu_option' );
		Options::update_config_file( $config_data );
	}

	if ( is_admin() ) {
		$h5pxapikatchu_table_view = new Table_View;
	}
}

/**
 * Activate the plugin.
 */
function on_activation() {
	// Add hook 'h5pxapikatchu_on_activation'
	do_action( 'h5pxapikatchu_on_activation' );

	Database::build_tables();
	Options::set_defaults();
	update_config_file();

	add_capabilities();
}

/**
 * Deactivate the plugin.
 */
function on_deactivation() {
	// Add hook 'h5pxapikatchu_on_deactivation'
	do_action( 'h5pxapikatchu_on_deactivation' );
}

/**
 * Uninstall the plugin.
 */
function on_uninstall() {
	// Add hook 'h5pxapikatchu_on_uninstall'
	do_action( 'h5pxapikatchu_on_uninstall' );

	Database::delete_tables();
	Options::delete_options();

	// Remove capabilities
	global $wp_roles;
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	$all_roles = $wp_roles->roles;
	foreach ( $all_roles as $role_name => $role_info ) {
		$role = get_role( $role_name );

		if ( isset( $role_info['capabilities']['manage_h5pxapikatchu_options'] ) ) {
			$role->remove_cap( 'manage_h5pxapikatchu_options' );
		}
		if ( isset( $role_info['capabilities']['view_h5pxapikatchu_results'] ) ) {
			$role->remove_cap( 'view_h5pxapikatchu_results' );
		}
		if ( isset( $role_info['capabilities']['view_others_h5pxapikatchu_results'] ) ) {
			$role->remove_cap( 'view_others_h5pxapikatchu_results' );
		}
		if ( isset( $role_info['capabilities']['download_h5pxapikatchu_results'] ) ) {
			$role->remove_cap( 'download_h5pxapikatchu_results' );
		}
		if ( isset( $role_info['capabilities']['delete_h5pxapikatchu_results'] ) ) {
			$role->remove_cap( 'delete_h5pxapikatchu_results' );
		}
	}
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
	if ( false === get_option( 'h5pxapikatchu_version' ) || '0.1.3' === get_option( 'h5pxapikatchu_version' ) ) {
		Database::complete_wp_user_id();
		Database::complete_content_id_subcontent_id();

		update_option( 'h5pxapikatchu_version', '0.2.0' );
	}

	// Update from 0.2.x to 0.3.0
	$version = explode( '.', get_option( 'h5pxapikatchu_version' ) );
	if ( '0' === $version[0] && '2' === $version[1] ) {
		// Add defaults for showing/hiding column labels
		Options::set_defaults_columns_visible();

		// From now on remember that defaults have been set already
		update_option( 'h5pxapikatchu_defaults_set', true );

		update_option( 'h5pxapikatchu_version', '0.3.0' );
	}

	// Update from 0.4.0 to 0.4.1
	if ( '0' === $version[0] && '4' === $version[1] && '0' === $version[2] ) {
		add_capabilities();

		update_option( 'h5pxapikatchu_version', '0.4.1' );
	}

	// Update from 0.4.1 through 0.4.7
	if ( '0' === $version[0] && '4' === $version[1] && '1' <= $version[2] && 7 >= $version[2] ) {
		$config_values = array();

		if ( Options::is_debug_enabled() ) {
			$config_values['debug_enabled'] = '1';
		}

		if ( Options::capture_all_h5p_content_types() ) {
			$config_values['capture_all_h5p_content_types'] = '1';
		}

		$config_values['h5p_content_types'] = implode( ',', Options::get_h5p_content_types() );

		Options::update_config_file( $config_values );
		update_option( 'h5pxapikatchu_version', '0.4.8' );
	}

	// Update from 0.4.15 to 0.4.16
	if ( '0' === $version[0] && '4' === $version[1] && '15' === $version[2] ) {
		update_config_file();

		update_option( 'h5pxapikatchu_version', '0.4.16' );
	}

	update_option( 'h5pxapikatchu_version', H5PXAPIKATCHU_VERSION );
}

/**
 * Add default capabilities.
 *
 * @since 0.4.2
 * @param stdClass $role Role object.
 * @param array $role_info Role information.
 * @param string|array $existing_cap Existing capability.
 * @param string $new_cap New capability.
 */
function add_capabilities() {
	// Add capabilities
	global $wp_roles;
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	$all_roles = $wp_roles->roles;
	foreach ( $all_roles as $role_name => $role_info ) {
		$role = get_role( $role_name );

		// Not good default options, but basically keeping behavior as in 0.4.0
		map_capability( $role, $role_info, 'manage_options', 'manage_h5pxapikatchu_options' );
		map_capability( $role, $role_info, 'edit_h5p_contents', 'view_h5pxapikatchu_results' );
		map_capability( $role, $role_info, 'edit_h5p_contents', 'view_others_h5pxapikatchu_results' );
		map_capability( $role, $role_info, 'edit_h5p_contents', 'download_h5pxapikatchu_results' );
		map_capability( $role, $role_info, 'manage_options', 'delete_h5pxapikatchu_results' );
	}
}

/**
 * Make sure that a role has or hasn't the provided capability depending on
 * existing roles.
 *
 * @since 0.4.1
 * @param stdClass $role Role object.
 * @param array $role_info Role information.
 * @param string|array $existing_cap Existing capability.
 * @param string $new_cap New capability.
 */
function map_capability( $role, $role_info, $existing_cap, $new_cap ) {
	if ( isset( $role_info['capabilities'][ $new_cap ] ) ) {
		// Already has new cap…

		if ( ! has_capability( $role_info['capabilities'], $existing_cap ) ) {
			// But shouldn't have it!
			$role->remove_cap( $new_cap );
		}
	} else {
		// Doesn't have new cap…
		if ( has_capability( $role_info['capabilities'], $existing_cap ) ) {
			// But should have it!
			$role->add_cap( $new_cap );
		}
	}
}

/**
 * Check that role has the needed capabilities.
 *
 * @since 0.4.1
 * @param array $role_capabilities Role capabilities.
 * @param string|array $capability Capabilities to check for.
 * @return bool True, if role has capability, else false.
 */
function has_capability( $role_capabilities, $capability ) {
	if ( is_array( $capability ) ) {
		foreach ( $capability as $cap ) {
			if ( ! isset( $role_capabilities[ $cap ] ) ) {
				return false;
			}
		}
	} elseif ( ! isset( $role_capabilities[ $capability ] ) ) {
		return false;
	}
	return true;
}

/**
 * Filter for xAPI actor object.
 *
 * @since 0.4.3
 * @param object actor XAPI actor object.
 * @return object Filtered xAPI actor object.
 */
function filter_insert_data_actor( $actor ) {
	return apply_filters( 'h5pxapikatchu_insert_data_actor', $actor );
}

/**
 * Filter for xAPI verb object.
 *
 * @since 0.4.3
 * @param object verb XAPI verb object.
 * @return object Filtered xAPI verb object.
 */
function filter_insert_data_verb( $verb ) {
	return apply_filters( 'h5pxapikatchu_insert_data_verb', $verb );
}

/**
 * Filter for xAPI object object.
 *
 * @since 0.4.3
 * @param object object XAPI object object.
 * @return object Filtered xAPI object object.
 */
function filter_insert_data_object( $object ) {
	return apply_filters( 'h5pxapikatchu_insert_data_object', $object );
}

/**
 * Filter for xAPI result object.
 *
 * @since 0.4.3
 * @param object result XAPI result object.
 * @return object Filtered xAPI result object.
 */
function filter_insert_data_result( $result ) {
	return apply_filters( 'h5pxapikatchu_insert_data_result', $result );
}

/**
 * Filter for raw xAPI data.
 *
 * @since 0.4.3
 * @param string xapi XAPI string.
 * @return string Filtered xAPI string.
 */
function filter_insert_data_xapi( $xapi ) {
	return apply_filters( 'h5pxapikatchu_insert_data_xapi', $xapi );
}

/**
 * Insert an entry into the database.
 *
 * @param string text Text to be added.
 */
function insert_data() {
	// Add hook 'h5pxapikatchu_insert_data'
	do_action( 'h5pxapikatchu_insert_data' );

	global $wpdb;

	$xapi     = $_REQUEST['xapi'];
	$xapidata = new XAPIDATA( $xapi );

	$actor             = $xapidata->get_actor();
	$actor['wpUserId'] = get_current_user_id();
	$actor['wpUserId'] = ( 0 === $actor['wpUserId'] ) ? null : $actor['wpUserId'];
	$actor             = filter_insert_data_actor( $actor );

	$verb = $xapidata->get_verb();
	$verb = filter_insert_data_verb( $verb );

	$object = $xapidata->get_object();
	preg_match( '/[&|?]id=([0-9]+)/', $object['id'], $matches );
	$object['h5pContentId'] = ( sizeof( $matches ) > 0 ) ? $matches[1] : null;
	preg_match( '/[&|?]subContentId=([0-9a-f-]{36})/', $object['id'], $matches );
	$object['h5pSubContentId'] = ( sizeof( $matches ) > 0 ) ? $matches[1] : null;
	$object                    = filter_insert_data_object( $object );

	$result = $xapidata->get_result();
	$result = filter_insert_data_result( $result );

	if ( Options::store_complete_xapi() ) {
		$xapi = str_replace( '\"', '"', $xapi );
		$xapi = str_replace( "\'", "'", $xapi );
	} else {
		$xapi = null;
	}
	$xapi = filter_insert_data_xapi( $xapi );

	// Add hook 'h5pxapikatchu_insert_data_pre_database'
	do_action( 'h5pxapikatchu_insert_data_pre_database' );

	$main_id = Database::insert_data( $actor, $verb, $object, $result, $xapi );

	// Add hook 'h5pxapikatchu_insert_data_post_database'
	do_action( 'h5pxapikatchu_insert_data_post_database', $main_id );

	// We could handle database errors here using $main_id.

	wp_die();
}

/**
 * Delete all data.
 */
function delete_data() {
	// Add hook 'h5pxapikatchu_delete_data'
	do_action( 'h5pxapikatchu_delete_data' );

	$response = Database::delete_data();
	exit( json_encode( $response ) );
	wp_die();
}

/**
 * Load the text domain for internationalization.
 */
function h5pxapikatchu_load_plugin_textdomain() {
	load_plugin_textdomain( 'H5PXAPIKATCHU', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

/**
 * Register custom style for admin area.
 */
function h5pxapikatchu_add_admin_styles() {
	wp_register_style( 'H5PxAPIkatchu', plugins_url( '/styles/h5pxapikatchu.css', __FILE__ ), array(), H5PXAPIKATCHU_VERSION );
	wp_enqueue_style( 'H5PxAPIkatchu' );
}

/**
 * Add xAPI listener to content if feasible.
 *
 * @param object &$scripts List of JavaScripts that will be loaded.
 * @param array $libraries The libraries which the scripts belong to.
 * @param string $embed_type Possible values are: div, iframe, external, editor.
 */
function alter_h5p_scripts( &$scripts, $libraries, $embed_type ) {
	// Is content embedded?
	$is_embed = ( false !== strpos( $_SERVER['REQUEST_URI'], 'action=h5p_embed' ) );

	// Is admin viewing H5P content in backend?
	$is_admin_h5p_view = (
		false !== strpos( $_SERVER['REQUEST_URI'], 'page=h5p' ) &&
		false !== strpos( $_SERVER['REQUEST_URI'], 'task=show' )
	);

	// Is admin editing post/page with embedded content?
	$is_admin_post_iframe = (
		isset( $_SERVER['HTTP_REFERER'] ) &&
		false !== strpos( $_SERVER['HTTP_REFERER'], 'action=edit' )
	);

	// Is iframe call from same origin?
	$is_same_origin = ( isset( $_SERVER['HTTP_SEC_FETCH_SITE'] ) && 'same-origin' === $_SERVER['HTTP_SEC_FETCH_SITE'] );

	if ( $is_admin_h5p_view || $is_admin_post_iframe ) {
		return; // Viewing H5P content in backend or editing post with embedded content
	}

	if ( ! Options::is_embed_supported() && ! $is_same_origin && $is_embed ) {
		return; // Embedding via link or iframe from external
	}

	// Try to determine H5P content id
	if (
		isset( $_SERVER['HTTP_REFERER'] ) &&
		false !== strpos( $_SERVER['HTTP_REFERER'], 'task=show' )
	) {
		$components = parse_url( $_SERVER['HTTP_REFERER'] );
	} elseif (
		isset( $_SERVER['REQUEST_URI'] ) &&
		false !== strpos( $_SERVER['REQUEST_URI'], 'action=h5p_embed' )
	) {
		$components = parse_url( $_SERVER['REQUEST_URI'] );
	}

	// Check whether current user is author of current content
	if ( isset( $components ) ) {
		// ID of content being displayed
		$content_id = array_reduce(
			explode( '&', $components['query'] ),
			function ( $id, $query ) {
				if ( '' !== $id ) {
					return $id;
				}

				$split = explode( '=', $query );
				if ( 'id' === $split[0] ) {
					return intval( $split[1] );
				}

				return '';
			},
			''
		);

		if ( Database::get_content_author_id( $content_id ) === get_current_user_id() ) {
			return; // User is author of the content
		}
	}

	/*
	 * Add JavaScript listener to H5P content. Configuration is created
	 * via dynamically created H5P file, because passing config via
	 * wp_localize_script cannot run if WordPress is bypassed by using
	 * embed code or direct link.
	 */
	$path = plugin_dir_path( __FILE__ ) . 'js' . '/' . 'h5pxapikatchu-config.js';
	if ( file_exists( $path ) ) {
		$scripts[] = (object) array(
			'path'    => plugins_url( 'js/h5pxapikatchu-config.js', __FILE__ ),
			'version' => '?ver=' . H5PXAPIKATCHU_VERSION,
		);
	}

	$scripts[] = (object) array(
		'path'    => plugins_url( 'js/h5pxapikatchu-listener.js', __FILE__ ),
		'version' => '?ver=' . H5PXAPIKATCHU_VERSION,
	);
}

/**
 * Update the configuration file.
 */
function update_config_file() {
	Options::update_config_file();
}

// Start setup
register_activation_hook( __FILE__, 'H5PXAPIKATCHU\on_activation' );
register_deactivation_hook( __FILE__, 'H5PXAPIKATCHU\on_deactivation' );
register_uninstall_hook( __FILE__, 'H5PXAPIKATCHU\on_uninstall' );

add_action( 'h5p_alter_library_scripts', 'H5PXAPIKATCHU\alter_h5p_scripts', 10, 3 );
add_action( 'wp_ajax_nopriv_insert_data', 'H5PXAPIKATCHU\insert_data' );
add_action( 'wp_ajax_insert_data', 'H5PXAPIKATCHU\insert_data' );
add_action( 'plugins_loaded', 'H5PXAPIKATCHU\h5pxapikatchu_load_plugin_textdomain' );
add_action( 'plugins_loaded', 'H5PXAPIKATCHU\update' );
add_action( 'update_option_siteurl', 'H5PXAPIKATCHU\update_config_file', 10, 3 );

// Custom style for admin area
add_action( 'admin_enqueue_scripts', 'H5PXAPIKATCHU\h5pxapikatchu_add_admin_styles' );

// Initialize plugin
add_action( 'init', 'H5PXAPIKATCHU\init' );
