Instructions:

1) Copy this folder in the basedir of all the other extensions
2) Rename __info.xml in info.xml in order to make the extension visible to CiviCRM
3) Rename all instances of `bootstrapexample` in the desired name for the extension (ie `bootstrapmytheme`)
    1) In info.xml
    2) In file names (ie: `bootstrapexample.php` -> `bootstrapmytheme.php`)
    3) In the code (ie: `bootstrapexample_civicrm_install` -> `bootstrapmytheme_civicrm_install`)
    4) In gulpfile.js (`bootstrap-example-` -> `bootstrap-mytheme-`)
4) In bootstrap<theme_name>.php, in the bootstrap<theme_name>_civicrm_pageRun function, change the css file name
5) Run `npm install`
6) Run `gulp`
7) Write your custom css
8) Activate the extension
9) Done!
