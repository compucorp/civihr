<?php

class CRM_HRCore_HookListener_BaseListener {

  public static function onConfig(&$config) {
    self::updateCiviSettings();
    self::addSmartyPluginDir();
  }

  public static function onEnable() {
    self::setActiveFields(FALSE);
    self::wordReplacement(FALSE);
    self::menuSetActive(1);
  }

  public static function onInstall() {
    //delete default tag of civicrm
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_tag WHERE name IN ('Non-profit', 'Company', 'Government Entity', 'Major Donor', 'Volunteer')");

    // make sure only relevant components are enabled
    $params = array(
      'domain_id' => CRM_Core_Config::domainID(),
      'enable_components' => array('CiviReport','CiviCase'),
    );
    $result = civicrm_api3('setting', 'create', $params);
    if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
      CRM_Core_Error::debug_var('setting-create result for enable_components', $result);
      throw new CRM_Core_Exception('Failed to create settings for enable_components');
    }

    // Disable Household contact type
    $contactTypeId = CRM_Core_DAO::getFieldValue(
      'CRM_Contact_DAO_ContactType',
      'Household',
      'id',
      'name'
    );
    if ($contactTypeId) {
      $paramsContactType = array(
        'name' => "Household",
        'id' => $contactTypeId,
        'is_active' => FALSE,
      );
      $resultContactType = civicrm_api3('contact_type', 'create', $paramsContactType);
      if (CRM_Utils_Array::value('is_error', $resultContactType, FALSE)) {
        CRM_Core_Error::debug_var('contact_type-create result for is_active', $resultContactType);
        throw new CRM_Core_Exception('Failed to disable contact type');
      }
    }

    // Delete unnecessary reports
    $reports = array("Constituent Summary", "Constituent Detail", "Current Employers");
    if (!empty($reports)) {
      foreach ($reports as $reportTitle) {
        $reportID = CRM_Core_DAO::getFieldValue(
          'CRM_Report_DAO_ReportInstance',
          $reportTitle,
          'id',
          'title'
        );
        if ($reportID) {
          $paramsReport = array(
            'id' => $reportID,
          );
          $resultContactType = civicrm_api3('report_instance', 'delete', $paramsReport);
          if (CRM_Utils_Array::value('is_error', $resultContactType, FALSE)) {
            CRM_Core_Error::debug_var('contact_type-create result for is_active', $resultContactType);
            throw new CRM_Core_Exception('Failed to disable contact type');
          }
        }
      }
    }

    // Reset Navigation
    CRM_Core_BAO_Navigation::resetNavigation();

    // get a list of all tab options
    $options = CRM_Core_OptionGroup::values('contact_view_options', TRUE, FALSE);
    $tabsToUnset = array($options['Activities'], $options['Tags']);

    // get tab options from DB
    $options = self::getViewOptionsSetting();

    // unset activity & tag tab options
    foreach ($tabsToUnset as $key) {
      unset($options[$key]);
    }
    $options = array_keys($options);

    // set modified options in the DB
    self::setViewOptionsSetting($options);
    self::setActiveFields(FALSE);

