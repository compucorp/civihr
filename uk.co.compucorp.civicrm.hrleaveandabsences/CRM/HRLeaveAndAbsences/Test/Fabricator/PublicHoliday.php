<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

class CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHoliday extends
  CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle {

  public static function fabricate($params = []) {
    $params = self::prepareParams($params);

    return PublicHoliday::create($params);
  }

  /**
   * Creates a new Public Holiday without running any validation. That is,
   * the public holiday is created without calling the create() method.
   *
   * @param array $params
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_PublicHoliday
   */
  public static function fabricateWithoutValidation($params = []) {
    $params = self::prepareParams($params);

    $publicHolidayFields = PublicHoliday::fields();
    $publicHoliday = new PublicHoliday();
    foreach($params as $field => $value) {
      if(!array_key_exists($field, $publicHolidayFields)) {
        continue;
      }

      $publicHoliday->$field = $value;
    }

    $publicHoliday->save();

    return $publicHoliday;
  }

  /**
   * Prepares the $params array passed to a fabricate* method, by adding default
   * values for empty fields
   *
   * @param array $params
   *
   * @return array
   */
  private static function prepareParams($params) {
    if (empty($params['title'])) {
      $params['title'] = static::nextSequentialTitle();
      return $params;
    }
    return $params;
  }
}
