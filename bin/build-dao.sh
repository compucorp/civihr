#!/usr/bin/env bash
#####################################################################################
# This script uses GenCode.php to generate the files and since GenCode has some
# problems dealing with extensions entities, the script does some tricks to get
# thing done. It works by adding the extension entities schema xml files to the
# CiviCRM own schema files, making it look like your entities are part of CiviCRM
# schema. Next it runs GenCode.php, which will generate DAOs for all the given
# entities (Civi's and your extension), and finally it copies your extension's
# DAOs to your extension CRM/NAMESPACE/DAO directory.

CIVIROOT="$1"
EXTROOT="$2"
NAMESPACE="$3"
EXTSRCDIR="$EXTROOT/CRM/$NAMESPACE"
EXTDAODIR="$EXTSRCDIR/DAO"
XMLBUILD="$EXTROOT/build/xml/schema"

function validateOptions() {
  if [ -z "$CIVIROOT" -o ! -d "$CIVIROOT" ]; then
    echo "ERROR: invalid civicrm-dir: [$CIVIROOT]"
    printUsage
  fi

  if [ -z "$EXTROOT" -o ! -d "$EXTROOT" ]; then
    echo "ERROR: invalid extension-dir: [$EXTROOT]"
    printUsage
  fi

  if [ -z "$NAMESPACE" -o ! -d "$EXTSRCDIR" ]; then
    echo "ERROR: invalid namespace: [$NAMESPACE]"
    printUsage
  fi
}

function printUsage() {
  echo ""
  echo "usage: $0 <civicrm-dir> <extension-dir> <namespace>"
  echo "example: $0 /var/www/drupal/sites/all/modules/civicrm /var/www/drupal/sites/all/modules/civicrm/tools/extensions/civihr/your-extension YourNamespace"
  exit
}

## Make a tempdir, $ext/build/xml/schema; compile full XML tree
function buildXmlSchema() {
  mkdir -p "$XMLBUILD"

  ## Mix together main xml files
  cp -fr "$CIVIROOT"/xml/schema/* "$XMLBUILD/"
  cp -fr "$EXTROOT"/xml/schema/* "$XMLBUILD/"

  ## Build root xml file
  ## We build on the core Schema.xml so that we don't have to do as much work to
  ## manage inter-table dependencies
  grep -v '</database>' "$CIVIROOT"/xml/schema/Schema.xml > "$XMLBUILD"/Schema.xml
  cat "$XMLBUILD"/Schema.xml.inc >> "$XMLBUILD"/Schema.xml
  echo '</database>' >> "$XMLBUILD"/Schema.xml
}

## Run GenCode; copy out the DAOs
function buildDAO() {
  pushd $CIVIROOT/xml > /dev/null
    php GenCode.php $XMLBUILD/Schema.xml
  popd > /dev/null

  [ ! -d "$EXTDAODIR" ] && mkdir -p "$EXTDAODIR"
  cp -f "$CIVIROOT/CRM/$NAMESPACE/DAO"/* "$EXTDAODIR/"
}

function cleanup() {
  for DIR in "$EXTROOT/build" "$CIVIROOT/CRM/$NAMESPACE" ; do
    if [ -e "$DIR" ]; then
      echo "Cleanup: removing $DIR"
      rm -rf "$DIR"
    fi
  done
}


##############################
## Main
set -e
validateOptions
cleanup
buildXmlSchema
buildDAO
cleanup
echo
echo "If there have been XML schema changes, then be sure to manually update the .sql files!"
