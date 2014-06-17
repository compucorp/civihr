## Upgrade: CiviHR (1.2 => 1.3-beta1)

> CiviHR 1.3-beta1 requires CiviCRM 4.5.
>
> If you have an older version of CiviCRM, first upgrade CiviCRM to 4.5 or above.
> (Please follow steps mentioned at:
> http://wiki.civicrm.org/confluence/display/CRMDOC/Upgrade+Drupal+Sites+to+4.5+-+Drupal+7 )

Make a backup of the CiviCRM database.

Download CiviHR 1.3-beta1:

```
cd </path/to/extension/dir>
(ex: $drupalroot/vendor/civicrm or $civiroot/tools/extensions or a custom-configed path)

rm -rf civihr
wget https://github.com/civicrm/civihr/archive/1.3-beta1.zip
unzip 1.3-beta1.zip
mv civihr-1.3-beta1 civihr
```
Goto 'Administer => System Settings => Manage Extension' and click the "Refresh" button.

Install the new CiviHR extensions: hrrecruitment.

> If you use drush, you can install the new extensions on the command-line:
>
> drush cvapi extension.install keys=org.civicrm.hrrecruitment

Notice the CiviCRM popup alert. Click on "Execute the updates".