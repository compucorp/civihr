<?php

use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementLog as LeavePeriodEntitlementLog;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementLogTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementLogTest extends BaseHeadlessTest  {

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 1;');
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementLogException
   * @expectedExceptionMessage The entitlement_id field should not be empty
   */
  public function testCreateEntitlementLogThrowsExceptionWhenEntitlementIdIsAbsent() {
    LeavePeriodEntitlementLog::create([
      'entitlement_amount' => 3,
      'editor_id' => 1,
      'comment' => '',
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementLogException
   * @expectedExceptionMessage The entitlement_amount field should not be empty
   */
  public function testCreateEntitlementLogThrowsExceptionWhenEntitlementAmountIsAbsent() {
    LeavePeriodEntitlementLog::create([
      'entitlement_id' => 2,
      'editor_id' => 1,
      'comment' => 'Sample Comment',
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementLogException
   * @expectedExceptionMessage The editor_id field should not be empty
   */
  public function testCreateEntitlementLogThrowsExceptionWhenAuthorIdIsAbsent() {
    LeavePeriodEntitlementLog::create([
      'entitlement_id' => 2,
      'entitlement_amount' => 3,
      'comment' => 'Sample Comment',
    ]);
  }

  public function testCreatedDateForTheLeavePeriodEntitlementLogCannotBeSetInTheParams() {
    $entitlementLog = LeavePeriodEntitlementLog::create([
      'entitlement_id' => 1,
      'entitlement_amount' => 3,
      'editor_id' => 1,
      'comment' => '',
      'created_date' => CRM_Utils_Date::processDate('2016-01-01')
    ]);

    $entitlementLog = LeavePeriodEntitlementLog::findById($entitlementLog->id);
    $now = new DateTime('now');
    $entitlementLogCreatedDate = new DateTime($entitlementLog->created_date);

    //The entitlement log created date cannot be set as a parameter to the
    //create method.
    $this->assertEquals($now, $entitlementLogCreatedDate, '', 5);
  }

  public function testUpdatesNotAllowedForTheEntitlementLogEntity() {
    $params = [
      'entitlement_id' => 1,
      'entitlement_amount' => 3,
      'editor_id' => 1,
      'comment' => 'Sample Comment'
    ];

    $entitlementLog = LeavePeriodEntitlementLog::create($params);
    $this->setExpectedException(
      CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementLogException::class,
      'Updates not allowed for Leave Period Entitlement Log entity'
    );
    $params['id'] = $entitlementLog->id;
    LeavePeriodEntitlementLog::create($params);
  }
}
