=== SNORDIAN's H5PxAPIkatchu ===
Contributors: otacke
Tags: h5p, xapi
Requires at least: 4.0
Tested up to: 5.4
Stable tag: 0.4.0
License: MIT
License URI: https://github.com/otacke/h5pxapikatchu/blob/master/LICENSE

This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements that have been sent by H5P content types. Users should be able to filter, store and view/export the xAPI statements.

== Description ==

This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements that have been sent by H5P (https://h5p.org) content types. Users should be able to store and view/export the xAPI statements.

This plugin is NOT intended to replace the H5P plugin's reporting or provide functionality for analysis, etc. There is no point in recreating what is already available in Learning Record Stores or what you can do yourself with a spreadsheet software, scikit-learn, etc.

If you need more, you should give Learning Locker (https://learninglocker.net/) a shot. It's open, free and shiny.

PLEASE NOTE: H5P IS A REGISTERED TRADEMARK OF JOUBEL. THIS PLUGIN WAS NEITHER CREATED BY JOUBEL NOR IS IT ENDORSED BY THEM.

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

= The plugin does not record anything! Is it broken? =
Maybe, but hopefully not.

1. The plugin does NOT record xAPI statement if you are using H5P content in the backend of WordPress. That would mess up your statistics, because all the authors' test runs would be recorded, too. So, are you running H5P within a blog post or within a page?
2. The plugin doesn't record xAPI statements if you told it so in the options :-)

== Screenshots ==

1. You can change some options to your particular needs.
2. You cannot only view the stored data, but also download them as an CSV file.

== Changelog ==

= 0.4.0 =
- Move xAPI listeners' initialization to H5P content.

= 0.3.7 =
- Fix listening for xAPI on Edge browsers.

= 0.3.6 =
- Allow any user that has the capability to create H5P content to see the results of content that he/she has created.

= 0.3.5 =
- Skip non H5P iframes from xAPI detection.
  Thanks to Patrick Kellogg for suggesting.
- Fix storing data if there's a " inside content.
  Thanks to Dominic Kennell for reporting.

= 0.3.4 =
- Add filters to table column.
- Add selector for the number of entries per page.

= 0.3.3 =
- Fix premature call to wp_enqueue_style.

= 0.3.2 =
- Fix bug that prevented storing statements when strings contained a single quote.

= 0.3.1 =
- Fix bug that prevented to run version 0.3.0 if it was installed freshly.

= 0.3.0 =
- Add support for showing/hiding columns on table view page
- Add option to set defaults for showing/hiding columns on table view page
- Add custom stylesheet file for easier customization
- Update DataTables from 1.10.16 to 1.10.18
- Update Datatables/Bootstrap from 4.0.0 to 4.1.1
- Update Datatables/Buttons from 1.5.1 to 1.5.4
- Make button design more appealing on table view page
- Add cache busting to script/style loading
- Improve performance in WordPress admin view

= 0.2.6 =
- Add support for divs instead of iframes and there's no item in the action bar.
  Thanks to Damien Romito for finding the gap.

= 0.2.5 =
- Add support for H5P content types that use divs instead of iframes, e.g Memory Game.

= 0.2.4 =
- Fix bug introduced in 0.2.0 that could prevent plugin from initializing the
  database properly. Thanks to "thedeviousdev" finding it!
- Fix behavior that was declared as deprecated. Thanks to "thedeviousdev" finding it!

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

= 0.4.0 =
Upgrade if you continuously encounter trouble with recording xAPI statements.

= 0.3.7 =
Upgrade if you expect someone using Edge.

= 0.3.6 =
Upgrade if you have "teacher" roles that create H5P content and need to see the results to their contents.

= 0.3.5 =
Upgrade if you are using a " inside your content or expect students to type one.

= 0.3.4 =
Upgrade if you want to filter for particular values in table columns or to change the number of entries per page.

= 0.3.3 =
Upgrade if you like clean code :-)

= 0.3.2 =
You should update if texts in your language are likely to contain a single quote.

= 0.3.1 =
If you upgraded from version 0.2.6 or before, you don't need this version - but it won't harm.

= 0.3.0 =
Update if you want to be able to show/hide columns on the table view page.

= 0.2.6 =
Update if you use divs instead of iframes, but have no item in the action bar (e.g. "Download").

= 0.2.5 =
Update if you use content types that use divs instead of iframes, e.g. Memory Game.

= 0.2.4 =
Update if you want to be future proof.

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
