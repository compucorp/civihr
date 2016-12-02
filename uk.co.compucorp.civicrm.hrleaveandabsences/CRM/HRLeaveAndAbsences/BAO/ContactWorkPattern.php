<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;

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

    $transaction = new CRM_Core_Transaction();
    try {
      self::endEmployeePreviousWorkPattern($params);
      $instance->save();
      $transaction->commit();

    } catch(Exception $e) {
      $transaction->rollback();
      // re-throw the error how it can be handled somewhere else
      throw $e;
    }

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
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
   *
   * @param int $contactID
   * @param \DateTime $date
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern|null
   */
  public static function getForDate($contactID, DateTime $date) {
    $tableName = self::getTableName();

    $query = "SELECT * FROM {$tableName}
              WHERE contact_id = %1 AND 
                    effective_date <= %2 AND 
                    (effective_end_date >= %2 OR effective_end_date IS NULL)";

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
}
