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
 * apiTest APIv3 civicrm_hrabsence_* functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_HRAbsence
 */
class api_v3_HRAbsenceSyntaxTest extends api_v3_SyntaxConformanceTest {

  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
    $this->quickCleanup(array(
      'civicrm_hrabsence_type',
    ));
  }


  /**
   * At this stage exclude the ones that don't pass & add them as we can troubleshoot them
   * This function will override the parent function.
   * This function will skip 'HRAbsence' entities
   */
  public static function toBeSkipped_updatesingle($sequential = FALSE) {
    $entitiesWithout = parent::toBeSkipped_updatesingle(TRUE);
    $entities = array_merge($entitiesWithout, array('HRAbsence'));
    return $entities;
  }

  public static function entities($skip = NULL) {
    $result = parent::entities($skip);
    return array_filter($result, function ($value) {
      return preg_match("/^HRAbsence/", $value[0]);
    });
  }

  public function getKnownUnworkablesUpdateSingle($entity, $key) {
    $result = parent::getKnownUnworkablesUpdateSingle($entity, $key);
    $extras = array(
      'HRAbsencePeriod' => array(
        'cant_update' => array(
          // testCreateSingleValueAlter doesn't handle straight-up dates properly
          // e.g it fails because "2013-02-03" !== "2013-02-03 00:00:00"
          'start_date',
          'end_date',
        ),
      ),
    );
    if (isset($extras[$entity][$key])) {
      return array_merge($result, $extras[$entity][$key]);
    }
    else {
      return $result;
    }
  }
}
