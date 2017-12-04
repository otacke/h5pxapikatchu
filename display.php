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
    add_action( 'admin_menu', array( $this, 'add_admin_page' ), 999 );
  }

  public function add_admin_page () {
    add_menu_page( 'h5pxapikatchu_options', 'H5PxAPIkatchu', 'manage_options', 'h5pxapikatchu_options', array( $this, 'add_plugin_page'), 'none' );
  }

  public function add_plugin_page () {

    global $wpdb;
  	$table_name = $wpdb->prefix . 'h5pxapikatchu';

  	if ( !current_user_can( 'manage_options' ) )  {
  		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  	}
  	echo '<div class="wrap">';
    echo '<div>This should be something more fancy. Open JS Grid is a hot candidate</div>';
    echo '<div><table border="1">';

    $result = $wpdb->get_results(
  		"
  			SELECT * FROM $table_name
  		"
  	);

    foreach($result as $fields) {
      $values = array_map(function ($field) {
        return '\'' . $field . '\'';
      }, (array)$fields);
      echo '<tr>';
      foreach ($fields as $key=>$value) {
        echo '<td>' . $value . '</td>';
      }
      echo '</tr>';
    }


    echo '</table></div>';
  	echo '<div class="h5pxapikatchu_button">EXPORT BUTTON</div>';
  	echo '</div>';
  }

}
