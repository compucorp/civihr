<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_HRVisa_ActivityTest
 *
 * @group headless
 */
class CRM_HRVisa_ActivityTest extends PHPUnit_Framework_TestCase implements HeadlessInterface , TransactionalInterface {

  protected $customFields;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      // hrdemog is necessary because it creates the Immigration fields used by the tests
      ->install('org.civicrm.hrdemog')
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->apply();
  }

  public function setUp() {
    // call after parent invocation as fields populated in parent
    $customFields = array(
      'Extended_Demographics:Is_Visa_Required' => 'Is_Visa_Required',
      'Immigration:Visa_Type' => 'Visa_Type',
      'Immigration:Start_Date' => 'Start_Date',
      'Immigration:End_Date' => 'End_Date',
      'Immigration:Visa_Number' => 'Visa_Number'
    );
    foreach ($customFields as $name => $storeValInHere) {
      $customFields[$name] = "custom_" . CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', $storeValInHere, 'id', 'name');
    }
    $this->customFields = $customFields;
    // create a logged in USER since the code references it for source_contact_id
    $this->createLoggedInUser();
  }

  /**
   * CASE 1 : is_visa_required = TRUE, and 2 migration records,
   * activity of type 'Visa Expiration' created with target contact as
   * the one whose record is being edited.
   */
  public function testSyncScenario1() {
    // create a test individual
    $cid = ContactFabricator::fabricate()['id'];
    // is visa required = 1
    $caseOneStartDate = date('YmdHis');
    $caseOneEndDate = date('YmdHis', strtotime('+1 year'));
    $caseOneEndDate2 = date('YmdHis', strtotime('+6 month'));
    $caseOneParams = array(
      'entity_id' => $cid,
      $this->customFields['Extended_Demographics:Is_Visa_Required']  => 1,
      "{$this->customFields['Immigration:Visa_Type']}:-1" => 'B-1',
      "{$this->customFields['Immigration:Start_Date']}:-1" => $caseOneStartDate,
      "{$this->customFields['Immigration:End_Date']}:-1" => $caseOneEndDate,
      "{$this->customFields['Immigration:Visa_Number']}:-1" => '4111111111111111',
      "{$this->customFields['Immigration:Visa_Type']}:-2" => 'B-1',
      "{$this->customFields['Immigration:Start_Date']}:-2" => $caseOneStartDate,
      "{$this->customFields['Immigration:End_Date']}:-2" => $caseOneEndDate2,
      "{$this->customFields['Immigration:Visa_Number']}:-2" => '4111111111111111'
    );
    civicrm_api3('custom_value', 'create', $caseOneParams);
    // sync activity with contact of above details
    CRM_HRVisa_Activity::sync($cid);

    // calling a common function for getting activity a particular target contact and activity type
    // this will return activity id and number of activities found
    list($count, $activityId) = self::getTargetContactActivity($cid);
    $caseOneActivityGetParams = array('id' => $activityId);
    $caseOneActivity = civicrm_api3('activity', 'get', $caseOneActivityGetParams);

    $this->assertEquals(1, $count);
    $this->assertEquals($caseOneEndDate2,
      date('YmdHis', strtotime($caseOneActivity['values'][$caseOneActivity['id']]['activity_date_time'])),
      "Activity date time not set to latest Immigration visa end date for 'Visa Expiration' activity (in line " . __LINE__ . ")");

  }

  /**
   * CASE 2 : is_visa_required = TRUE, one migration record.
   * later is_visa_required = FALSE,
   * activity status set to 'Cancelled' from 'Scheduled'
   */
  public function testSyncScenario2() {
    // create a test individual
    $cid = ContactFabricator::fabricate()['id'];
    $startDate = date('YmdHis');
    $endDate = date('YmdHis', strtotime('+1 year'));
    $params = array(
      'entity_id' => $cid,
      $this->customFields['Extended_Demographics:Is_Visa_Required']  => 1,
      "{$this->customFields['Immigration:Visa_Type']}:-1" => 'B-1',
      "{$this->customFields['Immigration:Start_Date']}:-1" => $startDate,
      "{$this->customFields['Immigration:End_Date']}:-1" => $endDate,
      "{$this->customFields['Immigration:Visa_Number']}:-1" => '4111111111111111',
    );
    civicrm_api3('custom_value', 'create', $params);
    // sync activity with contact of above details
    CRM_HRVisa_Activity::sync($cid);

    // calling a common function for getting activity a particular target contact and activity type
    // this will return activity id and number of activities found
    list($count, $activityId) = self::getTargetContactActivity($cid);
    $activityGetParams = array('id' => $activityId);
    $activity = civicrm_api3('activity', 'get', $activityGetParams);

    $this->assertEquals(1, $count);
    $this->assertEquals($endDate,
      date('YmdHis', strtotime($activity['values'][$activity['id']]['activity_date_time'])),
      "Activity date time not set to latest Immigration visa end date for 'Visa Expiration' activity (in line " . __LINE__ . ")");

    $this->assertEquals(CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'), $activity['values'][$activity['id']]['status_id'], 'in line ' . __LINE__ . ' Status of \'Visa Expiration\' activity should be \'Scheduled\' but wrongly is ' . $activity['values'][$activity['id']]['status_id']);

    // now mark the field visa required to false
    $params = array(
      'entity_id' => $cid,
      $this->customFields['Extended_Demographics:Is_Visa_Required']  => 0,
    );
    civicrm_api3('custom_value', 'create', $params);

    // sync activity with contact of above details
    CRM_HRVisa_Activity::sync($cid);

    $activityGetParams = array('id' => $activityId);
    $activity = civicrm_api3('activity', 'get', $activityGetParams);

    $this->assertEquals(CRM_Core_OptionGroup::getValue('activity_status', 'Cancelled', 'name'), $activity['values'][$activity['id']]['status_id'], 'in line ' . __LINE__ . ' Status of \'Visa Expiration\' activity should be \'Cancelled\' but wrongly is ' . $activity['values'][$activity['id']]['status_id']);
  }

  private function getTargetContactActivity($contactId) {
    $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Visa Expiration', 'name');
    // to check if visa expiration activity exists for the input target_contact_id
    $activityGetParams = array(
      'contact_id' => $contactId,
      'activity_type_id' => $activityTypeId,
      'sequential' => 1,
    );
    $activities = civicrm_api3('activity', 'get', $activityGetParams);

    $activityId = NULL;
    $count = 0;
    foreach($activities['values'] as $val) {
      $activityId = $val['id'];
      $count++;
    }

    return array($count, $activityId);
  }

  private function createLoggedInUser() {
    $params = [
      'first_name' => 'Logged In',
      'last_name' => 'User ' . rand(),
      'contact_type' => 'Individual',
    ];
    $contact = ContactFabricator::fabricate($params);
    civicrm_api3('UFMatch', 'create', [
      'contact_id' => $contact['id'],
      'uf_name' => 'superman',
      'uf_id' => 6,
    ]);

    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contact['id']);
  }
}
