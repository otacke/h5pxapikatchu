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
}
