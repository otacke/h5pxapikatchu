<?php

namespace H5PXAPIKATCHU;

/**
 * Display and handle the settings page
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class Display {
  /**
   * Start up
   */
  public function __construct() {
    add_action( 'admin_enqueue_scripts', array($this, 'add_scripts') );
    add_action( 'admin_menu', array( $this, 'add_admin_page' ), 999 );
  }

  public function add_scripts () {
    wp_register_script( 'DataTablesScript', plugins_url( '/DataTables/datatables.min.js', __FILE__ ), array( 'jquery' ) );
    wp_register_script( 'BuildDatatable', plugins_url( '/js/build_datatable.js', __FILE__ ) );
    wp_register_style( 'DataTablesStyle', plugins_url( '/DataTables/datatables.min.css', __FILE__ ));

    wp_enqueue_script( 'DataTablesScript' );
    wp_enqueue_script( 'BuildDatatable' );
    wp_enqueue_style( 'DataTablesStyle' );
  }

  public function add_admin_page () {
    add_menu_page( 'h5pxapikatchu_options', 'H5PxAPIkatchu', 'manage_options', 'h5pxapikatchu_options', array( $this, 'add_plugin_page'), 'none' );
  }

  public function add_plugin_page () {
    if ( !current_user_can( 'manage_options' ) )  {
  		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  	}

    global $wpdb;

  	echo '<div class="wrap">';
    echo '<h2>' . __( 'H5PxAPIkatchu', 'H5PxAPIkatchu' ) . '</h2>';

  	$table_name = $wpdb->prefix . 'h5pxapikatchu';

    $table_name = Database::$TABLE_NAME; // TODO: combine in SQL syntax
    $existing_columns = $wpdb->get_col("DESC {$table_name}", 0);

    $result = $wpdb->get_results(
  		"
  			SELECT * FROM $table_name
  		"
  	);

    if ( ! $result ) {
      echo __( 'There is no xAPI information stored.', 'H5PxAPIkatchu' );
    } else {
      // TODO: Clean this!
      echo '<div><table id="h5pxapikatchu-data-table" class="table-striped table-bordered" cellspacing="0">';

      $heads = '';
      for ( $i = 0; $i < sizeof( (array)$result[0] ); $i++ ) {
        $heads .= '<th>' . ( isset (Database::$COLUMN_TITLES[$existing_columns[$i]]) ? Database::$COLUMN_TITLES[$existing_columns[$i]] : '' ) . '</th>';
      }
      echo '<thead>' . $heads . '</thead>';
      echo '<tfoot>' . $heads . '</tfoot>';

      echo '<tbody>';
      foreach( $result as $fields ) {
        $values = array_map( function( $field ) {
          return '\'' . $field . '\'';
        }, (array)$fields );
        echo '<tr>';
        foreach ( $fields as $key => $value ) {
          echo '<td>' . $value . '</td>';
        }
        echo '</tr>';
      }
      echo '</tbody>';
      echo '</table></div>';
    }
  	echo '</div>';
  }
}
