<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1022 {
  
  /**
   * Default existing work pattern to As per contract (new default)
   *
   * @return bool
   */
  public function upgrade_1022()
  {
    civicrm_api3('ContactWorkPattern', 'get', [
      'return' => ['id'],
      'api.ContactWorkPattern.update' => [
        'change_reason' => 1,
        'id' => "\$value.id"
      ],
    ]);
    
    return TRUE;
  }
}
