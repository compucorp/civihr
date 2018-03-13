
<?php

trait CRM_HRCore_Upgrader_Steps_1014 {

  /**
   * Upgrade CustomGroup, setting Identify is_reserved value to Yes
   * if it exists. This implementation was made in HRCore instead of
   * HRIdent extension because HRIdent is currently disabled in some setups.
   *
   * @return bool
   */
  public function upgrade_1011() {
    $result = civicrm_api3('CustomGroup', 'get', [
      'return' => ['id'],
      'name' => 'Identify',
    ]);

    if ($result['id']) {
      civicrm_api3('CustomGroup', 'create', [
        'id' => $result['id'],
        'is_reserved' => 1,
      ]);
    }

    return TRUE;
  }
}
