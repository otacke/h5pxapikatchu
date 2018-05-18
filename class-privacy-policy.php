<?php

namespace H5PXAPIKATCHU;

/**
 * Handle privacy policy
 *
 * @package H5PXAPIKATCHU
 * @since 0.2.2
 */
class PrivacyPolicy {

	/**
	 * Start up
	 */
	public function __construct() {
	}

  /**
   * Add privacy policy text to WP.
   */
  public function add_privacy_policy() {
    wp_add_privacy_policy_content(
      __( 'H5PxAPIkatchu', 'H5PXAPIKATCHU' ),
      PrivacyPolicy::get_privacy_policy()
    );
  }

  /**
   * Get privacy policy text.
   *
   * @return string Privacy policy text.
   */
  function get_privacy_policy() {
    return
    '<h2>' . __( 'Lorem Ipsum', 'H5PXAPIKATCHU' ) . '</h2>' .
	  '<p>' . __( 'Dolor sit amet.', 'H5PXAPIKATCHU' ) . '</p>';
  }
}
