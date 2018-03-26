<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1022 {
  
  /**
   * Default existing work pattern to As per contract (new default)
   *
   * @return bool
   */
  public function upgrade_1022()
  {
    $query = 'UPDATE civicrm_hrleaveandabsences_contact_work_pattern SET change_reason =
      (
        SELECT cov.value FROM civicrm_option_value cov LEFT JOIN civicrm_option_group cog
          ON (cog.id = cov.option_group_id)
          WHERE cog.name = "hrleaveandabsences_work_pattern_change_reason"
          AND cov.is_default = 1
      )';
    
    CRM_Core_DAO::executeQuery($query);
    
    return TRUE;
  }
}
