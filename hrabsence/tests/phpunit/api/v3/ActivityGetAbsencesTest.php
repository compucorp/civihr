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
  const ACTION = 'getabsences';
  const EXAMPLE_ABSENCE_TYPE = 'TOIL';
  const EXAMPLE_ACTIVITY_TYPE = 'Meeting';

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

    CRM_HRAbsence_Upgrader_Base::instance()->installActivityTypes(NULL);
    CRM_HRAbsence_Upgrader_Base::instance()->installAbsenceTypes(NULL);

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
      foreach (array('2012-01-01 01:01:00', '2012-12-31 23:57:00', '2013-01-01 01:01:00') as $datetime) {
        foreach (array(self::EXAMPLE_ABSENCE_TYPE, self::EXAMPLE_ACTIVITY_TYPE) as $activityType) {
          $params = array(
            'activity_type_id' => $activityType,
            'source_contact_id' => $this->sourceContactId,
            'target_contact_id' => $contactId,
            'details' => "key is $datetime",
            'activity_date_time' => '2010-11-12 01:02:03',
          );
          $absenceRequest = $this->callAPISuccess('Activity', 'create', $params);

          if ($activityType == self::EXAMPLE_ABSENCE_TYPE) {
            $relatedParams = array(
              'activity_type_id' => 'Absence',
              'source_record_id' => $absenceRequest['id'],
              'source_contact_id' => $this->sourceContactId,
              'target_contact_id' => $contactId,
              'activity_date_time' => date("YmdHis", strtotime($datetime) + 24 * 60 * 60),
              'duration' => 8 * 60,
            );
            $r = $this->callAPISuccess('Activity', 'create', $relatedParams);

            $relatedParams = array(
              'activity_type_id' => 'Absence',
              'source_record_id' => $absenceRequest['id'],
              'source_contact_id' => $this->sourceContactId,
              'target_contact_id' => $contactId,
              'activity_date_time' => date("YmdHis", strtotime($datetime) + 3 * 24 * 60 * 60),
              'duration' => 8 * 60,
            );
            $this->callAPISuccess('Activity', 'create', $relatedParams);
          }
        }
      }
    }
  }

  public function tearDown() {
    $tablesToTruncate = array(
      'civicrm_contact',
      'civicrm_activity',
      'civicrm_activity_contact',
      'civicrm_hrabsence_period',
      'civicrm_hrabsence_type',
    );
    $this->quickCleanup($tablesToTruncate, TRUE);
    parent::tearDown();
  }

  public function testGetBlank() {
    $activities = $this->callAPISuccess('Activity', self::ACTION, array());

    $this->assertTrue(count($activities['values']) > 0);

    $activityTypes = CRM_Core_PseudoConstant::activityType();
    foreach ($activities['values'] as $activity) {
      // no exampleActivityType records! only exampleAbsenceType.
      $this->assertEquals(self::EXAMPLE_ABSENCE_TYPE, $activityTypes[$activity['activity_type_id']]);
    }
  }

  public function testGetPeriod() {
    $activities = $this->callAPISuccess('Activity', self::ACTION, array(
      'period_id' => $this->periods['2013']['id'],
    ));

    $details = array_unique(CRM_Utils_Array::collect('details', $activities['values']));
    sort($details);
    $this->assertEquals(array('key is 2012-12-31 23:57:00', 'key is 2013-01-01 01:01:00'), $details);
    $this->assertEquals(count($activities['values']), count(array_unique(CRM_Utils_Array::collect('id', $activities['values'])))); // no dupes

    $activityTypes = CRM_Core_PseudoConstant::activityType();
    foreach ($activities['values'] as $activity) {
      // no exampleActivityType records! only exampleAbsenceType.
      $this->assertEquals(self::EXAMPLE_ABSENCE_TYPE, $activityTypes[$activity['activity_type_id']]);
    }
  }

  public function testGetPeriods() {
    $activities = $this->callAPISuccess('Activity', self::ACTION, array(
      'period_id' => array($this->periods['2012']['id'], $this->periods['2013']['id']),
    ));

    $details = array_unique(CRM_Utils_Array::collect('details', $activities['values']));
    sort($details);
    $this->assertEquals(array('key is 2012-01-01 01:01:00', 'key is 2012-12-31 23:57:00', 'key is 2013-01-01 01:01:00'), $details);
    $this->assertEquals(count($activities['values']), count(array_unique(CRM_Utils_Array::collect('id', $activities['values'])))); // no dupes

    $activityTypes = CRM_Core_PseudoConstant::activityType();
    foreach ($activities['values'] as $activity) {
      // no exampleActivityType records! only exampleAbsenceType.
      $this->assertEquals(self::EXAMPLE_ABSENCE_TYPE, $activityTypes[$activity['activity_type_id']]);
    }
  }

  public function testGetTarget() {
    $activities = $this->callAPISuccess('Activity', self::ACTION, array(
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
      $this->assertEquals(self::EXAMPLE_ABSENCE_TYPE, $activityTypes[$activity['activity_type_id']]);
    }
  }

  /**
   * When getting an absence-request activity, pass "options.absence-range=1" to calculate and
   * return the earliest and latest specific absence dates.
   */
  public function testOptionAbsenceRange() {
    $activities = $this->callAPISuccess('Activity', self::ACTION, array(
      'options' => array(
        'absence-range' => 1,
      ),
    ));

    $this->assertEquals(6, count($activities['values']));
    foreach ($activities['values'] as $activity) {
      switch ($activity['details']) {
        case 'key is 2012-01-01 01:01:00':
          $this->assertEquals('2012-01-02 01:01:00', $activity['absence_range']['low']);
          $this->assertEquals('2012-01-04 01:01:00', $activity['absence_range']['high']);
          $this->assertEquals(2 * 8 * 60, $activity['absence_range']['duration']);
          $this->assertEquals(2, $activity['absence_range']['count']);
          $this->assertEquals(array('2012-01-02 01:01:00', '2012-01-04 01:01:00'), CRM_Utils_Array::collect('activity_date_time', $activity['absence_range']['items']));
          $this->assertEquals(array(8*60, 8*60), CRM_Utils_Array::collect('duration', $activity['absence_range']['items']));
          break;
        case 'key is 2012-12-31 23:57:00':
          $this->assertEquals('2013-01-01 23:57:00', $activity['absence_range']['low']);
          $this->assertEquals('2013-01-03 23:57:00', $activity['absence_range']['high']);
          $this->assertEquals(2 * 8 * 60, $activity['absence_range']['duration']);
          $this->assertEquals(2, $activity['absence_range']['count']);
          $this->assertEquals(array('2013-01-01 23:57:00', '2013-01-03 23:57:00'), CRM_Utils_Array::collect('activity_date_time', $activity['absence_range']['items']));
          $this->assertEquals(array(8*60, 8*60), CRM_Utils_Array::collect('duration', $activity['absence_range']['items']));
          break;
        case 'key is 2013-01-01 01:01:00':
          $this->assertEquals('2013-01-02 01:01:00', $activity['absence_range']['low']);
          $this->assertEquals('2013-01-04 01:01:00', $activity['absence_range']['high']);
          $this->assertEquals(2 * 8 * 60, $activity['absence_range']['duration']);
          $this->assertEquals(2, $activity['absence_range']['count']);
          $this->assertEquals(array('2013-01-02 01:01:00', '2013-01-04 01:01:00'), CRM_Utils_Array::collect('activity_date_time', $activity['absence_range']['items']));
          $this->assertEquals(array(8*60, 8*60), CRM_Utils_Array::collect('duration', $activity['absence_range']['items']));
          break;
        default:
          $this->fail("Unrecognized date time: " . $activity['activity_date_time']);
      }
    }
  }
}