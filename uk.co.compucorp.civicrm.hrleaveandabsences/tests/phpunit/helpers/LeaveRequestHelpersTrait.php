<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait {

  protected $leaveRequestDayTypes = [];

  protected function getLeaveRequestDayTypes() {
    if(empty($this->leaveRequestDayTypes)) {
      $leaveRequestDayTypeOptions = LeaveRequest::buildOptions('from_date_type');
      foreach($leaveRequestDayTypeOptions  as $key => $label) {
        $name = CRM_Core_Pseudoconstant::getName(LeaveRequest::class, 'from_date_type', $key);
        $this->leaveRequestDayTypes[$label] = [
          'id' => $key,
          'value' => $key,
          'name' => $name,
          'label' => $label
        ];
      }
    }

    return $this->leaveRequestDayTypes;
  }
}
