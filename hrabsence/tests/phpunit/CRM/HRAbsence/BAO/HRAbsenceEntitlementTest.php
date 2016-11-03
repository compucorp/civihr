<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRAbsence_Test_Fabricator_HRAbsencePeriod as AbsencePeriodFabricator;
use CRM_HRAbsence_Test_Fabricator_HRAbsenceType as AbsenceTypeFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as JobContractFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobLeave as JobLeaveFabricator;

/**
 * Class CRM_HRAbsence_BAO_HRAbsencePeriodTest
 *
 * @group headless
 */
class CRM_HRAbsence_BAO_HRAbsenceEntitlementTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('org.civicrm.hrjobcontract')
      ->installMe(__DIR__)
      ->apply();
  }

  public function testRecalculateAbsenceEntitlementsForPeriodCreatesTheContactsEntitlementsForThatPeriod() {
    $absenceType = AbsenceTypeFabricator::fabricate();

    $contact1 = ContactFabricator::fabricate([]);
    $contact2 = ContactFabricator::fabricate([]);

    $contract1 = JobContractFabricator::fabricate(['contact_id' => $contact1['id']], [
      'period_start_date' => '2015-01-01',
      'period_end_date' => '2015-12-31',
      'title' => 'Employee',
      'position' => 'Employee'
    ]);

    JobLeaveFabricator::fabricate([
      'jobcontract_id' => $contract1['id'],
      'leave_amount' => 14,
      'leave_type' => $absenceType->id
    ]);

    $contract2 = JobContractFabricator::fabricate(['contact_id' => $contact2['id']], [
      'period_start_date' => '2015-07-23',
      'title' => 'Employee 2',
      'position' => 'Employee 2'
    ]);

    JobLeaveFabricator::fabricate([
      'jobcontract_id' => $contract2['id'],
      'leave_amount' => 23,
      'leave_type' => $absenceType->id
    ]);

    $period = AbsencePeriodFabricator::fabricate([
      'name' => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    CRM_HRAbsence_BAO_HRAbsenceEntitlement::recalculateAbsenceEntitlementsForPeriod($period->id);

    $contact1Entitlement = new CRM_HRAbsence_BAO_HRAbsenceEntitlement();
    $contact1Entitlement->contact_id = $contact1['id'];
    $contact1Entitlement->period_id = $period->id;
    $contact1Entitlement->type_id = $absenceType->id;
    $contact1Entitlement->find(true);

    // The absence_entitlement record is created for contact 1,
    // but since their contract doesn't overlap the absence period,
    // the leave_amount will be 0, meaning it doesn't have entitlement
    // for that year
    $this->assertEquals(1, $contact1Entitlement->N);
    $this->assertEquals(0, $contact1Entitlement->amount);

    $contact1Entitlement = new CRM_HRAbsence_BAO_HRAbsenceEntitlement();
    $contact1Entitlement->contact_id = $contact2['id'];
    $contact1Entitlement->period_id = $period->id;
    $contact1Entitlement->type_id = $absenceType->id;
    $contact1Entitlement->find(true);

    // The absence_entitlement record is created for contact 2,
    // and since their contract overlaps the absence period,
    // the leave_amount will be fetched from the contractual
    // entitlement set in Job Leave
    $this->assertEquals(1, $contact1Entitlement->N);
    $this->assertEquals(23, $contact1Entitlement->amount);
  }



}
