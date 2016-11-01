<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation as PublicHolidayLeaveRequestCreation;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveReqeustCreationTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveReqeustCreationTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait;

  public function setUp() {
    // We delete everything two avoid problems with the default absence types
    // created during the extension installation
    $tableName = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");
  }

  public function testSomething() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => 1
    ]);

    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();
    $periodEntitlement->contact_id = 2;
    $periodEntitlement->type_id = $absenceType->id;

    $creationLogic = new PublicHolidayLeaveRequestCreation();
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate('first monday of this year');

    $creationLogic->createForContact($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

}
