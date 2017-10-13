<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_NotificationReceiverTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_NotificationReceiverTest extends BaseHeadlessTest {

  private $absenceType = null;

  public function setUp()
  {
    parent::setUp();
    $this->instantiateAbsenceType();
  }

  public function tearDown()
  {
    parent::tearDown();
  }

  public function testShouldAddReceiversToAbsenceType()
  {
    $this->addAndRetrieveReceiversForAbsenceType();
  }

  public function testShouldDeleteReceiversForAbsenceType()
  {
    $this->addAndRetrieveReceiversForAbsenceType();

    CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::removeReceiversFromAbsenceType($this->absenceType->id);

    $receiversIds = CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::getReceiversIDsForAbsenceType(
        $this->absenceType->id
    );
    $this->assertEmpty($receiversIds);
  }

  private function instantiateAbsenceType()
  {
    $this->absenceType = AbsenceType::create([
        'title' => 'Type ' . microtime(),
        'color' => '#000000',
        'default_entitlement' => 20,
        'allow_request_cancelation' => 1,
    ]);
  }

  private function getContactsIds($limit = 5)
  {
    $result = civicrm_api3('Contact', 'get', [
      'return' => 'id',
      'options' => ['limit' => $limit]
    ]);

    if($result['is_error'] == 0) {
      return array_keys($result['values']);
    }

    return [];
  }

  private function addAndRetrieveReceiversForAbsenceType()
  {
    $receiversIdsToAdd = $this->getContactsIds();
    $this->assertNotEmpty($receiversIdsToAdd);

    CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::addReceiversToAbsenceType(
        $this->absenceType->id, $receiversIdsToAdd
    );

    $receiversIds = CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::getReceiversIDsForAbsenceType(
        $this->absenceType->id
    );

    $this->assertEquals(count($receiversIdsToAdd), count($receiversIds));
    foreach ($receiversIds as $id) {
      $this->assertContains($id, $receiversIdsToAdd);
    }
  }
}
