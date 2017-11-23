<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Factory_LeaveBalanceChangeCalculation as LeaveBalanceChangeCalculationFactory;
use CRM_HRLeaveAndAbsences_Service_LeaveDaysBalanceChangeCalculation as LeaveDaysBalanceChangeCalculation;
use CRM_HRLeaveAndAbsences_Service_LeaveHoursBalanceChangeCalculation as LeaveHoursBalanceChangeCalculation;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;


/**
 * Class CRM_HRLeaveAndAbsences_Factory_LeaveBalanceChangeCalculationTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_LeaveBalanceChangeCalculationTest extends BaseHeadlessTest {

  public function testCreateReturnsLeaveDaysBalanceChangeCalculationClassWhenAbsenceTypeIsCalculatedInDays() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $leaveRequest = new LeaveRequest();
    $leaveRequest->type_id = $absenceType->id;

    $balanceCalculationClass = LeaveBalanceChangeCalculationFactory::create($leaveRequest);
    $this->assertInstanceOf(LeaveDaysBalanceChangeCalculation::class, $balanceCalculationClass);
  }

  public function testCreateReturnsLeaveHoursBalanceChangeCalculationClassWhenAbsenceTypeIsCalculatedInHours() {
    $calculationUnitOptions = array_flip(AbsenceType::buildOptions('calculation_unit', 'validate'));
    $absenceType = AbsenceTypeFabricator::fabricate(['calculation_unit' => $calculationUnitOptions['hours']]);
    $leaveRequest = new LeaveRequest();
    $leaveRequest->type_id = $absenceType->id;

    $balanceCalculationClass = LeaveBalanceChangeCalculationFactory::create($leaveRequest);
    $this->assertInstanceOf(LeaveHoursBalanceChangeCalculation::class, $balanceCalculationClass);
  }
}
