![banner](https://www.olivertacke.de/labs/wp-content/uploads/2019/12/h5pxapikatchu_bar_1920.png "banner")

# SNORDIAN's H5PxAPIkatchu
This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements
that have been sent by [H5P](https://h5p.org) content types. Users should be able
to store and view/export the xAPI statements.

Please cmp. [Gotta catch ’em all!](https://www.olivertacke.de/labs/2017/12/27/gotta-catch-em-all/) and [Collecting and analyzing data with H5P — and opening up education maybe](https://www.olivertacke.de/labs/2018/03/25/collecting-and-analyzing-data-with-h5p-and-opening-up-education-maybe/) for the ideas behind the plugin.

This plugin is NOT intended to provide functionality for analysis, etc. There is
no point in recreating what is already available in Learning Record Stores or
what you can do yourself with a spreadsheet software, scikit-learn, etc.

This plugin is NOT intended to work as a replacement for a decent Learning
Record Store. It will work well for smaller platforms, but if you are expecting
to track many xAPI statements, you will probably run into trouble with
displaying all the data at some point.

If you need more, you should give [Learning Locker](https://learninglocker.net/)
a shot. It's [open](https://github.com/LearningLocker/learninglocker), free and shiny.

*PLEASE NOTE: H5P IS A REGISTERED TRADEMARK OF JOUBEL. THIS PLUGIN WAS NEITHER CREATED BY JOUBEL NOR IS IT ENDORSED BY THEM.*

## Features
- Store important values of xAPI statements emitted from H5P content types in your database.
- View and export the data for further analysis.
- Optionally limit capturing to particular H5P content types only.
- Optionally store the complete xAPI statements as a string - know what you're doing ...

## Install/Usage
Install H5PxAPIkatchu from the [Wordpress Plugin directory](https://wordpress.org/plugins/h5pxapikatchu/) or via your Wordpress
instance and activate it. Done.

The most important parts  of the xAPI statements that are emitted by H5P content
types on your system should now be stored in your database. You can view and
download them via the new WordPress menu item.

Some capabilities can be set for WordPress user roles in order to specify who
should be allowed to do what:

- _manage_h5pxapikatchu_options_: Capability to change the plugin's options
- _view_h5pxapikatchu_results_: Capability to view results of content types that have been created by current user and that were stored by H5PxAPIkachu
- _view_others_h5pxapikatchu_results_: Capability to view results of all content types that were stored by H5PxAPIkachu
- _download_h5pxapikatchu_results_: Capability to download the results stored by H5PxAPIkachu and accessible to current user
- _delete_h5pxapikatchu_results_: Capability to delete ALL data stored by H5PxAPIkachu

## Screenshots
You can change some options to your particular needs.

![options](https://www.olivertacke.de/labs/wp-content/uploads/2017/12/screenshot-1.png "Options")

You cannot only view the stored data, but also download them as an CSV file.

![table_view](https://www.olivertacke.de/labs/wp-content/uploads/2017/12/screenshot-2.png "Data in Table")

## License
H5PxAPIkatchu is is licensed under the [MIT License](https://github.com/otacke/h5pxapikatchu/blob/master/LICENSE).

## GDPR
Please note that as of May 25, 2018 you may have to comply with the General Data Privacy Regulation ([GDPR](http://gdpr-info.eu/)).

H5PxAPIkatchu supports the functions that WordPress offers to

- use suggestion for your privacy statement text,
- export personal data of a user, and
- delete personal data of a user.

Background: If you're using H5PxAPIkachu, by processing the xAPI statements you're processing at least these personal data items according to art. 4 GDPR:

- xAPI statement: Actor/name (Full name of the Agent)
- xAPI statement:Actor/Inverse Functional Identifier (email address, openID or account data within the host system)
- WordPress user id (ID given by the WordPress host system)

Please make sure to account for these items in your GDPR processes and documentation.
