<?php

namespace H5PXAPIKATCHU;

/**
 * Display and handle the settings page
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class Table_View {
	private $CLASS_DATATABLE = 'h5pxapikatchu-data-table';
	private $menu_icon;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array($this, 'add_scripts') );
		add_action( 'admin_menu', array( $this, 'add_admin_page' ), 999 );

		$this->menu_icon = 'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path fill="black" d="M896 768q237 0 443-43t325-127v170q0 69-103 128t-280 93.5-385 34.5-385-34.5-280-93.5-103-128v-170q119 84 325 127t443 43zm0 768q237 0 443-43t325-127v170q0 69-103 128t-280 93.5-385 34.5-385-34.5-280-93.5-103-128v-170q119 84 325 127t443 43zm0-384q237 0 443-43t325-127v170q0 69-103 128t-280 93.5-385 34.5-385-34.5-280-93.5-103-128v-170q119 84 325 127t443 43zm0-1152q208 0 385 34.5t280 93.5 103 128v128q0 69-103 128t-280 93.5-385 34.5-385-34.5-280-93.5-103-128v-128q0-69 103-128t280-93.5 385-34.5z"/></svg>');

		add_action( 'wp_ajax_nopriv_delete_data', 'H5PXAPIKATCHU\delete_data' );
		add_action( 'wp_ajax_delete_data', 'H5PXAPIKATCHU\delete_data' );
	}

	public function add_scripts() {
		wp_register_script( 'DataTablesScript', plugins_url( '/DataTables/datatables.min.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'BuildDataTable', plugins_url( '/js/build_data_table.js', __FILE__ ) );
		wp_register_style( 'DataTablesStyle', plugins_url( '/DataTables/datatables.min.css', __FILE__ ));

		wp_enqueue_script( 'DataTablesScript' );
		wp_enqueue_script( 'BuildDataTable' );
		wp_enqueue_style( 'DataTablesStyle' );

		// pass variables to JavaScript
		wp_localize_script( 'BuildDataTable', 'classDataTable', $this->CLASS_DATATABLE );
		wp_localize_script( 'BuildDataTable', 'buttonLabelDownload', __( 'Download', 'H5PXAPIKATCHU' ) );
		// Used to allow translations for Datatables from within WordPress translations
		$language_datatables = array(
			'info' => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'H5PXAPIKATCHU' ),
			'loadingRecords' => __( 'Loading...' , 'H5PAPIKATCHU' ),
			'processing' => __( 'Processing...', 'H5PXAPIKATCHU' ),
			'search' => __( 'search', 'H5PXAPIKATCHU' ),
			'zeroRecords' => __( 'No matching records found', 'H5PXAPIKATCHU' ),
			'paginate' => array(
				'first' => __( 'First', 'H5PXAPIKATCHU' ),
				'last' => __( 'Last', 'H5PXAPIKATCHU' ),
				'next' => __( 'Next', 'H5PXAPIKATCHU' ),
				'previous' => __( 'Previous', 'H5PXAPIKATCHU' )
			)
		);
		wp_localize_script( 'BuildDataTable', 'languageData', $language_datatables );
		wp_localize_script( 'BuildDataTable', 'buttonLabelDelete', __( 'Delete', 'H5PXAPIKATCHU' ) );
		wp_localize_script( 'BuildDataTable', 'dialogTextDelete', __( 'Do you really want to delete all the data?', 'H5PXAPIKATCHU' ) );
		wp_localize_script( 'BuildDataTable', 'errorMessage', __( 'Sorry, something went wrong with deleting the data.', 'H5PXAPIKATCHU' ) );
		wp_localize_script( 'BuildDataTable', 'wpAJAXurl', admin_url( 'admin-ajax.php' ) );

	}

	public function add_admin_page() {
		add_menu_page( 'h5pxapikatchu_options', 'H5PxAPIkatchu', 'manage_options', 'h5pxapikatchu_options', array( $this, 'add_plugin_page'), $this->menu_icon );
	}

	public function add_plugin_page() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'H5PXAPIKATCHU' ) );
		}

		global $wpdb;

		$complete_table = Database::get_complete_table();
		$column_titles = Database::get_column_titles();

		echo '<h2>' . __( 'H5PxAPIkatchu', 'H5PXAPIKATCHU' ) . '</h2>';
		if ( ! $complete_table ) {
			echo __( 'There is no xAPI information stored.', 'H5PXAPIKATCHU' );
			wp_die();
		}

		// Use Datatable to make the table pretty.
		echo '<div><table id="' . $this->CLASS_DATATABLE . '" class="table-striped table-bordered" cellspacing="0">';

		// Table Head and Footer
		$heads = '';
		for ( $i = 0; $i < sizeof( (array) $complete_table[0] ); $i++ ) {
			$heads .= '<th>';
			$heads .= ( isset( Database::$COLUMN_TITLE_NAMES[ $column_titles[ $i ] ]) ?
					Database::$COLUMN_TITLE_NAMES[ $column_titles[ $i ] ] :
					'' );
			$heads .= '</th>';
		}
		echo '<thead>' . $heads . '</thead><tfoot>' . $heads . '</tfoot>';

		// Table Body
		echo '<tbody>';
		foreach( $complete_table as $fields ) {
			$values = array_map( function( $field ) {
				return '\'' . $field . '\'';
			}, (array) $fields );
			echo '<tr>';
			foreach ( $fields as $key => $value ) {
				echo '<td>' . $value . '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody>';

		echo '</table></div>';
	}
}
