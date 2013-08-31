<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviReportTestCase.php';
/**
 *  Test report outcome
 *
 * @package CiviCRM
 */
class CRM_HRReport_Form_Contact_HRDetailTest extends CiviReportTestCase {
  static $_tablesToTruncate = array(
    'civicrm_contact',
    'civicrm_email',
    'civicrm_phone',
    'civicrm_address',
    'civicrm_hrjob',
    'civicrm_hrjob_health',
    'civicrm_hrjob_hour',
    'civicrm_hrjob_leave',
    'civicrm_hrjob_pay',
    'civicrm_hrjob_pension',
    'civicrm_hrjob_role'
  );

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
    parent::setUp();
    $this->foreignKeyChecksOff();
    $this->quickCleanup(self::$_tablesToTruncate);
  }

  function tearDown() {
    parent::tearDown();
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }
    _hrjob_phpunit_populateDB();
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
