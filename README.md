# H5PxAPIkatchu
This Wordpress plugin is intended to become a simple solution to catch 'em all,
those xAPI statements that have been sent by [H5P](https://h5p.org) content types. Users should be
able to filter, store and view/export the xAPI statements.

No, it is not intended to provide funtionality for analytics, etc. There is no point in recreating what is already available in Learning Record Stores. I am just planning to offer a halfway decent option for storing the xAPI results. If you need more, you should give [Learning Locker](https://learninglocker.net/) a shot. It's [open](https://github.com/LearningLocker/learninglocker), free and shiny.

## TODO
- improve db table structure (normalization): store actor, verb, object and result in separate tables to save storage space
- add option to select xAPI values that should be stored
- add option to select which content types to track
- CLEAN UP THE MESS!
  - WordPress guidelines
  - Refactoring (a class for pseudo-xAPI handling could be useful)