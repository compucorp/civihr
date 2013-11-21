#!/bin/bash
##################################
## List of extensions defining basic entity types
ENTITY_EXTS=( hrreport \
hrjob \
hrvisa \
)
##################################

for var in "${ENTITY_EXTS[@]}"
  do
    cd ../${var}
    set -ex
    if [ ${var} == "hrjob" ]; then
      drush cvapi extension.install keys=org.civicrm.${var}
      civix test api_v3_AllTests
      drush cvapi extension.install keys=org.civicrm.${var}
    else
      drush cvapi extension.install keys=org.civicrm.hrjob,org.civicrm.${var}
    fi
    civix test CRM_AllTests
    set +ex
done