Scripts in this directory :

 * **build-dao**: Used to generate DAO files from XML files
 due to some problems with GenCode.php script when using it for extensions.

 * **drush-install**: A script that get called via the buildkit to install CiviHR
 extensions and preparing some configurations on a drupal+CiviCRM site.

 * **git-release**: A script that prepares CiviHR for release by updating extensions
 info files version and release data.

 * **pre-commit**: Git pre-commit hook script that run civilint on staged files to ensure there
 is no any code standard valuations before committing the changes. it need to be moved
 to your *.git/hooks/* directory in order for it to work on your local installation.

 * **civihr-crm-api-tests**: Legacy script to run all CiviHR extensions API tests.(require updating)

 * **civihr-webtests**: Legacy Script to run all CiviHR extensions Web tests.(require updating)

