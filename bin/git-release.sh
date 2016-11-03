#!/bin/bash
##################################
fileName=""
## List of extensions
ENTITY_EXTS=( hrabsence \
hrbank \
hrcareer \
hrcase \
hrdemog \
hrident \
hrim \
hrmed \
hrprofile
hrqual \
hrrecruitment \
hrreport \
hrsampledata \
hrstaffdir \
hrui \
hrvisa \
org.civicrm.bootstrapcivihr \
hrjobcontract \
contactsummary \
contactaccessrights \
org.civicrm.hremergency \
com.civicrm.hrjobroles \
hrjobcontract \
org.civicrm.reqangular \
uk.co.compucorp.civicrm.appraisals \
uk.co.compucorp.civicrm.hrcore \
uk.co.compucorp.civicrm.hrleaveandabsences
)

set -e
version="$1"
releaseDate="$2"
if [ ! -n "$version" -o ! -n "$releaseDate" ]; then
  echo "Please specify CiviHR Version and Release Date eg 'bash ./bin/git-release.sh 1.1.beta2 2013-11-28'"
  exit 1
fi

## More portable variant of "sed -i"
function sedi() {
  if [ $(uname) = "Darwin" ]; then
    ## BSD sed
    sed -i '' "$@"
  else
    ## GNU sed
    sed -i "$@"
  fi
}

for var in "${ENTITY_EXTS[@]}"
do
  CONF="${var}/info.xml"
  sedi "s|\(<version>\)[^<>]*\(</version>\)|\1${version}\2|" "$CONF"
  sedi "s|\(<releaseDate>\)[^<>]*\(</releaseDate>\)|\1${releaseDate}\2|" "$CONF"
  fileName="$fileName ${var}"/info.xml
done
git commit -m "Update CiviHR Version (${version}) and Release Date (${releaseDate})" $fileName
git tag -a ${version} -m "CiviHR Version ${version}"
echo ""
echo "Created tag, ${version}. Please push the branch and tag!"
