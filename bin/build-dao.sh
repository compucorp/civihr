#!/usr/bin/env bash
#####################################################################################
# This script uses GenCode.php to generate the files and since GenCode has some
# problems dealing with extensions entities, the script does some tricks to get
# thing done. It works by adding the extension entities schema xml files to the
# CiviCRM own schema files, making it look like your entities are part of CiviCRM
# schema. Next it runs GenCode.php, which will generate DAOs for all the given
# entities (Civi's and your extension), and finally it copies your extension's
# DAOs to your extension CRM/NAMESPACE/DAO directory.

function validatePaths() {
  if [ -z "$CIVIROOT" ]; then
    echo "ERROR: civicrm-dir couldn't be found"
    exit
  fi

  if [ -z "$EXTROOT" ]; then
    echo "ERROR: it was not possible to find a path to $1"
    exit
  fi

  if [ -z "$NAMESPACE" ]; then
    echo "ERROR: it was not possible for find the namespace of $1"
    exit
  fi
}

function printUsage() {
  echo ""
  echo "usage: $0 <extension>"
  echo "example: $0 key.of.your.extension"
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
  cp -f "$CIVIROOT/$NAMESPACE/DAO"/* "$EXTDAODIR/"
}

function cleanup() {
  for DIR in "$EXTROOT/build" "$CIVIROOT/$NAMESPACE" ; do
    if [ -e "$DIR" ]; then
      echo "Cleanup: removing $DIR"
      rm -rf "$DIR"
    fi
  done
}


##############################
## Main
set -e

if [ -z $1 ]; then
  printUsage
fi

CIVIROOT=`cv path -d '[civicrm.root]'`
EXTROOT=`cv path -x $1`
NAMESPACE=`cd $EXTROOT && civix info:get -x civix/namespace`
EXTDAODIR="$EXTROOT/$NAMESPACE/DAO"
XMLBUILD="$EXTROOT/build/xml/schema"

validatePaths
cleanup
buildXmlSchema
buildDAO
cleanup
echo
echo "If there have been XML schema changes, then be sure to manually update the .sql files!"
