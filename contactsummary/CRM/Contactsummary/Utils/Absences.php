<?php

class CRM_Contactsummary_Utils_Absences {

  /**
   * Get duration of absences for specific absence type, in minutes.
   *
   * @param string $absenceTypeName
   * @param int $periodId
   * @return int
   */
  public static function getTotalAbsences($absenceTypeName, $periodId) {

    // Nature of this contact's role in the activity: 1 assignee, 2 creator, 3 focus or target.
    $RECORD_TYPE = 3;

    $currentDate = date('Y-m-d');

    $sql = "
      SELECT SUM(duration) AS duration from (
      SELECT a.duration
      FROM civicrm_activity a
      LEFT JOIN civicrm_activity a2 ON a.source_record_id = a2.id
      LEFT JOIN civicrm_activity_contact ac ON a2.id = ac.activity_id
      LEFT JOIN civicrm_contact c ON ac.contact_id = c.id
      LEFT JOIN civicrm_hrjobcontract hrjc ON (c.id = hrjc.contact_id)
      LEFT JOIN civicrm_hrjobcontract_revision hrjr ON hrjr.jobcontract_id = hrjc.id
      LEFT JOIN civicrm_hrjobcontract_details hrjd ON hrjr.details_revision_id = hrjd.jobcontract_revision_id

      WHERE
      a.source_record_id
      IN (
      SELECT t2.id
      FROM civicrm_activity_contact t1
      LEFT JOIN civicrm_activity t2 ON t1.activity_id = t2.id
      WHERE t2.activity_type_id = %1
      AND t1.record_type_id = {$RECORD_TYPE}
      )

      AND a.status_id = %2
      AND (a.activity_date_time >= %3 AND a.activity_date_time < %4)
      AND a.is_deleted = 0

      AND ac.record_type_id ={$RECORD_TYPE}

      AND c.contact_type = 'Individual'

      AND hrjc.deleted = 0
      AND hrjr.deleted = 0
      AND hrjd.period_start_date <= '{$currentDate}'
      AND ( hrjd.period_end_date >= '{$currentDate}' OR hrjd.period_end_date IS NULL)
      group by a.id
      ) AS total_minutes
    ";

    $duration = 0;

    $activityStatuses = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus('name');
    $periodDetails    = CRM_HRAbsence_BAO_HRAbsencePeriod::getDefaultValues($periodId);

    $absenceID = static::getAbsenceTypeId($absenceTypeName);
    if ($absenceID == null)  {
      return $duration;
    }

    $params = array(
      1 => array($absenceID, 'Integer'),
      2 => array(CRM_Utils_Array::key('Completed', $activityStatuses), 'Integer'),
      3 => array($periodDetails['start_date'], 'String'),
      4 => array($periodDetails['end_date'], 'String'),
    );


    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    if ($dao->fetch()) {
      $duration = $dao->duration;
    }

    return $duration;
  }

  /**
   * Get the ID for the requested absence type using its name
   *
   * @param string $absenceTypeName
   *
   * @return int|NULL
   */
  private static function getAbsenceTypeId($absenceTypeName)  {
    $sql = "
      SELECT cov.value from civicrm_option_group cog
      LEFT JOIN civicrm_option_value cov ON cov.option_group_id = cog.id
      WHERE cog.name = 'activity_type'
      AND cov.name = %1
      AND cov.is_active = 1
    ";

    $params = array(
      1 => array($absenceTypeName, 'String'),
    );

    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    if ($dao->fetch()) {
      return $dao->value;
    }

    return null;
  }

}
