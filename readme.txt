=== SNORDIAN's H5PxAPIkatchu ===
Contributors: otacke
Tags: h5p, xapi
Requires at least: 4.0
Tested up to: 6.6
Stable tag: 0.4.14
License: MIT
License URI: https://github.com/otacke/h5pxapikatchu/blob/master/LICENSE

This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements that have been sent by H5P content types. It allows you to store (the most relevant) xAPI properties in the database of WordPress. It also allows you to view, filter and export these data as a CSV file for further processing.

== Description ==

This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements that have been sent by H5P (https://h5p.org) content types. It allows you to store (the most relevant) xAPI properties in the database of WordPress. It also allows you to view, filter and export these data as a CSV file for further processing.

Please cmp. https://www.olivertacke.de/labs/2017/12/27/gotta-catch-em-all/ and https://www.olivertacke.de/labs/2018/03/25/collecting-and-analyzing-data-with-h5p-and-opening-up-education-maybe/ for the ideas behind the plugin.

This plugin is NOT intended to work as a replacement for a decent Learning Record Store. It will work well for smaller platforms, but if you are expecting to track many xAPI statements, you will probably run into trouble with displaying all the data at some point.

This plugin is NOT intended to forward xAPI statements sent by H5P to a Learning Record Store. Please use https://github.com/tunapanda/wp-h5p-xapi if you need that functionality.

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

== Customization ==
=== Capabilities ===
Some capabilities can be set for WordPress user roles in order to specify who
should be allowed to do what:

- manage_h5pxapikatchu_options: Capability to change the plugin's options
- view_h5pxapikatchu_results: Capability to view results of content types that have been created by current user and that were stored by H5PxAPIkachu
- view_others_h5pxapikatchu_results: Capability to view results of all content types that were stored by H5PxAPIkachu
- download_h5pxapikatchu_results: Capability to download the results stored by H5PxAPIkachu and accessible to current user
- delete_h5pxapikatchu_results: Capability to delete ALL data stored by H5PxAPIkachu

=== Hooks and filters ===
H5PxAPIkachu provides some hooks and filters that developers can use to customize the behavior or to use H5PxAPIkachu as the basis of their own plugin.

==== Actions ====
- h5pxapikatchu_on_activation: Triggered on activation of H5PxAPIkachu
- h5pxapikatchu_on_deactivation: Triggered on deactivation of H5PxAPIkachu
- h5pxapikatchu_on_uninstall: Triggered on uninstall of H5PxAPIkachu
- h5pxapikatchu_insert_data: Triggered when data are supposed to be inserted into the database
- h5pxapikatchu_insert_data_pre_database: Triggered right before data will be inserted into the database
- h5pxapikatchu_insert_data_post_database: Triggered right after data was inserted into the database, contains the new row's id that was inserted to the main table
- h5pxapikatchu_delete_data: Triggered when data are supposed to be deleted from the database

==== Filters ====
- h5pxapikatchu_insert_data_actor: Allows to filter/retrieve the xAPI actor object when it is supposed to be inserted into the database
- h5pxapikatchu_insert_data_verb: Allows to filter/retrieve the xAPI verb object when it is supposed to be inserted into the database
- h5pxapikatchu_insert_data_object: Allows to filter/retrieve the xAPI object object when it is supposed to be inserted into the database
- h5pxapikatchu_insert_data_result: Allows to filter/retrieve the xAPI result object when it is supposed to be inserted into the database
- h5pxapikatchu_insert_data_xapi: Allows to filter/retrieve the complete xAPI statement string when it is supposed to be inserted into the database

== Frequently Asked Questions ==

= The plugin does not record anything! Is it broken? =
Maybe, but hopefully not.

1. The plugin does NOT record xAPI statements if you are using H5P content in the backend of WordPress. That would mess up your statistics, because all the authors' test runs would be recorded, too. So, are you running H5P within a blog post or within a page?
2. The plugin does NOT record xAPI statements if you are the author of that content and logged in. That would mess up your statistics as well.
3. The plugin doesn't record xAPI statements if you told it so in the options :-)

== Screenshots ==

1. You can change some options to your particular needs.
2. You cannot only view the stored data, but also download them as an CSV file.

== Changelog ==

= 0.4.14 =
- Fix potential type error by fixing return value in get_h5p_contents().

= 0.4.13 =
- Add Dutch translation contributed by René Breedveld.

= 0.4.12 =
- Fix statements of subcontents not being stored when not capturing statements of all H5P contents.

= 0.4.11 =
- Fix bug on PHP 8.0 (implode argument order).

= 0.4.10 =
- Make configuration file use more robust.
- Test with WordPress 5.8.

= 0.4.9 =
- Stop logging statements if the current user is the author of the currently used content.

= 0.4.8 =
- Fix initializing new dynamic config file on update.

= 0.4.7 =
- Allow catching xAPI statements from embeds on other pages.
- Add option for catching xAPI statements from embeds on other pages (default: not allowed)

= 0.4.6 =
- Add action h5pxapikatchu_insert_data_post_database (contributed by R. L. Joseph)

= 0.4.5 =
- Use wp_localize_script with arrays

= 0.4.4 =
- Fix readystatechange listener to avoid conflicts with other plugins

= 0.4.3 =
- Fix deprecated use of Privacy Policy register hooks.
- Add hooks:
  - h5pxapikatchu_on_activation
  - h5pxapikatchu_on_deactivation
  - h5pxapikatchu_on_uninstall
  - h5pxapikatchu_insert_data
  - h5pxapikatchu_insert_data_pre_database
  - h5pxapikatchu_delete_data
- Add filters:
  - h5pxapikatchu_insert_data_actor
  - h5pxapikatchu_insert_data_verb
  - h5pxapikatchu_insert_data_object
  - h5pxapikatchu_insert_data_result
  - h5pxapikatchu_insert_data_xapi

= 0.4.2 =
- Fix capabilities for new installs.
- Fix re-writing default data to result table when reactivating/updating

= 0.4.1 =
- Add capabilities:
  - manage_h5pxapikatchu_options: Capability to change the plugin's options
  - view_h5pxapikatchu_results: Capability to view results of content types that have been created by current user and that were stored by H5PxAPIkachu
  - view_others_h5pxapikatchu_results: Capability to view results of all content types that were stored by H5PxAPIkachu
  - download_h5pxapikatchu_results: Capability to download the results stored by H5PxAPIkachu and accessible to current user
  - delete_h5pxapikatchu_results: Capability to delete ALL data stored by H5PxAPIkachu

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

= 0.4.14 =
Upgrade at will.

= 0.4.13 =
Upgrade if you need Dutch translations.

= 0.4.12 =
Upgrade if you do not want to store all statements from all contents but only from a few selected contents.

= 0.4.11 =
Upgrade if you're running PHP 8.0 or above.

= 0.4.10 =
Upgrade if you experience problems with the configuration file.

= 0.4.9 =
Upgrade if you do not want xAPI statements to be logged if the author of a content himself/herself is running it.

= 0.4.8 =
Upgrade if you need the features from version 0.4.7 and don't want to save the configuration manually once.

= 0.4.7 =
Upgrade if you want to register xAPI statements from your content that is embedded on other pages.
Please note that you need to activate this option first. Activating this option may lead to unexpected xAPI statements (in high numbers) if others embed your content somewhere. Your server will have to cope with all these statements. This behavior may deplete your resources and is a potential gateway for a denial-of-service attack.

= 0.4.6 =
Upgrade if you require an action triggered after adding an entry to the database including the entry's id

= 0.4.5 =
Upgrade if you have WP debugging activated.

= 0.4.4 =
Upgrade if you need to run H5P with other plugins, in particular the MathDisplay library for LaTeX support using MathJax.

= 0.4.3 =
Upgrade if you need other plugins to customize the behavior or need the Privacy Policy features.

= 0.4.2 =
Upgrade if you want two bugs less :-)

= 0.4.1 =
Upgrade if you want to specify capabilities for user roles.

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
