#!/bin/bash

##########################################################
# Shows the scripts description and usage options
#
# Arguments:
#   None
# Returns:
#   None
# Usage:
#   show_usage
##########################################################
function show_usage() {
  cat <<EOT
Description: Imports or clears the L&A Sample Data

Usage:
  sample_data.sh <action>

The possible values for action are:
  import: Imports the Sample Data to the system
  clear: Removes the Sample Data from the system
EOT
}

##########################################################
# Checks if all the necessary commands for the script are
# available
#
# Arguments:
#   None
# Returns:
#   None
# Usage:
#   check_requirements
##########################################################
function check_requirements() {
  command -v cv > /dev/null 2>&1 || { echo "Please download cv to continue."; exit 1; }
  command -v drush > /dev/null 2>&1 || { echo "Please download drush to continue."; exit 1; }
}

##########################################################
# Executes the given SQL file on top of the CiviCRM database
#
# Arguments:
#   A sql file inside the L&A sql folder
# Returns:
#   None
# Usage:
#   execute_la_sql_file FILENAME
##########################################################
function execute_la_sql_file() {
  FILE=$1
  EXT_PATH=$(cv path -x uk.co.compucorp.civicrm.hrleaveandabsences)
  drush civicrm-sql-query --file="$EXT_PATH/sql/$1" > /dev/null
}

check_requirements

if [ $1 == "import" ]; then
  execute_la_sql_file "sample_data.sql"
elif [ $1 == "clear" ]; then
  execute_la_sql_file "sample_data_cleanup.sql"
else
  show_usage
  exit 99
fi
