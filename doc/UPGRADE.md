CiviHR is a collection of extensions defining a human-resources application
that runs on top of the CiviCRM platform.

See also:
 * Wiki: http://wiki.civicrm.org/confluence/display/CRM/CiviHR
 * Issues: http://issues.civicrm.org/jira/secure/Dashboard.jspa?selectPageId=11213

## Upgrade
CiviHR 1.1 is based on CiviCRM 4.4.3 and above.
Upgrade CiviCRM to 4.4.3 and above if needed. (Please follow steps mentioned at: http://wiki.civicrm.org/confluence/display/CRMDOC/Upgrade+Drupal+Sites+to+4.4+-+Drupal+7 )
Before upgrade take backup of CiviCRM database.

Follow following steps to upgrade CiviHR1.0 to CiviHR1.1:
cd </path/to/extension/dir>
(ex: $drupalroot/vendor/civicrm or $civiroot/tools/extensions or a custom-configed path)

Remove old civihr extension folder.

wget https://github.com/civicrm/civihr/archive/1.1.zip

unzip 1.1.zip

mv civihr-1.1 civihr

Goto 'Manage Extension' page from UI and Click "Refresh" button.
Click on "execute the updates" link of CiviCRM popup alert.

** For hrcase, hrim, hrprofile extension: You can install it manuaaly OR run following command If you have Drush installed :
drush cvapi extension.install keys=org.civicrm.hrcase,org.civicrm.hrim,org.civicrm.hrprofile
