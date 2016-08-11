<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRVisa_ActivityTest
 *
 * @group headless
 */
class api_v3_HRCaseUtils_HRCaseUtilsPipelineTest extends CiviUnitTestCase implements HeadlessInterface , TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    // create a logged in USER since the code references it for source_contact_id
    $this->createLoggedInUser();

    $caseTypes = CRM_Case_PseudoConstant::caseType();
    self::mocktest($caseTypes);
  }

  public function mocktest(&$caseTypes) {

    $paramsAct = array(
      'option_group_id' => 2,
      'version' => 3,
    );

    $resultAct = civicrm_api3('activity_type', 'get', $paramsAct);

    if (in_array("Open Case",$resultAct['values'])) {
      $activityIdd = array_search('Open Case', $resultAct['values']);
      $paramsAct1 = array(
        'option_group_id' => 2,
        'version' => 3,
        'id' => 18,
      );

      $resultAct1 = civicrm_api3('option_value', 'delete', $paramsAct1);
    }

    $import = new CRM_Utils_Migrate_Import();
    $path = __DIR__ .'/Hrdata.xml';
    $dom = new DomDocument();
    $dom->load($path);
    $dom->xinclude();
    $xml = simplexml_import_dom($dom);
    $caseTypeName = $xml->name;

    $proc = new CRM_Case_XMLProcessor();
    $caseTypesGroupId = civicrm_api3('OptionGroup', 'getvalue', array('name' => 'case_type', 'return' => 'id'));
    if (!is_numeric($caseTypesGroupId)) {
      throw new CRM_Core_Exception("Found invalid ID for OptionGroup (case_type)");
    }

    // Create case type with 'Program Hiring Process'
    $paramsCaseType = array(
      'option_group_id' => $caseTypesGroupId,
      'version' => 3,
      'name' => $caseTypeName,
      'label' => $caseTypeName,
    );

    $resultCaseType = civicrm_api3('option_value', 'create', $paramsCaseType);
    $this->casetype_id = $resultCaseType['values'][$resultCaseType['id']]['value'];

    $activitytype = $xml->ActivityTypes->ActivityType;

    foreach ($activitytype as $key => $val) {
      $activitytypename = (array)$val->name ;
      $params = array(
        'weight' => '2',
        'label' => $activitytypename[0],
        'name' => $activitytypename[0],
        'filter' => 0,
        'is_active' => 1,
        'is_optgroup' => 1,
        'is_default' => 0,
      );

      $result = civicrm_api3('activity_type', 'create', $params);
      $this->activityIds[$result['values'][$result['id']]['name']] = $result['values'][$result['id']]['value'];

      //get id of open case activity type
      if ( $activitytypename[0] == "Open Case") {
        $paramsAct = array(
          'option_group_id' => 2,
          'version' => 3,
        );
        $resultAct = civicrm_api3('activity_type', 'get', $paramsAct);
        $this->opencase_activityId = array_search('Open Case', $resultAct['values']);
      }
    }
  }

  public function testPipeline() {

    $contact = $this->callAPISuccess('contact', 'create', array(
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
      'activity_type_id' => $this->opencase_activityId,//'46',
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

  function tearDown() {

  }
}
