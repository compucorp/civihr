<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

/**
 * Class api_v3_LeavePeriodEntitlementTest
 *
 * @group headless
 */
class api_v3_LeavePeriodEntitlementTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testOnGetRemainderDoesNotAcceptContactIdWithoutPeriodId() {
    civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testOnGetRemainderDoesNotAcceptPeriodIdWithoutContactId() {
    civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['period_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetRemainderWhenAllParametersArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['entitlement_id'=> 1, 'period_id' => 1, 'contact_id'=>1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetRemainderWhenEntitlementIdAndPeriodIdArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['entitlement_id'=> 1, 'contact_id'=>1]);
  }

  public function testGetRemainderWhenContactAndPeriodIdArePassed() {
    $result = civicrm_api3('LeavePeriodEntitlement', 'getremainder', ['period_id'=> 1, 'contact_id'=>1]);
    $this->assertEmpty($result['values']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testOnGetBreakdownDoesNotAcceptContactIdWithoutPeriodId() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testOnGetBreakdownDoesNotAcceptPeriodIdWithoutContactId() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['period_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetBreakdownWhenAllParametersArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['entitlement_id' => 1, 'period_id' => 1, 'contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetBreakdownWhenEntitlementIdAndPeriodIdArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['entitlement_id' => 1, 'contact_id' => 1]);
  }

  public function testGetBreakdownWhenContactAndPeriodIdArePassed() {
    $result = civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['period_id' => 1, 'contact_id' => 1]);
    $this->assertEmpty($result['values']);
  }

  public function testGetBreakdownWhenOnlyEntitlementIdIsPassed() {

    $absencePeriod = AbsencePeriodFabricator::fabricate();
    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id
    ]);
    $result = civicrm_api3('LeavePeriodEntitlement', 'getbreakdown', ['entitlement_id' => $periodEntitlement->id]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'id' => 0,
      'values' => [
        [
          'id' => $periodEntitlement->id,
          'breakdown' => []
        ]
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementDoesNotAcceptContactIdWithoutPeriodId() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementDoesNotAcceptPeriodIdWithoutContactId() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['period_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementWhenAllParametersArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['entitlement_id'=> 1, 'period_id' => 1, 'contact_id'=>1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementWhenEntitlementIdAndContactIdArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['entitlement_id'=> 1, 'contact_id'=>1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage You must include either the id of a specific entitlement, or both the contact and period id
   */
  public function testGetEntitlementWhenEntitlementIdAndPeriodIdArePassed() {
    civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', ['entitlement_id'=> 1, 'period_id'=>1]);
  }

  public function testGetEntitlementCanReturnTheEntitlementsForAContactInAPeriod() {
    $contact = ContactFabricator::fabricate();

    $type1 = AbsenceTypeFabricator::fabricate();
    $type2 = AbsenceTypeFabricator::fabricate();
    $type3 = AbsenceTypeFabricator::fabricate();

    $period1 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $period2 = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2017-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2017-12-31')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type1->id,
      'period_id' => $period1->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type2->id,
      'period_id' => $period1->id
    ]);

    $periodEntitlement3 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type2->id,
      'period_id' => $period2->id
    ]);

