#!/bin/bash

## About: Install the CiviHR extensions using drush

##################################
## List of CiviHR core extensions
CORE_EXTS=\
uk.co.compucorp.civicrm.hrcore,\
uk.co.compucorp.civicrm.hremails

## List of extensions defining basic entity types
ENTITY_EXTS=\
org.civicrm.hrbank,\
org.civicrm.hrdemog,\
org.civicrm.hrjobcontract,\
com.civicrm.hrjobroles,\
org.civicrm.hrmed,\
org.civicrm.hrqual,\
org.civicrm.hremergency,\
org.civicrm.hrcareer,\
uk.co.compucorp.contactaccessrights,\
uk.co.compucorp.civicrm.tasksassignments,\
uk.co.compucorp.civicrm.hrcomments,\
uk.co.compucorp.civicrm.hrleaveandabsences

## List of extensions defining applications/UIs on top of the basic entity types
APP_EXTS=\
org.civicrm.hrreport,\
org.civicrm.hrui,\
org.civicrm.hrcase,\
org.civicrm.hrim,\
org.civicrm.hrrecruitment,\
org.civicrm.reqangular,\
org.civicrm.contactsummary,\
org.civicrm.shoreditch,\
org.civicrm.bootstrapcivihr,\
org.civicrm.styleguide,\
uk.co.compucorp.civicrm.hrcontactactionsmenu

##################################
## Main

# Get CiviCRM Path and shift to the next option
CIVI_PATH=$1
shift

set -ex
drush "$@" cvapi extension.install keys=$CORE_EXTS
drush cvapi Extension.refresh
drush "$@" cvapi extension.install keys=$ENTITY_EXTS
drush cvapi Extension.refresh
drush "$@" cvapi extension.install keys=$APP_EXTS
set +ex

if [ "$WITH_HR_SAMPLE" == "1" ]; then
  set -ex
  drush "$@" cvapi extension.install keys=uk.co.compucorp.civicrm.hrsampledata
  set +ex
fi
