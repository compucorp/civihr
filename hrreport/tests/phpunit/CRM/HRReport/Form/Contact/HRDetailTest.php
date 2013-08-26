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
    $testCase1 = array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'manager',
            'level_type',
            'state_province_id',
            'country_id',
          ),
        ),
        'fixtures/dataset-detail.sql',
        'fixtures/report-detail.csv',
    );

    //testCase with hrjob_pay filters
    $testCase2 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'pay_grade',
            'pay_amount',
            'pay_unit',
          ),
          'filters' => array(
            'pay_grade_op' => 'in',
            'pay_grade_value' => 'paid',
            'pay_amount_op' => 'gt',
            'pay_amount_value' => 90,
            'pay_unit_op' => 'notin',
            'pay_unit_value' => 'Year,Week',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase2.csv',
    );

    //testCase with hrjob_pension filters
    $testCase3 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'ee_contrib_pct',
            'er_contrib_pct',
          ),
          'filters' => array(
            'is_enrolled_op' => 'eq',
            'is_enrolled_value' => 1,
            'ee_contrib_pct_op' => 'lte',
            'ee_contrib_pct_value' => 200,
            'er_contrib_pct_op' => 'lte',
            'er_contrib_pct_value' => 100,
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase3.csv',
    );

    //testCase with hrjob title, level type and period type filters
    $testCase4 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'contract_type',
            'level_type',
            'period_type',
            'location',
          ),
          'filters' => array(
            'title_op' => 'like',
            'title_value' => 'Manager2',
            'level_type_op' => 'in',
            'level_type_value' => "Senior Manager",
            'period_type_op' => 'in',
            'period_type_value' => 'Temporary',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase4.csv',
    );

    //testCase with hrjob contract type and period type filters
    $testCase5 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'contract_type',
            'level_type',
            'period_type',
            'location',
          ),
          'filters' => array(
            'contract_type_op' => 'in',
            'contract_type_value' => 'Apprentice',
            'period_type_op' => 'in',
            'period_type_value' => 'Temporary,Permanent',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase5.csv',
    );

    //testCase with hrjob isTiedToFunding,level type and contract type filters with "in" operator
    $testCase6 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'contract_type',
            'level_type',
            'period_type',
            'location',
          ),
          'filters' => array(
            'is_tied_to_funding_op' => 'eq',
            'is_tied_to_funding_value' => 1,
            'level_type_op' => 'in',
            'level_type_value' => "Senior Manager,Junior Manager,Junior Staff",
            'contract_type_op' => 'in',
            'contract_type_value' => 'Employee,Volunteer,Contractor',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase6.csv',
    );

    //testCase with hrjob isTiedToFunding,level type and contract type filters with "notin" operator
    $testCase7 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'contract_type',
            'level_type',
            'period_type',
            'location',
          ),
          'filters' => array(
            'is_tied_to_funding_op' => 'eq',
            'is_tied_to_funding_value' => 1,
            'level_type_op' => 'notin',
            'level_type_value' => "Senior Staff",
            'contract_type_op' => 'notin',
            'contract_type_value' => 'Apprentice,Intern,Trustee',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase6.csv',
    );

    //testCase with combination of hrjob health and hrjob filters
    $testCase8 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'contract_type',
            'level_type',
            'period_type',
            'location',
            'provider',
            'plan_type',
          ),
          'filters' => array(
            'level_type_op' => 'in',
            'level_type_value' => "Senior Manager,Junior Manager",
            'contract_type_op' => 'in',
            'contract_type_value' => 'Employee,Contractor',
            'provider_op' => 'in',
            'provider_value' => 'Unknown',
            'plan_type_op' => 'in',
            'plan_type_value' => 'Individual,Family',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase8.csv',
    );

     //testCase with hrjob hours filters
    $testCase9 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'contract_type',
            'level_type',
            'period_type',
            'location',
            'hours_type',
            'hours_unit',
          ),
          'filters' => array(
            'hours_type_op' => 'in',
            'hours_type_value' => 'part,full',
            'hours_amount_op' => 'gte',
            'hours_amount_value' => 15,
            'hours_unit_op' => 'notin',
            'hours_unit_value' => 'Month,Year',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase9.csv',
    );

    //some variation to testCase9
    $testCase10 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'contract_type',
            'level_type',
            'period_type',
            'location',
            'hours_type',
            'hours_unit',
          ),
          'filters' => array(
            'hours_type_op' => 'notin',
            'hours_type_value' => 'casual',
            'hours_amount_op' => 'gte',
            'hours_amount_value' => 15,
            'hours_unit_op' => 'in',
            'hours_unit_value' => 'Day,Week',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase9.csv',
    );

     //some variation to testCase8
    $testCase11 =  array(
      'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => array(
            'sort_name',
            'email',
            'position',
            'title',
            'contract_type',
            'level_type',
            'period_type',
            'location',
            'provider',
            'plan_type',
          ),
          'filters' => array(
            'level_type_op' => 'notin',
            'level_type_value' => "Senior Staff,Junior Staff",
            'contract_type_op' => 'notin',
            'contract_type_value' => 'Intern,Trustee,Apprentice,Volunteer',
            'provider_op' => 'in',
            'provider_value' => 'Unknown',
            'plan_type_op' => 'in',
            'plan_type_value' => 'Individual,Family',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/testCase8.csv',
    );

    return array(
      $testCase1,
      $testCase2,
      $testCase3,
      $testCase4,
      $testCase5,
      $testCase6,
      $testCase7,
      $testCase8,
      $testCase9,
      $testCase10,
      $testCase11,
    );
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
    $fields = array(
      'id',
      'sort_name',
      'email',
      'position',
      'title',
      'contract_type',
      'level_type',
      'period_type',
      'location',
      'provider',
      'plan_type',
    );

    $contact_211_1_filters = include __DIR__ . '/fixtures/singleFilter-contact-211.php';
    foreach ($contact_211_1_filters as $contact_211_1_filter) {
      $cases[] = array(
        'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => $fields,
          'filters' => $contact_211_1_filter,
        ),
        'fixtures/singleFilter-dataset.sql',
        'fixtures/singleFilter-contact-211.csv',
      );
    }

    $contact_213_6_filters = include __DIR__ . '/fixtures/singleFilter-contact-211.php';
    foreach ($contact_213_6_filters as $contact_213_6_filter) {
      $cases[] = array(
        'CRM_HRReport_Form_Contact_HRDetail',
        array(
          'fields' => $fields,
          'filters' => $contact_213_6_filter,
        ),
        'fixtures/singleFilter-dataset.sql',
        'fixtures/singleFilter-contact-213.csv',
      );
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
