
<?php

trait CRM_HRCore_Upgrader_Steps_1019 {

  /**
   * Migrating Phone Types and Deleting unwanted Phone Types
   */
  public function upgrade_1019() {
    $this->up1019_migratePhoneTypes();
    $this->up1019_relabelPhoneType('Phone', 'Landline');
    $this->up1019_reservePhoneTypes(['Phone', 'Mobile']);
    $this->up1019_deletePhoneTypes(['Fax', 'Pager', 'Voicemail']);

    return TRUE;
  }

  /**
   * Migrate the Phone Type Data as Follows:
   * Fax -> Phone
   * Pager -> Mobile
   * Voicemail -> Mobile
   */
  private function up1019_migratePhoneTypes() {
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
        civicrm_api3('Phone', 'create', [
          'id' => $phone['id'],
          'phone_type_id' => $newType,
        ]);
      }
    }
  }

  /**
   * Changes the Label of Phone to Landline
   *
   * @param string $originalName the name of the phone_type to change
   * @param string $newLabel the new Label to set to the phone_type
   */
  private function up1019_relabelPhoneType($originalName, $newLabel) {
    $result = civicrm_api3('OptionValue', 'get', [
      'return' => ['id', 'value', 'label'],
      'option_group_id' => 'phone_type',
      'name' => $originalName,
    ]);
    if ($result['count'] != 1) {
      return;
    }
    $optionValue = array_shift($result['values']);
    civicrm_api3('OptionValue', 'create', [
      'id' => $optionValue['id'],
      'label' => $newLabel,
    ]);
  }

  /**
   * Marks the Phone Types as Reserved
   *
   * @param array $phoneTypes
   *
   */
  private function up1019_reservePhoneTypes($phoneTypes) {
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
      civicrm_api3('OptionValue', 'create', [
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
   */
  private function up1019_deletePhoneTypes($phoneTypes) {
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
      civicrm_api3('OptionValue', 'delete', [
        'id' => $optionValue['id'],
      ]);
    }
  }

}
