<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait ContractSummaryTestTrait {

  /**
   * Creates absence period from specified start and end dates and return its ID
   *
   * @param string $startDate A date string in YmdHis format
   * @param string $endDate A date string in YmdHis format
   *
   * @return int period ID
   */
  protected function createAbsencePeriod($startDate, $endDate)  {
    $params = array('name' => $startDate, 'start_date' => $startDate, 'end_date' => $endDate);
    $period = CRM_HRAbsence_BAO_HRAbsencePeriod::create($params);
    return $period->id;
  }

  /**
   * Creates an absence entitlement
   *
   * @param $params
   *
   * @return int entitlement ID
   */
  protected function createAbsenceEntitlement($params)  {
    $entitlement = CRM_HRAbsence_BAO_HRAbsenceEntitlement::create($params);
    return $entitlement->id;
  }

  /**
   * Creates a new Job Contract for the given contact
   *
   * If a startDate is given, it will also create a JobDetails instance to save
   * the contract's start date and end date(if given)
   *
   * @param $contactID
   * @param null $startDate
   * @param null $endDate
   * @param array $extraParams
   *
   * @return \CRM_HRJob_DAO_HRJobContract|NULL
   */
  protected function createJobContract($contactID, $startDate = null, $endDate = null, $extraParams = array()) {
    $contract = CRM_Hrjobcontract_BAO_HRJobContract::create(['contact_id' => $contactID]);
    if($startDate) {
      $params = [
        'jobcontract_id' => $contract->id,
        'period_start_date' => CRM_Utils_Date::processDate($startDate),
        'period_end_date' => null,
      ];
      if($endDate) {
        $params['period_end_date'] = CRM_Utils_Date::processDate($endDate);
      }
      $params = array_merge($params, $extraParams);
      CRM_Hrjobcontract_BAO_HRJobDetails::create($params);
    }
    return $contract;
  }

  /**
   * Request Leave for a specific contact
   *
   * @param string $absenceType
   * @param int $contactID
   * @param string $from leave request start date in Y-m-d format
   * @param string $to leave request end date in Y-m-d format
   * @param string $absence full_day or half_day
   *
   * @return int
   */
  protected function requestLeave($absenceType, $contactID, $from, $to, $absence)  {
    $begin = new DateTime($from);
    $end = new DateTime($to);
    $end = $end->modify('+1 day');

    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($begin, $interval, $end);

    $activityStatus = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus('name');
    $statusID = CRM_Utils_Array::key('Completed', $activityStatus);

    $absenceTypeID = $this->getAbsenceTypeId('absence');
    $sickTypeID = $this->getAbsenceTypeId($absenceType);

    $activityParam = array(
      'activity_type_id' => $sickTypeID,
      'activity_date_time' => date('Y-m-d H:i:s'),
      'status_id' => $statusID,
      'source_contact_id' => 1, // 1 is the (default organization) contract ID
    );
    $mainActivity = civicrm_api3('Activity', 'create', $activityParam);
    $sourceActivityID = $mainActivity['id'];

    civicrm_api3('ActivityContact', 'create', array(
      'activity_id' => $sourceActivityID,
      'contact_id' => $contactID,
      'record_type_id' => 3,
    ));

    foreach ( $period as $dt )  {
      $leaveDate =  $dt->format('Y-m-d');
      $activityParam = array(
        'source_record_id'   => $sourceActivityID,
        'activity_type_id'   => $absenceTypeID,
        'activity_date_time' => $leaveDate,
        'duration'           =>  ($absence == 'full_day') ? 480 : 240,
        'status_id'          => $statusID,
        'source_contact_id' => 1, // 1 is the (default organization) contract ID
      );
      civicrm_api3('Activity', 'create', $activityParam);
    }
  }

  /**
   * Get the ID for the requested absence type using its name
   *
   * @param $absenceTypeName
   *
   * @return int|NULL
   */
  protected function getAbsenceTypeId($absenceTypeName)  {
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
