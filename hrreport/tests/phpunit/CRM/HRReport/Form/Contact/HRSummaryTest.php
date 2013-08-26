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
class CRM_HRReport_Form_Contact_HRSummaryTest extends CiviReportTestCase {
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
    //testcase for CiviHR Contact Summary Report
    $testCase1 = array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'id',
            'state_province_id',
            'job_positions',
           ),
          'group_bys' => array(
            'state_province_id',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-bystate.csv',
    );

    //testcase for CiviHR Full Time Equivalents Report 
    $testCase2 = array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'title',
            'level_type',
            'fte',
          ),
          'group_bys' => array(
            'title',
            'level_type',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-fte.csv',
    );

    //testcase for CiviHR Annual and Monthly Cost Equivalents Report 
    $testCase3 =  array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'level_type',
            'period_type',
            'location',
            'monthly_cost_eq',
            'annual_cost_eq',
          ),
          'group_bys' => array(
            'level_type',
            'location',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-annual-monthly-equiv.csv',
    );

    //testcase for CiviHR Contact Summary Report using "in" operator for filters
    $testCase4 = array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'id',
            'state_province_id',
            'contract_type',
            'level_type',
            'period_type',
            'location',
            'job_positions',
           ),
          'group_bys' => array(
            'state_province_id',
          ),
          'filters' => array(
            'is_tied_to_funding_op' => 'eq',
            'is_tied_to_funding_value' => 1,
            'contract_type_op' => 'in',
            'contract_type_value' => 'Apprentice,Employee,Volunteer',
            'level_type_op' => 'in',
            'level_type_value' => 'Senior Manager,Junior Manager,Senior Staff',
            'period_type_op' => 'in',
            'period_type_value' => 'Permanent',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-filter-testCase1.csv',
    );

    //testcase for CiviHR Contact Summary Report using "notin" operator for filters
    $testCase5 = array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'id',
            'state_province_id',
            'contract_type',
            'level_type',
            'period_type',
            'location',
            'job_positions',
           ),
          'group_bys' => array(
            'state_province_id',
          ),
          'filters' => array(
            'is_tied_to_funding_op' => 'eq',
            'is_tied_to_funding_value' => 1,
            'contract_type_op' => 'notin',
            'contract_type_value' => 'Intern,Trustee,Contractor',
            'level_type_op' => 'notin',
            'level_type_value' => 'Junior Staff',
            'period_type_op' => 'notin',
            'period_type_value' => 'Temporary',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-filter-testCase1.csv',
    );

    //testcase for CiviHR Contact fte Report with some filters using "in" operator for level type
    $testCase6= array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'title',
            'level_type',
            'fte',
           ),
          'group_bys' => array(
            'title',
            'level_type',
          ),
          'filters' => array(
            'title_op' => 'like',
            'title_value' => 'Manager2',
            'level_type_op' => 'in',
            'level_type_value' => 'Senior Manager,Junior Staff,Senior Staff', 
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-filter-testCase2.csv',
    );

    //testcase for CiviHR Contact fte Report with some filters using "notin" operator for level type
    $testCase7 = array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'title',
            'level_type',
            'fte',
           ),
          'group_bys' => array(
            'title',
            'level_type',
          ),
          'filters' => array(
            'title_op' => 'like',
            'title_value' => 'Manager2',
            'level_type_op' => 'notin',
            'level_type_value' => 'Junior Manager', 
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-filter-testCase2.csv',
    );

    //testcase for CiviHR Annual and Monthly Cost Equivalents Report with "in" operator for level type and "notin" operator for period type filters
    $testCase8 =  array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'level_type',
            'period_type',
            'location',
            'monthly_cost_eq',
            'annual_cost_eq',
          ),
          'group_bys' => array(
            'level_type',
            'location',
          ),
          'filters' => array(
            'level_type_op' => 'in',
            'level_type_value' => 'Senior Manager,Junior Manager,Senior Staff',
            'period_type_op' => 'notin',
            'period_type_value' => 'Temporary',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-filter-testCase3.csv',
    );

     //testcase for CiviHR Annual and Monthly Cost Equivalents Report with "in" operator for period type and "notin" operator for level type filters
    $testCase9 =  array(
      'CRM_HRReport_Form_Contact_HRSummary',
        array(
          'fields' => array(
            'level_type',
            'period_type',
            'location',
            'monthly_cost_eq',
            'annual_cost_eq',
          ),
          'group_bys' => array(
            'level_type',
            'location',
          ),
          'filters' => array(
            'level_type_op' => 'notin',
            'level_type_value' => 'Junior Staff',
            'period_type_op' => 'in',
            'period_type_value' => 'Permanent',
          ),
        ),
      'fixtures/dataset-detail.sql',
      'fixtures/summary-filter-testCase3.csv',
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
    );
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
}
