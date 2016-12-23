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

  public function testSicknessRequestGetShouldReturnAssociatedLeaveRequestData() {
    $fromDate = new DateTime("2016-11-14");
    $toDate = new DateTime("2016-11-17");

    $fromDate2 = new DateTime("2016-11-20");
    $toDate2 = new DateTime("2016-11-30");

    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $sicknessRequest1 = SicknessRequest::create([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Appointment'],
      'required_documents' => $this->requiredDocumentOptions['Self certification form required']['value'],
    ], false);

    $sicknessRequest2 = SicknessRequest::create([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Accident'],
      'required_documents' => $this->requiredDocumentOptions['Self certification form required']['value']. ',' . $this->requiredDocumentOptions['Back to work interview required']['value'],
    ], false);

    $expectedResult = [
      [
        'id' => $sicknessRequest1->id,
        'type_id' => 1,
        'contact_id' => 1,
        'status_id' => 1,
        'from_date' => $fromDate->format('Y-m-d'),
        'from_date_type' => $fromType,
        'to_date' => $toDate->format('Y-m-d'),
        'to_date_type' => $toType,
        'leave_request_id' => $sicknessRequest1->leave_request_id,
        'reason' => $sicknessRequest1->reason,
        'required_documents' => $sicknessRequest1->required_documents
      ],
      [
        'id' => $sicknessRequest2->id,
        'type_id' => 1,
        'contact_id' => 1,
        'status_id' => 1,
        'from_date' => $fromDate2->format('Y-m-d'),
        'from_date_type' => $fromType,
        'to_date' => $toDate2->format('Y-m-d'),
        'to_date_type' => $toType,
        'leave_request_id' => $sicknessRequest2->leave_request_id,
        'reason' => $sicknessRequest2->reason,
        'required_documents' => $sicknessRequest2->required_documents
      ]
    ];

    $result = civicrm_api3('SicknessRequest', 'get', ['contact_id'=> 1, 'sequential' => 1]);
    $this->assertEquals($expectedResult, $result['values']);
  }
}
