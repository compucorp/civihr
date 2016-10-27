<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait HRRecruitmentTestTrait {

  /**
   * Creates single (Individuals) contact from the provided data.
   *
   * @param array $params should contain first_name and last_name
   * @return int return the contact ID
   * @throws \CiviCRM_API3_Exception
   */
  protected function createContact($params) {
    $result = civicrm_api3('Contact', 'create', array(
      'contact_type' => "Individual",
      'first_name' => $params['first_name'],
      'last_name' => $params['last_name'],
      'display_name' => $params['first_name'] . ' ' . $params['last_name'],
    ));
    return $result['id'];
  }

  /**
   * Create option value for a specific option group
   * and if the group is not exist it will be created
   * before creating the option value.
   *
   * @param array $params Should contain 'name'
   * @param string $group Option group name
   *
   * @return string Option value (value)
   * @throws \CiviCRM_API3_Exception
   */
  protected function createOptionValue($params, $group) {
    $groupParams = ['name' => $group];
    $defaults = NULL;
    $optionGroup = CRM_Core_BAO_OptionGroup::retrieve($groupParams, $defaults);

    if (empty($optionGroup->id)) {
      $groupParams['is_active'] = 1;
      $optionGroup = CRM_Core_BAO_OptionGroup::add($groupParams);
    }
    $optionGroupID = $optionGroup->id;

    $params['option_group_id'] = $optionGroupID;
    $params['sequential'] = 1;
    $optionValue = civicrm_api3('OptionValue', 'create', $params);

    return $optionValue['values'][0]['value'];
  }

}
