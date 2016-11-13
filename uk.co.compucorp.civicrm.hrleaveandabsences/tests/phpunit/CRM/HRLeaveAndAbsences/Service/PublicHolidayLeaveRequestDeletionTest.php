<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as PublicHolidayLeaveRequestDeletion;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletionTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletionTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeavePeriodEntitlementHelpersTrait;

  /**
   * @var CRM_HRLeaveAndAbsences_BAO_AbsenceType
   */
  private $absenceType;

  public function setUp() {
    // We delete everything two avoid problems with the default absence types
    // created during the extension installation
    $tableName = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");

    $this->absenceType = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => 1
    ]);
  }

  public function testCanDeleteAPublicHolidayLeaveRequestForASingleContact() {
    $periodEntitlement = $this->createLeavePeriodEntitlementMockForBalanceTests();
    $periodEntitlement->contact_id = 2;
    $periodEntitlement->type_id = $this->absenceType->id;

    $publicHoliday = $this->instantiatePublicHoliday('2016-01-01');

    PublicHolidayLeaveRequestFabricator::fabricate($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(-1, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));

    $deletionLogic = new PublicHolidayLeaveRequestDeletion();
    $deletionLogic->deleteForContact($periodEntitlement->contact_id, $publicHoliday);

    $this->assertEquals(0, LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($periodEntitlement));
  }

  private function instantiatePublicHoliday($date) {
    $publicHoliday = new PublicHoliday();
    $publicHoliday->date = CRM_Utils_Date::processDate($date);

    return $publicHoliday;
  }
}
