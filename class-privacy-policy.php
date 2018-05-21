<?php

namespace H5PXAPIKATCHU;

/**
 * Handle privacy policy
 *
 * @package H5PXAPIKATCHU
 * @since 0.2.2
 */
class PrivacyPolicy {
	private static $PAGE_SIZE = 25;

	/**
	 * Start up
	 */
	public function __construct() {
	}

  /**
   * Add privacy policy text to WP.
   */
  function add_privacy_policy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
      return;
    }

		$link_xapi = sprintf(
			'<a href="https://github.com/adlnet/xAPI-Spec/blob/master/xAPI-Data.md" target="_blank">%s</a>',
			__( 'xAPI', 'H5PXAPIKATCHU')
		);

		// Intentionally using the WordPress translation here.
		$content  = '<h2>' . __( 'What personal data we collect and why we collect it' ) . '</h2>';

		$content .= '<h3>' . __( 'H5PxAPIkatchu', 'H5PXAPIKATCHU' ) . '</h3>';
		$content .= '<p>';
		$content .= sprintf(
			__(
				'When you use interactive content, we may collect data about your interaction using %s.',
				'H5PXAPIKATCHU'
			),
			$link_xapi
		) . ' ';
		$content .= __( 'The data may e.g. contain what answer was given, how long it took to answer, what score was achieved, etc.', 'H5PXAPIKATCHU' ) . ' ';
		$content .= __( 'We use the data to learn about how well the interaction is designed and how it could be adapted to improve the usability and learning outcomes in general.', 'H5PXAPIKATCHU' );
		$content .= '</p>';
		$content .= '<p>';
		$content .= __( 'However, if and only if you are logged in, this data will be tied to your full name, your email address, and your WordPress user id.', 'H5PXAPIKATCHU' ) . ' ';
		$content .= __( 'In consequence, your interactions could be linked to you.', 'H5PXAPIKATCHU' ) . ' ';
		$content .= __( 'Therefore, all personal data can be stripped to anonymize the data.', 'H5PXAPIKATCHU' );
		$content .= '</p>';

    wp_add_privacy_policy_content(
      __( 'H5PxAPIkatchu', 'H5PXAPIKATCHU' ),
      wp_kses_post( wpautop( $content, false ) )
    );
  }

	/**
	 * Export user data.
	 * @param string $email Email address.
	 * @param int $page Page.
	 * @return array Export data.
	 */
	function h5pxapikatchu_exporter( $email, $page = 1 ) {
		$page_size = self::$PAGE_SIZE; // Limit of xAPI items to process to avoid timeout
		$page = (int) $page;

		$export_items = array();
		$results = array();

		$wp_user = get_user_by( 'email', $email );
		if ($wp_user) {
			// Retrieve xAPI data for user
			$results = Database::get_user_table( $wp_user->ID, $page, $page_size );
			$column_titles = Database::get_column_titles();

			// Build items for exporter
			foreach ( $results as $result ) {
				$data = array();

				foreach ( $result as $label => $value ) {
					$name = isset( Database::$COLUMN_TITLE_NAMES[$label]) ?
						Database::$COLUMN_TITLE_NAMES[ $label ] :
						$label;
					$data[] = array( 'name' => $name, 'value' => $value );
				}

				$export_items[] = array(
					'group_id' => 'h5pxapikatchu',
					'group_label' => __( 'H5PxAPIkatchu', 'H5PXAPIKATCHU' ),
					'item_id' => "h5pxapikatchu-{$result->id}",
					'data' => $data
				);
			}
		}

		return array(
			'data' => $export_items,
			'done' => count ( $results ) < $page_size,
		);
	}

	/**
	 * Register the exporter.
	 * @param array $exporters Previous exporters.
	 * @return array H5PxAPIkatchu exporters.
	 */
	function register_h5pxapikatchu_exporter( $exporters ) {
	  $exporters['h5pxapikatchu'] = array(
	    'exporter_friendly_name' => __( 'H5PxAPIkatchu', 'H5PXAPIKATCHU' ),
	    'callback' => 'H5PXAPIKATCHU\PrivacyPolicy::h5pxapikatchu_exporter'
	  );
	  return $exporters;
	}

	/**
	 * Anonymize/erase user data
	 */
	function h5pxapikatchu_eraser( $email, $page = 1 ) {
		$page_size = self::$PAGE_SIZE;
		$wp_user = get_user_by( 'email', $email );
		$error = false;

		if ($wp_user) {
      // There should only be one actor with the user ID, but pagination doesn't hurt
			$number = Database::anonymize( $wp_user->ID, $page_size );
		}

		return array(
			'items_removed' => $number,
			'items_retained' => false,
			'messages' => array(),
			'done' => $number < $page_size
		);
	}

	/**
	 * Register the eraser.
	 * @param array $erasers Previous erasers.
	 * @return array H5PxAPIkatchu erasers.
	 */
	function register_h5pxapikatchu_eraser( $erasers ) {
	  $erasers['h5pxapikatchu'] = array(
	    'eraser_friendly_name' => __( 'H5PxAPIkatchu', 'H5PXAPIKATCHU' ),
	    'callback' => 'H5PXAPIKATCHU\PrivacyPolicy::h5pxapikatchu_eraser'
	  );
	  return $erasers;
	}
}
