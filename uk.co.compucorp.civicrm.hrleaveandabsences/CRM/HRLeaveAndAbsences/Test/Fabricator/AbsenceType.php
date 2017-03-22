<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

class CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType extends
  CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle {

  private static $defaultParams = [
    'color'                     => '#000000',
    'default_entitlement'       => 20,
    'allow_request_cancelation' => 1,
    'allow_carry_forward'       => 1,
  ];

  public static function fabricate($params = []) {
    $params = array_merge(static::$defaultParams, $params);

    if(empty($params['title'])) {
      $params['title'] = static::nextSequentialTitle();
    }

    return AbsenceType::create($params);
  }

  /**
   * Since we cannot create reserved types through the API,
   * we have this helper method to insert one directly in
   * the database
   */
  public static function fabricateReservedType($params = []) {
    $absenceType = self::fabricate($params);
    $absenceTypeTable = AbsenceType::getTableName();

    $query = "UPDATE {$absenceTypeTable} SET is_reserved = 1 WHERE id = {$absenceType->id}";
    CRM_Core_DAO::executeQuery($query);

    return $absenceType;
  }
}
