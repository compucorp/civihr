#!/bin/bash
##################################
HASERROR=0

CONF=`dirname $0`/setup.conf
if [ -f "$CONF" ]; then
  source "$CONF"
fi

## Fill in defaults
TESTOUTDIR=${TESTOUTDIR:-${CIVISOURCEDIR}/build}
SQLDUMP=${SQLDUMP:-${CIVIHRDIR}/bin/dump.sql}

##################################
set -x

##################################
## runTest <extension-name> <dependencies> <test-name>
function runTest() {
  var="$1"
  deps="$2"
  test="$3"

  set +e
  pushd "${DRUPALDIR}" >> /dev/null
    if [ -f "$SQLDUMP" ]; then
      ## add a file ~/.my.cnf in your home directory and it will disable the mysqldump password prompting
      mysql "${CIVIDBNAME}" < "${SQLDUMP}"
    else
      drush cvapi extension.install keys=${deps}
    fi
  popd >> /dev/null

  pushd "${CIVISOURCEDIR}/tools" >> /dev/null
    ./scripts/phpunit \
      --include-path "${CIVIHRDIR}/${var}/tests/phpunit" \
      --tap \
      --log-junit "${TESTOUTDIR}/junit-${var}-${test}.xml" \
      ${test}
    if [ $? != "0" ]; then
      HASERROR=1
    fi
  popd >> /dev/null
  set -e
}

##################################
## Main
if [ "${DSNDBNAME}" == "${CIVIDBNAME}" ]; then
  pushd "${DRUPALDIR}" >> /dev/null
    drush sql-dump > "$SQLDUMP"
  popd >> /dev/null
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

runTest hrreport org.civicrm.hrabsence,org.civicrm.hrjob,org.civicrm.hrreport CRM_AllTests
runTest hrjob org.civicrm.hrjob api_v3_AllTests
runTest hrjob org.civicrm.hrjob CRM_AllTests
runTest hrabsence org.civicrm.hrabsence api_v3_AllTests
runTest hrrecruitment org.civicrm.hrrecruitment CRM_AllTests

if [ -f "${SQLDUMP}" ] ; then
  rm "${SQLDUMP}"
fi

exit $HASERROR
