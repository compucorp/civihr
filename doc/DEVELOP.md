## Download

Clone this git repository, e.g.

```bash
cd /var/www/drupal/sites/all/modules/civicrm
mkdir -p tools/extensions
git clone git://github.com/civicrm/civihr.git tools/extensions/civihr
```

## Install

If you have Drush installed, then you can enable all the extensions at once:

```bash
cd /var/www/drupal
bash sites/all/modules/civicrm/tools/extensions/civihr/bin/drush-install.sh --with-sample-data
```

If you're familiar with different ways to call drush, then you can use the same
techniques with drush-install.sh, e.g.

```bash
cd /var/www/drupal/sites/all/modules/civicrm/tools/extensions/civihr
./bin/drush-install.sh --with-sample-data --root=/var/www/drupal

or

cd /var/www/drupal/sites/example.com
bash /var/www/drupal/sites/all/modules/civicrm/tools/extensions/civihr/bin/drush-install.sh --with-sample-data
```

Read the drush-install.sh for details.

## Schema development

Most CiviHR extensions define their schema using CiviCRM's custom-data system.
During installation, modules using this sytem will load "xml/auto_install.xml"
which was [re]generated using the command "civix generate:custom-xml".
(Note: The XML won't be reloaded during upgrade. To support upgrades, one must
add an upgrade_N() function to CRM/*/Upgrader.php.)

The hrjob extension uses XML/GenCode to manage schema. When modifying the
schema, be sure to:

 1. Edit the XML files in "hrjob/xml/schema/CRM/HRJob"
 2. Run the command "hrjob/bin/setup.sh {CIVICRM_ROOT}"
 3. Manually copy relevant SQL snippets from "{CIVICRM_ROOT}/sql/civicrm.mysql" to "hrjob/sql/auto_install.sql"
 4. (If appropriate) Add an upgrade_N() function to hrjob/CRM/HRJob/Upgrader.php

## Test

To run the unit-tests, one must configure CiviCRM to run unit-tests, install
cv, and populate the cv vars file. To do that, run:

```bash
$ cv vars:fill
Site: /path/to/your/installation/sites/default/civicrm.settings.php
These fields were missing. Setting defaults:
{
    "ADMIN_EMAIL": "admin@example.com",
    "ADMIN_PASS": "t0ps3cr3t",
    "ADMIN_USER": "admin",
    "CMS_TITLE": "Untitled installation",
    "DEMO_EMAIL": "demo@example.com",
    "DEMO_PASS": "t0ps3cr3t",
    "DEMO_USER": "demo",
    "SITE_TOKEN": "38022b28355040d28e1f6dd2f7248b96",
    "TEST_DB_DSN": "mysql://dbUser:dbPass@dbHost/dbName?new_link=true"
}
Please edit /home/user/.cv.json
```

As the command output suggests, you need to edit "~/.cv.json" with your installation ADMIN_PASS, DEMO_PASS
and TEST_DB_DSN. You'll also need to add the "CMS_URL" option with the URL of you installation.

To execute particular tests, use "phpunit4":

```bash
user@host:/path/to/civihr/hrjob$ phpunit4 tests/phpunit/api/v3/HRJobTest.php
Adding Individual
Adding Organization
PHPUnit 4.8.21 by Sebastian Bergmann.

.
Installing civicrm_tests_dev database


Time: 24 seconds, Memory: 28.25Mb

OK (1 test, 4 assertions)
```

To run all the tests for an extension, just run "phpunit4" without passing the test file:

```bash
user@host:/path/to/civihr/hrjob$ phpunit4
```

(Note: We're assuming that there are two databases. The "live database",
used with "civix civicrm:ping", which is part of a fully-functioning CiviCRM/CiviHR installation.
The "headless testing database" is, which is the one you configured in the "TEST_DB_DSN" option
of the ~/.cv.json file.)

(Note: For "hrjob", there's an extra pre-requisite: before running tests, run
"hrjob/bin/setup.sh {CIVICRM_ROOT}".)
