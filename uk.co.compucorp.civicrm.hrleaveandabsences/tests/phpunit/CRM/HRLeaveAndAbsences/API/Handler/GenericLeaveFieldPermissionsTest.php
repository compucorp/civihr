<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions as GenericLeaveFieldPermissions;
/**
 * Class CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissionsTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissionsTest extends BaseHeadlessTest  {

  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $sampleData = [
    'is_error' => 0,
    'version' => 3,
    'count' => 6,
    'values' =>
      [
        0 =>
          [
            'id' => '17',
            'type_id' => '1',
            'contact_id' => '209',
            'status_id' => '6',
            'from_date' => '2016-01-30 00:00:00',
            'from_date_type' => '1',
            'to_date' => '2016-02-01 00:00:00',
            'to_date_type' => '1',
            'request_type' => 'leave',
            'is_deleted' => '0',
            'from_date_amount' => '0.00',
            'to_date_amount' => '0.00',
            'balance_change' => -1,
            'dates' =>
              [
                0 =>
                  [
                    'id' => '17',
                    'date' => '2016-01-30',
                  ],
                1 =>
                  [
                    'id' => '18',
                    'date' => '2016-01-31',
                  ],
                2 =>
                  [
                    'id' => '19',
                    'date' => '2016-02-01',
                  ],
              ],
          ],
        1 =>
          [
            'id' => '18',
            'type_id' => '1',
            'contact_id' => '204',
            'status_id' => '1',
            'from_date' => '2016-02-01 00:00:00',
            'from_date_type' => '1',
            'to_date' => '2016-02-03 00:00:00',
            'to_date_type' => '1',
            'request_type' => 'leave',
            'is_deleted' => '0',
            'from_date_amount' => '0.00',
            'to_date_amount' => '0.00',
            'balance_change' => -3,
            'dates' =>
              [
                0 =>
                  [
                    'id' => '20',
                    'date' => '2016-02-01',
                  ],
                1 =>
                  [
                    'id' => '21',
                    'date' => '2016-02-02',
                  ],
                2 =>
                  [
                    'id' => '22',
                    'date' => '2016-02-03',
                  ],
              ],
          ],
        2 =>
          [
            'id' => '24',
            'type_id' => '2',
            'contact_id' => '204',
            'status_id' => '1',
            'from_date' => '2016-10-20 00:00:00',
            'from_date_type' => '1',
            'to_date' => '2016-10-20 23:45:00',
            'to_date_type' => '1',
            'request_type' => 'leave',
            'is_deleted' => '0',
            'from_date_amount' => '0.00',
            'to_date_amount' => '0.00',
            'balance_change' => 1,
            'dates' =>
              [
                0 =>
                  [
                    'id' => '51',
                    'date' => '2016-10-20',
                  ],
              ],
          ],
        3 =>
          [
            'id' => '25',
            'type_id' => '2',
            'contact_id' => '208',
            'status_id' => '4',
            'from_date' => '2016-12-15 00:00:00',
            'from_date_type' => '1',
            'to_date' => '2016-12-15 23:45:00',
            'to_date_type' => '1',
            'request_type' => 'leave',
            'is_deleted' => '0',
            'from_date_amount' => '0.00',
            'to_date_amount' => '0.00',
            'balance_change' => 2,
            'dates' =>
              [
                0 =>
                  [
                    'id' => '52',
                    'date' => '2016-12-15',
                  ],
              ],
          ],
        4 =>
          [
            'id' => '27',
            'type_id' => '3',
            'contact_id' => '204',
            'status_id' => '6',
            'from_date' => '2017-02-01 00:00:00',
            'from_date_type' => '1',
            'to_date' => '2017-02-01 00:00:00',
            'to_date_type' => '1',
            'sickness_reason' => '2',
            'request_type' => 'sickness',
            'is_deleted' => '0',
            'from_date_amount' => '0.00',
            'to_date_amount' => '0.00',
            'balance_change' => -1,
            'dates' =>
              [
                0 =>
                  [
                    'id' => '63',
                    'date' => '2017-02-01',
                  ],
              ],
          ],
        5 =>
          [
            'id' => '28',
            'type_id' => '3',
            'contact_id' => '209',
            'status_id' => '6',
            'from_date' => '2017-02-01 00:00:00',
            'from_date_type' => '1',
            'to_date' => '2017-02-01 00:00:00',
            'to_date_type' => '1',
            'sickness_reason' => '2',
            'request_type' => 'sickness',
            'is_deleted' => '0',
            'from_date_amount' => '0.00',
            'to_date_amount' => '0.00',
            'balance_change' => -1,
            'dates' =>
              [
                0 =>
                  [
                    'id' => '63',
                    'date' => '2017-02-01',
                  ],
              ],
          ],
      ],
  ];

  public function testProcessWhenUserIsNotAnAdminUser() {
    $this->setPermissions();
    //User has access to two leave contacts.
    $leaveRequestRights = $this->prophesize(LeaveRequestRightsService::class);
    $leaveRequestRights->getLeaveContactsCurrentUserHasAccessTo()->willReturn([209, 208]);
    $apiRequest = [];
    $genericFieldHandler = new GenericLeaveFieldPermissions($apiRequest, $leaveRequestRights->reveal());

    $results = $this->sampleData;
    $expectedParams = $this->sampleData;
    $expectedParams['values'][1]['from_date_amount'] = '';
    $expectedParams['values'][1]['to_date_amount'] = '';
    $expectedParams['values'][1]['balance_change'] = '';
    $expectedParams['values'][1]['type_id'] = '';
    $expectedParams['values'][2]['from_date_amount'] = '';
    $expectedParams['values'][2]['to_date_amount'] = '';
    $expectedParams['values'][2]['balance_change'] = '';
    $expectedParams['values'][2]['type_id'] = '';
    $expectedParams['values'][4]['sickness_reason'] = '';
    $expectedParams['values'][4]['from_date_amount'] = '';
    $expectedParams['values'][4]['to_date_amount'] = '';
    $expectedParams['values'][4]['balance_change'] = '';
    $expectedParams['values'][4]['type_id'] = '';
    $genericFieldHandler->process($results);

    $this->assertEquals($expectedParams, $results);
  }


  public function testProcessWhenUserIsAdmin() {
    //Admin User
    $this->setPermissions(['administer leave and absences']);
    $leaveRequestRights = $this->prophesize(LeaveRequestRightsService::class);
    $leaveRequestRights->getLeaveContactsCurrentUserHasAccessTo()->willReturn([]);
    $apiRequest = [];
    $genericFieldHandler = new GenericLeaveFieldPermissions($apiRequest, $leaveRequestRights->reveal());

    $results = $this->sampleData;
    $expectedParams = $this->sampleData;
    $genericFieldHandler->process($results);

    $this->assertEquals($expectedParams, $results);
    $this->setPermissions();
  }

  public function testProcessWillHideAccessibleFieldsWhenRowIdentifierIsAbsentEvenIfUserHasAccess() {
    //User is Staff with ID of 204 and has full access to only his data
    $this->setPermissions();
    $leaveRequestRights = $this->prophesize(LeaveRequestRightsService::class);
    $leaveRequestRights->getLeaveContactsCurrentUserHasAccessTo()->willReturn([204]);
    $apiRequest = [];
    $genericFieldHandler = new GenericLeaveFieldPermissions($apiRequest, $leaveRequestRights->reveal());

    $sampleData = $this->sampleData;
    unset($sampleData['values'][0], $sampleData['values'][3], $sampleData['values'][5]);
    unset($sampleData['values'][1]['contact_id']);
    unset($sampleData['values'][2]['contact_id']);
    unset($sampleData['values'][4]['contact_id']);

    $results = $sampleData;
    $expectedParams = $sampleData;

    //Staff will not be able to access all restricted fields for his
    //records since the row identifier is absent
    $expectedParams['values'][1]['from_date_amount'] = '';
    $expectedParams['values'][1]['to_date_amount'] = '';
    $expectedParams['values'][1]['balance_change'] = '';
    $expectedParams['values'][1]['type_id'] = '';
    $expectedParams['values'][2]['from_date_amount'] = '';
    $expectedParams['values'][2]['to_date_amount'] = '';
    $expectedParams['values'][2]['balance_change'] = '';
    $expectedParams['values'][2]['type_id'] = '';
    $expectedParams['values'][4]['sickness_reason'] = '';
    $expectedParams['values'][4]['from_date_amount'] = '';
    $expectedParams['values'][4]['to_date_amount'] = '';
    $expectedParams['values'][4]['balance_change'] = '';
    $expectedParams['values'][4]['type_id'] = '';

    $genericFieldHandler->process($results);

    $this->assertEquals($expectedParams, $results);
  }
}
