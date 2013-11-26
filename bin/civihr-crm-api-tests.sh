#!/bin/bash
##################################
HASERROR=0

CONF=`dirname $0`/setup.conf
if [ -f "$CONF" ]; then
  source "$CONF"
fi

DUMP="${CIVIHRDIR}/bin/dump.sql"
##################################
set -x
## runTest <extension-name> <dependencies> <test-name>
function runTest() {
  var="$1"
  deps="$2"
  test="$3"

  set +e
  pushd "${DRUPALDIR}"
    if [ -f "${CIVIHRDIR}/bin/dump.sql" ]; then
      ## add a file ~/.my.cnf in your home directory and it will disable the mysqldump password prompting
      mysql "${CIVIDBNAME}" < "${DUMP}"
    else
      drush cvapi extension.install keys=${deps}
    fi
  popd

  pushd "${CIVISOURCEDIR}/tools"
    ./scripts/phpunit \
      --include-path "${CIVIHRDIR}/${var}/tests/phpunit" \
      --tap \
      --log-junit "${CIVISOURCEDIR}/build/junit-${var}-${test}.xml" \
      ${test}
    if [ $? != "0" ]; then
      HASERROR=1
    fi
  popd
  set -e
}

if [ "${DSNDBNAME}" == "${CIVIDBNAME}" ]; then
  pushd "${DRUPALDIR}"
    drush sql-dump > "$DUMP"
  popd
  if [ -n "${DBUSER}" -a -n "${DBPASS}" ]; then
    file=~/.my.cnf
    combine="[mysql]\nuser=${DBUSER}\npassword=${DBPASS}"    
    if [ -f "$file" ]; then
      echo -e $combine >> $file
    else
      touch $file
      echo -e $combine >> $file
    fi
  fi
fi

runTest hrreport org.civicrm.hrjob,org.civicrm.hrreport CRM_AllTests
runTest hrjob org.civicrm.hrjob api_v3_AllTests
runTest hrjob org.civicrm.hrjob CRM_AllTests
#runTest hrvisa org.civicrm.hrjob,org.civicrm.hrvisa CRM_AllTests

if [ -f "${DUMP}" ] ; then
  rm "${DUMP}"
fi

exit $HASERROR