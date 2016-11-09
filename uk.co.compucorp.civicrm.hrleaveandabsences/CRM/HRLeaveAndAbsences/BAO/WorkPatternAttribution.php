<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;

class CRM_HRLeaveAndAbsences_BAO_WorkPatternAttribution extends CRM_HRLeaveAndAbsences_DAO_WorkPatternAttribution {

  /**
   * Create a new WorkPatternAttribution based on array-data
   *
   * @param array $params
   *  Key-value pairs
   *
   * @return \CRM_HRLeaveAndAbsences_DAO_WorkPatternAttribution|NULL
   *
   * @throws \Exception
   */
  public static function create($params) {
    $entityName = 'WorkPatternAttribution';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);

    $transaction = new CRM_Core_Transaction();
    try {
      self::endEmployeePreviousAttribution($params);
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
   * Updates the effective_end_date of the current WorkPatternAttribution for the
   * Contact which we're trying to add a new WorkPatternAttribution. The end date
   * will be the effective date of the new attribution - 1 day.
   *
   * @param $params
   *  The params array passed to the create() method
   */
  private static function endEmployeePreviousAttribution($params) {
    $newAttributionEffectiveDate = strtotime($params['effective_date']);
    $oldAttributionEndDate = date('Y-m-d', strtotime('-1 day', $newAttributionEffectiveDate));

    $tableName = self::getTableName();

    $query = "UPDATE {$tableName} 
              SET effective_end_date = %1
              WHERE contact_id = %2 AND
                    effective_end_date IS NULL";

    $params = [
      1 => [$oldAttributionEndDate, 'String'],
      2 => [$params['contact_id'], 'Integer']
    ];

    CRM_Core_DAO::executeQuery($query, $params);
  }

  /**
   * Returns the WorkPatternAttribution instance for the given contact and $date
   *
   * @param int $contactID
   * @param \DateTime $date
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_WorkPatternAttribution|null
   */
  public static function getForDate($contactID, DateTime $date) {
    $attributionTableName = self::getTableName();

    $query = "SELECT * FROM {$attributionTableName}
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
   * Returns the WorkPattern active during the given date for the contact with
   * the given $contactID.
   *
   * @param int $contactID
   * @param \DateTime $date
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_WorkPattern|null
   */
  public static function getWorkPatternForDate($contactID, DateTime $date) {
    $attributionTableName = self::getTableName();
    $workPatternTableName = WorkPattern::getTableName();

    $query = "SELECT wp.* FROM {$attributionTableName} wpa
              INNER JOIN {$workPatternTableName} wp
                ON wpa.pattern_id = wp.id
              WHERE wpa.contact_id = %1 AND 
                    wpa.effective_date <= %2 AND 
                    (wpa.effective_end_date >= %2 OR wpa.effective_end_date IS NULL)";

    $params = [
      1 => [$contactID, 'Integer'],
      2 => [$date->format('Y-m-d'), 'String']
    ];

    $result = CRM_Core_DAO::executeQuery($query, $params);
    if($result->N == 1) {
      $result->fetch();

      return self::buildWorkPatternFromDBResult($result);
    }

    return WorkPattern::getDefault();
  }

  /**
   * Builds a WorkPattern instance based on the values of a DB result
   *
   * @param $result
   *  The result of a DB query, returned by CRM_Core_DAO::executeQuery()
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_WorkPattern
   */
  private static function buildWorkPatternFromDBResult($result) {
    $workPattern = new WorkPattern();

    $fields = WorkPattern::fields();
    foreach($fields as $field) {
      if(!property_exists($result, $field['name'])) {
        throw Exception("The DB result doesn't contain all the required fields");
      }

      $workPattern->$field['name'] = $result->$field['name'];
    }

    return $workPattern;
  }
}
