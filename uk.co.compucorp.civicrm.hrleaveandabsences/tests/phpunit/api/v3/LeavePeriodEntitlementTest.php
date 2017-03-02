<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
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
}
