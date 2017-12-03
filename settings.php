<?php

namespace H5PXAPIKATCHU;

/**
 * Display and handle the settings page
 *
 * @package H5PXAPIKATCHU
 * @since 0.1
 */
class Settings {

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
            'debug_enabled',
            __( 'Debug', 'H5PxAPIkatchu' ),
            array( $this, 'debug_enabled_callback' ),
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
		    if( isset( $input['debug_enabled'] ) )
            $new_input['debug_enabled'] = absint( $input['debug_enabled'] );
        return $new_input;
    }

    /**
     * Print Widget Section text
     */
    public function print_general_section_info() {
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function debug_enabled_callback() {
		echo'<label for="debug_enabled">';
		echo '<input type="checkbox" name="h5pxapikatchu_option[debug_enabled]" id="debug_enabled" value="1" ' . ( isset( $this->options['debug_enabled']) ? checked( '1', $this->options['debug_enabled'], false ) : '') . ' />';
		echo __('Enable option to display xAPI statements in the JavaScript debug console', 'H5PxAPIkatchu');
		echo '</label>';
    }
}
