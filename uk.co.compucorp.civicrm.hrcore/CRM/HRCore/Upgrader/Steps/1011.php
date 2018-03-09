<?php

trait CRM_HRCore_Upgrader_Steps_1011 {

  /**
   * Sets the 'Work' Location type to reserved if its not
   * already reserved.
   *
   * @return bool
   */
  public function upgrade_1011() {
    $result = civicrm_api3('LocationType', 'get', [
      'name' => 'Work',
      'is_reserved' => 0,
    ]);

    if ($result['count'] > 0) {
      civicrm_api3('LocationType', 'create', [
        'id' => $result['id'],
        'is_reserved' => 1,
      ]);
    }

    return TRUE;
  }
}
