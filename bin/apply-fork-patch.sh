#!/bin/bash

API_URL_BASE="https://api.github.com/repos/compucorp/civicrm-core"
LAST_REMOTE_COMMIT_FILE="last-remote-commit.json"
META_FILE="fork-patch-meta.json"
PATCH_FILE="fork-patch.diff"

civiRoot=""

#######################################
# Applies the patch on the core files
#
# Globals:
#   $civiRoot
#   $PATCH_FILE
# Arguments:
#   None
# Returns:
#   None
#######################################
applyPatch () {
  (cd "$civiRoot" && git apply --whitespace=nowarn "$PATCH_FILE")
}

#######################################
# Creates a temporary file containing the info related to the last
# commit of the given branch, fetched via the GitHub api
#
# Globals:
#   $API_URL_BASE
#   $civiRoot
#   $PATCH_FILE
# Arguments:
#   $1 name of the branch
# Returns:
#   None
#######################################
createLastRemoteCommitFile () {
  curl "$API_URL_BASE/commits/$1" -s > "$civiRoot/$LAST_REMOTE_COMMIT_FILE"
}

#######################################
# Creates a diff patch file by sending a request to the given GitHub API url
#
# Globals:
#   $API_URL_BASE
#   $civiRoot
#   $PATCH_FILE
# Arguments:
#   $1 base commit of the comparison
#   $2 head commit of the comparison
# Returns:
#   None
#######################################
createPatch () {
  curl "$API_URL_BASE/compare/$1...$2" -s -H "Accept: application/vnd.github.v3.diff" > "$civiRoot/$PATCH_FILE"
}

#######################################
# Reads or writes a property's value from a JSON file
#
# Globals:
#   None
# Arguments:
#   $1 JSON file
#   $2 property name
#   $3 new value of the property (optional)
# Returns:
#   None/String
#######################################
JSONValue () {
  if ! [ -z "$3" ]; then
    JSONValueWrite "$1" "$2" "$3"
  else
    JSONValueRead "$1" "$2"
  fi
}

#######################################
# Uses Python to read the property's value of a JSON file
#
# Globals:
#   None
# Arguments:
#   $1 JSON file
#   $2 property name
# Returns:
#   String
##
JSONValueRead () {
  python -c "import sys, json; print json.load(sys.stdin)['$2']" < "$1"
}

#######################################
# Uses Python to update the property's value of a JSON file
# The resulting JSON then overwrites the old content of the file
#
# Globals:
#   None
# Arguments:
#   $1 JSON file
#   $2 property name
#   $3 new value of the property
# Returns:
#   None
#######################################
JSONValueWrite () {
  updatedJSON=$(python -c "import sys, json; \
  jsonFile = json.load(sys.stdin); \
  jsonFile['$2'] = '$3'; \
  print json.dumps(jsonFile, indent=2);" < "$1")

  echo "$updatedJSON" > "$1"
}

#######################################
# Checks if the meta file exists or not
#
# Globals:
#   $civiRoot
#   $META_FILE
# Arguments:
#   None
# Returns:
#   Integer
#######################################
metaFileExists () {
  if [ -e "$civiRoot/$META_FILE" ]; then return 0; else return 1; fi
}

#######################################
# Returns the civicrm root path
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   String
#######################################
setCivicrmRootPath () {
  civiRoot=$(drush eval "echo \\Civi::paths()->getPath('[civicrm.root]/.')")
}

#######################################
# Updates the reference, in the META_FILE, to the last commit patched onto
# the core files
#
# Globals:
#   $LAST_REMOTE_COMMIT_FILE
#   $civiRoot
#   $META_FILE
# Arguments:
#   None
# Returns:
#   None
#######################################
updateLastCommitPatched () {
  sha=$(JSONValue "$civiRoot/$LAST_REMOTE_COMMIT_FILE" 'sha')

  JSONValue "$civiRoot/$META_FILE" "last-fork-commit-patched" "$sha"
}

# ---------------

setCivicrmRootPath
if ! metaFileExists; then exit 0; fi

civiVersion=$(JSONValue "$civiRoot/$META_FILE" 'civi-version')
lastCommitPatched=$(JSONValue "$civiRoot/$META_FILE" "last-fork-commit-patched")

patchesBranch="$civiVersion-patches"
[ ! -z "$lastCommitPatched" ] && baseHead=$lastCommitPatched || baseHead=$civiVersion

createPatch "$baseHead" "$patchesBranch"
applyPatch && rm "$civiRoot/$PATCH_FILE"

createLastRemoteCommitFile "$patchesBranch"
updateLastCommitPatched && rm "$civiRoot/$LAST_REMOTE_COMMIT_FILE"
