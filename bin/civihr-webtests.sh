#!/bin/bash
##################################
## List of extensions defining basic entity types
ENTITY_EXTS=( hrbank \
hrcareer \
hrident \
hrjob \
hrmed \
hrqual \
hrstaffdir \
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
      set +e
      pushd "$CIVISOURCEDIR/tools"
        ./scripts/phpunit \
          --include-path "$CIVIHRDIR/${var}/tests/phpunit" \
          --tap \
          --log-junit "$CIVISOURCEDIR/build/junit-${var}-CRM_AllTests.xml" \
          WebTest_AllTests
      popd
      [ $? ] && HASERROR=1
      set -e
    popd    
done

if [ -n $HASERROR ]; then
  exit 1
fi