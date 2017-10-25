<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 *  Test report outcome
 *
 * @package CiviCRM
 * @group headless
 */
class CRM_HRReport_Form_Activity_HRAbsenceTest extends CiviReportTestCase implements HeadlessInterface , TransactionalInterface {

  public function dataProvider() {
    $cases = array();
    foreach (glob(__DIR__ . '/fixtures/absence-*.reports.php') as $file) {
      $cases = array_merge($cases, include $file);
    }
    return $cases;
  }

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->install('org.civicrm.hrabsence')
      ->install('org.civicrm.hrjobcontract')
      ->apply();
  }

  function setUp() {
    $this->_sethtmlGlobals();
  }

  function tearDown() {
    CRM_Core_DAO::executeQuery('DROP TEMPORARY TABLE IF EXISTS civireport_activity_temp_target');
  }

   protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    return TRUE;
   }

  /**
   * @dataProvider dataProvider
   */
  public function testReportOutput($reportClass, $inputParams, $dataSet, $expectedOutputCsvFile) {
    $config = CRM_Core_Config::singleton();
    CRM_Utils_File::sourceSQLFile($config->dsn, dirname(__FILE__) . "/{$dataSet}");

    $reportCsvFile = $this->getReportOutputAsCsv($reportClass, $inputParams);
    $reportCsvArray = $this->getArrayFromCsv($reportCsvFile);

    $expectedOutputCsvArray = $this->getArrayFromCsv(dirname(__FILE__) . "/{$expectedOutputCsvFile}");
    $this->assertCsvArraysEqual($expectedOutputCsvArray, $reportCsvArray);
  }
}
