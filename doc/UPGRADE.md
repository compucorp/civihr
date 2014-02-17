## Upgrade: CiviHR (1.0 => 1.1)

> CiviHR 1.1 requires CiviCRM 4.4.3+.
>
> If you have an older version of CiviCRM, first upgrade CiviCRM to 4.4.3 or above.
> (Please follow steps mentioned at:
> http://wiki.civicrm.org/confluence/display/CRMDOC/Upgrade+Drupal+Sites+to+4.4+-+Drupal+7 )

Make a backup of the CiviCRM database.

Download CiviHR 1.1:

```
cd </path/to/extension/dir>
(ex: $drupalroot/vendor/civicrm or $civiroot/tools/extensions or a custom-configed path)

rm -rf civihr
wget https://github.com/civicrm/civihr/archive/1.1.0.zip
unzip 1.1.0.zip
mv civihr-1.1.0 civihr
```

Goto 'Administer => System Settings => Manage Extension' and click the "Refresh" button.

Notice the CiviCRM popup alert. Click on "Execute the updates".

Install the new CiviHR extensions: hrcase, hrim, and hrprofile.

> If you use drush, you can install the new extensions on the command-line:
>
> drush cvapi extension.install keys=org.civicrm.hrcase,org.civicrm.hrim,org.civicrm.hrprofile

## Upgrade: CiviHR (1.1 => 1.2)
Make a backup of the CiviCRM database.

Download CiviHR 1.2:

```
cd </path/to/extension/dir>
(ex: $drupalroot/vendor/civicrm or $civiroot/tools/extensions or a custom-configed path)

rm -rf civihr
wget https://github.com/civicrm/civihr/archive/1.1.0.zip
unzip 1.1.0.zip
mv civihr-1.1.0 civihr
```
Goto 'Administer => System Settings => Manage Extension' and click the "Refresh" button.

Install the new CiviHR extensions: hrabsence.

> If you use drush, you can install the new extensions on the command-line:
>
> drush cvapi extension.install keys=org.civicrm.hrabsence

Notice the CiviCRM popup alert. Click on "Execute the updates".