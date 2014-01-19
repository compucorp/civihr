<?php
/**
 * Tests for the API queries required by absence JS widget
 *
 * @copyright Copyright CiviCRM LLC (C) 2009
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 * @version   $Id: ActivityTest.php 31254 2010-12-15 10:09:29Z eileen $
 * @package   CiviCRM
 *
 *   This file is part of CiviCRM
 *
 *   CiviCRM is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Affero General Public License
 *   as published by the Free Software Foundation; either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   CiviCRM is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public
 *   License along with this program.  If not, see
 *   <http://www.gnu.org/licenses/>.
 */

/**
 *  Include class definitions
 */
require_once 'CiviTest/CiviUnitTestCase.php';


/**
 *  Test APIv3 civicrm_activity_* functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Activity
 */

class api_v3_ActivityGetAbsencesTest extends CiviUnitTestCase {
  protected $exampleAbsenceType = 'TOIL';
  protected $exampleActivityType = 'Meeting';

  /**
   * @var int
   */
  protected $contactId;

  /**
   * @var array (string $name => array $apiResult)
   */
  protected $periods;

  public function setUp() {
    parent::setUp();
    $this->contactId = $this->individualCreate();
    $this->contactId2 = $this->individualCreate(array(
      'first_name' => 'Alt',
      'email' => 'alt@example.org',
    ));
    $this->sourceContactId = $this->individualCreate(array(
      'first_name' => 'Source',
      'email' => 'source@example.org',
    ));
    $this->periods['2012'] = $this->callAPISuccess('HRAbsencePeriod', 'create', array(
      'name' => '2012',
      'title' => 'Jan 2012 - Dec 2012',
      'start_date' => '2012-01-01 00:00:00',
      'end_date' => '2012-12-31 23:59:59',
    ));
    $this->periods['2013'] = $this->callAPISuccess('HRAbsencePeriod', 'create', array(
      'name' => '2013',
      'title' => 'Jan 2013 - Dec 2013',
      'start_date' => '2013-01-01 00:00:00',
      'end_date' => '2013-12-31 23:59:59',
    ));

    foreach (array($this->contactId, $this->contactId2) as $contactId) {
      foreach (array('2012-01-01 01:01', '2012-12-31 23:57', '2013-01-01 01:01') as $datetime) {
        foreach (array($this->exampleAbsenceType, $this->exampleActivityType) as $activityType) {
          $params = array(
            'source_contact_id' => $this->sourceContactId,
            'target_contact_id' => $contactId,
            'activity_date_time' => $datetime,
            'activity_type_id' => $activityType,
          );
          $this->callAPISuccess('Activity', 'create', $params);
        }
      }
    }
  }

  public function tearDown() {
    $tablesToTruncate = array(
      'civicrm_contact',
      'civicrm_activity',
      'civicrm_activity_contact',
      'civicrm_absence_period',
    );
    $this->quickCleanup($tablesToTruncate, TRUE);
    parent::tearDown();
  }

  public function testGetBlank() {
    $activities = $this->callAPISuccess('Activity', 'getAbsences', array());

    $this->assertTrue(count($activities['values']) > 0);

    $activityTypes = CRM_Core_PseudoConstant::activityType();
    foreach ($activities['values'] as $activity) {
      // no exampleActivityType records! only exampleAbsenceType.
      $this->assertEquals($this->exampleAbsenceType, $activityTypes[$activity['activity_type_id']]);
    }
  }

  public function testGetPeriod() {
    $activities = $this->callAPISuccess('Activity', 'getAbsences', array(
      'period_id' => $this->periods['2013']['id'],
    ));

    $dates = array_unique(CRM_Utils_Array::collect('activity_date_time', $activities['values']));
    sort($dates);
    $this->assertEquals(array('2013-01-01 01:01:00'), $dates);
    $this->assertEquals(count($activities['values']), count(array_unique(CRM_Utils_Array::collect('id', $activities['values'])))); // no dupes

    $activityTypes = CRM_Core_PseudoConstant::activityType();
    foreach ($activities['values'] as $activity) {
      // no exampleActivityType records! only exampleAbsenceType.
      $this->assertEquals($this->exampleAbsenceType, $activityTypes[$activity['activity_type_id']]);
    }
  }

  public function testGetPeriods() {
    $activities = $this->callAPISuccess('Activity', 'getAbsences', array(
      'period_id' => array($this->periods['2012']['id'], $this->periods['2013']['id']),
    ));

    $dates = array_unique(CRM_Utils_Array::collect('activity_date_time', $activities['values']));
    sort($dates);
    $this->assertEquals(array('2012-01-01 01:01:00', '2012-12-31 23:57:00', '2013-01-01 01:01:00'), $dates);
    $this->assertEquals(count($activities['values']), count(array_unique(CRM_Utils_Array::collect('id', $activities['values'])))); // no dupes

    $activityTypes = CRM_Core_PseudoConstant::activityType();
    foreach ($activities['values'] as $activity) {
      // no exampleActivityType records! only exampleAbsenceType.
      $this->assertEquals($this->exampleAbsenceType, $activityTypes[$activity['activity_type_id']]);
    }
  }

  public function testGetTarget() {
    $activities = $this->callAPISuccess('Activity', 'getAbsences', array(
      'target_contact_id' => $this->contactId,
      'return' => array('target_contact_id'),
    ));

    $this->assertTrue(count($activities['values']) > 0);
    $this->assertEquals(count($activities['values']), count(array_unique(CRM_Utils_Array::collect('id', $activities['values'])))); // no dupes
    foreach ($activities['values'] as $activity) {
      $this->assertEquals(array($this->contactId), $activity['target_contact_id']);
    }

    $activityTypes = CRM_Core_PseudoConstant::activityType();
    foreach ($activities['values'] as $activity) {
      // no exampleActivityType records! only exampleAbsenceType.
      $this->assertEquals($this->exampleAbsenceType, $activityTypes[$activity['activity_type_id']]);
    }
  }

}