<?php

class CRM_HRJob_BAO_HRJob extends CRM_HRJob_DAO_HRJob {

  public static function create($params) {
    $entityName = 'HRJob';
    $hook = empty($params['id']) ? 'create' : 'edit';

    if (is_numeric(CRM_Utils_Array::value('is_primary', $params)) || empty($params['id'])) {
      CRM_Core_BAO_Block::handlePrimary($params, get_class());
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

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
