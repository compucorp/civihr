<?php

trait CRM_HRCore_Upgrader_Steps_1004 {

  /**
   * Upgrader to set 'Home' as default location and set all 'Home' addresses as
   * primary.
   *
   * @return bool
   */
  public function upgrade_1004() {
    $this->up1004_setHomeLocationAsDefault();
    $this->up1004_setAllHomeAddressesAsPrimary();

    return true;
  }

  private function up1004_setHomeLocationAsDefault() {
    civicrm_api3('LocationType', 'get', [
      'sequential' => 1,
      'name' => 'Home',
      'api.LocationType.create' => [
        'id' => '$value.id',
        'is_default' => 1,
        'is_reserved' => 1,
      ],
    ]);
  }

  private function up1004_setAllHomeAddressesAsPrimary() {
    $homeLocation = civicrm_api3('LocationType', 'getsingle', [
      'name' => 'Home',
    ]);

    $contactsWithNonPrimaryHomeAddress = civicrm_api3('Address', 'get', [
      'sequential' => 1,
      'return' => ['contact_id'],
      'location_type_id' => $homeLocation['id'],
      'is_primary' => 0,
    ]);

    $contactIDs = [];
    foreach ($contactsWithNonPrimaryHomeAddress['values'] as $currentContact) {
      $contactIDs[] = $currentContact['contact_id'];
    }

    if (!empty($contactIDs)) {
      $query = "
        UPDATE civicrm_address
        SET is_primary = CASE WHEN location_type_id = {$homeLocation['id']} THEN 1 ELSE 0 END
        WHERE contact_id IN (" . implode(',', $contactIDs) . ")
      ";
      CRM_Core_DAO::executeQuery($query);
    }
  }
}
