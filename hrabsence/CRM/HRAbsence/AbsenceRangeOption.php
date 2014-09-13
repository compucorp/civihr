<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.4                                                |
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

/**
 * Implement the "absence-range" option which determines the start and end dates for the specific
 * absence dates in an absence-request.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id$
 */

require_once 'api/Wrapper.php';
class CRM_HRAbsence_AbsenceRangeOption implements API_Wrapper {

  /**
   * @var CRM_HRAbsence_AbsenceRangeOption
   */
  private static $_singleton = NULL;

  /**
   * @return CRM_HRAbsence_AbsenceRangeOption
   */
  public static function singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_HRAbsence_AbsenceRangeOption();
    }
    return self::$_singleton;
  }

  /**
   * {@inheritDoc}
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * {@inheritDoc}
   */
  public function toApiOutput($apiRequest, $result) {
    if (isset($apiRequest['params']['options']) && CRM_Utils_Array::value('absence-range', $apiRequest['params']['options'], FALSE)) {
      if (!CRM_Utils_Array::value('is_error', $result, FALSE) && !empty($result['values'])) {
        $absenceTypeId = array_search('Absence', CRM_Core_PseudoConstant::activityType());
        if (!$absenceTypeId) {
          throw new API_Exception("Failed to determine activity type ID of absences");
        }

        $ids = array_keys($result['values']);
        $ids = array_filter($ids, 'is_numeric'); // paranoia

        foreach ($ids as $id) {
          $result['values'][$id]['absence_range'] = array(
            'low' => NULL,
            'high' => NULL,
            'approved_duration' => 0,
            'duration' => 0,
            'count' => 0,
            'items' => array(),
          );
        }

        $sql = "
          SELECT id, source_record_id, activity_date_time, status_id, duration as duration
          FROM civicrm_activity
          WHERE activity_type_id = %1 AND source_record_id in (" . implode(',', $ids) . ")
          ";
        $params = array(
          1 => array($absenceTypeId, 'Integer'),
        );

        $dao = CRM_Core_DAO::executeQuery($sql, $params);
        $activityStatus = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus('name');
        while ($dao->fetch()) {
          $ar = &$result['values'][$dao->source_record_id]['absence_range'];
          if ($ar['low'] === NULL || $ar['low'] > $dao->activity_date_time) {
            $ar['low'] = $dao->activity_date_time;
          }
          if ($ar['high'] === NULL || $ar['high'] < $dao->activity_date_time) {
            $ar['high'] = $dao->activity_date_time;
          }
          if ($dao->status_id == CRM_Utils_Array::key('Completed', $activityStatus)) {
            $ar['approved_duration'] += $dao->duration;
          }
          $ar['duration'] += $dao->duration;
          $ar['count']++;

          $ar['items'][] = array(
            'id' => $dao->id,
            'activity_date_time' => $dao->activity_date_time,
            'duration' => $dao->duration,
            // ignore source_record_id; it's implicit
          );
        }
      }
    }
    return $result;
  }
}
