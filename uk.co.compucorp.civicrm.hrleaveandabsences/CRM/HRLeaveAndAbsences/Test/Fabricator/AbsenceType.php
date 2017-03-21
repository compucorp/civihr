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
  public static function createReservedType() {
    $title = 'Title ' . microtime();
    $query = "
      INSERT INTO
        civicrm_hrleaveandabsences_absence_type(title, color, default_entitlement, allow_request_cancelation, is_reserved, weight)
        VALUES('{$title}', '#000000', 0, 1, 1, 1)
    ";
    CRM_Core_DAO::executeQuery($query);

    $query = "SELECT id FROM civicrm_hrleaveandabsences_absence_type WHERE title = '{$title}'";
    $dao = CRM_Core_DAO::executeQuery($query);
    if($dao->N == 1) {
      $dao->fetch();
      return $dao->id;
    }

    return null;
  }
}
