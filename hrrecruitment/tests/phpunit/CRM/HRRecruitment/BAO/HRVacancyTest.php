<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRRecruitment_BAO_HRVacancyTest
 *
 * @group headless
 */
class CRM_HRRecruitment_BAO_HRVacancyTest extends CiviUnitTestCase implements HeadlessInterface , TransactionalInterface {

  use HRRecruitmentTestTrait;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  function setUp() {
  }

  function teardown() {
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }
    self::phpunitPopulateDB();
    return TRUE;
  }

  /**
   * create() method (create and update modes)
   */
  function testCreateGet() {

    $params1 = array('first_name' => 'micky', 'last_name' => 'mouse');
    $permissionIndividual1Id = $this->createContact($params1);
    $params2 = array('first_name' => 'john', 'last_name' => 'snow');
    $permissionIndividual2Id = $this->createContact($params2);

    $stages = array_keys(CRM_Core_OptionGroup::values('case_status', FALSE, FALSE, FALSE, " AND grouping = 'Vacancy'"));

    $params = array(
      'position' => 'Senior Support Specialist',
      'location' => 'Headquaters',
      'salary' => '$110-$130k/yr',
      'description' => 'Answer phone calls and emails from irate customers.',
      'benefits' => 'Have a place to park',
      'requirements' => 'Pro-actively looks to build cross discipline experience and increase knowledge.',
      'stages' => $stages,
      'application_profile' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'application_profile', 'id', 'name'),
      'evaluation_profile' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'evaluation_profile', 'id', 'name'),
      'status_id' => '4',
      'start_date' => '20140425151100',
      'end_date' => '20140426231100',
      'permission' => array('manage Applicants', 'administer Vacancy'),
      'permission_contact_id' => array($permissionIndividual1Id, $permissionIndividual2Id),
    );

    $vacancy = CRM_HRRecruitment_BAO_HRVacancy::create($params);
    $getValues = array();
    $getParams = array('id' => $vacancy->id);

    CRM_HRRecruitment_BAO_HRVacancy::retrieve($getParams, $getValues);
    //stage array index always starts with 1 so in order to make changes in
    //$getValues['stages'] in order to just match the value
    $getValues['stages'] = array_values($getValues['stages']);
    $getValues['permission'] = array_values($getValues['permission']);
    $getValues['permission_contact_id'] = array_values($getValues['permission_contact_id']);

    //process date back to mysql format
    $getValues['start_date'] = CRM_Utils_Date::isoToMysql($getValues['start_date']);
    $getValues['end_date'] = CRM_Utils_Date::isoToMysql($getValues['end_date']);

    foreach ($params as $column => $value) {
      $this->assertEquals($params[$column], $getValues[$column], "Check for Job $column.");
    }
  }

}


