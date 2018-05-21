=== H5PxAPIkatchu ===
Contributors: otacke
Tags: h5p, xapi
Donate link: https://www.patreon.com/otacke
Requires at least: 4.0
Tested up to: 4.9.6
Stable tag: 0.2.3
License: MIT
License URI: https://github.com/otacke/h5pxapikatchu/blob/master/LICENSE

This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements that have been sent by H5P content types. Users should be able to filter, store and view/export the xAPI statements.

== Description ==

This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements that have been sent by H5P (https://h5p.org) content types. Users should be able to store and view/export the xAPI statements.

This plugin is NOT intended to provide functionality for analysis, etc. There is no point in recreating what is already available in Learning Record Stores or what you can do yourself with a spreadsheet software, scikit-learn, etc.

If you need more, you should give Learning Locker (https://learninglocker.net/) a shot. It's open, free and shiny.

== GDPR ==
Please note that as of May 25, 2018 you may have to comply with the General Data Privacy Regulation (GDPR, http://gdpr-info.eu/).

H5PxAPIkatchu supports the functions that WordPress offers to

- use suggestion for your privacy statement text,
- export personal data of a user, and
- delete personal data of a user.

Background: If you're using H5PxAPIkachu, by processing the xAPI statements you're processing at least these personal data items according to art. 4 GDPR:

- xAPI statement: Actor/name (Full name of the Agent)
- xAPI statement:Actor/Inverse Functional Identifier (email address, openID or account data within the host system)
- WordPress user id (ID given by the WordPress host system)

Please make sure to account for these items in your GDPR processes and documentation.

== Installation ==

Install H5PxAPIkatchu from the Wordpress Plugin directory or via your Wordpress instance and activate it. Done.

The most important parts of the xAPI statements that are emitted by H5P content types on your system should now be stored in your database. You can view and download them via the new WordPress menu item.

== Frequently Asked Questions ==

None yet.

== Screenshots ==

1. You can change some options to your particular needs.
2. You cannot only view the stored data, but also download them as an CSV file.

== Changelog ==

= 0.2.3 =
- Add support for pagination in GDPR exporter and eraser (avoid timeout for huge data)
- Improve translation structure.

= 0.2.2 =
- Add support for privacy support functions of WordPress.

= 0.2.1 =
- Fix bug that prevented recording of events.

= 0.2.0 =
- Added support for tracking the WP User ID
- Added the H5P content ID and H5P subcontent ID (although redundant to the xAPI object ID)
- Added update routines for old data to set WP User ID, H5P content ID and H5P subcontent ID

= 0.1.3 =
- Added support for locally embedded iframes for those who don't like short codes.

= 0.1.2 =
- Updated Datatables/Bootstrap from 3.3.7 to 4.0.0
- Updated Datatables/Buttons from 1.4.2 to 1.5.1
- Modified Bootstrap Stylesheet
- Changed visual appearance slightly

= 0.1.1 =
- Removed unnecessary debug output.

= 0.1 =
Initial release.

== Upgrade Notice ==

= 0.2.3 =
Update if you're dealing with huge data that might need to be exported for GDPR compliance.

= 0.2.2 =
Update if you need support for the privacy (GDPR) support functions of WordPress.

= 0.2.1 =
Update if you want to record with as many browsers as possible. You should.

= 0.2.0 =
Update if you need the WP User ID to combine data with other sources

= 0.1.3 =
Update if you want to embed local content using iframe code instead of a short code.

= 0.1.2 =
Update if you experience weird visual glitches with WordPress after activating the plugin.

= 0.1.1 =
Update if you use the debug output feature.

= 0.1 =
Initial release.
