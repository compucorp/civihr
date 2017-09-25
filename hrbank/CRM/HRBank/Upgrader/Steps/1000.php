<?php

trait CRM_HRBank_Upgrader_Steps_1000 {

  /**
   * @return bool
   */
  public function upgrade_1000() {
    $oldLabel = 'Bank Post Code';
    $newLabel = 'Bank Postcode';
    $old = civicrm_api3('CustomField', 'get', ['label' => $oldLabel]);

    if ($old['count'] != 1) {
      return TRUE;
    }
    $old = array_shift($old['values']);

    $params = ['id' => $old['id'], 'label' => $newLabel];
    civicrm_api3('CustomField', 'create', $params);

    return TRUE;
  }
}
