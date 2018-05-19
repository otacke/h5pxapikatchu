<?php

namespace H5PXAPIKATCHU;

/**
 * Handle privacy policy
 *
 * @package H5PXAPIKATCHU
 * @since 0.2.2
 */
class PrivacyPolicy {
	private static $PAGE_LENGTH = 25;

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

		$content = sprintf(
			__( '
				<h2>What personal data we collect and why we collect it</h2>
				<h3>H5PxAPIkatchu</h3>
				<p>When you use interactive content, we may collect data about
				your interaction using <a href="%s" target="_blank">the xAPI standard</a>,
				e.g. what answer was given, how long did it take to answer,
				what score was achieved, etc. We use it to learn about how
				well the interaction is designed and how it could be improved
				to improve the general experience and learning outcomes in general.</p>
				<p>However, if and only if you are logged in, this data will be tied to
				<ul>
				  <li>your full name (as entered in your profile),</li>
					<li>your email address or openID (whatever you use),</li>
					<li>and your WordPress user id.</li>
				</ul>
				In consequence, your interactions could be linked to you.</p>
			',
			'H5PXAPIKATCHU' ),
			'https://github.com/adlnet/xAPI-Spec/blob/master/xAPI-Data.md'
		);

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
		$number = self::$PAGE_LENGTH; // Limit of xAPI items to process to avoid timeout
		$page = (int) $page;

		$export_items = array();
		$results = array();

		$wp_user = get_user_by( 'email', $email );
		if ($wp_user) {
			// Retrieve xAPI data for user
			$results = Database::get_user_table( $wp_user->ID );
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
			'done' => count ( $results ) < $number,
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
		$wp_user = get_user_by( 'email', $email );
		$error = false;

		if ($wp_user) {
			$ok = Database::anonymize( $wp_user->ID );
		}

		return array(
			'items_removed' => $ok,
			'items_retained' => false,
			'messages' => array(),
			'done' => true
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
