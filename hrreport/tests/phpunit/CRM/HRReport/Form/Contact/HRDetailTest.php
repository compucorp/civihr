<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 *  Test report outcome
 *
 * @package CiviCRM
 * @group headless
 */
class CRM_HRReport_Form_Contact_HRDetailTest extends CiviReportTestCase implements HeadlessInterface , TransactionalInterface  {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->install('org.civicrm.hrabsence')
      ->install('org.civicrm.hrjobcontract')
      ->apply();
  }

  public function dataProvider() {
    $cases = array();
    foreach (glob(__DIR__ . '/fixtures/detail-*.reports.php') as $file) {
      $cases = array_merge($cases, include $file);
    }
    return $cases;
  }

  /**
   * The single-filter test cases ensure that each filter works correctly when used
   * individually.
   *
   * To reduce clutter/duplication, we pick two contacts/jobs as our "targets"
   * (contacts 211 and 213). For a given field like "title", we write one query
   * which matches contact 211 based on title; then we write a second query which
   * matches contact 213 based on title.
   *
   * In the end, we have a list of filters which match contact 211 (singleFilter-contact-211.php)
   * and an example output file displaying contact 211 (singleFilter-contact-211.csv). Similarly,
   * there's a list of filters and an example output for contact 213 (singleFilter-contact-213.php
   * and singlefilter-contact-213.csv).
   */
  function singleFilterTestCases() {
    $cases = array();
    foreach (glob(__DIR__ . '/fixtures/singleFilter-*.reports.php') as $file) {
      $cases = array_merge($cases, include $file);
    }
    return $cases;
  }

  function setUp() {
    $this->_sethtmlGlobals();
  }

  function tearDown() {
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }

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

  /**
   * @dataProvider singleFilterTestCases
   */
  public function testSingleFilters($reportClass, $inputParams, $dataSet, $expectedOutputCsvFile) {
    $config = CRM_Core_Config::singleton();
    CRM_Utils_File::sourceSQLFile($config->dsn, dirname(__FILE__) . "/{$dataSet}");

    $reportCsvFile = $this->getReportOutputAsCsv($reportClass, $inputParams);
    $reportCsvArray = $this->getArrayFromCsv($reportCsvFile);

    $expectedOutputCsvArray = $this->getArrayFromCsv(dirname(__FILE__) . "/{$expectedOutputCsvFile}");
    $this->assertCsvArraysEqual($expectedOutputCsvArray, $reportCsvArray);
  }

}
