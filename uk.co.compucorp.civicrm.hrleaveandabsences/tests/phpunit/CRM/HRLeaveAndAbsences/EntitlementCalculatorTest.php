<?php

require_once __DIR__."/BaseTest.php";

use CRM_HRLeaveAndAbsences_BaseTest as BaseTest;
use CRM_HRLeaveAndAbsences_EntitlementCalculator as EntitlementCalculator;
use CRM_HRLeaveAndAbsences_EntitlementCalculation as EntitlementCalculation;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Class CRM_HRLeaveAndAbsences_EntitlementCalculator
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_EntitlementCalculatorTest extends BaseTest {

  public function testCanReturnCalculationsForMultipleAbsenceTypes()
  {
    $this->createBasicType();
    $period = new AbsencePeriod();

    // mock the array returned by an API call
    $contract = [
      'id' => 1,
      'is_primary' => 1,
      'contact_id' => 2,
    ];

    $calculator = new EntitlementCalculator($period);
    $calculations = $calculator->calculateEntitlementsFor($contract);
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
    $contract = [
      'id' => 1,
      'is_primary' => 1,
      'contact_id' => 2,
    ];

    $calculator = new EntitlementCalculator($period);
    $calculations = $calculator->calculateEntitlementsFor($contract);
    // He have 3 reserved absence types that cannot be deleted, and added a
    // new disabled AbsenceType, so we should get only 3 calculations, since
    // the new one is disabled and should not be included in the calculation
    $this->assertCount(3, $calculations);
  }

  private function createBasicType($params = array()) {
    $basicRequiredFields = [
      'title' => 'Type ' . microtime(),
      'color' => '#000000',
      'default_entitlement' => 20,
      'allow_request_cancelation' => 1,
    ];

    $params = array_merge($basicRequiredFields, $params);
    return AbsenceType::create($params);
  }
}
