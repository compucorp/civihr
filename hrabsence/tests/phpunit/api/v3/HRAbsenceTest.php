<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.0                                                 |
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

require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * FIXME
 */
class api_v3_HRAbsenceTest extends CiviUnitTestCase {
  function setUp() {
    // If your test manipulates any SQL tables, then you should truncate
    // them to ensure a consisting starting point for all tests
    // $this->quickCleanup(array('example_table_name'));
    parent::setUp();

    $this->fixtures['fullAbsenceType'] = array(
      'version' => 3,
      'api.HRAbsence.create' => array(
   	    'title' => "Absence type test",
  	    'allow_debits' => 1,
      ),
    );
  }

  function tearDown() {
    parent::tearDown();
    $this->quickCleanup(array(
      'civicrm_absence_type',
    ));
  }

  /**
   * Create a job and several subordinate entities using API chaining
   */
  function testCreateChained() {
    $result = civicrm_api('HRAbsence', 'create', $this->fixtures['fullAbsenceType']);
    $this->assertAPISuccess($result);
    foreach ($result['values'] as $hrAbsenceResult) {
      $this->assertAPISuccess($hrAbsenceResult['api.HRAbsence.create']);
      foreach ($hrAbsenceResult['api.HRJobPay.create']['values'] as $hrAbsence) {
        $this->assertEquals(1, $hrAbsence['allow_debits']);
      }
    }
  }


}
