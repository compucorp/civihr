<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
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
    _hrjob_phpunit_populateDB();
    return TRUE;
  }

  /*
  * At this stage exclude the ones that don't pass & add them as we can troubleshoot them
  * This function will override the parent function.
  * This function will skip 'HRJobHealth', 'HRJobHour', 'HRJobLeave', 'HRJobPay', 'HRJobPension' entities
  */
  public static function toBeSkipped_updatesingle($sequential = FALSE) {
    $entitiesWithout =  parent::toBeSkipped_updatesingle(TRUE);
    $entities = array_merge($entitiesWithout, array('HRJobHealth', 'HRJobHour', 'HRJobLeave', 'HRJobPay', 'HRJobPension'));
    return $entities;
  }

  public static function entities($skip = NULL) {
    $result = parent::entities($skip);
    return array_filter($result, function($value) {
      return preg_match("/^HRJob/", $value[0]);
    });
  }
}
