<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_HRLeaveAndAbsences_BAO_NotificationReceiverTest extends CiviUnitTestCase {

  private $absenceType = null;

  protected $_tablesToTruncate = [
    'civicrm_hrleaveandabsences_notification_receiver'
  ];

  public function setUp()
  {
    parent::setUp();
    $this->loadAllFixtures();
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

    CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::removeReceiversFromAbsenceType($this->absenceType['id']);

    $receiversIds = CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::getReceiversIDsForAbsenceType(
        $this->absenceType['id']
    );
    $this->assertEmpty($receiversIds);
  }

  private function instantiateAbsenceType()
  {
    $result = $this->callAPISuccess('AbsenceType', 'create', [
        'title' => 'Type ' . microtime(),
        'color' => '#000000',
        'default_entitlement' => 20,
        'allow_request_cancelation' => 1,
    ]);

    $this->absenceType = reset($result['values']);
  }

  private function getContactsIds($limit = 5)
  {
    $result = $this->callAPISuccess('Contact', 'get', [
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
        $this->absenceType['id'], $receiversIdsToAdd
    );

    $receiversIds = CRM_HRLeaveAndAbsences_BAO_NotificationReceiver::getReceiversIDsForAbsenceType(
        $this->absenceType['id']
    );

    $this->assertEquals(count($receiversIdsToAdd), count($receiversIds));
    foreach ($receiversIds as $id) {
      $this->assertContains($id, $receiversIdsToAdd);
    }
  }
}
