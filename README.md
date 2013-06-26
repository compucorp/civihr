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

To enable all extensions at once, run:

```bash
## Enable major new features
drush cvapi extension.install keys=org.civicrm.hrident,org.civicrm.hrjob,org.civicrm.hrmed,org.civicrm.hrqual,org.civicrm.hrreport,org.civicrm.hrvisa,org.civicrm.hremerg,org.civicrm.hrcareer,org.civicrm.hrui

## Install sample data
drush cvapi extension.install keys=org.civicrm.hrsampledata
```

Or you can break them out and install some selected pieces:

```bash
## Enable major new features piecemeal
drush cvapi extension.install keys=org.civicrm.hrident
drush cvapi extension.install keys=org.civicrm.hrjob
drush cvapi extension.install keys=org.civicrm.hrmed
drush cvapi extension.install keys=org.civicrm.hrqual
drush cvapi extension.install keys=org.civicrm.hrreport
drush cvapi extension.install keys=org.civicrm.hrvisa
drush cvapi extension.install keys=org.civicrm.hremerg
drush cvapi extension.install keys=org.civicrm.hrcareer

## Enable high-level UI options
drush cvapi extension.install keys=org.civicrm.hrui

## Install sample data; requires all the new features
drush cvapi extension.install keys=org.civicrm.hrsampledata
```

