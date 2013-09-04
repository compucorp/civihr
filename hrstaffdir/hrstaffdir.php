<?php

require_once 'hrstaffdir.civix.php';

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrstaffdir_civicrm_pageRun($page) {
  if ($page instanceof CRM_Profile_Page_Listings) {
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrstaffdir', 'js/hrstaffdir.js');
  }
}

/**
 * Implementation of hook_civicrm_config
 */
function hrstaffdir_civicrm_config(&$config) {
  _hrstaffdir_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_searchColumns
 */
function hrstaffdir_civicrm_searchColumns($objectName, &$headers, &$values, &$selector) {
  if ($objectName == 'profile') {
    $profileId = hrstaffdir_getUFGroupID();
    $session = CRM_Core_Session::singleton();
    $gid = CRM_Utils_Request::retrieve('gid', 'Positive', CRM_Core_DAO::$_nullObject);
    // Note: This protocol is not safe when concurrently browsing multiple profile-listings, but
    // that doesn't work anyway, so we can't implement/test a better protocol.
    if (isset($gid) && $profileId == $gid) {
      $session->set('staffDirectoryGid', $gid);
    }
    if ($profileId == $session->get('staffDirectoryGid')) {
      $imageUrlHeader[]["name"] = "";
      $headers = array_merge($imageUrlHeader,$headers);
      foreach ($values as &$value) {
        $found = preg_match('/;id=([^&]*)/', $value[0], $matches);
        if ($found) {
          $imageCol = array();   
          $imageUrl = CRM_Core_DAO::getFieldValue(
                                                  'CRM_Contact_DAO_Contact',
                                                  $matches[1],
                                                  'image_URL',
                                                  'id'
                                                  );   
          $imageCol[] = ($imageUrl)?'<a href="'.$imageUrl.'" class="crm-image-popup"><img src="'.$imageUrl.'" height = "56" width="100"></a>':"";
          $value[1] = "<a href='" . CRM_Utils_System::url('civicrm/profile/view', "reset=1&id={$matches[1]}&gid={$profileId }") . "'>{$value[1]}</a>";
          $value = array_merge($imageCol,$value);
        }
      }
    }
  }
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrstaffdir_civicrm_xmlMenu(&$files) {
  _hrstaffdir_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrstaffdir_civicrm_install() {
  _hrstaffdir_civix_civicrm_install();

  $profileId = hrstaffdir_getUFGroupID();
  if ($profileId) {
    // add to navigation
    $navigationParams =
      array(
        'label' => 'Directory',
        'url' => "civicrm/profile&reset=1&gid={$profileId}&force=1",
        'is_active' => 1,
      );
    $navigation = CRM_Core_BAO_Navigation::add($navigationParams);
    CRM_Core_BAO_Navigation::resetNavigation();
    
    // set the profile as search view
    $params = array();
    CRM_Core_BAO_ConfigSetting::retrieve($params);
    if (!empty($params)) {
      $params['defaultSearchProfileID'] = $profileId;
      CRM_Core_BAO_ConfigSetting::create($params);
    }
  }
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrstaffdir_civicrm_uninstall() {
  return _hrstaffdir_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrstaffdir_civicrm_enable() {
  return _hrstaffdir_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrstaffdir_civicrm_disable() {
  return _hrstaffdir_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function hrstaffdir_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrstaffdir_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrstaffdir_civicrm_managed(&$entities) {
  return _hrstaffdir_civix_civicrm_managed($entities);
}

/**
 * Determine the ID of the profile which defines the staff directory
 *
 * @return int
 */
function hrstaffdir_getUFGroupID() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
  return array_search('hrstaffdir_listing', $groups);
}
