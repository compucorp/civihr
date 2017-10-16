<?php

trait CRM_Hremergency_Upgrader_Steps_1001 {

  /**
   * Replace the label on the dependant flag custom field.
   *
   * @return bool
   */
  public function upgrade_1001() {
    $oldLabel = 'Dependant(s)';
    $newLabel = 'Is a Dependant?';
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
