<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRAbsence_Test_Fabricator_HRAbsenceType as AbsenceTypeFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobLeave as HRJobLeaveFabricator;
use CRM_HRCore_Test_Fabricator_Activity as ActivityFabricator;

/**
 * Class CRM_HRAbsence_BAO_HRAbsenceTypeTest
 * 
 * @group headless
 */
class CRM_HRAbsence_BAO_HRAbsenceTypeTest extends PHPUnit_Framework_TestCase 
  implements HeadlessInterface, TransactionalInterface {
  
  protected $absenceType;
  protected $contact;
  protected $jobContract;
  protected $leave;
  
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('org.civicrm.hrjobcontract')
      ->installMe(__DIR__)
      ->apply();
  }
  
  /**
   * Creates absence type, contact, contract and leave entitlement to be used in
   * tests.
   */
  protected function setUp() {
    $this->absenceType = AbsenceTypeFabricator::fabricateUsingAPI();

    $this->contact = ContactFabricator::fabricate();

    $this->jobContract = HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']], 
      ['period_start_date' => '2015-01-01']
    );

    $this->leave = HRJobLeaveFabricator::fabricate([
      'jobcontract_id' => $this->jobContract['id'],
      'leave_type' => $this->absenceType['id']
    ]);
  }
  
  /**
   * @expectedException CRM_Core_Exception
   */
  public function testDoesNotAllowAbsenceTypeInUseByLeaveRequestToBeDeleted() {
    $activityParam = [
      'source_contact_id' => $this->contact['id'],
      'target_contact_id' => $this->contact['id'],
      'assignee_contact_id' => $this->contact['id'],
      'activity_type_id' => $this->absenceType['debit_activity_type_id'],
    ];
    ActivityFabricator::fabricate($activityParam);
    
    CRM_HRAbsence_BAO_HRAbsenceType::del($this->absenceType['id']);
  }
  
  public function testIfDeletionOfAbsenceTypeDeletesAssociatedEntitlements() {
    $this->assertEquals(1, $this->countEntitlementsForAbsenceType());

    CRM_HRAbsence_BAO_HRAbsenceType::del($this->absenceType['id']);

    $this->assertEquals(0, $this->countEntitlementsForAbsenceType());
  }
  
  private function countEntitlementsForAbsenceType() {
    $result = civicrm_api3('HRJobLeave', 'get', [
      'sequential' => 1,
      'id' => $this->leave['id'],
      'jobcontract_id' => $this->jobContract['id'],
    ]);
    
    return $result['count'];
  }
}
