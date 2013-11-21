#!/bin/bash
##################################
HASERROR=

## List of extensions defining basic entity types
ENTITY_EXTS=( hrreport \
hrjob \
hrvisa \
)
CONF=`dirname $0`/setup.conf
if [ -f "$CONF" ]; then
  source "$CONF"
fi
##################################
set -x
for var in "${ENTITY_EXTS[@]}"
  do
    pushd $CIVIHRDIR/${var}
      if [ ${var} == "hrjob" ]; then
        drush cvapi extension.install keys=org.civicrm.${var}
        set +e
        pushd "$CIVISOURCEDIR/tools"
          ./scripts/phpunit \
            --include-path "$CIVIHRDIR/${var}/tests/phpunit" \
            --tap \
            --log-junit "$CIVISOURCEDIR/build/junit-${var}-api_v3_AllTests.xml" \
            api_v3_AllTests
        popd
        [ -n $? ] && HASERROR=1
        set -e
        drush cvapi extension.install keys=org.civicrm.${var}
      else
        drush cvapi extension.install keys=org.civicrm.hrjob,org.civicrm.${var}
      fi

      set +e
      pushd "$CIVISOURCEDIR/tools"
        ./scripts/phpunit \
          --include-path "$CIVIHRDIR/${var}/tests/phpunit" \
          --tap \
          --log-junit "$CIVISOURCEDIR/build/junit-${var}-CRM_AllTests.xml" \
          CRM_AllTests
      popd
      [ -n $? ] && HASERROR=1
      set -e
    popd
done

if [ -n $HASERROR ]; then
  exit 1
fi