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

class CRM_HRAbsence_BAO_HRAbsenceType extends CRM_HRAbsence_DAO_HRAbsenceType {

  public static function create($params) {
    $entityName = 'HRAbsenceType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    if (!array_key_exists('name', $params) && !array_key_exists('id', $params)) {
      $params['name'] = CRM_Utils_String::munge($params['title']);
    }

    // If this is an existing type, we'll need to know about previously linked activity-type-ids
    if (!empty($params['id'])) {
      $existing = civicrm_api3('HRAbsenceType', 'getsingle', array('id' => $params['id']));
      $params = array_merge($existing, $params);
    }

    $activityTypesResult = civicrm_api3('activity_type', 'get', array());
    if (CRM_Utils_Array::value('allow_debits', $params)) {
      if (empty($params['debit_activity_type_id'])) {
        $debitActivityTypeId = array_search($params['title'], $activityTypesResult["values"]);
        if (!$debitActivityTypeId) {
          $resultCreateActivityType = self::createActivityType($activityTypesResult, $params, true);
          $debitActivityTypeId = $resultCreateActivityType['values'][$resultCreateActivityType["id"]]['value'];
        }
        $params["debit_activity_type_id"] = $debitActivityTypeId;
      } else {
        self::updateOptionValue($params, true);
      }
    }
    if (CRM_Utils_Array::value('allow_credits', $params)){
      if (empty($params['credit_activity_type_id'])) {
        $creditActivityTypeId = array_search(ts('%1 (Credit)', array(1 => $params["title"])), $activityTypesResult["values"]);
        if (!$creditActivityTypeId) {
          $resultCreateActivityType = self::createActivityType($activityTypesResult, $params, false);
          $creditActivityTypeId = $resultCreateActivityType['values'][$resultCreateActivityType["id"]]['value'];
        }
        $params["credit_activity_type_id"] = $creditActivityTypeId;
      } else {
        self::updateOptionValue($params, false);
      }
    }
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Creates Activity Types based on $params and $activityTypes
   *
   * @param array $activityTypes
   * @param array $params
   * @param boolean $isDebit 'true' for 'debit' and 'false' for 'credit'
   *
   * @return array Created Activity Type as array
   */
  private static function createActivityType($activityTypes, $params, $isDebit) {
    $weight = count($activityTypes["values"]);
    if ($isDebit) {
      $valueId = $params['debit_activity_type_id'];
      $valueLabel = $params['title'];
    } else {
      $valueId = $params['credit_activity_type_id'];
      $valueLabel = ts('%1 (Credit)', array(1 => $params["title"]));
    }

    $paramsCreate = array(
      'weight' => ($weight+1),
      'label' => $valueLabel,
      'filter' => 1,
      'is_active' => 1,
      'is_optgroup' => 0,
      'is_default' => 0,
      'grouping' => 'Timesheet'
    );

    return civicrm_api3('activity_type', 'create', $paramsCreate);
  }

