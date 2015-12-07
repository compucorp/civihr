<?php

class CRM_Contactsummary_Utils_Absences {
  const TYPE_MATERNITY = 'maternity';
  const TYPE_OTHER = 'other';
  const TYPE_PATERNITY = 'paternity';
  const TYPE_SICKNESS = 'sick';
  const TYPE_TOIL = 'toil';
  const TYPE_VACATION = 'vacation';

  /**
   * todo: review if this method is needed at all
   *
   * @param array $absenceTypes
   * @param       $periodId
   *
   * @return int
   */
  public static function getTotalAbsences($absenceTypes = array(), $periodId) {
    $total = static::getAbsenceDuration($absenceTypes, $periodId);

    return $total;
  }

  /**
   * Get duration of absences, in minutes.
   *
   * @param array $absenceTypeNames
   * @param       $periodId
   *
   * @return int
   */
  private static function getAbsenceDuration($absenceTypeNames = array(), $periodId) {
    $absenceTypeIds = static::getActivityIdsForAbsenceTypeNames($absenceTypeNames, $periodId);
    $activityStatuses = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus('name');

    $sql = "
      SELECT SUM(a1.duration) duration
      FROM civicrm_activity a

      INNER JOIN civicrm_activity_contact ac
      ON a.id = ac.activity_id

      INNER JOIN civicrm_contact c
      ON (ac.contact_id = c.id AND c.contact_type = 'Individual')

      INNER JOIN civicrm_hrjobcontract jc
      ON c.id = jc.contact_id AND jc.is_primary = 1

      INNER JOIN civicrm_hrjobcontract_revision jcr
      ON (jc.id = jcr.jobcontract_id AND jcr.effective_date <= NOW())

      INNER JOIN civicrm_hrjobcontract_details jcd
      ON (jcr.id = jcd.jobcontract_revision_id AND (jcd.period_end_date >= NOW() OR jcd.period_end_date IS NULL))

      INNER JOIN civicrm_activity a1
      ON (a.id = a1.source_record_id AND a1.activity_type_id = %1 AND a1.status_id = %2)

      WHERE
        a.id IN (" . implode(',', $absenceTypeIds) . ")
        AND a.status_id = %2
    ";

    $params = array(
        1 => array(static::getAbsenceActivityTypeId(), 'Integer'),
        2 => array(CRM_Utils_Array::key('Completed', $activityStatuses), 'Integer'),
    );

    $duration = 0;

    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    if ($dao->fetch()) {
      $duration = $dao->duration;
    }

    return $duration;
  }

  /**
   * Get an array of activity IDs for absences, corresponding to a given array of absence type names.
   *
   * @param array $absenceTypeNames
   * @param       $periodId
   *
   * @return array
   */
  private static function getActivityIdsForAbsenceTypeNames($absenceTypeNames = array(), $periodId) {
    $ids = array();

    $absenceTypeIds = array();
    foreach (static::getAbsenceTypes() as $type) {
      if (in_array(strtolower($type['name']), $absenceTypeNames) || !$absenceTypeNames) {
        $absenceTypeIds[] = $type['debit_activity_type_id'];
      }
    }

    $absences = static::getAbsences($periodId);

    foreach ($absences as $id => $absence) {
      if (in_array($absence['activity_type_id'], $absenceTypeIds)) {
        $ids[] = $id;
      }
    }

    return array_filter($ids);
  }

  /**
   * Get a list of all absence types.
   *
   * @return mixed
   * @throws \CiviCRM_API3_Exception
   */
  private static function getAbsenceTypes() {
    $result = civicrm_api3('HRAbsenceType', 'get');

    return $result['values'];
  }

  /**
   * Get a list of all absences.
   *
   * @param $periodId
   *
   * @return
   * @throws \CiviCRM_API3_Exception
   */
  private static function getAbsences($periodId) {
    $result = civicrm_api3('Activity', 'getabsences', array('period_id' => $periodId));

    return $result['values'];
  }

  /**
   * Get the activity type ID for absences.
   */
  private static function getAbsenceActivityTypeId() {
    return array_search('Absence', CRM_Core_PseudoConstant::activityType());
  }
}