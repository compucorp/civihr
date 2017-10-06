<?php

use CRM_HRLeaveAndAbsences_Service_EntitlementCalculator as EntitlementCalculator;
use CRM_HRLeaveAndAbsences_Service_EntitlementCalculation as EntitlementCalculation;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_EntitlementCalculatorTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_EntitlementCalculatorTest extends BaseHeadlessTest {

  public function testCanReturnCalculationsForMultipleAbsenceTypes()
  {
    $this->createBasicType();
    $period = new AbsencePeriod();

    // mock the array returned by an API call
    $contact = [
      'id' => 1
    ];

    $calculator = new EntitlementCalculator($period);
    $calculations = $calculator->calculateEntitlementsFor($contact);
    // He have 3 reserved absence types that cannot be deleted, and added a
    // new AbsenceType, so we should get 4 calculations (one for each type)
    $this->assertCount(4, $calculations);
    foreach($calculations as $calculation) {
      $this->assertInstanceOf(EntitlementCalculation::class, $calculation);
    }
  }

  public function testCanOnlyReturnCalculationsForEnabledAbsenceTypes()
  {
    $this->createBasicType(['is_active' => false]);
    $period = new AbsencePeriod();

    // mock the array returned by an API call
    $contact = [
      'id' => 1
    ];

    $calculator = new EntitlementCalculator($period);
    $calculations = $calculator->calculateEntitlementsFor($contact);
    // He have 3 reserved absence types that cannot be deleted, and added a
    // new disabled AbsenceType, so we should get only 3 calculations, since
    // the new one is disabled and should not be included in the calculation
    $this->assertCount(3, $calculations);
  }

  private function createBasicType($params = array()) {
    return AbsenceTypeFabricator::fabricate($params);
  }
}
