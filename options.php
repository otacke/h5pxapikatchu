<?php

namespace H5PXAPIKATCHU;

/**
 * Display and handle the settings page
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class Options {

  public static $SLUG = 'h5pxapikatchu_option';
  public static $OPTIONS;

	/**
   * Holds the values to be used in the fields callbacks
   */
  private $options;

  /**
   * Start up
   */
  public function __construct() {
      add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
      add_action( 'admin_init', array( $this, 'page_init' ) );
  }

  public static function delete_options() {
	  delete_option( self::$SLUG );
	  delete_site_option( self::$SLUG );
  }

  /**
   * Add options page
   */
  public function add_plugin_page() {
      // This page will be under "Settings"
      add_options_page(
          'Settings Admin',
          'H5PxAPIkatchu',
          'manage_options',
          'h5pxapikatchu-admin',
          array( $this, 'create_admin_page' )
      );
  }

  /**
   * Options page callback
   */
  public function create_admin_page() {
      // Set class property
      $this->options = get_option( 'h5pxapikatchu_option' );
      ?>
      <div class="wrap">
          <h2>H5PxAPIkatchu</h2>
          <form method="post" action="options.php">
          <?php
              // This prints out all hidden setting fields
              settings_fields( 'h5pxapikatchu_option_group' );
              do_settings_sections( 'h5pxapikatchu-admin' );
              submit_button();
          ?>
          </form>
      </div>
      <?php
  }

  /**
   * Register and add settings
   */
  public function page_init() {
      register_setting(
          'h5pxapikatchu_option_group',
          'h5pxapikatchu_option',
          array( $this, 'sanitize' )
      );

      add_settings_section(
          'general_settings',
          '',
          array( $this, 'print_general_section_info' ),
          'h5pxapikatchu-admin'
      );

      add_settings_field(
          'store_complete_xapi',
          __( 'Store complete statements', 'H5PxAPIkatchu' ),
          array( $this, 'store_complete_xapi_callback' ),
          'h5pxapikatchu-admin',
          'general_settings'
      );

      add_settings_field(
          'debug_enabled',
          __( 'Debug', 'H5PxAPIkatchu' ),
          array( $this, 'debug_enabled_callback' ),
          'h5pxapikatchu-admin',
          'general_settings'
      );

      add_settings_field(
          'capture_all_h5p_content_types',
          __( 'H5P Content Types', 'H5PxAPIkatchu' ),
          array( $this, 'capture_all_h5p_content_types_callback' ),
          'h5pxapikatchu-admin',
          'general_settings'
      );
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   * @return array Output
   */
  public function sanitize( $input ) {
      $new_input = array();
      if ( isset( $input['store_complete_xapi'] ) ) {
          $new_input['store_complete_xapi'] = absint( $input['store_complete_xapi'] );
      }
      if ( isset( $input['debug_enabled'] ) ) {
          $new_input['debug_enabled'] = absint( $input['debug_enabled'] );
      }
      if ( isset( $input['capture_all_h5p_content_types'] ) ) {
        $new_input['capture_all_h5p_content_types'] = absint( $input['capture_all_h5p_content_types'] );
      }
      return $new_input;
  }

  /**
   * Print Widget Section text
   */
  public function print_general_section_info () {
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function store_complete_xapi_callback () {
		echo'<label for="store_complete_xapi">';
		echo '<input type="checkbox" name="h5pxapikatchu_option[store_complete_xapi]" id="store_complete_xapi" value="1" ' . ( isset( $this->options['store_complete_xapi']) ? checked( '1', $this->options['store_complete_xapi'], false ) : '') . ' />';
		echo __('Enable option to store the complete xAPI statement as JSON data. Be sure to check your database storage limit!', 'H5PxAPIkatchu');
		echo '</label>';
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function debug_enabled_callback () {
		echo'<label for="debug_enabled">';
		echo '<input type="checkbox" name="h5pxapikatchu_option[debug_enabled]" id="debug_enabled" value="1" ' . ( isset( $this->options['debug_enabled']) ? checked( '1', $this->options['debug_enabled'], false ) : '') . ' />';
		echo __('Enable option to display xAPI statements in the JavaScript debug console', 'H5PxAPIkatchu');
		echo '</label>';
  }

  public function capture_all_h5p_content_types_callback () {
    echo'<label for="capture_all_h5p_content_types">';
    echo '<input type="checkbox" name="h5pxapikatchu_option[capture_all_h5p_content_types]" id="capture_all_h5p_content_types" value="1" ' . ( isset( $this->options['capture_all_h5p_content_types']) ? checked( '1', $this->options['capture_all_h5p_content_types'], false ) : '') . ' />';
    echo __('Capture the xAPI statemetns of all H5P content types', 'H5PxAPIkatchu');
    echo '</label>';
  }

  public static function store_complete_xapi () {
    return isset( self::$OPTIONS['store_complete_xapi'] );
  }

  public static function is_debug_enabled () {
    return isset( self::$OPTIONS['debug_enabled'] );
  }

  public static function capture_all_h5p_content_types () {
    return isset( self::$OPTIONS['capture_all_h5p_content_types'] );
  }

  static function init() {
    self::$OPTIONS =  get_option(self::$SLUG, false);
  }
}
Options::init();
