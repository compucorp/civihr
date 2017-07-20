<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRLeaveAndAbsences_Exception_InvalidContactWorkPatternException as InvalidContactWorkPatternException;

class CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern extends CRM_HRLeaveAndAbsences_DAO_ContactWorkPattern {

  /**
   * Create a new ContactWorkPattern based on array-data
   *
   * @param array $params
   *  Key-value pairs
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern|NULL
   *
   * @throws \Exception
   */
  public static function create($params) {
    $entityName = 'ContactWorkPattern';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);

    self::validateParams($params);

    $transaction = new CRM_Core_Transaction();
    try {
      self::endEmployeePreviousWorkPattern($params);
      $instance->save();
      $transaction->commit();

    } catch (Exception $e) {
      $transaction->rollback();
      // re-throw the error how it can be handled somewhere else
      throw $e;
    }

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * A method for validating the params passed to the ContactWorkPattern create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidContactWorkPatternException
   */
  private static function validateParams($params) {
    self::validateMandatory($params);
    self::validateUniquePerContactAndEffectiveDate($params);
  }

  /**
   * A method for validating the mandatory fields in the params
   * passed to the ContactWorkPattern create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidContactWorkPatternException
   */
  private static function validateMandatory($params) {
    $mandatoryFields = [
      'contact_id',
      'effective_date',
      'pattern_id',
    ];

    foreach($mandatoryFields as $field) {
      if (empty($params[$field])) {
        throw new InvalidContactWorkPatternException("The {$field} field should not be empty");
      }
    }
  }

  /**
   * Gets the contact_id and effective_date from the params and validates that
   * the contact doesn't have another work pattern with the same effective date.
   *
   * In case of an update, this method also considers the ID, to make sure the
   * other work pattern with the same effective date is not itself.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidContactWorkPatternException
   */
  private static function validateUniquePerContactAndEffectiveDate($params) {
    $tableName = self::getTableName();
    $query = "SELECT id FROM {$tableName} WHERE contact_id = %1 AND effective_date = %2";

    $queryParams = [
      1 => [$params['contact_id'], 'Integer'],
      2 => [date('Y-m-d', strtotime($params['effective_date'])), 'String']
    ];

    if (!empty($params['id'])) {
      $query .= ' AND id <> %3';
      $queryParams[3] = [$params['id'], 'Integer'];
    }

    $result = CRM_Core_DAO::executeQuery($query, $queryParams);

    if ($result->N) {
      throw new InvalidContactWorkPatternException('This contact already have a Work Pattern with this effective date');
    }
  }

  /**
   * Updates the effective_end_date of the current ContactWorkPattern for the
   * Contact which we're trying to add a new WorkPattern. The end date
   * will be the effective date of the new one - 1 day.
   *
   * @param $params
   *  The params array passed to the create() method
   */
  private static function endEmployeePreviousWorkPattern($params) {
    $newPatternEffectiveDate = strtotime($params['effective_date']);
    $oldPatternEndDate = date('Y-m-d', strtotime('-1 day', $newPatternEffectiveDate));

    $tableName = self::getTableName();

    $query = "UPDATE {$tableName} 
              SET effective_end_date = %1
              WHERE contact_id = %2 AND
                    effective_end_date IS NULL";

    $params = [
      1 => [$oldPatternEndDate, 'String'],
      2 => [$params['contact_id'], 'Integer']
    ];

    CRM_Core_DAO::executeQuery($query, $params);
  }

  /**
   * Returns the ContactWorkPattern instance for the given contact and $date
   * The function will only return the ContactWorkPattern instance that is
   * linked to an active WorkPattern.
   *
   * @param int $contactID
   * @param \DateTime $date
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern|null
   */
  public static function getForDate($contactID, DateTime $date) {
    $contactWorkPatternTable = self::getTableName();
    $workPatternTable = WorkPattern::getTableName();

    $query = "SELECT * FROM {$contactWorkPatternTable} cwp
              INNER JOIN {$workPatternTable} wp 
                ON cwp.pattern_id = wp.id
              WHERE cwp.contact_id = %1 AND 
              cwp.effective_date <= %2 AND 
              (cwp.effective_end_date >= %2 OR cwp.effective_end_date IS NULL) AND
              wp.is_active = 1";

    $params = [
      1 => [$contactID, 'Integer'],
      2 => [$date->format('Y-m-d'), 'String']
    ];

    $result = CRM_Core_DAO::executeQuery($query, $params, true, self::class);
    if($result->N == 1) {
      $result->fetch();
      return $result;
    }

    return null;
  }

