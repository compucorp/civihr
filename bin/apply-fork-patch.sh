#!/bin/bash

API_URL_BASE="https://api.github.com/repos/compucorp/civicrm-core"
LAST_COMMIT_PATCHED_FILE="core-fork-last-commit-patched.txt"
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
# Programmatically gets the current CiviCRM version
#
# Globals:
#   None
# Arguments:
#   None
# Returns:
#   String
#######################################
getCiviVersion () {
  drush eval "echo CRM_Utils_System::version();"
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
#######################################
JSONValue () {
  python -c "import sys, json; print json.load(sys.stdin)['$2']" < "$1"
}

#######################################
# Checks if the meta file exists or not
#
# Globals:
#   $civiRoot
#   $LAST_COMMIT_PATCHED_FILE
# Arguments:
#   None
# Returns:
#   Integer
#######################################
metaFileExists () {
  if [ -e "$civiRoot/$LAST_COMMIT_PATCHED_FILE" ]; then return 0; else return 1; fi
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
# Updates the reference, in the LAST_COMMIT_PATCHED_FILE, to the last commit
# patched onto the core files
#
# Globals:
#   $civiRoot
#   $LAST_COMMIT_PATCHED_FILE
# Arguments:
#   None
# Returns:
#   None
#######################################
updateLastCommitPatched () {
  # It uses the same file as temporary recipient of the full commit data
  curl "$API_URL_BASE/commits/$1" -s > "$civiRoot/$LAST_COMMIT_PATCHED_FILE"
  sha=$(JSONValue "$civiRoot/$LAST_COMMIT_PATCHED_FILE" "sha")

  echo "$sha" > "$civiRoot/$LAST_COMMIT_PATCHED_FILE"
}

# ---------------

setCivicrmRootPath

if ! metaFileExists; then
  echo "No $LAST_COMMIT_PATCHED_FILE file found in $civiRoot, skipping compucorp:civicrm-core patch"
  exit 0
fi

civiVersion=$(getCiviVersion)
lastCommitPatched=$(cat "$civiRoot/$LAST_COMMIT_PATCHED_FILE")

patchesBranch="$civiVersion-patches"
[ ! -z "$lastCommitPatched" ] && baseHead=$lastCommitPatched || baseHead=$civiVersion


echo "Fetching compucorp:civicrm-core patch..."
createPatch "$baseHead" "$patchesBranch"

if [ -s "$civiRoot/$PATCH_FILE" ]; then
  echo "Applying compucorp:civicrm-core patch..."
  applyPatch

  echo "Updating reference to SHA of last commit patched..."
  updateLastCommitPatched "$patchesBranch"

  echo "Patch applied"
else
  echo "Patch was empty, no diffs found"
fi

rm "$civiRoot/$PATCH_FILE"
