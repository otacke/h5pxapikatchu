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

    // TODO: Make this flexible!
    $TABLE_FIELDS = [
      __( 'Id', 'H5PxAPIkatchu'),
      __( 'Actor Type', 'H5PxAPIkatchu'),
      __( 'Actor Name', 'H5PxAPIkatchu'),
      __( 'Actor Email', 'H5PxAPIkatchu'),
      __( 'Actor Account Homepage', 'H5PxAPIkatchu' ),
      __( 'Actor Account Name', 'H5PxAPIkatchu'),
      __( 'Verb Id', 'H5PxAPIkatchu'),
      __( 'Verb Display', 'H5PxAPIkatchu'),
      __( 'Object Id', 'H5PxAPIkatchu'),
      __( 'Object Def. Name', 'H5PxAPIkatchu' ),
      __( 'Object Def. Description', 'H5PxAPIkatchu' ),
      __( 'Object Def. Choices', 'H5PxAPIkatchu' ),
      __( 'Object Def. Correct Responses', 'H5PxAPIkatchu' ),
      __( 'Result Response', 'H5PxAPIkatchu' ),
      __( 'Result Score Raw', 'H5PxAPIkatchu' ),
      __( 'Result Score Scaled', 'H5PxAPIkatchu' ),
      __( 'Result Completion', 'H5PxAPIkatchu' ),
      __( 'Result Success', 'H5PxAPIkatchu' ),
      __( 'Result Duration', 'H5PxAPIkatchu' ),
      __( 'Time', 'H5PxAPIkatchu' ),
      __( 'xAPI', 'H5PxAPIkatchu' )
    ];

    global $wpdb;

  	echo '<div class="wrap">';
    echo '<h2>' . __( 'H5PxAPIkatchu', 'H5PxAPIkatchu' ) . '</h2>';

  	$table_name = $wpdb->prefix . 'h5pxapikatchu';
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
        $heads .= '<th>' . $TABLE_FIELDS[$i] . '</th>';
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
