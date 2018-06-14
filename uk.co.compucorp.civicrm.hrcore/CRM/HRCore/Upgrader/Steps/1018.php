<?php

trait CRM_HRCore_Upgrader_Steps_1018 {

  /**
   * Migrating Phone Types and Deleting unwanted Phone Types
   */
  public function upgrade_1018() {
    $this->migratePhoneTypes();
    $this->relabelPhoneType('Phone', 'Landline');
    $this->reservePhoneTypes(['Landline', 'Mobile']);
    $this->deletePhoneTypes(['Fax', 'Pager', 'Voicemail']);

    return TRUE;
  }


  /**
   * Migrate the Phone Type Data as Follows:
   * Fax -> Phone
   * Pager -> Mobile
   * Voicemai -> Mobile
   */
  private function migratePhoneTypes() {
    $phoneTypes = [
      'Fax' => 'Phone',
      'Pager' => 'Mobile',
      'Voicemail' => 'Mobile',
    ];
    foreach ($phoneTypes as $originalType => $newType) {
      $result = civicrm_api3('Phone', 'get', [
        'sequential' => 1,
        'return' => ['id', 'phone_type_id', 'is_primary', 'phone'],
        'phone_type_id' => $originalType,
      ]);
      if ($result['count'] == 0) {
        continue;
      }

      foreach ($result['values'] as $phone) {
        $result = civicrm_api3('Phone', 'create', [
          'id' => $phone['id'],
          'phone_type_id' => $newType,
        ]);
      }
    }
  }

  /**
   * Changes the Label of Phone to Landline
   *
   * @param string $originalLabel
   * @param string $newLabel
   */
  private function relabelPhoneType($originalLabel, $newLabel) {
    $result = civicrm_api3('OptionValue', 'get', [
      'return' => ['id', 'value', 'label'],
      'option_group_id' => 'phone_type',
      'name' => $originalLabel,
    ]);
    if ($result['count'] != 1) {
      return;
    }
    $optionValue = array_shift($result['values']);
    $result = civicrm_api3('OptionValue', 'create', [
      'id' => $optionValue['id'],
      'label' => $newLabel,
    ]);
  }

  /**
   * Marks the Phone Types as Reserved
   *
   * @param array $phoneTypes
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function reservePhoneTypes($phoneTypes) {
    foreach ($phoneTypes as $phoneType) {
      $result = civicrm_api3('OptionValue', 'get', [
        'return' => ['id', 'value', 'label'],
        'option_group_id' => 'phone_type',
        'name' => $phoneType,
      ]);

      if ($result['count'] != 1) {
        return;
      }

      $optionValue = array_shift($result['values']);
      $result = civicrm_api3('OptionValue', 'create', [
        'id' => $optionValue['id'],
        'is_reserved' => 1,
      ]);
    }
  }

  /**
   * Deletes Phone Types Passed as arguments
   *
   * @param array $phoneTypes
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function deletePhoneTypes($phoneTypes) {
    foreach ($phoneTypes as $phoneType) {
      $result = civicrm_api3('OptionValue', 'get', [
        'return' => ['id', 'value', 'label'],
        'option_group_id' => 'phone_type',
        'name' => $phoneType,
      ]);

      if ($result['count'] != 1) {
        return;
      }

      $optionValue = array_shift($result['values']);
      $result = civicrm_api3('OptionValue', 'delete', [
        'id' => $optionValue['id'],
      ]);
    }
  }

}