  /**
   * This method returns the Work Pattern for a contact ID
   * valid for the $date parameter supplied.
   * If the contact has no work pattern, the default work pattern is returned.
   *
   * @param int $contactId
   * @param \DateTime $date
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_WorkPattern
   */
  public static function getWorkPattern($contactId, DateTime $date) {
    $contactWorkPattern = self::getForDate($contactId, $date);
    if (is_null($contactWorkPattern)) {
      $workPattern = WorkPattern::getDefault();
    }
    else {
      $workPattern = WorkPattern::findById($contactWorkPattern->pattern_id);
    }
    return $workPattern;
  }

  /**
   * This method returns the effective start date of the Contact Work Pattern for a contact ID
   * valid for the $date parameter supplied.
   * If the contact has no work pattern, the start date of
   * the contact's contract that overlaps with the $date parameter supplied is returned.
   *
   * @param int $contactId
   * @param \DateTime $date
   *
   * @return \DateTime
   */
  public static function getStartDate($contactId, DateTime $date) {
    $contactWorkPattern = self::getForDate($contactId, $date);
    if (is_null($contactWorkPattern)) {
      $startDate = self::getStartDateOfContractOverlappingDate($contactId, $date);
    }
    else {
      $startDate = new \DateTime($contactWorkPattern->effective_date);
    }
    return $startDate;
  }

  /**
   * Fetches the contract of the given contact overlapping the given date and
   * then return it's period start date as a DateTime object. Null is returned
   * if there's no contract is found.
   *
   * @param int $contactID
   * @param \DateTime $date
   *
   * @return \DateTime|null
   */
  private static function getStartDateOfContractOverlappingDate($contactID, DateTime $date) {
    $result = civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', [
      'contact_id' => $contactID,
      'start_date' => $date->format('Y-m-d'),
      'sequential' => 1
    ]);

    if(!empty($result['values'])) {
      return new DateTime($result['values'][0]['period_start_date']);
    }

    return null;
  }

  /**
   * Returns the type of this day for the contact whether its a Working Day, Non Working Day or Weekend
   * The method first of all finds the valid work pattern for this contact for the given date
   * and also the effective start date of the contact work pattern
   * This information is then used to find the work day type
   *
   * @param int $contactId
   * @param \DateTime $date
   *
   * @return int
   *   The WorkDay Type
   */
  public static function getWorkDayType($contactId, DateTime $date) {
    $workPattern = self::getWorkPattern($contactId, $date);
    $startDate = self::getStartDate($contactId, $date);

    if(!$workPattern || !$startDate) {
      return Workday::getNonWorkingDayTypeValue();
    }

    $workDayTypeId = $workPattern->getWorkDayTypeForDate($date, $startDate);
    return $workDayTypeId;
  }

  /**
   * Returns a list of ContactWorkPattern instances for the given $contactID,
   * which overlap the period enclosed by the given $start and $end dates
   *
   * @param int $contactID
   * @param \DateTime $start
   * @param \DateTime $end
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern[]
   */
  public static function getAllForPeriod($contactID, DateTime $start, DateTime $end) {
    $tableName = self::getTableName();

    $query = "SELECT * FROM {$tableName}
              WHERE contact_id = %1 AND 
                    effective_date <= %2 AND 
                    (effective_end_date >= %3 OR effective_end_date IS NULL)";

    $params = [
      1 => [$contactID, 'Integer'],
      2 => [$end->format('Y-m-d'), 'String'],
      3 => [$start->format('Y-m-d'), 'String']
    ];

    $result = CRM_Core_DAO::executeQuery($query, $params, true, self::class);

    $contactWorkPatterns = [];
    while($result->fetch()) {
      $contactWorkPatterns[] = clone $result;
    }

    return $contactWorkPatterns;
  }

  /**
   * Gets the contacts that have work patterns with effective_end_date
   * greater than or equal to the date parameter.
   *
   * @param \DateTime $date
   * @param int $workPatternID
   *
   * @return array
   *   Array of contact ID's
   */
  public static function getContactsUsingWorkPatternFromDate(DateTime $date, $workPatternID) {
    $tableName = self::getTableName();
    $query = "SELECT DISTINCT contact_id FROM {$tableName} WHERE pattern_id = %1 AND
              (effective_end_date >= %2 OR effective_end_date IS NULL)";

    $params = [
      1 => [$workPatternID, 'Integer'],
      2 => [$date->format('Y-m-d'), 'String']
    ];
    $result = CRM_Core_DAO::executeQuery($query, $params);

    $contacts = [];
    while($result->fetch()) {
      $contacts[] = $result->contact_id;
    }

    return $contacts;
  }
}
