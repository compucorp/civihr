<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

class CRM_HRLeaveAndAbsences_BAO_LeaveRequest extends CRM_HRLeaveAndAbsences_DAO_LeaveRequest {

  /**
   * Create a new LeaveRequest based on array-data
   *
   * @param array $params key-value pairs
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|NULL
   *
   * @throws \Exception
   */
  public static function create($params) {
    $entityName = 'LeaveRequest';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);

    $transaction = new CRM_Core_Transaction();
    try {
      $instance->save();
      $instance->saveDates();
      $transaction->commit();
    } catch(Exception $e) {
      $transaction->rollback();
      // We throw the catched Exception so forms can handle the
      // error and properly inform the user about what happened
      throw $e;
    }

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Returns all the LeaveRequestDate instances related to this LeaveRequest.
   *
   * @return CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate[]
   *  The dates in ascending order according to the date
   */
  public function getDates() {
    return LeaveRequestDate::getDatesForLeaveRequest($this->id);
  }

  /**
   * Creates and saves LeaveRequestDates for this LeaveRequest
   */
  private function saveDates() {
    $this->deleteDates();

    $startDate = new DateTime($this->from_date);

    if (!$this->to_date) {
      $endDate = new DateTime($this->from_date);
    }
    else {
      $endDate = new DateTime($this->to_date);
    }

    // We need to add 1 day to the end date to include it
    // when we loop through the DatePeriod
    $endDate->modify('+1 day');

    $interval   = new DateInterval('P1D');
    $datePeriod = new DatePeriod($startDate, $interval, $endDate);

    foreach ($datePeriod as $date) {
      LeaveRequestDate::create([
        'date' => $date->format('YmdHis'),
        'leave_request_id' => $this->id
      ]);
    }
  }

  /**
   * Deletes all the dates related to this LeaveRequest
   */
  private function deleteDates() {
    LeaveRequestDate::deleteDatesForLeaveRequest($this->id);
  }
}
