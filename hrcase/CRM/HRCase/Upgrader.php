<?php

/**
 * Collection of upgrade steps
 */
class CRM_HRCase_Upgrader extends CRM_HRCase_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    $this->setComponentStatuses(array(
      'CiviCase' => true,
    ));
  }

  /**
   * Set components as enabled or disabled. Leave any other
   * components unmodified.
   *
   * Note: This API has only been tested with CiviCRM 4.4.
   *
   * @param array $components keys are component names (e.g. "CiviMail"); values are booleans
   * @throws CRM_Core_Exception
   */
  public function setComponentStatuses($components) {
    $getResult = civicrm_api3('setting', 'getsingle', array(
      'domain_id' => CRM_Core_Config::domainID(),
      'return' => array('enable_components'),
    ));
    if (!is_array($getResult['enable_components'])) {
      throw new CRM_Core_Exception("Failed to determine component statuses");
    }

    // Merge $components with existing list
    $enableComponents = $getResult['enable_components'];
    foreach ($components as $component => $status) {
      if ($status) {
        $enableComponents = array_merge($enableComponents, array($component));
      } else {
        $enableComponents = array_diff($enableComponents, array($component));
      }
    }
    civicrm_api3('setting', 'create', array(
      'domain_id' => CRM_Core_Config::domainID(),
      'enable_components' => array_unique($enableComponents),
    ));
    CRM_Core_Component::flushEnabledComponents();
  }

  // Note : if you want to add a new upgrader method , start from 1401 or above ( e.g : upgrader_1401 )


}
