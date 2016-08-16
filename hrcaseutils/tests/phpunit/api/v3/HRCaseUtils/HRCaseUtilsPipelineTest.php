<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRVisa_ActivityTest
 *
 * @group headless
 */
class api_v3_HRCaseUtils_HRCaseUtilsPipelineTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface,
  TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    // create a logged in USER since the code references it for source_contact_id
    $params = array(
      'first_name' => 'Logged In',
      'last_name' => 'User ' . rand(),
    );
    $contactID = $this->createContact($params);

    civicrm_api3('UFMatch', 'create', array(
      'sequential' => 1,
      'uf_id' => 6,
      'uf_name' => 'superman',
      'contact_id' => $contactID,
    ));

    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);

    $this->mocktest();

  }

  public function mocktest() {

    $openCaseActivityType = civicrm_api3('OptionValue', 'getsingle', array(
      'sequential' => 1,
      'option_group_id' => "activity_type",
      'name' => "Open Case",
    ));
    $this->opencase_activityId = $openCaseActivityType['value'];


    $hrdataCaseType = civicrm_api3('CaseType', 'getsingle', array(
      'sequential' => 1,
      'name' => "Hrdata",
    ));

    $this->casetype_id = $hrdataCaseType['id'];

    // build Name => ID array for used activity types
    $result = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'return' => array("value", "name"),
      'option_group_id' => "activity_type",
      'name' => array('IN' => array("Background Check", "Phone Call", "Interview Prospect")),
    ));
    $activities = $result['values'];
    foreach ($activities as $activity)  {
      $this->activityIds[$activity['name']] = $activity['value'];
    }

  }

  public function testPipeline() {

    $contact = civicrm_api3('contact', 'create', array(
      'first_name' => 'JohnHR',
      'contact_type' => 'Individual',
      'last_name' => 'Smith',
      'email' => 'johnhrsmith@hrtest.com')
    );

    // create case
    $newParams = array(
      'case_type_id' => $this->casetype_id,
      'creator_id' =>$contact['id'],
      'status_id' => 1,
      'subject' => 'Case for HR Unit Test',
      'contact_id' =>$contact['id'],
      'version' => 3,
      'label' => 'test',
      'name' => 'test',
    );
    // assign case to contact
    $caseBAO = CRM_Case_BAO_Case::create($newParams);
    $newParams['id'] = $caseBAO->id;
    $caseContact = new CRM_Case_DAO_CaseContact();
    $caseContact->case_id = $caseBAO->id;
    $caseContact->contact_id = $contact['id'];
    $caseContact->find(TRUE);
    $caseContact->save();

    // create activity
    $paramsActivity = array(
      'source_contact_id' =>$contact['id'],
      'activity_type_id' => $this->opencase_activityId,
      'subject' => 'Open Case',
      'activity_date_time' => '2011-06-02 14:36:13',
      'status_id' => 1,
      'priority_id' => 2,
      'duration' => 120,
      'location' => 'Pensulvania',
      'details' => 'A test activity',
      'case_id' => $caseBAO->id,
      'is_current_revision' => 1,
    );
    $resultActivity = civicrm_api3('activity', 'create', $paramsActivity);

    $opencase_activityIds = $resultActivity['id'];
    $analyzer = new CRM_HRCaseUtils_Analyzer($caseBAO->id,$resultActivity['id']);
    $analyzer->case_id = $caseBAO->id;
    $caseStatus = $caseBAO->status_id;
    $activityStatus = $resultActivity['values'][$resultActivity['id']]['status_id'];
    $activityTypeId = $resultActivity['values'][$resultActivity['id']]['activity_type_id'];

    $this->assertEquals('1', $caseStatus); // Check case stauts id
    $activityCheck = $analyzer->getSingleActivity($activityTypeId);

    $this->assertEquals('1', $activityCheck[1]['status_id']); // Check activity stauts id
    $this->assertFalse($analyzer->hasActivity('Interview Prospect'));
    $this->assertFalse($analyzer->hasActivity('Background Check'));


    $paramsActivity = array(
      'id' => $opencase_activityIds,
      'source_contact_id' =>$contact['id'],
      'activity_type_id' => $this->opencase_activityId,
      'subject' => 'Open Case',
      'activity_date_time' => '2011-06-02 14:36:13',
      'status_id' => 2,
      'priority_id' => 2,
      'duration' => 120,
      'location' => 'Pensulvania',
      'details' => 'A test activity',
      'case_id' => $caseBAO->id,
      'is_current_revision' => 1,
      'original_id' => $opencase_activityIds,
    );
    $resultActivity = civicrm_api3('activity', 'create', $paramsActivity);

    $paramsActivityGet = array(
      'activity_type_id' => $this->activityIds['Interview Prospect'],
      'version' => 3,
    );
    $resultActivityGet = civicrm_api3('activity', 'get', $paramsActivityGet);

    $analyzer = new CRM_HRCaseUtils_Analyzer($caseBAO->id, $resultActivityGet['values'][$resultActivityGet['id']]['id']);
    $ActivityIP = $analyzer->getSingleActivity($this->activityIds['Interview Prospect']);

    $this->assertEquals('2', $resultActivity['values'][$resultActivity['id']]['status_id']);
    $this->assertEquals('1', $ActivityIP[1]['status_id']);
    $this->assertFalse($analyzer->hasActivity('Background Check'));

    // get second activity
    $params = array(
      'activity_type_id' => $this->activityIds['Interview Prospect'],
      'sequential' => 1,
      'version' => 3,
    );
    $result = civicrm_api3('activity', 'get', $params);

    $paramsActivity2 = array(
     'id' => $result['id'],
     'status_id' => 'Completed',
     'version' => 3,
     'case_id' => $caseBAO->id,
     'activity_type_id' => $this->activityIds['Interview Prospect'],
     'original_id' => $result['id'],
    );
    $resultActivity2 = civicrm_api3('activity', 'create', $paramsActivity2);

    $paramsActivityGet1 = array(
      'activity_type_id' => $this->activityIds['Background Check'],
      'version' => 3,
    );

    $resultActivityGet1 = civicrm_api3('activity', 'get', $paramsActivityGet1);

    $analyzer = new CRM_HRCaseUtils_Analyzer($caseBAO->id, $resultActivityGet1['values'][$resultActivityGet1['id']]['id']);
    $ActivityIP1 = $analyzer->getSingleActivity($this->activityIds['Background Check']);
    $this->assertEquals('2', $resultActivity2['values'][$resultActivity2['id']]['status_id']);
    $this->assertEquals('1', $ActivityIP1[1]['status_id']);

    // get second activity
    $params = array(
      'activity_type_id' => $this->activityIds['Background Check'],
      'sequential' => 1,
      'version' => 3,
    );
    $result = civicrm_api3('activity', 'get', $params);

    $paramsActivity3 = array(
      'id' => $result['id'],
      'status_id' => 'Completed',
      'version' => 3,
      'case_id' => $caseBAO->id,
      'activity_type_id' => $this->activityIds['Background Check'],
      'original_id' => $result['id'],
    );
    $resultActivity3 = civicrm_api3('activity', 'create', $paramsActivity3);

    $this->assertEquals('2', $resultActivity3['values'][$resultActivity3['id']]['status_id']);
  }

  /**
   * Creates single (Individuals) contact from the provided data.
   *
   * @param array $params should contain first_name and last_name
   * @return int return the contact ID
   * @throws \CiviCRM_API3_Exception
   */
  private function createContact($params) {
    $result = civicrm_api3('Contact', 'create', array(
      'contact_type' => "Individual",
      'first_name' => $params['first_name'],
      'last_name' => $params['last_name'],
      'display_name' => $params['first_name'] . ' ' . $params['last_name'],
    ));
    return $result['id'];
  }

}
