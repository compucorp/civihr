<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait {

  protected $leaveRequestDayTypes = [];
  protected $leaveRequestStatuses = [];

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

  protected function getLeaveRequestStatuses() {
    if(empty($this->leaveRequestStatuses)) {
      $leaveRequestStatusOptions = LeaveRequest::buildOptions('status_id');
      foreach($leaveRequestStatusOptions  as $key => $label) {
        $name = CRM_Core_Pseudoconstant::getName(LeaveRequest::class, 'status_id', $key);
        $this->leaveRequestStatuses[$label] = [
          'id' => $key,
          'value' => $key,
          'name' => $name,
          'label' => $label
        ];
      }
    }

    return $this->leaveRequestStatuses;
  }
}
