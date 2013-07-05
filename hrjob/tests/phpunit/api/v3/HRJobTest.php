<?php

require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * FIXME
 */
class api_v3_HRJobTest extends CiviUnitTestCase {
  function setUp() {
    // If your test manipulates any SQL tables, then you should truncate
    // them to ensure a consisting starting point for all tests
    // $this->quickCleanup(array('example_table_name'));
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }

    $import = new CRM_Utils_Migrate_Import();
    $import->run(
      CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrjob')
        . '/xml/option_group_install.xml'
    );

    return TRUE;
  }

  /**
   * Create a job and several subordinate entities using API chaining
   */
  function testCreateChained() {
    $params = array(
      'version' => 3,
      'contract_type' => 'Volunteer',
      'api.HRJobPay.create' => array(
        'pay_amount' => 20,
      ),
    );
    $result = civicrm_api('HRJob', 'create', $params);
    $this->assertAPISuccess($result);
    foreach ($result['values'] as $hrJobResult) {
      $this->assertEquals('Volunteer', $hrJobResult['contract_type']);

      $this->assertAPISuccess($hrJobResult['api.HRJobPay.create']);
      foreach ($hrJobResult['api.HRJobPay.create']['values'] as $hrJobPayResult) {
        $this->assertEquals(20, $hrJobPayResult['pay_amount']);
      }
    }
  }
}
