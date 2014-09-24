<?php

/**
 * Maintain the pay estimates in civicrm_hrjob_pay.pay_annualized_est which
 * is roughly: pay_amount * settings{pay_unit} * hours_fte
 */
class CRM_HRJob_Estimator {
  const YEAR_UNIT = 'Year';

  /**
   * @var array (string $setting_name => string $unit_name)
   */
  protected static $unitSettingMap = array(
    'work_months_per_year' => 'Month',
    'work_weeks_per_year' => 'Week',
    'work_days_per_week' => 'Day',
    'work_hour_per_day' => 'Hour',
    'work_days_per_month' => 'DaysPerMonth'
  );

  /**
   * @see settings/HRJob.setting.php
   */
  public static function validateEstimateConstant($value, $metadata) {
    return is_numeric($value) && $value > 0;
  }

  /**
   * @see settings/HRJob.setting.php
   */
  public static function onUpdateEstimateConstants($oldValue, $newValue, $metadata) {
    if ($oldValue == $newValue) {
      return;
    }

    if (!isset(self::$unitSettingMap[$metadata['name']])) {
      throw new CRM_Core_Exception("onUpdateEstimateConstants: Failed match setting to unit");
    }
    self::updateEstimatesByUnit(self::$unitSettingMap[$metadata['name']], $newValue);
  }

  /**
   * Update all estimates
   */
  public static function updateEstimates() {
    $settings = civicrm_api3('Setting', 'getsingle', array(
      'return' => array_keys(self::$unitSettingMap),
    ));
    foreach (self::$unitSettingMap as $setting => $unit) {
      $setVal = self::getEstimateValue($unit, $settings);
      self::updateEstimatesByUnit($unit, $setVal);
    }
    self::updateEstimatesByUnit(self::YEAR_UNIT, 1);
  }

  public static function getEstimateValue($unit, $settings) {
    $settingName = array_search($unit, self::$unitSettingMap);
    if (empty($settingName)) {
      throw new CRM_Core_Exception("Failed to determine setting name for unit [$unit]");
    }
    switch ($unit) {
      case Day:
        $setVal = $settings['work_days_per_month'] * 12;
        break;
      case Hour:
        $setVal = $settings['work_days_per_month'];
        break;
      default:
        if (!isset($settings[$settingName])) {
          throw new CRM_Core_Exception("updateEstimates: Failed to locate setting \"$setting\" for \"$unit\"");
        }
        $setVal = $settings[$settingName];
        break;
    }
    return $setVal;
  }


  /**
   * Update estimates for a given pay-unit
   *
   * @param string $unit Month|Week|Day|Hour
   * @param float $value
   */
  public static function updateEstimatesByUnit($unit, $value) {
    // See also: CRM_HRJob_Estimator::updateEstimatesByJob (singular)
    // See also: CRM_HRJob_Upgrader::upgrade_1202
    // After HR-1.2.0 ships, don't make changes to the logic of upgrade_1202.
    $result = civicrm_api3('OptionValue', 'getsingle', array(
      'sequential' => 1,
      'option_group_id' => 'hrjob_hours_type',
      'name' => "Full_Time",
    ));
    $hour = $result['value'] ? $result['value'] : $settings['work_hour_per_day'];
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_hrjob_pay p, civicrm_hrjob_hour h
      SET p.pay_annualized_est = CASE p.pay_unit
      WHEN 'Hour' THEN %1 * IFNULL(h.hours_amount, %3) * h.fte_num * p.pay_amount / h.fte_denom
      ELSE %1 * h.fte_num * p.pay_amount / h.fte_denom END
      WHERE p.job_id = h.job_id
      AND p.pay_unit = %2
      AND p.pay_is_auto_est = 1
    ", array(
        1 => array($value, 'Float'),
        2 => array($unit, 'String'),
        3 => array($hour, 'Int'),
      )
    );
  }

  /**
   * Update estimates for a single job
   *
   * @param int $job_id
   */
  public static function updateEstimatesByJob($job_id) {
    $unit = CRM_Core_DAO::singleValueQuery('SELECT pay_unit FROM civicrm_hrjob_pay WHERE job_id = %1',
      array(
        1 => array($job_id, 'Positive')
      )
    );
    if (empty($unit)) {
      CRM_Core_DAO::executeQuery('UPDATE civicrm_hrjob_pay SET pay_unit = NULL WHERE job_id = %1', array(
        1 => array($job_id, 'Positive')
      ));
      return;
    }

    if ($unit == self::YEAR_UNIT) {
      $multiplier = 1;
    }
    else {
      $settings = civicrm_api3('Setting', 'getsingle', array(
        'return' => array_keys(self::$unitSettingMap),
      ));
      $multiplier = self::getEstimateValue($unit, $settings);
    }
    $result = civicrm_api3('OptionValue', 'getsingle', array(
      'sequential' => 1,
      'option_group_id' => 'hrjob_hours_type',
      'name' => "Full_Time",
    ));
    $hour = $result['value'] ? $result['value'] : $settings['work_hour_per_day'];

    // See also: CRM_HRJob_Estimator::updateEstimatesByUnit (plural)
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_hrjob_pay p, civicrm_hrjob_hour h
      SET p.pay_annualized_est = CASE p.pay_unit
      WHEN 'Hour' THEN %1 * IFNULL(h.hours_amount, %4) * h.fte_num * p.pay_amount / h.fte_denom
      ELSE %1 * h.fte_num * p.pay_amount / h.fte_denom END
      WHERE p.job_id = %3
      AND p.job_id = h.job_id
      AND p.pay_unit = %2
      AND p.pay_is_auto_est = 1
    ", array(
        1 => array($multiplier, 'Float'),
        2 => array($unit, 'String'),
        3 => array($job_id, 'Positive'),
        4 => array($hour, 'Integer'),
      )
    );
  }
}
