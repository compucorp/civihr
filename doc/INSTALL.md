## Pre-requisites

Install CiviCRM 4.4.0 or higher

## Download and Install

```bash
cd </path/to/extension/dir>
#  (ex: $drupalroot/vendor/civicrm or $civiroot/tools/extensions or a custom-configed path)

wget https://github.com/civicrm/civihr/archive/1.0.0.zip
unzip civihr-1.0.0.zip
mv civihr-1.0.0 civihr
```

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

or

cd /var/www/drupal/sites/example.com
bash /var/www/drupal/vendor/civicrm/civihr/bin/drush-install.sh --with-sample-data
```

Read the drush-install.sh for details.
