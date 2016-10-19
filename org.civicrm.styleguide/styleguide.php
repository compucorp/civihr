<?php

require_once 'styleguide.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function styleguide_civicrm_config(&$config) {
  _styleguide_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function styleguide_civicrm_xmlMenu(&$files) {
  _styleguide_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function styleguide_civicrm_install() {
  _styleguide_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function styleguide_civicrm_uninstall() {
  _styleguide_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function styleguide_civicrm_enable() {
  _styleguide_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function styleguide_civicrm_disable() {
  _styleguide_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function styleguide_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _styleguide_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function styleguide_civicrm_managed(&$entities) {
  _styleguide_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function styleguide_civicrm_caseTypes(&$caseTypes) {
  _styleguide_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function styleguide_civicrm_angularModules(&$angularModules) {
_styleguide_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function styleguide_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _styleguide_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function listElementsAsOptions ($type) {
  $files = array();
  $handle=opendir(CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide') . '/' . $type . '/');
  var_dump($handle);
  while (false !== ($file = readdir($handle))):
      if(stristr($file,'.html')):
          $files[] = $file;
      endif;
  endwhile;
echo '<li role="presentation" class="divider"></li><li role="presentation" class="dropdown-header">'.$type.'</li>';
  sort($files);
  foreach ($files as $file):
      $filename = preg_replace("/\.html$/i", "", $file);
      $title = preg_replace("/\-/i", " ", $filename);
      $title = ucwords($title);
      echo '<li><a href="#sg-'.$filename.'">'.$title.'</a></li>';
  endforeach;
}

// Display title of each markup samples as a select option
function listMarkupAsOptions ($type) {
  $files = array();
  $handle=opendir(CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide') . '/markup/' . $type);
  while (false !== ($file = readdir($handle))):
      if(stristr($file,'.html')):
          $files[] = $file;
      endif;
  endwhile;

  sort($files);
  foreach ($files as $file):
      $filename = preg_replace("/\.html$/i", "", $file);
      $title = preg_replace("/\-/i", " ", $filename);
      $title = ucwords($title);
      echo '<li><a href="#sg-'.$filename.'">'.$title.'</a></li>';
  endforeach;
}
  // Display foundation elements
  function showDocs() {
    $files = array();
    $handle=opendir(CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide') . '/docs/');
    while (false !== ($file = readdir($handle))):
        if(stristr($file,'.html')):
            $files[] = $file;
        endif;
    endwhile;

    sort($files);
    foreach ($files as $file):
        $filename = preg_replace("/\.html$/i", "", $file);
        $title = preg_replace("/\-/i", " ", $filename);
        echo '<div class="col-lg-12 sg-section" id="sg-'.$filename.'">';
        echo '<div class="sg-display">';
        echo '<h2 class="sg-h2">'.$title.'</h2>';
        include('docs/'.$file);
        echo '</div><!--/.sg-display-->';
        echo '</div><!--/.sg-section-->';
    endforeach;
  }

// Display foundation elements
function showFoundation() {
  $files = array();
  $handle=opendir(CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide') . '/foundation/');
  while (false !== ($file = readdir($handle))):
      if(stristr($file,'.html')):
          $files[] = $file;
      endif;
  endwhile;

  sort($files);
  foreach ($files as $file):
      $filename = preg_replace("/\.html$/i", "", $file);
      $title = preg_replace("/\-/i", " ", $filename);
      echo '<div class="col-lg-12 sg-section" id="sg-'.$filename.'">';
      echo '<div class="sg-display">';
      echo '<h2 class="sg-h2">'.$title.'</h2>';
      include('foundation/'.$file);
      echo '</div><!--/.sg-display-->';
      echo '</div><!--/.sg-section-->';
  endforeach;
}

// Display markup view & source
function showMarkup($type) {
  $files = array();
  $handle=opendir(CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide') . '/markup/'.$type);
  while (false !== ($file = readdir($handle))):
      if(stristr($file,'.html')):
          $files[] = $file;
      endif;
  endwhile;

  sort($files);
  foreach ($files as $file):
      $filename = preg_replace("/\.html$/i", "", $file);
      $title = preg_replace("/\-/i", " ", $filename);
      $documentation = CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide') . '/usage/'.$type.'/'.$file;
      echo '<div class="sg-markup sg-section">';
      echo '<div class="sg-display">';
      echo '<h2 class="sg-h2"><a id="sg-'.$filename.'" class="sg-anchor">'.$title.'</a></h2>';
      //echo '<div class="col-md-10 col-md-offset-1">';
      echo '<div class="row"><div class="col-md-8">';
      echo '<h3 class="sg-h3">Example</h3>';
      include('markup/'.$type.'/'.$file);
      echo '</div>';
       if (file_exists($documentation)) {
        echo '<div class="col-md-4"><div class="well sg-doc">';
        echo '<h3 class="sg-h3">Usage Notes</h3>';
        include($documentation);
        echo '</div></div></div>';
      }
      echo '</div><!--/.sg-display-->';
      echo '<div class="sg-markup-controls"><a class="btn btn-primary sg-btn sg-btn--source" href="#">View Source</a> <a class="sg-btn--top" href="#top">Back to Top</a> </div>';
      echo '<div class="sg-source sg-animated">';
      echo '<a class="btn btn-default sg-btn sg-btn--select" href="#">Copy Source</a>';
      echo '<pre class="prettyprint linenums"><code>';
      echo htmlspecialchars(file_get_contents(CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide') . '/markup/'.$type.'/'.$file));
      echo '</code></pre>';
      echo '</div><!--/.sg-source-->';
      //echo '</div><!--/.colmd10-->';
      echo '</div><!--/.sg-section-->';
  endforeach;
}
