<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Factory_LeaveBalanceChangeCalculation as LeaveBalanceChangeCalculationFactory;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;

/**
 * Class CRM_HRSampleData_Importer_LeaveRequest
 */
class CRM_HRSampleData_Importer_LeaveRequest extends CRM_HRSampleData_CSVImporterVisitor {

  /**
   * @var array
   */
  private $absenceTypesMap;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange
   */
  private $leaveBalanceChangeService;

  public function __construct() {
    $this->removeAllLeaveRequests();
    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();
    $this->absenceTypesMap = $this->getAbsenceTypesMap();
  }

  /**
   * Imports Leave Requests and creates their balance changes
   */
  protected function importRecord(array $row) {
    $this->prepareAbsenceTypeID($row);
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

  /**
   * Returns an Absence Types map where the keys are their names and the values
   * are their IDs
   *
   * @return array
   */
  private function getAbsenceTypesMap() {
    $allAbsenceTypes = $this->callAPI('AbsenceType', 'get', []);

    $absenceTypes = [];
    foreach ($allAbsenceTypes['values'] as $absenceType) {
      $absenceTypes[$absenceType['title']] = $absenceType['id'];
    }

    return $absenceTypes;
  }

  /**
   * Converts the type_name column in the given $row into a type_id
   *
   * @param array $row
   */
  private function prepareAbsenceTypeID(array &$row) {
    $absenceTypeID = $this->absenceTypesMap[$row['type_name']];
    $row['type_id'] = $absenceTypeID;
    unset($row['type_name']);
  }

}
