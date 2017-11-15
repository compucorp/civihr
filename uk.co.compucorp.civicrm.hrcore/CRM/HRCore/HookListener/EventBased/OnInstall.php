<?php

class CRM_HRCore_HookListener_EventBased_OnInstall extends CRM_HRCore_HookListener_BaseListener {

  public function handle() {
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
    $options = $this->getViewOptionsSetting();

    // unset activity & tag tab options
    foreach ($tabsToUnset as $key) {
      unset($options[$key]);
    }
    $options = array_keys($options);

    // set modified options in the DB
    $this->setViewOptionsSetting($options);
    $this->setActiveFields(FALSE);

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

    $this->wordReplacement(FALSE);
  }
}