  /**
   * Updates Option Value based on $params
   *
   * @param array $params
   * @param boolean $isDebit 'true' for 'debit' and 'false' for 'credit'
   */
  private static function updateOptionValue($params, $isDebit) {
    if ($isDebit) {
      $valueId = $params['debit_activity_type_id'];
      $valueLabel = $params['title'];
    } else {
      $valueId = $params['credit_activity_type_id'];
      $valueLabel = ts('%1 (Credit)', array(1 => $params["title"]));
    }

    $optionValue = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'option_group_id' => "activity_type",
      'value' => $valueId
    ));

    civicrm_api3('OptionValue', 'create', array(
      'id' => $optionValue['id'],
      'label' => $valueLabel
    ));
  }

  /**
   * Get a count of records with the given property
   *
   * @param $params
   * @return int
   */
  public static function getRecordCount($params) {
    $dao = new CRM_HRAbsence_DAO_HRAbsenceType();
    $dao->copyValues($params);
    return $dao->count();
  }

  public static function getDefaultValues($id) {
    $absenceTypes =  civicrm_api3('HRAbsenceType', 'get', array('id' => $id));
    return $absenceTypes['values'][$id];
  }
  /**
   * Get the list of absence-related activity types
   *
   * @return array (int activity_type_id => string activity_label)
   */
  public static function getActivityTypes() {
    $activityTypes = civicrm_api3('ActivityType', 'get', array());
    $absenceTypes = civicrm_api3('HRAbsenceType', 'get', array());
    $result = array();
    foreach ($absenceTypes['values'] as $absenceType) {
      if (!empty($absenceType['credit_activity_type_id'])) {
        $result[$absenceType['credit_activity_type_id']] = $activityTypes['values'][$absenceType['credit_activity_type_id']];
      }
      if (!empty($absenceType['debit_activity_type_id'])) {
        $result[$absenceType['debit_activity_type_id']] = $activityTypes['values'][$absenceType['debit_activity_type_id']];
      }
    }
    return $result;
  }

  /**
   * Get the list of absence-related activity status
   *
   * @return array (int activity_status_id => string activity_status_label)
   */
  public static function getActivityStatus($return = 'label') {

    $activityStatus = CRM_Activity_BAO_Activity::buildOptions('status_id', 'validate');

    $absenceStatus = array(
      'Scheduled' => ts('Requested'),
      'Completed' => ts('Approved'),
      'Cancelled' => ts('Cancelled'),
      'Rejected' => ts('Rejected'),
    );
    $result = array();
    foreach ($absenceStatus as $name => $title) {
      if ($key = CRM_Utils_Array::key($name, $activityStatus)) {
        $result[$key] = $title;
        if ($return == 'name') {
          $result[$key] = $name;
        }
      }
    }
    return $result;
  }

  /**
   * Delete Absence Type
   *
   * @param int $absenceTypeId
   *   ID of Absence Type to be deleted
   * @throws CRM_Core_Exception
   *   If Absence Type is being used on existing leave requests
   */
  public static function del($absenceTypeId) {
    $absenceType = new CRM_HRAbsence_DAO_HRAbsenceType();
    $absenceType->id = $absenceTypeId;
    $absenceType->find(TRUE);

    if (($countRequests = self::countLeaveRequestsForAbsenceType($absenceType)) > 0) {
      throw new CRM_Core_Exception(ts("You can not delete this absence type -- it is assigned to %1 existing leave request(s). If you do not want this absence type to be used going forward, consider disabling it instead.", array(1 => $countRequests)));
    }

    self::deleteLeaveEntitlements($absenceTypeId);

    $absenceActivities = CRM_Core_OptionGroup::values('activity_type', FALSE, FALSE, FALSE, " AND grouping = 'Timesheet'", 'id');

    if ($absenceType->debit_activity_type_id && $id = CRM_Utils_Array::value($absenceType->debit_activity_type_id, $absenceActivities)) {
      CRM_Core_BAO_OptionValue::del($id);
    }
    if ($absenceType->credit_activity_type_id && $id = CRM_Utils_Array::value($absenceType->credit_activity_type_id, $absenceActivities)) {
      CRM_Core_BAO_OptionValue::del($id);
    }

    $absenceType->delete();

    CRM_Utils_Hook::post('delete', 'HRAbsenceType', $absenceTypeId, $absenceType);
  }

  /**
   * Count leave requests for given absence type object
   *
   * @param CRM_HRAbsence_BAO_HRAbsenceType $absenceType
   *   Instance of Absence Type object for which leave requests need to be counted
   * @return int
   *   Number of activities found for given Absence Type object
   */
  private static function countLeaveRequestsForAbsenceType(CRM_HRAbsence_DAO_HRAbsenceType $absenceType) {
    $count = 0;

    if (!empty($absenceType->credit_activity_type_id)) {
      $count += civicrm_api3('Activity', 'getcount', [
        'sequential' => 1,
        'activity_type_id' => $absenceType->credit_activity_type_id
      ]);
    }
    if (!empty($absenceType->debit_activity_type_id)) {
      $count += civicrm_api3('Activity', 'getcount', [
        'sequential' => 1,
        'activity_type_id' => $absenceType->debit_activity_type_id
      ]);
    }

    return $count;
  }

  /**
   * Deletes all leave entitlements for given absence type ID.
   *
   * @param int $absenceTypeId
   *   ID of Absence Type for which entitlements have to be deleted
   */
  private static function deleteLeaveEntitlements($absenceTypeId) {
    $result = civicrm_api3('HRJobLeave', 'get', [
      'sequential' => 1,
      'leave_type' => $absenceTypeId,
      'jobcontract_revision_id' => array('>' => 0)
    ]);
    foreach ($result['values'] as $currentLeave) {
      $result = civicrm_api3('HRJobLeave', 'delete', [
        'id' => $currentLeave['id'],
      ]);
    }
  }


  /**
   * Get the total duration for given 'Source Absence ID'
   *
   * @param int source Activity ID
   * @return int
   */
  public static function getAbsenceDuration($sourceAbsenceId) {
    $duration = 0;
    $absences = civicrm_api3('Activity', 'get', array('source_record_id' => $sourceAbsenceId));
    foreach ($absences['values'] as $absenceKey => $absenceVal) {
      $duration += $absenceVal['duration'];
    }
    return $duration;
  }

  static function setIsActive($id, $is_active) {
    return CRM_Core_DAO::setFieldValue('CRM_HRAbsence_DAO_HRAbsenceType', $id, 'is_active', $is_active);
  }
}
