<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_EntitlementTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_EntitlementTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->install('org.civicrm.hrjobcontract')
      ->apply();
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testThereCannotBeMoreThanOneEntitlementForTheSameSetOfAbsenceTypeAbsencePeriodAndContract() {
    $type = CRM_HRLeaveAndAbsences_BAO_AbsenceType::create([
        'title' => 'Type ' . microtime(),
        'color' => '#000000',
        'default_entitlement' => 20,
        'allow_request_cancelation' => 1,
    ]);

    $period = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);

    $contract = CRM_Hrjobcontract_BAO_HRJobContract::create([
      'contact_id' => 2, //Existing contact from civicrm_data.mysql,
      'is_primary' => 1
    ]);

    CRM_HRLeaveAndAbsences_BAO_Entitlement::create([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 20,
      'brought_forward_days' => 0,
      'pro_rata' => 0
    ]);

    CRM_HRLeaveAndAbsences_BAO_Entitlement::create([
      'period_id' => $period->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 30,
      'brought_forward_days' => 4,
      'pro_rata' => 0
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   * @expectedExceptionMessage The author of the comment cannot be null
   */
  public function testCommentsShouldHaveAuthor()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::create([
      'comment' => 'Lorem ipsum dolor sit....',
      'comment_updated_at' => date('YmdHis')
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   * @expectedExceptionMessage The date of the comment cannot be null
   */
  public function testCommentsShouldHaveDate()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::create([
      'comment' => 'Lorem ipsum dolor sit....',
      'comment_author_id' => 2
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   * @expectedExceptionMessage The date of the comment should be null if the comment is empty
   */
  public function testEmptyCommentsShouldNotHaveDate()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::create([
      'comment_updated_at' => date('YmdHis')
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   * @expectedExceptionMessage The author of the comment should be null if the comment is empty
   */
  public function testEmptyCommentsShouldNotHaveAuthor()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::create([
      'comment_author_id' => 2
    ]);
  }

  public function testGetContractEntitlementForPeriod()
  {
    $type = CRM_HRLeaveAndAbsences_BAO_AbsenceType::create([
      'title' => 'Type ' . microtime(),
      'color' => '#000000',
      'default_entitlement' => 20,
      'allow_request_cancelation' => 1,
    ]);

    $period1 = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create([
      'title' => 'Period 1',
      'start_date' => date('YmdHis'),
      'end_date' => date('YmdHis', strtotime('+1 day'))
    ]);

    $period2 = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create([
      'title' => 'Period 2',
      'start_date' => date('YmdHis', strtotime('+2 days')),
      'end_date' => date('YmdHis', strtotime('+3 days'))
    ]);

    $contract = CRM_Hrjobcontract_BAO_HRJobContract::create([
      'contact_id' => 2, //Existing contact from civicrm_data.mysql,
      'is_primary' => 1
    ]);

    CRM_HRLeaveAndAbsences_BAO_Entitlement::create([
      'period_id' => $period1->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 20,
      'brought_forward_days' => 0,
      'pro_rata' => 0
    ]);

    CRM_HRLeaveAndAbsences_BAO_Entitlement::create([
      'period_id' => $period2->id,
      'type_id' => $type->id,
      'contract_id' => $contract->id,
      'proposed_entitlement' => 15,
      'brought_forward_days' => 10,
      'pro_rata' => 0
    ]);

    $entitlementPeriod1 = CRM_HRLeaveAndAbsences_BAO_Entitlement::getContractEntitlementForPeriod(
      $contract->id,
      $period1->id,
      $type->id
    );

    $this->assertEquals(20, $entitlementPeriod1->proposed_entitlement);
    $this->assertEquals(0, $entitlementPeriod1->brought_forward_days);
    $this->assertEquals(0, $entitlementPeriod1->pro_rata);

    $entitlementPeriod2 = CRM_HRLeaveAndAbsences_BAO_Entitlement::getContractEntitlementForPeriod(
      $contract->id,
      $period2->id,
      $type->id
    );

    $this->assertEquals(15, $entitlementPeriod2->proposed_entitlement);
    $this->assertEquals(10, $entitlementPeriod2->brought_forward_days);
    $this->assertEquals(0, $entitlementPeriod2->pro_rata);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the Contract ID
   */
  public function testContractIdIsRequiredForGetContractEntitlementForPeriod()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::getContractEntitlementForPeriod(null, 10, 11);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the AbsencePeriod ID
   */
  public function testAbsencePeriodIdIsRequiredForGetContractEntitlementForPeriod()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::getContractEntitlementForPeriod(10, null, 11);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage You must inform the AbsenceType ID
   */
  public function testAbsenceTypeIdIsRequiredForGetContractEntitlementForPeriod()
  {
    CRM_HRLeaveAndAbsences_BAO_Entitlement::getContractEntitlementForPeriod(10, 15, null);
  }
}
