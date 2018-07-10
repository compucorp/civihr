<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_Hrjobroles_Test_Fabricator_HrJobRoles as HRJobRolesFabricator;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData as LeaveRequestCalendarFeedData;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig as LeaveRequestCalendarFeedConfig;
use CRM_HRLeaveAndAbsences_Helper_CalendarFeed_LeaveTime as CalendarLeaveTimeHelper;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedDataTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedDataTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $leaveTypes;

  public function testDateRangeForLeaveData() {
    $feedConfig =  $this->createLeaveCalendarFeedConfig([]);
    $feedData = new LeaveRequestCalendarFeedData($feedConfig->hash);
    $startDate = $feedData->getStartDate();
    $endDate = $feedData->getEndDate();
    $expectedStartDate = new DateTime('today');
    $expectedEndDate = new DateTime('+ 3 months');
    $expectedEndDate->setTime('23', '59');

    $this->assertEquals($expectedStartDate, $startDate, '', 10);
    $this->assertEquals($expectedEndDate, $endDate, '', 10);
  }

  public function testGetFeedConfig() {
    $feedConfig =  $this->createLeaveCalendarFeedConfig([]);

    $feedData = new LeaveRequestCalendarFeedData($feedConfig->hash);
    $this->assertEquals($feedConfig->id, $feedData->getFeedConfig()->id);
    $this->assertEquals($feedConfig->hash, $feedData->getFeedConfig()->hash);
  }

  public function testExceptionIsThrownWhenFeedHashIsEmpty() {
    $this->setExpectedException('RuntimeException', 'The feed hash should not be empty');
    new LeaveRequestCalendarFeedData('');
  }

  public function testExceptionIsThrownWhenFeedConfigIsDisabled() {
    $this->setExpectedException('RuntimeException', 'An enabled feed with the given hash does not exist!');
    $feedConfig1 = $this->createLeaveCalendarFeedConfig(['is_active' => 0]);
    new LeaveRequestCalendarFeedData($feedConfig1->hash);
  }

  public function testExceptionIsThrownWhenFeedConfigHashDoesNotExist() {
    $this->setExpectedException('RuntimeException', 'An enabled feed with the given hash does not exist!');
    new LeaveRequestCalendarFeedData("blabla");
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

    $feedConfig1 = $this->createLeaveCalendarFeedConfig([
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
    $feedConfig1 = $this->createLeaveCalendarFeedConfig([
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
    $feedConfig1 = $this->createLeaveCalendarFeedConfig([
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
    $absenceType2 = AbsenceTypeFabricator::fabricate();
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

    //This leave request will not be returned because  the absence type is disabled
    //just after the leave was created for it.
    $params[2] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType2->id,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
    ];

    //disable absence type 2
    AbsenceTypeFabricator::fabricate(['id' => $absenceType2->id, 'is_active' => 0]);

    foreach ($params as &$param) {
      $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($param);
      $param['id'] =  $leaveRequest->id;
    }

    //feed config is for all contacts in any department/location
    $feedConfig1 = $this->createLeaveCalendarFeedConfig([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id, $absenceType2->id],
      ]
    ]);

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);

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
    $feedConfig1 = $this->createLeaveCalendarFeedConfig([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id],
      ]
    ]);

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);

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
    $feedConfig1 = $this->createLeaveCalendarFeedConfig([
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
    $contact1 = ContactFabricator::fabricate(['first_name' => 'Contact1', 'last_name' => 'LastContact1']);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('yesterday')]
    );

    //Leave is outside date range for feed data
    $params[1] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('yesterday'),
      'to_date' => CRM_Utils_Date::processDate('yesterday'),
    ];

    $params[2] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('+2 days'),
    ];

    //Leave is outside date range for feed data
    $params[2] = [
      'contact_id' => $contact1['id'],
      'first_name' => $contact1['first_name'],
      'last_name' => $contact1['last_name'],
      'type_id' => $absenceType->id,
      'from_date' => CRM_Utils_Date::processDate('+4 months'),
      'to_date' => CRM_Utils_Date::processDate('+4 months'),
    ];

    foreach ($params as &$param) {
      $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($param);
      $param['id'] =  $leaveRequest->id;
    }

    //feed config is for all contacts in any department/location
    $feedConfig1 = $this->createLeaveCalendarFeedConfig([
      'title' => 'Feed 1',
      'composed_of' => [
        'leave_type' => [$absenceType->id],
      ]
    ]);

    $leaveFeedData = new LeaveRequestCalendarFeedData($feedConfig1->hash);

    unset($params[1], $params[2]);
    $this->assertEquals($this->getExpectedLeaveDataResult($params), $leaveFeedData->get());
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

  private function createLeaveCalendarFeedConfig($params) {
    $defaultParameters = [
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
      'composed_of' => [
        'leave_type' => [1]
      ],
      'visible_to' => []
    ];

    $params = array_merge($defaultParameters, $params);

    return LeaveRequestCalendarFeedConfig::create($params);
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
