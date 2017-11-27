<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Factory_LeaveDateAmountDeduction as LeaveDateAmountDeductionFactory;
use CRM_HRLeaveAndAbsences_Service_LeaveDateHoursAmountDeduction as LeaveDateHoursAmountDeduction;
use CRM_HRLeaveAndAbsences_Service_LeaveDateDaysAmountDeduction as LeaveDateDaysAmountDeduction;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_LeaveDateAmountDeductionTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_LeaveDateAmountDeductionTest extends BaseHeadlessTest {

  public function testCreateForAbsenceTypeReturnsDaysAmountDeductionClassWhenAbsenceTypeIsCalculatedInDays() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $dateDeductionClass = LeaveDateAmountDeductionFactory::createForAbsenceType($absenceType->id);
    $this->assertInstanceOf(LeaveDateDaysAmountDeduction::class, $dateDeductionClass);
  }

  public function testCreateForAbsenceTypeReturnsHoursAmountDeductionClassWhenAbsenceTypeIsCalculatedInHours() {
    $calculationUnitOptions = array_flip(AbsenceType::buildOptions('calculation_unit', 'validate'));
    $absenceType = AbsenceTypeFabricator::fabricate(['calculation_unit' => $calculationUnitOptions['hours']]);
    $dateDeductionClass = LeaveDateAmountDeductionFactory::createForAbsenceType($absenceType->id);
    $this->assertInstanceOf(LeaveDateHoursAmountDeduction::class, $dateDeductionClass);
  }
}
