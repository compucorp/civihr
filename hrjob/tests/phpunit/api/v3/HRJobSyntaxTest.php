<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/SyntaxConformanceTest.php';
/**
 * apiTest APIv3 civicrm_hrjob_* functions
 *
 *  @package CiviCRM_APIv3
 *  @subpackage API_HRJob
 */
class api_v3_HRJobSyntaxTest extends api_v3_SyntaxConformanceTest {

  function setUp() {
    parent::setUp();
    $this->extensionEntitiesWithoutUpdate = array('HRJobHealth', 'HRJobHour', 'HRJobLeave', 'HRJobPay', 'HRJobPension');
  }

  function tearDown() {
    parent::tearDown(); 
    $this->quickCleanup(array(
      'civicrm_hrjob',
      'civicrm_hrjob_health',
      'civicrm_hrjob_hour',
      'civicrm_hrjob_leave',
      'civicrm_hrjob_pay',
      'civicrm_hrjob_pension',
      'civicrm_hrjob_role',
    ));
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
}