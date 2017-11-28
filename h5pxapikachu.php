<?php

/**
 * Plugin Name: H5PxAPIkatchu
 * Plugin URI: TO DO
 * Description: Catch xAPI statements of H5P
 * Version: 0.1
 * Author: Oliver Tacke
 * Author URI: https://www.olivertacke.de
 * License: WTFPL
 */

// as suggested by the Wordpress community
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * setup the plugin
 */
function h5pxapikatchu_setup() {
	wp_enqueue_script( 'H5PxAPIkatchu', plugins_url( '/js/h5pxapikatchu.js', __FILE__ ) );
	load_plugin_textdomain( 'H5PxAPIkatchu', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

// Start setup
add_action( 'the_post', 'h5pxapikatchu_setup' );
