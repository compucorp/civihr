<?php

trait CRM_HRCore_Upgrader_Steps_1031 {

  /**
   * Sets up option values, custom group and custom field
   * for case type categorization
   *
   * @return bool
   */
  public function upgrade_1031() {
    $optionValues = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'cg_extend_objects',
      'name' => 'civicrm_case_type'
    ]);
    if ($optionValues['count'] == 0) {
      $params = [
        'option_group_id' => 'cg_extend_objects',
        'name' => 'civicrm_case_type',
        'label' => ts('Case Type'),
        'value' => 'CaseType',
      ];
      $this->createOptionValue($params);
    }

    $customGroups = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'CaseType',
      'name' => 'case_type_category',
    ]);
    if ($customGroups['count'] == 0) {
      $this->createCaseTypeCategoryCustomGroup();
    }

    $customFields = civicrm_api3('CustomField', 'get', [
      'custom_group_id' => 'case_type_category',
    ]);
    if ($customFields['count'] == 0) {
      $optionGroupId = $this->createCaseTypeCategoryOptionValues();
      if ($optionGroupId == null) {
        return FALSE;
      }

      $this->createCaseTypeCategoryCustomField($optionGroupId);
    }

    return TRUE;
  }

  /**
   * Creates option group
   *
   * @param array $params
   *
   * @return array
   */
  private function createOptionGroup($params) {
    return civicrm_api3('OptionGroup', 'create', $params);
  }

  /**
   * Creates option value
   *
   * @param array $params
   */
  private function createOptionValue($params) {
    civicrm_api3('OptionValue', 'create', $params);
  }

  /**
   * Creates case type category custom group
   */
  private function createCaseTypeCategoryCustomGroup() {
    civicrm_api3('CustomGroup', 'create', [
      'title' => ts('Case Type Category'),
      'extends' => 'CaseType',
      'name' => 'case_type_category',
      'table_name' => 'civicrm_value_case_type_category'
    ]);
  }

  /**
   * Sets up option group and values used for case type category custom field
   *
   * @return null
   */
  private function createCaseTypeCategoryOptionValues() {
    $result = civicrm_api3('OptionGroup', 'get', [
      'name' => 'case_type_category',
    ]);
    if ($result['count'] == 0) {
      $optionValues = ['Workflow', 'Vacancy'];
      $groupParams = [
        'name' => 'case_type_category',
        'title' => 'Category',
      ];

      $optionGroupResult = $this->createOptionGroup($groupParams);
      foreach ($optionValues as $optionValue) {
        $valueParams = [
          'option_group_id' => 'case_type_category',
          'label' => $optionValue,
          'name' => $optionValue,
          'value' => $optionValue
        ];
        $this->createOptionValue($valueParams);
      }

      return $optionGroupResult['id'];
    }

    return null;
  }

  /**
   * Creates category custom field for case type category custom group
   *
   * @param int $optionGroupId
   */
  private function createCaseTypeCategoryCustomField($optionGroupId) {
    civicrm_api3('CustomField', 'create', [
      'custom_group_id' => 'case_type_category',
      'label' => 'Category',
      'name' => 'category',
      'data_type' => 'String',
      'html_type' => 'Select',
      'is_required' => 1,
      'column_name' => 'category',
      'option_group_id' => $optionGroupId,
    ]);
  }

}
