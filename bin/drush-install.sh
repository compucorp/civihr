#!/bin/bash

## About: Install the CiviHR extensions using drush

##################################
## List of CiviHR core extensions
CORE_EXTS=\
uk.co.compucorp.civicrm.hrcore

## List of extensions defining basic entity types
ENTITY_EXTS=\
org.civicrm.hrbank,\
org.civicrm.hrdemog,\
org.civicrm.hrident,\
org.civicrm.hrjobcontract,\
com.civicrm.hrjobroles,\
org.civicrm.hrmed,\
org.civicrm.hrqual,\
org.civicrm.hrvisa,\
org.civicrm.hremergency,\
org.civicrm.hrcareer,\
uk.co.compucorp.contactaccessrights,\
uk.co.compucorp.civicrm.tasksassignments

## List of extensions defining applications/UIs on top of the basic entity types
APP_EXTS=\
org.civicrm.hrreport,\
org.civicrm.hrui,\
org.civicrm.hrcase,\
org.civicrm.hrim,\
org.civicrm.hrrecruitment,\
org.civicrm.reqangular,\
org.civicrm.contactsummary,\
org.civicrm.bootstrapcivicrm,\
org.civicrm.bootstrapcivihr

##
# Set Default localisation settings
# It expect one parameter ($1) which points to civicrm absolute path
function set_default_localisation_settings() {
  LOC_FILE="en_US"
  if wget -q "https://download.civicrm.org/civicrm-l10n-core/mo/en_GB/civicrm.mo" > /dev/null; then
    mkdir -p $1/l10n/en_GB/LC_MESSAGES/
    mv civicrm.mo $1/l10n/en_GB/LC_MESSAGES/civicrm.mo
    LOC_FILE="en_GB"
  fi

  UKID=$(drush cvapi Country.getsingle return="id" iso_code="GB" | grep -oh '[0-9]*')

  drush cvapi Setting.create defaultCurrency="GBP" \
  dateformatDatetime="%d/%m/%Y %l:%M %P" dateformatFull="%d/%m/%Y" \
  dateformatFinancialBatch="%d/%m/%Y" dateInputFormat="dd/mm/yy" \
  lcMessages=${LOC_FILE} defaultContactCountry=${UKID}

  drush cvapi OptionValue.create option_group_id="currencies_enabled" \
  label="GBP (£)" value="GBP" is_default=1 is_active=1
}

##
# Set Any needed Resource URLs
function set_resource_urls() {
  # Set Custom CSS URL
  drush cvapi Setting.create \
  customCSSURL="[civicrm.root]/tools/extensions/civihr/org.civicrm.bootstrapcivicrm/css/custom-civicrm.css"
}

##################################
## Main

# Get CiviCRM Path and shift to the next option
CIVI_PATH=$1
shift

set -ex
drush "$@" cvapi extension.install keys=$CORE_EXTS,$ENTITY_EXTS,$APP_EXTS
set +ex

set_default_localisation_settings ${CIVI_PATH}
set_resource_urls

if [ "$WITH_HR_SAMPLE" == "1" ]; then
  set -ex
  drush "$@" cvapi extension.install keys=uk.co.compucorp.civicrm.hrsampledata
  set +ex
fi

