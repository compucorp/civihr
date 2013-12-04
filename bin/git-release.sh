#!/bin/bash
##################################
fileName=""
## List of extensions
ENTITY_EXTS=( hrbank \
hrcareer \
hrcase \
hrdemog \
hremerg \
hrident \
hrim \
hrjob \
hrmed \
hrprofile
hrqual \
hrreport \
hrstaffdir \
hrui \
hrvisa \
)

set -x
version="$1"
releaseDate="$2"
if [ ! -n "$version" -o ! -n "$releaseDate" ]; then
  exit 1
fi

for var in "${ENTITY_EXTS[@]}"
  do
    CONF="${var}/info.xml"
    sed -i "s|\(<version>\)[^<>]*\(</version>\)|\1${version}\2|" "$CONF"
    sed -i "s|\(<releaseDate>\)[^<>]*\(</releaseDate>\)|\1${releaseDate}\2|" "$CONF"
    fileName="$fileName ${var}"/info.xml
done
git commit -m 'Updation of CiviHR Version and Release date' $fileName
git tag -a ${version} -m "HR version ${version}"