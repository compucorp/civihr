<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_API_Handler_GetBreakDownFieldPermissions as GetBreakDownFieldHandler;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;

/**
 * Class CRM_HRLeaveAndAbsences_API_Handler_GetBreakDownFieldPermissionsTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_API_Handler_GetBreakDownFieldPermissionsTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  private $sampleData = [
    'is_error' => 0,
    'version' => 3,
    'count' => 3,
    'values' =>
      [
        0 =>
          [
            'id' => '17',
            'type' => 1,
            'label' => 'Test Label',
            'date' => '2016-01-30',
            'amount' => '0.00',
          ],
        1 =>
          [
            'id' => '18',
            'type' => 1,
            'label' => 'Test Label',
            'date' => '2016-01-31',
            'amount' => '0.00',
          ],
        2 =>
          [
            'id' => '19',
            'type' => 1,
            'label' => 'Test Label',
            'date' => '2016-02-01',
            'amount' => '-1.00',
          ],
      ],
  ];

  private function createLeaveRequest($contactID) {
    return LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contactID,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-30'),
      'to_date' => CRM_Utils_Date::processDate('2016-02-01'),
      'status_id' => 1
    ]);
  }

  public function testProcessForUserRequestingBreakDownForLeaveRequestContactHeHasAccessTo() {
    //User with contact ID of 204
    $contactID = 204;
    $this->setPermissions();
    $leaveRequestRights = $this->prophesize(LeaveRequestRightsService::class);
    $leaveRequestRights->getLeaveContactsCurrentUserHasAccessTo()->willReturn([$contactID]);
    $leaveRequest = $this->createLeaveRequest($contactID);
    $apiRequest = [
      'params' => [
        'leave_request_id' => $leaveRequest->id
      ]
    ];
    $getBreakDownFieldHandler = new GetBreakDownFieldHandler($apiRequest, $leaveRequestRights->reveal());
    $results = $this->sampleData;
    $expectedParams = $this->sampleData;

    $getBreakDownFieldHandler->process($results);
    $this->assertEquals($expectedParams, $results);
  }

  public function testProcessForUserRequestingBreakdownForLeaveRequestContactHeDoesNotHaveAccessTo() {
    //Staff with contact ID of 204 trying to access breakdown of staff with contact ID 206
    $contactID = 204;
    $staffID = 206;
    $this->setPermissions();
    $leaveRequestRights = $this->prophesize(LeaveRequestRightsService::class);
    $leaveRequestRights->getLeaveContactsCurrentUserHasAccessTo()->willReturn([$contactID]);
    $leaveRequest = $this->createLeaveRequest($staffID);
    $apiRequest = [
      'params' => [
        'leave_request_id' => $leaveRequest->id
      ]
    ];
    $getBreakDownFieldHandler = new GetBreakDownFieldHandler($apiRequest, $leaveRequestRights->reveal());
    $results = $this->sampleData;
    $expectedParams = $this->sampleData;
    $expectedParams['values'][0]['amount'] = '';
    $expectedParams['values'][1]['amount'] = '';
    $expectedParams['values'][2]['amount'] = '';

    $getBreakDownFieldHandler->process($results);
    $this->assertEquals($expectedParams, $results);
  }
  
  public function testProcessForAdmin() {
    //Admin can access all restricted fields for a contaxt
    $this->setPermissions(['administer leave and absences']);
    $staffID = 206;
    $leaveRequestRights = $this->prophesize(LeaveRequestRightsService::class);
    $leaveRequestRights->getLeaveContactsCurrentUserHasAccessTo()->willReturn([]);
    $leaveRequest = $this->createLeaveRequest($staffID);
    $apiRequest = [
      'params' => [
        'leave_request_id' => $leaveRequest->id
      ]
    ];
    $getBreakDownFieldHandler = new GetBreakDownFieldHandler($apiRequest, $leaveRequestRights->reveal());
    $results = $this->sampleData;
    $expectedParams = $this->sampleData;

    $getBreakDownFieldHandler->process($results);
    $this->assertEquals($expectedParams, $results);
  }

}
