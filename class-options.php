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
	private static $CLASS_CTS_TABLE = 'h5pxapikatchu-cts-table'; // Content types
	private static $CLASS_COLVIS_TABLE = 'h5pxapikatchu-colvis-table'; // Column visibility
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

	public function add_scripts( $hook ) {
		if ( 'settings_page_h5pxapikatchu-admin' !== $hook ) {
			return;
		}

		wp_register_script( 'Options', plugins_url( '/js/options.js', __FILE__ ) );
		wp_register_script( 'DataTablesScript', plugins_url( '/DataTables/datatables.min.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'BuildCtsTable', plugins_url( '/js/build_cts_table.js', __FILE__ ) );
		wp_register_script( 'BuildColVisTable', plugins_url( '/js/build_column_visibility_table.js', __FILE__ ) );
		wp_register_style( 'DataTablesStyle', plugins_url( '/DataTables/datatables.min.css', __FILE__ ));

		wp_enqueue_script( 'Options' );
		wp_enqueue_script( 'DataTablesScript' );
		wp_enqueue_script( 'BuildCtsTable' );
		wp_enqueue_script( 'BuildColVisTable' );
		wp_enqueue_style( 'DataTablesStyle' );

		// pass variables to JavaScript
		wp_localize_script( 'BuildCtsTable', 'classCtsTable', self::$CLASS_CTS_TABLE );
		wp_localize_script( 'BuildColVisTable', 'h5pxapikatchuClassColVisTable', self::$CLASS_COLVIS_TABLE );
	}

	public static function setDefaults() {
		// Set version
		update_option('h5pxapikatchu_version', H5PXAPIKATCHU_VERSION );

		if ( get_option( 'h5pxapikatchu_defaults_set') ) {
			return; // No need to set defaults
		}

		// Remember that defaults have been set
		update_option( 'h5pxapikatchu_defaults_set', true );

		// Store all content types by default, show all columns by default
		update_option( self::$OPTION_SLUG, array(
			'capture_all_h5p_content_types' => 1,
			'columns_visible' => implode( Database::get_column_titles(), ',' )
		) );
	}

	/**
	 * Get column ids that should be hidden in table view.
	 */
	public static function get_columns_hidden() {
		$column_titles = Database::get_column_titles();

		$columns_visible = array();
		$options = get_option( self::$OPTION_SLUG );
		if ( false !== $options && isset( $options['columns_visible'] ) ) {
			$columns_visible = explode( ',', $options['columns_visible'] );
		}

		$columns_hidden = array_diff( $column_titles, $columns_visible );

		$ids = [];
		foreach ($columns_hidden as $column_title) {
			array_push( $ids, array_search( $column_title, $column_titles ) );
		}

		return $ids;
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

		add_settings_section(
			'columns_visible_settings',
			__( 'Visible columns', 'H5PXAPIKATCHU' ),
			array( $this, 'print_columns_visible_section_info' ),
			'h5pxapikatchu-admin'
		);

		add_settings_field(
			'columns_visible',
			__( 'Visible columns', 'H5PXAPIKATCHU' ),
			array( $this, 'columns_visible_callback' ),
			'h5pxapikatchu-admin',
			'columns_visible_settings'
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

		// Settings for column title
		$columns_visible = array();
		foreach ( Database::get_column_titles() as $column_title ) {
			if ( isset( $input['column_titles-' . $column_title] ) ) {
				array_push( $columns_visible, $input['column_titles-' . $column_title ] );
			}
		}
		$new_input['columns_visible'] = implode( $columns_visible, ',' );

		return $new_input;
	}

	/**
	 * Print section text for general settings
	 */
	public function print_general_section_info() {
	}

	/**
	 * Print section text for column labels settings
	 */
	public function print_columns_visible_section_info() {
		echo __( 'By checking the column titles below you can select if the corresponding columns will be displayed by default.', 'H5PXAPIKATCHU' );
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
	 * Show the selector table for choosing columns to be displayed by default.
	 * Will be made pretty using Datatables.
	 */
	public function columns_visible_callback() {
		$column_titles = Database::get_column_titles();
		if ( empty( $column_titles ) ) {
			echo __( 'It seems there are no column titles defined. Wicked!', 'H5PXAPIKATCHU' );
			return;
		}

		$columns_visible = self::get_columns_visible();

		echo '<div><table id="' . self::$CLASS_COLVIS_TABLE . '" class="table-striped table-bordered">';
		echo '<thead>';
		echo '<tr>';
		echo '<th></th>';
		echo '<th>' . __( 'Column title', 'H5PXAPIKATCHU' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $column_titles as $column_title ) {
			echo '<tr>';
			echo '<td>';
			echo '<input ' .
				'type="checkbox" ' .
				'name="h5pxapikatchu_option[column_titles-' . $column_title . ']" ' .
				'id="h5pxapikatchu-column-title-' . $column_title . '" ' .
				'class="h5pxapikatchu-column-labels-selector" ' .
				'value="' . $column_title . '" ' . checked( in_array( $column_title, $columns_visible ), true, false ) .
				' />';
			echo '</td>';
			echo '<td>' .
				(isset( Database::$COLUMN_TITLE_NAMES[ $column_title ]) ?
					Database::$COLUMN_TITLE_NAMES[ $column_title ] :
					$column_title) .
				'</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table></div>';
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
		echo '<div><table id="' . self::$CLASS_CTS_TABLE . '" class="table-striped table-bordered">';
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
			echo '<td>' . $content_type['lib_title'] . '</td>';
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
	 * Get list of column labels to be displayed.
	 * @return string[] Array of column titles.
	 */
	public static function get_columns_visible() {
		return isset( self::$OPTIONS['columns_visible'] ) ?
			explode( ',', self::$OPTIONS['columns_visible'] ) :
			array();
	}

	/**
	 * Set default values for columns visible.
	 */
	public static function set_defaults_columns_visible() {
		$settings = get_option( self::$OPTION_SLUG );
		$settings['columns_visible'] = implode( ',', Database::get_column_titles() );
		update_option( self::$OPTION_SLUG, $settings );
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
