<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class api_v3_HRVacancyTest
 *
 * @group headless
 */
class api_v3_HRVacancyStageTest extends CiviUnitTestCase implements HeadlessInterface , TransactionalInterface {
  protected $_apiversion = 3;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  function setUp() {
    $this->_entity = 'HRVacancy';

    parent::setUp();
    $this->_apiversion = 3;
    $this->createLoggedInUser();
    $session = CRM_Core_Session::singleton();
    $this->_loggedInUser = $session->get('userID');

    //Create Vacancy
    $vacancyParams = array(
      'position' => 'Test',
      'is_template' => '0',
      'start_date' => '2014-05-08 00:00:00',
      'end_date' => '2014-05-27 00:00:00',
      'status_id' => 'Draft',
      'created_id' => $this->_loggedInUser,
    );

    $vacancyResult = $this->callAPISuccess('HRVacancy', 'create', $vacancyParams);
    //vacancy stage params
    $this->_params = array(
      'case_status_id' => '1',
      'vacancy_id' => $vacancyResult['id'],
      'weight' => '1',
    );
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }
    //populate vacancy_status of type Application
    $result = civicrm_api3('OptionGroup', 'create', array(
                'name' => 'vacancy_status',
                'title' => ts('Vacancy Status'),
                'is_reserved' => 1,
                'is_active' => 1,
              )
    );
    $vacancyStatus = array(
      'Draft' => ts('Draft'),
      'Open' => ts('Open'),
      'Closed' => ts('Closed'),
      'Cancelled' => ts('Cancelled'),
      'Rejected' => ts('Rejected')
    );
    $weight = 1;
    foreach ($vacancyStatus as $name => $label) {
      $statusParam = array(
        'option_group_id' => $result['id'],
        'label' => $label,
        'name' => $name,
        'value' => $weight++,
        'is_active' => 1,
      );
      if ($name == 'Draft') {
        $statusParam['is_default'] = 1;
      }
      elseif ($name == 'Open') {
        $statusParam['is_reserved'] = 1;
      }
      civicrm_api3('OptionValue', 'create', $statusParam);
    }

    $import = new CRM_Utils_Migrate_Import();
    $import->run(
      CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrrecruitment')
      . '/xml/auto_install.xml'
    );
    return TRUE;
  }

  function tearDown() {
  }

  /**
   * test create methods with valid data
   * success expected
   */
  function testVacancyStageCreate() {
    // Create VacancyStage
    $result = $this->callAPISuccess('HRVacancyStage', 'create', $this->_params);
    $id = $result['id'];

    // Check result
    $result = $this->callAPISuccess('HRVacancyStage', 'get', array('id' => $id));
    $this->assertEquals($result['values'][$id]['id'], $id, 'in line ' . __LINE__);
    $this->assertEquals($result['values'][$id]['vacancy_id'], $this->_params['vacancy_id'], 'in line ' . __LINE__);
  }

  /**
   * Test update (create with id) function with valid parameters
   */
  function testVacancyStageUpdate() {
    // Create VacancyStage
    $result = $this->callAPISuccess('HRVacancyStage', 'create', $this->_params);
    $id = $result['id'];

    $result = $this->callAPISuccess('HRVacancyStage', 'get', array('id' => $id));
    $vacancy = $result['values'][$id];

    // Update VacancyStage
    $params = array('id' => $id);
    $params['case_status_id'] = $vacancy['case_status_id'] = '2';
    $this->callAPISuccess('HRVacancyStage', 'create', $params);

    // Verify that updated VacancyStage is exactly equal to the original with new case_status_id
    $result = $this->callAPISuccess('HRVacancyStage', 'get', array('id' => $id));
    $this->assertEquals($result['values'][$id], $vacancy, 'in line ' . __LINE__);
  }

  /**
   * Test delete function with valid parameters
   */
  function testVacancyStageDelete() {
    // Create VacancyStage
    $result = $this->callAPISuccess('HRVacancyStage', 'create', $this->_params);
    $id = $result['id'];
    $this->callAPISuccess('HRVacancyStage', 'delete', array('id' => $id));

    // Check result - vacancy should no longer exist
    $result = $this->callAPISuccess('HRVacancyStage', 'get', array('id' => $id));
    $this->assertEquals(0, $result['count']);
  }
}


