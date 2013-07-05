CiviHR is a collection of extensions defining a human-resources application
that runs on top of the CiviCRM platform.

See also:
 * Wiki: http://wiki.civicrm.org/confluence/display/CRM/CiviHR
 * Issues: http://issues.civicrm.org/jira/secure/Dashboard.jspa?selectPageId=11213

## Download

Clone this git repository, e.g.

```bash
mkdir -p /var/www/drupal/vendor/civicrm
cd  /var/www/drupal/vendor/civicrm
git clone git://github.com/civicrm/civihr.git
```

## Install

If you have Drush installed, then you can enable all the extensions at once:

```bash
cd /var/www/drupal
bash vendor/civicrm/civihr/bin/drush-install.sh --with-sample-data
```

If you're familiar with different ways to call drush, then you can use the same
techniques with drush-install.sh, e.g.

```bash
cd /var/www/drupal/vendor/civicrm/civihr
./bin/drush-install.sh --with-sample-data --root=/var/www/drupal

## or

cd /var/www/drupal/sites/example.com
bash /var/www/drupal/vendor/civicrm/civihr/bin/drush-install.sh --with-sample-data
```

Read the drush-install.sh for details.

## Test

To run the unit-tests, one must configure CiviCRM to run unit-tests, install
civix, and link civix to CiviCRM. To validate that the link is setup, run:

```bash
user@host:/path/to/civihr/hrjob$ civix civicrm:ping
Ping successful
```

To execute particular tests, use "civix test":

```bash
user@host:/path/to/civihr/hrjob$ civix test api_v3_HRJobTest
Adding Individual
Adding Organization
PHPUnit 3.6.10 by Sebastian Bergmann.

.
Installing civicrm_tests_dev database


Time: 24 seconds, Memory: 28.25Mb

OK (1 test, 4 assertions)
```
