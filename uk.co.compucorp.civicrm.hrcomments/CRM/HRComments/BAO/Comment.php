<?php

class CRM_HRComments_BAO_Comment extends CRM_HRComments_DAO_Comment {

  /**
   * Create a new Comment based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRComments_DAO_Comment|NULL
   */
  public static function create($params) {
    $entityName = 'Comment';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
}
