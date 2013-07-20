<?php

class CRM_HRJob_BAO_HRJob extends CRM_HRJob_DAO_HRJob {

  /**
   * Get a count of records with the given property
   *
   * @param $params
   * @return int
   */
  public static function getRecordCount($params) {
    $dao = new CRM_HRJob_DAO_HRJob();
    $dao->copyValues($params);
    return $dao->count();
  }
}
