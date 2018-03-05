<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Factory_LeaveBalanceChangeCalculation as LeaveBalanceChangeCalculationFactory;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;

/**
 * Class CRM_HRSampleData_Importer_LeaveRequest
 */
class CRM_HRSampleData_Importer_LeaveRequest extends CRM_HRSampleData_CSVImporterVisitor {

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange
   */
  private $leaveBalanceChangeService;

  public function __construct() {
    $this->removeAllLeaveRequests();
    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();
  }

  /**
   * Imports Leave Requests and creates their balance changes
   *
   * @param array $row
   */
  protected function importRecord(array $row) {
    $row['type_id'] = $this->getDataMapping('absence_type_mapping', $row['type_id']);
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);

    $leaveRequest = LeaveRequest::create($row, LeaveRequest::VALIDATIONS_OFF);
    $balanceCalculationService = LeaveBalanceChangeCalculationFactory::create($leaveRequest);
    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest, $balanceCalculationService);
  }

  /**
   * Removes existing absence period.
   */
  private function removeAllLeaveRequests() {
    $leaveRequestTable = LeaveRequest::getTableName();

    // Via the API, the Leave Requests will only be soft deleted, which is not
    // what we want here. This is why we with a raw SQL query
    CRM_Core_DAO::executeQuery("DELETE FROM {$leaveRequestTable}");
  }

}
