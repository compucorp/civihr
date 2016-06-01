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
}
