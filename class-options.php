<?php

namespace H5PXAPIKATCHU;

/**
 * Display and handle the settings page
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class Options {

	// Waiting for PHP 7 to hit the mainstream ...
	private static $OPTION_SLUG = 'h5pxapikatchu_option';
	private static $CLASS_CTS_TABLE = 'h5pxapikatchu-cts-table';
	private static $OPTIONS;

	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	public function add_scripts() {
		wp_register_script( 'Options', plugins_url( '/js/options.js', __FILE__ ) );
		wp_register_script( 'DataTablesScript', plugins_url( '/DataTables/datatables.min.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'BuildCtsTable', plugins_url( '/js/build_cts_table.js', __FILE__ ) );
		wp_register_style( 'DataTablesStyle', plugins_url( '/DataTables/datatables.min.css', __FILE__ ));

		wp_enqueue_script( 'Options' );
		wp_enqueue_script( 'DataTablesScript' );
		wp_enqueue_script( 'BuildCtsTable' );
		wp_enqueue_style( 'DataTablesStyle' );

		// pass variables to JavaScript
		wp_localize_script( 'BuildCtsTable', 'classCtsTable', self::$CLASS_CTS_TABLE );
	}

	public static function setDefaults() {
		// Store all content types be default
		update_option( self::$OPTION_SLUG, array( 'capture_all_h5p_content_types' => 1 ) );
	}

	public static function delete_options() {
		delete_option( self::$OPTION_SLUG );
		delete_site_option( self::$OPTION_SLUG );
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
			__( 'General', 'H5PXAPIKATCHU' ),
			array( $this, 'print_general_section_info' ),
			'h5pxapikatchu-admin'
		);

		add_settings_field(
			'store_complete_xapi',
			__( 'Store complete statements', 'H5PXAPIKATCHU' ),
			array( $this, 'store_complete_xapi_callback' ),
			'h5pxapikatchu-admin',
			'general_settings'
		);

		add_settings_field(
			'debug_enabled',
			__( 'Debug', 'H5PXAPIKATCHU' ),
			array( $this, 'debug_enabled_callback' ),
			'h5pxapikatchu-admin',
			'general_settings'
		);

		add_settings_field(
			'capture_all_h5p_content_types',
			__( 'Capture everything', 'H5PXAPIKATCHU' ),
			array( $this, 'capture_all_h5p_content_types_callback' ),
			'h5pxapikatchu-admin',
			'general_settings'
		);

		add_settings_section(
			'content_type_settings',
			__( 'H5P content types', 'H5PXAPIKATCHU' ),
			array( $this, 'print_content_type_section_info' ),
			'h5pxapikatchu-admin'
		);

		add_settings_field(
			'h5p_content_types',
			__( 'H5P content types', 'H5PXAPIKATCHU' ),
			array( $this, 'h5p_content_types_callback' ),
			'h5pxapikatchu-admin',
			'content_type_settings'
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
		// Settings for individual content type capturing
		$captured_contents = array();
		$length = sizeof( Database::get_h5p_content_types() );
		for ( $i = 0; $i < $length; $i++ ) {
			if ( isset( $input['h5p_content_types-' . $i ] ) ) {
				array_push( $captured_contents, $input['h5p_content_types-' . $i ] );
			}
		}

		$new_input['h5p_content_types'] = implode( $captured_contents, ',' );

		return $new_input;
	}

	/**
	 * Print section text for general settings
	 */
	public function print_general_section_info() {
	}

	/**
	 * Print section text for content type settings
	 */
	public function print_content_type_section_info() {
		echo __( 'By checking the H5P content types below you can select their xAPI statements for being captured.', 'H5PXAPIKATCHU' );
	}

	/**
	 * Get the option for storing the complete xAPI statement in a database field
	 */
	public function store_complete_xapi_callback() {
		// I don't likt this mixing of HTML and PHP, but it seems to be WordPress custom.
		?>
		<label for="store_complete_xapi">
		<input
			type="checkbox"
			name="h5pxapikatchu_option[store_complete_xapi]"
			id="store_complete_xapi"
			value="1"
			<?php echo isset( $this->options['store_complete_xapi']) ? checked( '1', $this->options['store_complete_xapi'], false ) : '' ?>
		/>
		<?php echo __('Store the complete xAPI statement as JSON data. Be sure to check your database storage limit!', 'H5PXAPIKATCHU'); ?>
		</label>
		<?php
	}

	/**
	 * Show the option for showing xAPI statements in the JavaScript console
	 */
	public function debug_enabled_callback() {
		?>
		<label for="debug_enabled">
		<input
			type="checkbox"
			name="h5pxapikatchu_option[debug_enabled]"
			id="debug_enabled"
			value="1"
			<?php echo isset( $this->options['debug_enabled']) ? checked( '1', $this->options['debug_enabled'], false ) : '' ?>
		/>
		<?php echo __('Display xAPI statements in the JavaScript debug console', 'H5PXAPIKATCHU'); ?>
		</label>
		<?php
	}

	/**
	 * Show the option for capturing statements from all content types.
	 */
	public function capture_all_h5p_content_types_callback() {
		?>
		<label for="capture_all_h5p_content_types">
		<input
			type="checkbox"
			name="h5pxapikatchu_option[capture_all_h5p_content_types]"
			id="h5pxapikatchu_capture_all_content_types"
			value="1"
			<?php echo isset( $this->options['capture_all_h5p_content_types']) ? checked( '1', $this->options['capture_all_h5p_content_types'], false ) : '' ?>
		/>
		<?php echo __('Capture the xAPI statements of all H5P content types', 'H5PXAPIKATCHU'); ?>
		</label>
		<?php
	}

	/**
	 * Show the selector table for choosing H5P content types to be stored.
	 * Will be made pretty using Datatables.
	 */
	public function h5p_content_types_callback() {
		$content_types = Database::get_h5p_content_types();
		if ( empty( $content_types ) ) {
			echo __( 'It seems that H5P is not installed on this WordPress system.', 'H5PXAPIKATCHU' );
			return;
		}

		$content_types_options = self::get_h5p_content_types();
		echo '<div><table id="' . self::$CLASS_CTS_TABLE . '" class="table-striped table-bordered" cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th></th>';
		echo '<th>' . __( 'Title', 'H5PXAPIKATCHU' ) . '</th>';
		echo '<th>' . __( 'Type', 'H5PXAPIKATCHU' ) . '</th>';
		echo '<th>' . __( 'Id', 'H5PXAPIKATCHU' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $content_types as $i => $content_type ) {
			echo '<tr>';
			echo '<td>';
			echo '<input type="checkbox" name="h5pxapikatchu_option[h5p_content_types-' . $i . ']" id="h5p_content_type-' . $i . '" class="h5pxapikatchu-content-type-selector" value="' . $content_type['ct_id'] . '" ' . checked( in_array( $content_type['ct_id'], $content_types_options ), true, false ) . ' />';
			echo '</td>';
			echo '<td>' . $content_type['ct_title'] . '</td>';
			echo '<td>' . $content_type['lib_name'] . '</td>';
			echo '<td>' . $content_type['ct_id'] . '</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table></div>';
	}

	/**
	 * Get flag for storing the complete xapi statement.
	 * @return boolean True, if flag set.
	 */
	public static function store_complete_xapi() {
		return isset( self::$OPTIONS['store_complete_xapi'] );
	}

	/**
	 * Get flag for showinf debug output.
	 * @return boolean True, if flag set.
	 */
	public static function is_debug_enabled() {
		return isset( self::$OPTIONS['debug_enabled'] );
	}

	/**
	 * Get flag for capturing from all H5P content types.
	 * @return boolean True, if flag set.
	 */
	public static function capture_all_h5p_content_types() {
		return isset( self::$OPTIONS['capture_all_h5p_content_types'] );
	}

	/**
	 * Get list of H5P content type IDs to be captured from.
	 * @return string Comma separated list of IDs.
	 */
	public static function get_h5p_content_types() {
		return isset( self::$OPTIONS['h5p_content_types'] ) ? explode( ',', self::$OPTIONS['h5p_content_types'] ) : array();
	}

	/**
	 * Init function for the class.
	 */
	static function init() {
		self::$OPTIONS = get_option( self::$OPTION_SLUG, false );
	}
}
Options::init();
