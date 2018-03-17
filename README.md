![banner](https://www.olivertacke.de/labs/wp-content/uploads/2017/12/h5pxapikatchu_bar_1920.png "banner")

# H5PxAPIkatchu
This Wordpress plugin is a simple solution to catch 'em all, those xAPI statements
that have been sent by [H5P](https://h5p.org) content types. Users should be able
to store and view/export the xAPI statements.

This plugin is NOT intended to provide functionality for analysis, etc. There is
no point in recreating what is already available in Learning Record Stores or
what you can do yourself with a spreadsheet software, scikit-learn, etc.

If you need more, you should give [Learning Locker](https://learninglocker.net/)
a shot. It's [open](https://github.com/LearningLocker/learninglocker), free and shiny.

## Features
* Store important values of xAPI statements emitted from H5P content types in your database.
* View and export the data for further analysis.
* Optionally limit capturing to particular H5P content types only.
* Optionally store the complete xAPI statements as a string - know what you're doing ...

## Support me at patreon!
If you like what I do, please consider to become my supporter at patreon: https://www.patreon.com/otacke

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

## License
H5PxAPIkatchu is is licensed under the [MIT License](https://github.com/otacke/h5pxapikatchu/blob/master/LICENSE).
