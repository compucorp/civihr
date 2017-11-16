<?php

class CRM_HRCore_HookListener_EventBased_OnUninstall extends CRM_HRCore_HookListener_BaseListener {

  public function handle() {
    // get a list of all tab options
    $options = CRM_Core_OptionGroup::values('contact_view_options', TRUE, FALSE);
    $tabsToSet = array($options['Activities'], $options['Tags']);

    // get tab options from DB
    $options = $this->getViewOptionsSetting();

    // set activity & tag tab options
    foreach ($tabsToSet as $key) {
      $options[$key] = 1;
    }
    $options = array_keys($options);

    // set modified options in the DB
    $this->setViewOptionsSetting($options);
    $this->setActiveFields(TRUE);
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
    $this->wordReplacement(TRUE);

    // Remove 'Import Custom Fields' Navigation item and reset the menu
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name = 'import_custom_fields'");
    CRM_Core_BAO_Navigation::resetNavigation();
  }
}
