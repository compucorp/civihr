<?php

use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;

/**
 * Class api_v3_TOILRequestTest
 *
 * @group headless
 */
class SicknessRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_SicknessRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
    $this->requiredDocumentOptions = $this->requiredDocumentOptionsBuilder();
    $this->leaveRequestDayTypes = $this->leaveRequestDayTypeOptionsBuilder();
  }

  public function testSicknessRequestIsValidReturnsErrorWhenReasonIsEmpty() {
    $fromDate = new DateTime("2016-11-14");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    $result = civicrm_api3('SicknessRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'required_documents' => $requiredDocuments
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'reason' => ['sickness_request_empty_reason']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testSicknessRequestIsValidShouldNotReturnErrorWhenValidationsPass() {
    $contactID = 1;
    $fromDate = new DateTime("2016-11-14");
    $toDate = new DateTime("2016-11-17");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $result = civicrm_api3('SicknessRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Appointment'],
      'required_documents' => $requiredDocuments
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 0,
      'values' => []
    ];
    $this->assertArraySubset($expectedResult, $result);
  }
}
