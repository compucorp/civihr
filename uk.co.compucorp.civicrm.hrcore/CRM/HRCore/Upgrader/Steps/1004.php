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
    civicrm_api3('Address', 'get', [
      'sequential' => 1,
      'location_type_id' => 'Home',
      'is_primary' => 0,
      'api.Address.create' => ['id' => '$value.id', 'is_primary' => 1],
    ]);
  }
}
