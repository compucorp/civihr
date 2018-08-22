<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_Hrjobroles_Test_Fabricator_HrJobRoles as HRJobRolesFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequestCalendarFeedConfig as LeaveCalendarFeedConfigFabricator;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData as LeaveRequestCalendarFeedData;
use CRM_HRLeaveAndAbsences_Helper_CalendarFeed_LeaveTime as CalendarLeaveTimeHelper;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedDataTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedDataTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $leaveTypes;

  public function testDateRangeForLeaveDataIsBetweenCurrentDateAndThreeMonthsTime() {
    $feedConfig = LeaveCalendarFeedConfigFabricator::fabricate();
    $feedData = new LeaveRequestCalendarFeedData($feedConfig->hash);
    $startDate = $feedData->getStartDate();
    $endDate = $feedData->getEndDate();
    $expectedStartDate = new DateTime('today');
    $expectedEndDate = new DateTime('+ 3 months');
    $expectedEndDate->setTime('23', '59');

    $this->assertEquals($expectedStartDate, $startDate, '', 10);
    $this->assertEquals($expectedEndDate, $endDate, '', 10);
  }

  public function testExceptionIsThrownWhenFeedHashIsEmpty() {
    $this->setExpectedException('RuntimeException', 'The feed hash should not be empty');
    new LeaveRequestCalendarFeedData('');
  }

  public function testGetTimezoneReturnsFeedConfigTimezone() {
    $feedConfig =  LeaveCalendarFeedConfigFabricator::fabricate();
    $feedData = new LeaveRequestCalendarFeedData($feedConfig->hash);
    $this->assertEquals($feedConfig->timezone, $feedData->getTimeZone());
  }

  public function testGetWillReturnDataForFeedConfigLocationAndDepartmentContacts() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate(['first_name' => 'Contact1', 'last_name' => 'LastContact1']);
    $contact2 = ContactFabricator::fabricate(['first_name' => 'Contact2', 'last_name' => 'LastContact2']);
    $contact3 = ContactFabricator::fabricate(['first_name' => 'Contact3', 'last_name' => 'LastContact3']);
    $contact4 = ContactFabricator::fabricate(['first_name' => 'Contact4', 'last_name' => 'LastContact4']);

    $location1 = $this->createLocation('location1');
    $location2 = $this->createLocation('location2');
    $department1 = $this->createDepartment('department1');
    $department2 = $this->createDepartment('department2');

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );
    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $contract3 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact3['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $contract4 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact4['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    //contact1 assigned to department1
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract1['id'],
      'department' => $department1['value'],
    ]);

    //contact2 assigned to location1
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract2['id'],
      'location' => $location1['value']
    ]);

    //contact3 assigned to location1 and department1
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract3['id'],
      'location' => $location1['value'],
      'department' => $department1['value']
    ]);

    //contact4 assigned to both location2 and department2
    HRJobRolesFabricator::fabricate([
      'job_contract_id' => $contract4['id'],
      'location' => $location2['value'],
      'department' => $department2['value']
    ]);

    $params[1] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ];

    $params[2] = [
      'contact_id' => $contact2['id'],
      'first_name' => $contact2['first_name'],
      'last_name' => $contact2['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
    ];

    $params[3] = [
      'contact_id' => $contact3['id'],
      'first_name' => $contact3['first_name'],
      'last_name' => $contact3['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
    ];

    $params[4] = [
      'contact_id' => $contact4['id'],
      'first_name' => $contact4['first_name'],
      'last_name' => $contact4['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
    ];

    foreach ($params as &$param) {
      $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($param);
      $param['id'] =  $leaveRequest->id;
    }

    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id],
        'location' => [$location1['value']],
        'department' => [$department1['value']]
      ]
    ]);

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);
    //Leave data for contact4 will be exempted because the contacts does not belong to
    //department or location allowed by leave feed.
    unset($params[4]);
    $this->assertEquals($this->getExpectedLeaveDataResult($params), $leaveFeedData->get());
  }

  public function testGetWillHideLeaveLabelForAbsenceTypeWithHideLabelAsTrueForUserWithoutAccessToLeaveContact() {
    $this->setPermissions([]);
    $absenceType = AbsenceTypeFabricator::fabricate(['title' => 'Sample Type', 'hide_label' => 1]);
    $contact1 = ContactFabricator::fabricate(['first_name' => 'Contact1', 'last_name' => 'LastContact1']);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $fromDate = new DateTime('+1 day');
    $toDate = new DateTime('+2 days');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => $absenceType->id,
      'from_date' => $fromDate->format('Y-m-d H:i:s'),
      'to_date' => $toDate->format('Y-m-d H:i:s'),
      'from_date_type' => '',
      'to_date_type' => ''
    ]);

    //feed config is for all contacts in any department/location
    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id],
      ]
    ]);

    $displayName = "{$contact1['first_name']} {$contact1['last_name']}";
    $expectedResult[0] = [
      'id' => $leaveRequest->id,
      'contact_id' => $leaveRequest->contact_id,
      //Leave label name will simply be displayed as Leave
      'display_name' => $displayName . ' (Leave)',
      'from_date' => $fromDate->format('Y-m-d H:i:s'),
      'to_date' => $toDate->format('Y-m-d H:i:s')
    ];

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);
    $this->assertEquals($expectedResult, $leaveFeedData->get());
  }

  public function testGetWillNotHideLeaveLabelForAbsenceTypeWithHideLabelAsTrueForAdminUser() {
    $this->setPermissions(['administer leave and absences']);
    $absenceType = AbsenceTypeFabricator::fabricate(['title' => 'Sample Type', 'hide_label' => 1]);
    $contact1 = ContactFabricator::fabricate(['first_name' => 'Contact1', 'last_name' => 'LastContact1']);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $fromDate = new DateTime('+1 day');
    $toDate = new DateTime('+2 days');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => $absenceType->id,
      'from_date' => $fromDate->format('Y-m-d H:i:s'),
      'to_date' => $toDate->format('Y-m-d H:i:s'),
      'from_date_type' => '',
      'to_date_type' => ''
    ]);

    //feed config is for all contacts in any department/location
    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id],
      ]
    ]);

    $displayName = "{$contact1['first_name']} {$contact1['last_name']}";
    $expectedResult[0] = [
      'id' => $leaveRequest->id,
      'contact_id' => $leaveRequest->contact_id,
      //Leave label will be displayed for Admin user
      'display_name' => $displayName . ' (Sample Type)',
      'from_date' => $fromDate->format('Y-m-d H:i:s'),
      'to_date' => $toDate->format('Y-m-d H:i:s')
    ];

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);
    $this->assertEquals($expectedResult, $leaveFeedData->get());
  }

  public function testGetWillNotReturnDisabledLeaveTypesData() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $absenceType2 = AbsenceTypeFabricator::fabricate(['is_active' => 0]);
    $contact1 = ContactFabricator::fabricate(['first_name' => 'Contact1', 'last_name' => 'LastContact1']);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $params[1] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
    ];

    //This leave request will not be returned because the absence type is disabled
    //just after the leave was created for it.
    $params[2] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType2->id,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
    ];

    foreach ($params as &$param) {
      $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($param);
      $param['id'] =  $leaveRequest->id;
    }

    //feed config is for all contacts in any department/location
    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id, $absenceType2->id],
      ]
    ]);

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);

    //The second leave request will not be returned because it is linked to a
    //disabled absence type
    unset($params[2]);
    $this->assertEquals($this->getExpectedLeaveDataResult($params), $leaveFeedData->get());
  }

  public function testGetWillOnlyReturnApprovedRequests() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $leaveStatuses = LeaveRequest::getStatuses();
    $contact1 = ContactFabricator::fabricate(['first_name' => 'Contact1', 'last_name' => 'LastContact1']);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    //This leave request will not be returned because it is not an approved request
    $params[1] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveStatuses['more_information_required']
    ];

    $params[2] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'status_id' => $leaveStatuses['approved']
    ];

    foreach ($params as &$param) {
      $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($param);
      $param['id'] =  $leaveRequest->id;
    }

    //feed config is for all contacts in any department/location
    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id],
      ]
    ]);

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);

    //The first leave request will not be returned because it is not an approved leave
    unset($params[1]);
    $this->assertEquals($this->getExpectedLeaveDataResult($params), $leaveFeedData->get());
  }

  public function testGetWillNotReturnToilRequestsData() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate(['first_name' => 'Contact1', 'last_name' => 'LastContact1']);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('today')]
    );

    $params[1] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ];

    foreach ($params as &$param) {
      $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($param);
      $param['id'] =  $leaveRequest->id;
    }

    //feed config is for all contacts in any department/location
    $feedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id],
      ]
    ]);

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);
    $this->assertEquals([], $leaveFeedData->get());
  }

  public function testGetWillNotReturnDataForRequestsOutsideTheDateRange() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $contact = ContactFabricator::fabricate(['first_name' => 'ContactName', 'last_name' => 'ContactLastName']);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('yesterday')]
    );

    // These are test cases for different leave request dates.
    // As a general rule, leave requests should only be included if at least
    // one day appears in the period of [today - 3 months from today].
    $testCases = [
      [
        'from_date' => CRM_Utils_Date::processDate('-3 days'),
        'to_date' => CRM_Utils_Date::processDate('-2 days'),
        'shouldBeIncluded' => false
      ],
      [
        'from_date' => CRM_Utils_Date::processDate('-1 days'),
        'to_date' => CRM_Utils_Date::processDate('today'),
        'shouldBeIncluded' => true
      ],
      [
        'from_date' => CRM_Utils_Date::processDate('+1 month +10 days'),
        'to_date' => CRM_Utils_Date::processDate('+1 month +20 days'),
        'shouldBeIncluded' => true
      ],
      [
        'from_date' => CRM_Utils_Date::processDate('+3 months'),
        'to_date' => CRM_Utils_Date::processDate('+3 months +1 day'),
        'shouldBeIncluded' => true
      ],
      [
        'from_date' => CRM_Utils_Date::processDate('+3 months +2 days'),
        'to_date' => CRM_Utils_Date::processDate('+3 months +3 days'),
        'shouldBeIncluded' => false
      ]
    ];

    $expectedFeedData = [];

    foreach ($testCases as $testCase) {
      $params = [
        'contact_id' => $contact['id'],
        'first_name' => $contact['first_name'],
        'last_name' => $contact['last_name'],
        'type_id' => $absenceType->id,
        'from_date' => $testCase['from_date'],
        'to_date' => $testCase['to_date'],
      ];

      $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);
      $params['id'] =  $leaveRequest->id;

      $testCase['shouldBeIncluded'] && array_push($expectedFeedData, $params);
    }

    // A sample feed config which is visible to everyone
    $feedConfig = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed',
      'composed_of' => [
        'leave_type' => [$absenceType->id],
      ]
    ]);

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig->hash);

    $this->assertEquals($this->getExpectedLeaveDataResult($expectedFeedData), $leaveFeedData->get());
  }

  private function getExpectedLeaveDataResult($leaveData) {
    $result = [];

    foreach ($leaveData as $data) {
      $data['from_date_type'] = 1;
      $data['to_date_type'] = 1;
      CalendarLeaveTimeHelper::adjust($data);
      $displayName = "{$data['first_name']} {$data['last_name']}";
      $result[] = [
        'id' => $data['id'],
        'contact_id' => $data['contact_id'],
        'display_name' => $displayName . ' (' . $this->getLeaveTypeName($data['type_id']) . ')',
        'from_date' => $data['from_date'],
        'to_date' => $data['to_date']
      ];
    }

    return $result;
  }

  private function getLeaveTypeName($typeId) {
    if (!$this->leaveTypes) {
      $absenceTypes = AbsenceType::getEnabledAbsenceTypes();
      $absenceTypesList = [];

      foreach ($absenceTypes as $absenceType) {
        $absenceTypesList[$absenceType->id] = $absenceType->title;
      }
      $this->leaveTypes = $absenceTypesList;
    }

    return $this->leaveTypes[$typeId];
  }

  private function createDepartment($departmentName) {
    $department = OptionValueFabricator::fabricate([
      'option_group_id' => 'hrjc_department',
      'name' => $departmentName,
      'value' => $departmentName,
      'label' => $departmentName,
    ]);

    return $department;
  }

  private function createLocation($locationName) {
    $location = OptionValueFabricator::fabricate([
      'option_group_id' => 'hrjc_location',
      'name' => $locationName,
      'value' => $locationName,
      'label' => $locationName,
    ]);

    return $location;
  }
}
