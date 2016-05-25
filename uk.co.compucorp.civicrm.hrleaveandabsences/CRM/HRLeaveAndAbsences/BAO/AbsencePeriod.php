<?php

class CRM_HRLeaveAndAbsences_BAO_AbsencePeriod extends CRM_HRLeaveAndAbsences_DAO_AbsencePeriod {

  /**
   * Create a new AbsencePeriod based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_AbsencePeriod|NULL
   */
  public static function create($params) {
    $className = 'CRM_HRLeaveAndAbsences_DAO_AbsencePeriod';
    $entityName = 'AbsencePeriod';
    $hook = empty($params['id']) ? 'create' : 'edit';

    self::validateParams($params);

    if(!empty($params['start_date'])) {
      $params['start_date'] = CRM_Utils_Date::processDate($params['start_date']);
    }

    if(!empty($params['end_date'])) {
      $params['end_date'] = CRM_Utils_Date::processDate($params['end_date']);
    }

    if(empty($params['weight'])) {
      $params['weight'] = self::getMaxWeight() + 1;
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);

    $transaction = new CRM_Core_Transaction();
    $instance->save();
    if(!$instance->id) {
      $transaction->rollback();
    }
    self::updatePeriodsOrder($instance);
    $transaction->commit();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Validates all the params passed to the create method
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException
   */
  private static function validateParams($params)
  {
    self::validateDates($params);

    if(self::overlapsWithAnotherPeriod($params)) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException(
        'This Absence Period overlaps with another existing Period'
      );
    }
  }

  /**
   * Checks if start_date and end_date values in the $params array are valid.
   *
   * A date cannot be empty, must be a real date and start_date can't be greater
   * than end_date.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException
   */
  private static function validateDates($params)
  {
    if(empty($params['start_date']) || empty($params['end_date'])) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException(
        'Both the start and end dates are required'
      );
    }

    if(!self::isValidDate($params['start_date']) || !self::isValidDate($params['end_date'])) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException(
        'Both the start and end dates should be valid'
      );
    }

    $startDate = strtotime($params['start_date']);
    $endDate = strtotime($params['end_date']);
    if($startDate >= $endDate) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException(
        'Start Date should be less than End Date'
      );
    }
  }

  /**
   * Checks if there's an existing Absence Period that overlaps with the
   * start and end dates on the given params array.
   *
   * @param array $params - The params array passed to the create method
   *
   * @return bool
   */
  private static function overlapsWithAnotherPeriod($params)
  {
    $tableName = self::getTableName();
    $query = "
      SELECT COUNT(*) as overlaping_periods
      FROM {$tableName}
      WHERE (start_date <= %1) AND (end_date >= %2)
    ";
    $queryParams = [
      1 => [$params['end_date'], 'String'],
      2 => [$params['start_date'], 'String'],
    ];

    if(!empty($query['id'])) {
      $query .= ' AND (id != %3)';
      $queryParams[3] = [$params['id'], 'Integer'];
    }

    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $dao->fetch();
    return $dao->overlaping_periods > 0;
  }

  /**
   * This method is called after saving an entity and it makes sure that there
   * will only one period for each weight.
   *
   * It checks if there's another period with the same weight and, if positive,
   * increase the weight of every period that has an equal or greater weight.
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance - The just saved AbsencePeriod
   */
  private static function updatePeriodsOrder($instance)
  {
    if(self::theresAnotherPeriodWithTheSameWeight($instance)) {
      self::increaseWeightsEqualOrGreaterTo($instance);
    }
  }

  /**
   * Checks if there's another period with the same weight of the given AbsencePeriod
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance - An AbsencePeriod
   *
   * @return bool
   */
  private static function theresAnotherPeriodWithTheSameWeight($instance)
  {
    $tableName = self::getTableName();
    $query = "
      SELECT COUNT(*) as periods
      FROM {$tableName}
      WHERE weight = %1 AND id != %2
    ";
    $queryParams = [
      1 => [$instance->weight, 'Integer'],
      2 => [$instance->id, 'Integer'],
    ];

    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $dao->fetch();
    return $dao->periods > 0;
  }

  /**
   * Increases the weight of every existing period with a weight equal to or
   * greater than the weight of the given AbsencePeriod
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsencePeriod $instance - An AbsencePeriod
   */
  private static function increaseWeightsEqualOrGreaterTo($instance)
  {
    $tableName = self::getTableName();
    $query = "
      UPDATE {$tableName}
      SET weight = weight + 1
      WHERE weight >= %1 AND id != %2
    ";
    $queryParams = [
      1 => [$instance->weight, 'Integer'],
      2 => [$instance->id, 'Integer'],
    ];

    CRM_Core_DAO::executeQuery($query, $queryParams);
  }

  /**
   * Gets the maximum weight of all Absence Periods
   *
   * Returns 0 if there's no Period available
   *
   * @return int the maximum weight
   */
  private static function getMaxWeight() {
    $tableName = self::getTableName();
    $query = "SELECT MAX(weight) as max_weight FROM {$tableName}";
    $dao = CRM_Core_DAO::executeQuery($query);
    if($dao->fetch()) {
      return $dao->max_weight;
    }

    return 0;
  }

  /**
   * This uses PHP's date_parse to check if the given date is valid.
   *
   * The date will be valid it no error or warning is found while parsing it.
   *
   * @param $date The date to be checked
   *
   * @return bool
   */
  private static function isValidDate($date)
  {
    $parsed = date_parse($date);

    return $parsed['warning_count'] == 0 && $parsed['error_count'] == 0;
  }
}
