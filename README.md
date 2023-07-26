![banner](https://www.olivertacke.de/labs/wp-content/uploads/2019/12/h5pxapikatchu_bar_1920.png "banner")

# SNORDIAN's H5PxAPIkatchu
This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements
that have been sent by [H5P](https://h5p.org) content types. It allows you to
store (the most relevant) xAPI properties in the database of WordPress. It also
allows you to view, filter and export these data as a CSV file for further
processing.

Please cmp. [Gotta catch ’em all!](https://www.olivertacke.de/labs/2017/12/27/gotta-catch-em-all/) and [Collecting and analyzing data with H5P — and opening up education maybe](https://www.olivertacke.de/labs/2018/03/25/collecting-and-analyzing-data-with-h5p-and-opening-up-education-maybe/) for the ideas behind the plugin.

This plugin is NOT intended to work as a replacement for a decent Learning
Record Store. It will work well for smaller platforms, but if you are expecting
to track many xAPI statements, you will probably run into trouble with
displaying all the data at some point.

This plugin is NOT intended to forward xAPI statements sent by H5P to a Learning
Record Store. Please use [WP-H5P-xAPI](https://github.com/tunapanda/wp-h5p-xapi)
if you need that functionality.

This plugin is NOT intended to replace the H5P plugin's reporting or provide
functionality for analysis, etc. There is no point in recreating what is already
available in Learning Record Stores or what you can do yourself with a
spreadsheet software, scikit-learn, etc.

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

## Screenshots
You can change some options to your particular needs.

![options](https://www.olivertacke.de/labs/wp-content/uploads/2017/12/screenshot-1.png "Options")

You cannot only view the stored data, but also download them as an CSV file.

![table_view](https://www.olivertacke.de/labs/wp-content/uploads/2017/12/screenshot-2.png "Data in Table")

## Customizing

### Capabilities
Some capabilities can be set for WordPress user roles in order to specify who
should be allowed to do what:

- _manage_h5pxapikatchu_options_: Capability to change the plugin's options
- _view_h5pxapikatchu_results_: Capability to view results of content types that have been created by current user and that were stored by H5PxAPIkachu
- _view_others_h5pxapikatchu_results_: Capability to view results of all content types that were stored by H5PxAPIkachu
- _download_h5pxapikatchu_results_: Capability to download the results stored by H5PxAPIkachu and accessible to current user
- _delete_h5pxapikatchu_results_: Capability to delete ALL data stored by H5PxAPIkachu

### Hooks and filters
H5PxAPIkachu provides some hooks and filters that developers can use to customize the behavior or to use H5PxAPIkachu as the basis of their own plugin.

#### Hooks
- _h5pxapikatchu_on_activation_: Triggered on activation of H5PxAPIkachu
- _h5pxapikatchu_on_deactivation_: Triggered on deactivation of H5PxAPIkachu
- _h5pxapikatchu_on_uninstall_: Triggered on uninstall of H5PxAPIkachu
- _h5pxapikatchu_insert_data_: Triggered when data are supposed to be inserted into the database
- _h5pxapikatchu_insert_data_pre_database_: Triggered right before data will be inserted into the database
- _h5pxapikatchu_delete_data_: Triggered when data are supposed to be deleted from the database

#### Filters
- _h5pxapikatchu_insert_data_actor_: Allows to filter/retrieve the xAPI actor object when it is supposed to be inserted into the database
- _h5pxapikatchu_insert_data_verb_: Allows to filter/retrieve the xAPI verb object when it is supposed to be inserted into the database
- _h5pxapikatchu_insert_data_object_: Allows to filter/retrieve the xAPI object object when it is supposed to be inserted into the database
- _h5pxapikatchu_insert_data_result_: Allows to filter/retrieve the xAPI result object when it is supposed to be inserted into the database
- _h5pxapikatchu_insert_data_xapi_: Allows to filter/retrieve the complete xAPI statement string when it is supposed to be inserted into the database

### Example filters

#### Not saving certain verbs
In certain situation, one may only be interested in xAPI statements with particular verbs. The plugin does not provide a list to define what should be listend to and what should not (as in LRS logic one would rather do this by filtering the data later on), but one can do this by adding a filter to one's WordPress environment.

_Please note:_ Despite not trying to be a gradebook replacement, people seem to be using H5PxAPIkatchu as such. They are only interested in "scores and answers". However, the whole point of xAPI is to be able to gain deeper knowledge about what the user is experiencing. If one is only interested in storing scores and answers, one should rather amend the original H5P plugin (or create a separate plugin for this job), so only the relevant values are stored which then then can be displayed easily using https://github.com/h5p/h5p-php-report.

Nevertheless, if you're interested in "scores and answers" only and want to do this using H5PxAPIkatchu, filtering for verbs may not be the proper approach - one would rather filter for statements that contains a `results` property, because there's no fixed list of verbs that could pop up with xAPI, and content types could as well use other verbs than `completed` or `answered` and yet the statements could contain "scores and answers".

```php
add_filter('h5pxapikatchu_insert_data_verb', 'filter_h5pxapikatchu_insert_data_verb', 10);
function filter_h5pxapikatchu_insert_data_verb($verb)
{
  if ( is_array( $verb ) ) {
    if ( in_array($verb['display'], array('interacted', 'attempted'))) {
      wp_send_json_error( false );  // or make the caller die in some other way
    }
  }
  return $verb;
}
```

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