    $periodEntitlement4 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type3->id,
      'period_id' => $period2->id
    ]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'contact_id' => $contact['id'],
      'period_id' => $period1->id
    ]);

    //For the period 1, the contact only has entitlements for Types 1 and 2
    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 2,
      'values' => [
        [
          'id' => $periodEntitlement1->id,
          'entitlement' => 0,
        ],
        [
          'id' => $periodEntitlement2->id,
          'entitlement' => 0,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'contact_id' => $contact['id'],
      'period_id' => $period2->id
    ]);

    //For the period 1, the contact only has entitlements for Types 2 and 3
    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 2,
      'values' => [
        [
          'id' => $periodEntitlement3->id,
          'entitlement' => 0,
        ],
        [
          'id' => $periodEntitlement4->id,
          'entitlement' => 0,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);
  }

  public function testGetEntitlementCanReturnTheEntitlementsForASpecificLeavePeriodEntitlement() {
    $contact = ContactFabricator::fabricate();

    $type1 = AbsenceTypeFabricator::fabricate();
    $type2 = AbsenceTypeFabricator::fabricate();

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type1->id,
      'period_id' => $period->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type2->id,
      'period_id' => $period->id
    ]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'entitlement_id' => $periodEntitlement1->id,
    ]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        [
          'id' => $periodEntitlement1->id,
          'entitlement' => 0,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'entitlement_id' => $periodEntitlement2->id,
    ]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        [
          'id' => $periodEntitlement2->id,
          'entitlement' => 0,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);
  }

  public function testGetEntitlementCanReturnTheEntitlementsForLeavePeriodEntitlements() {
    $contact = ContactFabricator::fabricate();

    $type1 = AbsenceTypeFabricator::fabricate();
    $type2 = AbsenceTypeFabricator::fabricate();

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type1->id,
      'period_id' => $period->id
    ]);

    $periodEntitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'type_id' => $type2->id,
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement1->id, 20);
    $this->createPublicHolidayBalanceChange($periodEntitlement1->id, 5);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'entitlement_id' => $periodEntitlement1->id,
    ]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        [
          'id' => $periodEntitlement1->id,
          'entitlement' => 25,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);

    $this->createOverriddenBalanceChange($periodEntitlement2->id, 50);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getEntitlement', [
      'entitlement_id' => $periodEntitlement2->id,
    ]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        [
          'id' => $periodEntitlement2->id,
          'entitlement' => 50,
        ]
      ]
    ];

    $this->assertEquals($expected, $result);
  }

  public function testGetReturnsOnlyDataLinkedToLoggedInUserWhenUserIsNotALeaveApproverOrAdmin() {
    $contactID1 = 1;
    $contactID2 = 2;

    $this->registerCurrentLoggedInContactInSession($contactID1);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactID1,
      'type_id' => 1,
      'period_id' => 1
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactID2,
      'type_id' => 1,
      'period_id' => 1
    ]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contactID1, $result['values'][0]['contact_id']);
  }

  public function testGetReturnsOnlyDataLinkedToContactsThatLoggedInUserManagesWhenLoggedInUserIsALeaveApprover() {
    $manager = ContactFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    $this->registerCurrentLoggedInContactInSession($manager['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    $this->setContactAsLeaveApproverOf($manager, $contact2);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'period_id' => 1
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'period_id' => 1
    ]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contact2['id'], $result['values'][0]['contact_id']);
  }

  public function testGetReturnsOnlyDataLinkedToContactsThatLoggedInUserManagesWhenLoggedInIsALeaveApproverWithOneOfTheAvailableRelationships() {
    $this->setLeaveApproverRelationshipTypes([
      'has leaves approved by',
      'has things managed by',
    ]);

    $manager1 = ContactFabricator::fabricate();
    $manager2 = ContactFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contact3 = ContactFabricator::fabricate();

    $this->setContactAsLeaveApproverOf($manager1, $contact2, null, null, true, 'manage things for');
    $this->setContactAsLeaveApproverOf($manager2, $contact1, null, null, true, 'has leaves approved by');
    $this->setContactAsLeaveApproverOf($manager2, $contact3, null, null, true, 'manage things for');

    $entitlementContact1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'period_id' => 1
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'period_id' => 1
    ]);

    $entitlementContact3 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact3['id'],
      'type_id' => 1,
      'period_id' => 1
    ]);

    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    // Manager1 only manages contact2 (though the 'has things managed by' relationship),
    // so only contact2 leave requests will be returned
    $this->registerCurrentLoggedInContactInSession($manager1['id']);
    $result = civicrm_api3('LeavePeriodEntitlement', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($contact2['id'], $result['values'][0]['contact_id']);

    // Manager2 manages contact1 (through the 'has leaves approved by' relationship),
    // and contact3 (through the 'manage things for' relationship), so the
    // entitlement for both will be returned
    $this->registerCurrentLoggedInContactInSession($manager2['id']);
    $result = civicrm_api3('LeavePeriodEntitlement', 'get', ['check_permissions' => true]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contact1['id'], $result['values'][$entitlementContact1->id]['contact_id']);
    $this->assertEquals($contact3['id'], $result['values'][$entitlementContact3->id]['contact_id']);
  }

  public function testGetReturnsAllDataWhenLoggedInUserHasViewAllContactsPermission() {
    $adminID = 1;
    $contactID1 = 2;
    $contactID2 = 3;

    $this->registerCurrentLoggedInContactInSession($adminID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API', 'view all contacts'];

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactID1,
      'type_id' => 1,
      'period_id' => 1
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactID2,
      'type_id' => 1,
      'period_id' => 1
    ]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contactID1, $result['values'][0]['contact_id']);
    $this->assertEquals($contactID2, $result['values'][1]['contact_id']);
  }

  public function testGetReturnsAllDataWhenLoggedInUserHasEditAllContactsPermission() {
    $adminID = 1;
    $contactID1 = 2;
    $contactID2 = 3;

    $this->registerCurrentLoggedInContactInSession($adminID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API', 'edit all contacts'];

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactID1,
      'type_id' => 1,
      'period_id' => 1
    ]);

    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactID2,
      'type_id' => 1,
      'period_id' => 1
    ]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'get', ['check_permissions' => true, 'sequential' => 1]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals($contactID1, $result['values'][0]['contact_id']);
    $this->assertEquals($contactID2, $result['values'][1]['contact_id']);
  }

  public function testGetLeaveBalancesReturnsTheBalancesForAllContactsWithAContractDuringTheGivenPeriod() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contact3 = ContactFabricator::fabricate();

    $contract1 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => CRM_Utils_Date::processDate('-5 days'),
        'period_end_date' => CRM_Utils_Date::processDate('-1 day')
      ]
    );

    $contract2 = HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('+5 days')]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact3['id']],
      ['period_start_date' => CRM_Utils_Date::processDate('+15 days')]
    );

    $absenceTypeID = 1;

    $entitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact1['id'],
    ]);

    $entitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact2['id'],
    ]);

    $entitlement3 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact3['id'],
    ]);

    $this->createLeaveBalanceChange($entitlement1->id, 15.25);
    $this->createLeaveBalanceChange($entitlement2->id, 5.5);
    $this->createLeaveBalanceChange($entitlement3->id, 7);

    // within the first contract, will be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract1['contact_id'],
      'status_id' => $leaveRequestStatuses['approved'],
      'from_date' => date('YmdHis', strtotime('-4 days')),
      'to_date' => date('YmdHis', strtotime('-2 days'))
    ], true);

    // within second contract, will be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract2['contact_id'],
      'status_id' => $leaveRequestStatuses['awaiting_approval'],
      'from_date' => date('YmdHis', strtotime('+6 days')),
      'to_date' => date('YmdHis', strtotime('+6 days'))
    ], true);

    // within the third contract, which is outside the absence period, so it
    // won't be included
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => $contract2['contact_id'],
      'status_id' => $leaveRequestStatuses['more_information_required'],
      'from_date' => date('YmdHis', strtotime('+16 days')),
      'to_date' => date('YmdHis', strtotime('+16 days'))
    ], true);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id
    ])['values'];

    $this->assertCount(2, $result);

    $contact1ExpectedBalances = [
      'contact_id' => $contact1['id'],
      'contact_display_name' => $contact1['display_name'],
      'absence_types' => [
        [
          'id' => $absenceTypeID,
          'entitlement' => 15.25,
          'used' => 3,
          'balance' => 12.25,
          'requested' => 0
        ]
      ]
    ];
    $this->assertEquals($contact1ExpectedBalances, $result[$contact1['id']]);

    $contact2ExpectedBalances = [
      'contact_id' => $contact2['id'],
      'contact_display_name' => $contact2['display_name'],
      'absence_types' => [
        [
          'id' => $absenceTypeID,
          'entitlement' => 5.5,
          'used' => 0,
          'balance' => 5.5,
          'requested' => 1
        ]
      ]
    ];
    $this->assertEquals($contact2ExpectedBalances, $result[$contact2['id']]);
  }

  public function testGetLeaveBalancesCanReturnBalancesForASpecificContact() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $absenceTypeID = 1;

    $entitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact1['id'],
    ]);

    $entitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact2['id'],
    ]);

    $this->createLeaveBalanceChange($entitlement1->id, 7.75);
    $this->createLeaveBalanceChange($entitlement2->id, 5.5);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'contact_id' => $contact1['id']
    ])['values'];

    $this->assertCount(1, $result);

    $expectedResult = [
      'contact_id' => $contact1['id'],
      'contact_display_name' => $contact1['display_name'],
      'absence_types' => [
        [
          'id' => $absenceTypeID,
          'entitlement' => 7.75,
          'used' => 0,
          'balance' => 7.75,
          'requested' => 0
        ]
      ]
    ];
    $this->assertEquals($expectedResult, $result[$contact1['id']]);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'contact_id' => $contact2['id']
    ])['values'];

    $this->assertCount(1, $result);

    $expectedResult = [
      'contact_id' => $contact2['id'],
      'contact_display_name' => $contact2['display_name'],
      'absence_types' => [
        [
          'id' => $absenceTypeID,
          'entitlement' => 5.5,
          'used' => 0,
          'balance' => 5.5,
          'requested' => 0
        ]
      ]
    ];
    $this->assertEquals($expectedResult, $result[$contact2['id']]);
  }

  public function testGetLeaveBalancesCanReturnBalancesForContactsManagedByASpecificManager() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $manager1 = ContactFabricator::fabricate();
    $manager2 = ContactFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    $this->setContactAsLeaveApproverOf($manager1, $contact1);
    $this->setContactAsLeaveApproverOf($manager1, $contact2);
    $this->setContactAsLeaveApproverOf($manager2, $contact2);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $absenceTypeID = 1;

    $entitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact1['id'],
    ]);

    $entitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact2['id'],
    ]);

    $this->createLeaveBalanceChange($entitlement1->id, 7.75);
    $this->createLeaveBalanceChange($entitlement2->id, 5.5);

    // Manager 1 manages both contacts, so they all should be returned
    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'managed_by' => $manager1['id']
    ])['values'];

    $this->assertCount(2, $result);
    $this->assertNotEmpty($result[$contact1['id']]);
    $this->assertNotEmpty($result[$contact2['id']]);

    // Manager 2 manages only contact 2, so that's the only one returned
    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'managed_by' => $manager2['id']
    ])['values'];

    $this->assertCount(1, $result);
    $this->assertNotEmpty($result[$contact2['id']]);
  }

  public function testGetLeaveBalancesOnlyReturnBalancesForContactsManagedByTheCurrentUser() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $manager1 = ContactFabricator::fabricate();
    $manager2 = ContactFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    $this->setContactAsLeaveApproverOf($manager1, $contact1);
    $this->setContactAsLeaveApproverOf($manager2, $contact2);

    $this->registerCurrentLoggedInContactInSession($manager2['id']);
    $this->setPermissions([]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $absenceTypeID = 1;

    $entitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact1['id'],
    ]);

    $entitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact2['id'],
    ]);

    $this->createLeaveBalanceChange($entitlement1->id, 7.75);
    $this->createLeaveBalanceChange($entitlement2->id, 5.5);

    // Currently logged in as Manager 2, so only Contact 2 should be returned
    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id
    ])['values'];

    $this->assertCount(1, $result);
    $this->assertNotEmpty($result[$contact2['id']]);
  }

  public function testGetLeaveBalancesShouldThrowAnErrorIfAInvalidAbsencePeriodIsGiven() {
    $randomID = rand(1, 500);

    $this->setExpectedException(
      'CiviCRM_API3_Exception',
      "Unable to find a CRM_HRLeaveAndAbsences_BAO_AbsencePeriod with id {$randomID}."
    );

    civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $randomID
    ]);
  }

  public function testGetLeaveBalancesShouldThrowAnErrorIfAnAbsencePeriodIsNotGiven() {
    $this->setExpectedException(
      'CiviCRM_API3_Exception',
      'Mandatory key(s) missing from params array: period_id'
    );

    civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', []);
  }

  public function testGetLeaveBalanceShouldReturnTheNumberOfRecordsWhenIsCountIsPresent() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $absenceTypeID = 1;

    $entitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact1['id'],
    ]);

    $entitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceTypeID,
      'contact_id' => $contact2['id'],
    ]);

    $this->createLeaveBalanceChange($entitlement1->id, 7.75);
    $this->createLeaveBalanceChange($entitlement2->id, 5.5);

    // Balances for both contacts will be returned
    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'options' => ['is_count' => 1]
    ]);
    $this->assertEquals(2, $result);

    // There's no manager with ID 100, so count will be 0
    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'managed_by' => 100,
      'options' => ['is_count' => 1]
    ]);
    $this->assertEquals(0, $result);

    // Querying for a single specific contact, count will be 1
    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'contact_id' => $contact1['id'],
      'options' => ['is_count' => 1]
    ]);
    $this->assertEquals(1, $result);
  }

  public function testGetLeaveBalanceCanReturnBalancesForASpecificAbsenceType() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+10 days')
    ]);

    $contact1 = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      ['period_start_date' => $absencePeriod->start_date]
    );

    $absenceType1ID = 1;
    $absenceType2ID = 2;

    $entitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType1ID,
      'contact_id' => $contact1['id'],
    ]);

    $entitlement2 = LeavePeriodEntitlementFabricator::fabricate([
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType2ID,
      'contact_id' => $contact1['id'],
    ]);

    $this->createLeaveBalanceChange($entitlement1->id, 7.75);
    $this->createLeaveBalanceChange($entitlement2->id, 5.5);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType1ID
    ])['values'];
    $this->assertCount(1, $result[$contact1['id']]['absence_types']);
    $this->assertEquals($absenceType1ID, $result[$contact1['id']]['absence_types'][0]['id']);

    $result = civicrm_api3('LeavePeriodEntitlement', 'getLeaveBalances', [
      'period_id' => $absencePeriod->id,
      'type_id' => $absenceType2ID
    ])['values'];
    $this->assertCount(1, $result[$contact1['id']]['absence_types']);
    $this->assertEquals($absenceType2ID, $result[$contact1['id']]['absence_types'][0]['id']);
  }
}
