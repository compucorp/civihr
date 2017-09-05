#!/bin/bash
##################################
HASERROR=0

## List of extensions defining basic entity types
ENTITY_EXTS=( hrbank \
hrcareer \
hrjob \
hrmed \
hrqual \
hrstaffdir \
hrrecruitment \
)

CONF=`dirname $0`/setup.conf
if [ -f "$CONF" ]; then
  source "$CONF"
fi
TESTOUTDIR=${TESTOUTDIR:-${CIVISOURCEDIR}/build}

##################################
set -x

for var in "${ENTITY_EXTS[@]}"
  do
    set +e
    pushd "$CIVISOURCEDIR/tools"
      ./scripts/phpunit \
        --include-path "$CIVIHRDIR/${var}/tests/phpunit" \
        --tap \
        --log-junit "$TESTOUTDIR/junit-${var}-WebTest_AllTests.xml" \
        WebTest_AllTests
    popd
    if [ $? != "0" ]; then
      HASERROR=1
    fi
    set -e
done
exit $HASERROR
