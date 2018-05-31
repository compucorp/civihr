<?php

trait CRM_HRCore_Upgrader_Steps_1004 {

  /**
   * Upgrader to set all 'Personal' addresses as primary.
   *
   * @return bool
   */
  public function upgrade_1004() {
    $this->up1004_setAllPersonalAddressesAsPrimary();

    return TRUE;
  }

  private function up1004_setAllPersonalAddressesAsPrimary() {
    civicrm_api3('Address', 'get', [
      'sequential' => 1,
      'location_type_id' => 'Personal',
      'is_primary' => 0,
      'api.Address.create' => ['id' => '$value.id', 'is_primary' => 1],
    ]);
  }
}
