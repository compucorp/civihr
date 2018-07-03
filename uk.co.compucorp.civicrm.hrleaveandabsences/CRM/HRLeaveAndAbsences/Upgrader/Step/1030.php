<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1030 {
  
  /**
   * Disables or deletes existing "sickness reason" option values
   * and added new ones
   *
   * @return bool
   */
  public function upgrade_1030() {
    $oldSicknessReasons = civicrm_api3('OptionValue', 'get', [
      'return' => ['name'],
      'option_group_id' => 'hrleaveandabsences_sickness_reason',
    ]);
  
    $result = $this->up1030_getLeaveRequestTotal();
    if ($result == 0) {
      $this->up1030_deleteOldSicknessReasons($oldSicknessReasons['values']);
    }
    else {
      $this->up1030_disableOldSicknessReasons($oldSicknessReasons['values']);
    }
    
    $this->up1030_addNewSicknessReasons();
    
    return TRUE;
  }
  
  /**
   * Counts the total number of leave requests related to sickness reasons
   *
   * @return int
   */
  private function up1030_getLeaveRequestTotal() {
    $leaveRequest = new LeaveRequest();
    $leaveRequest->whereAdd('sickness_reason IS NOT NULL');
    $leaveRequest->find();
  
    // N is Number of rows returned from a query
    return $leaveRequest->N;
  }
  
  /**
   * Disables existing "sickness reason" option values
   *
   * @param array $sicknessReasons
   */
  private function up1030_disableOldSicknessReasons($sicknessReasons) {
    foreach ($sicknessReasons as $id => $sicknessReason) {
      civicrm_api3('OptionValue', 'create', [
        'id' => $id,
        'is_active' => 0
      ]);
    }
  }
  
  /**
   * Deletes existing "sickness reason" option values
   *
   * @param array $sicknessReasons
   */
  private function up1030_deleteOldSicknessReasons($sicknessReasons) {
    foreach ($sicknessReasons as $id => $sicknessReason) {
      civicrm_api3('OptionValue', 'delete', ['id' => $id]);
    }
  }
  
  /**
   * Adds new "sickness reason" option values
   */
  public function up1030_addNewSicknessReasons() {
    $newSicknessOptions = [
      ['label' => 'Cold, Cough, Flu - Influenza', 'name' => 'cold_cough_flu-influenza'],
      ['label' => 'Headache/Migraine', 'name' => 'headache_migraine'],
      ['label' => 'Gastro-intestinal Problems', 'name' => 'gastro_intestinal_problems'],
      ['label' => 'Back Problems', 'name' => 'back_problems'],
      ['label' => 'Injury, Fracture', 'name' => 'injury_fracture'],
      ['label' => 'Dental and Oral Problems', 'name' => 'dental_oral_problems'],
      ['label' => 'Pregnancy Related', 'name' => 'pregnancy_related'],
      [
        'label' => 'Anxiety/Stress/Depression/Other Psychiatric Illnesses',
        'name' => 'anxiety_stress_depression_psychiatric_illnesses'
      ],
      ['label' => 'Chest and Respiratory Problems', 'name' => 'chest_respiratory_problems'],
      ['label' => 'Ear, Nose, Throat (ENT)', 'name' => 'ent'],
      ['label' => 'Endocrine/Glandular Problems', 'name' => 'endocrine_glandular_problems'],
      ['label' => 'Eye Problems', 'name' => 'eye_problems'],
      [
        'label' => 'Genitourinary and Gynaecological Disorders',
        'name' => 'genitourinary_gynaecological_disorders'
      ],
      ['label' => 'Other Musculoskeletal Problems', 'name' => 'musculoskeletal_problems'],
      ['label' => 'Surgery Related', 'name' => 'surgery_related'],
      ['label' => 'Other', 'name' => 'other']
    ];
    
    foreach ($newSicknessOptions as $sicknessReason) {
      $sicknessReason['option_group_id'] = 'hrleaveandabsences_sickness_reason';
      civicrm_api3('OptionValue', 'create', $sicknessReason);
    }
  }
}