    //hide communication preferences block
    $groupID = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_OptionGroup',
      'contact_edit_options',
      'id',
      'name'
    );

    $params = array(
      'option_group_id' => $groupID,
      'name' => 'CommunicationPreferences',
    );

    CRM_Core_BAO_OptionValue::retrieve($params, $defaults);
    $defaults['is_active'] = 0;
    CRM_Core_BAO_OptionValue::create($defaults);

    // Change the blog URL
    civicrm_api3('setting', 'create', array(
      'blogUrl' => 'https://civicrm.org/taxonomy/term/198/feed',
    ));

    self::wordReplacement(FALSE);
  }

  public static function onUninstall() {
    // get a list of all tab options
    $options = CRM_Core_OptionGroup::values('contact_view_options', TRUE, FALSE);
    $tabsToSet = array($options['Activities'], $options['Tags']);

    // get tab options from DB
    $options = self::getViewOptionsSetting();

    // set activity & tag tab options
    foreach ($tabsToSet as $key) {
      $options[$key] = 1;
    }
    $options = array_keys($options);

    // set modified options in the DB
    self::setViewOptionsSetting($options);
    self::setActiveFields(TRUE);
    // show communication preferences block
    $groupID = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_OptionGroup',
      'contact_edit_options',
      'id',
      'name'
    );

    $params = array(
      'option_group_id' => $groupID,
      'name' => 'CommunicationPreferences',
    );

    CRM_Core_BAO_OptionValue::retrieve($params, $defaults);
    $defaults['is_active'] = 1;
    CRM_Core_BAO_OptionValue::create($defaults);
    self::wordReplacement(TRUE);

    // Remove 'Import Custom Fields' Navigation item and reset the menu
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name = 'import_custom_fields'");
    CRM_Core_BAO_Navigation::resetNavigation();
  }

  protected function isExtensionEnabled($key) {
    $isEnabled = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Extension',
      $key,
      'is_active',
      'full_name'
    );

    return !empty($isEnabled) ? true : false;
  }

  private static function updateCiviSettings() {
    global $civicrm_setting;
    $civicrm_setting['CiviCRM Preferences']['communityMessagesUrl'] = FALSE;
  }

  private static function addSmartyPluginDir() {
    $smarty = CRM_Core_Smarty::singleton();
    $extensionPath = CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.hrcore');

    array_push($smarty->plugins_dir, $extensionPath . '/CRM/Smarty/plugins');
  }

  /**
   * get tab options from DB using setting-get api
   */
  private static function getViewOptionsSetting() {
    $domainID = CRM_Core_Config::domainID();
    $params = [
      'domain_id' => $domainID,
      'return' => 'contact_view_options',
    ];
    $result = civicrm_api3('setting', 'get', $params);

    if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
      CRM_Core_Error::debug_var('setting-get result for contact_view_options', $result);
      throw new CRM_Core_Exception('Failed to retrieve settings for contact_view_options');
    }

    return array_flip($result['values'][$domainID]['contact_view_options']);
  }

  /**
   * set modified options in the DB using setting-create api
   */
  private static function setViewOptionsSetting($options = array()) {
    $domainID = CRM_Core_Config::domainID();
    $params = array(
      'domain_id' => $domainID,
      'contact_view_options' => $options,
    );
    $result = civicrm_api3('setting', 'create', $params);

    if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
      CRM_Core_Error::debug_var('setting-create result for contact_view_options', $result);
      throw new CRM_Core_Exception('Failed to create settings for contact_view_options');
    }

    return TRUE;
  }


  private static function setActiveFields($setActive) {
    $setActive = $setActive ? 1 : 0;
    //disable/enable optionGroup and optionValue
    $query = "UPDATE civicrm_option_value JOIN civicrm_option_group ON civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name IN ('custom_most_important_issue', 'custom_marital_status')";
    CRM_Core_DAO::executeQuery($query);
    CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name IN ('custom_most_important_issue', 'custom_marital_status')");

    //disable/enable customgroup and customvalue
    $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'constituent_information'";
    CRM_Core_DAO::executeQuery($sql);
    CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name = 'constituent_information'");
    CRM_Core_DAO::executeQuery("UPDATE civicrm_relationship_type SET is_active = {$setActive} WHERE name_a_b IN ( 'Employee of', 'Head of Household for', 'Household Member of' )");
  }

  private static function wordReplacement($isActive) {
    if( $isActive) {
      CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'CiviCRM News' WHERE name = 'blog' ");
      CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'Case Dashboard Dashlet' WHERE name = 'casedashboard' ");
    }
    else {
      CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'CiviHR News' WHERE name = 'blog' ");
      CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'Assignments Dashlet' WHERE name = 'casedashboard' ");
    }
  }

  /**
   * Enable/Disable Menu items created by hrui extension
   *
   */
  private static function menuSetActive($isActive) {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_navigation SET is_active = {$isActive} WHERE name = 'import_custom_fields'");
    CRM_Core_BAO_Navigation::resetNavigation();
  }
}
