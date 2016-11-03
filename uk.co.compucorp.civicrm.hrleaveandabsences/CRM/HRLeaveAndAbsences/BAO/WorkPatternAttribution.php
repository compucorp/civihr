<?php

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
}
