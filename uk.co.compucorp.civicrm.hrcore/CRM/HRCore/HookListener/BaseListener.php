<?php

class CRM_HRCore_HookListener_BaseListener {

  /**
   * get tab options from DB using setting-get api
   */
  protected function getViewOptionsSetting() {
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

  protected function isExtensionEnabled($key) {
    $isEnabled = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Extension',
      $key,
      'is_active',
      'full_name'
    );

    return !empty($isEnabled) ? true : false;
  }

  /**
   * Enable/Disable Menu items created by hrui extension
   *
   */
  protected function menuSetActive($isActive) {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_navigation SET is_active = {$isActive} WHERE name = 'import_custom_fields'");
    CRM_Core_BAO_Navigation::resetNavigation();
  }

  protected function setActiveFields($setActive) {
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

  /**
   * set modified options in the DB using setting-create api
   */
  protected function setViewOptionsSetting($options = array()) {
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

  protected function wordReplacement($isActive) {
    if( $isActive) {
      CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'CiviCRM News' WHERE name = 'blog' ");
      CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'Case Dashboard Dashlet' WHERE name = 'casedashboard' ");
    }
    else {
      CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'CiviHR News' WHERE name = 'blog' ");
      CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET label = 'Assignments Dashlet' WHERE name = 'casedashboard' ");
    }
  }
}
