<?php

class CRM_HRLeaveAndAbsences_BAO_NotificationReceiver extends CRM_HRLeaveAndAbsences_DAO_NotificationReceiver {

  /**
   * Create a new NotificationReceiver based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_NotificationReceiver|NULL
   **/
  public static function create($params) {
    $className = 'CRM_HRLeaveAndAbsences_DAO_NotificationReceiver';
    $entityName = 'NotificationReceiver';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  public static function addReceiversToAbsenceType($typeId, $receiversIdsToAdd)
  {
    foreach($receiversIdsToAdd as $id) {
      self::create(['type_id' => $typeId, 'contact_id' => $id]);
    }

    return true;
  }

  public static function getReceiversIDsForAbsenceType($id)
  {
    $id = (int)$id;
    $ids = [];
    $table = self::getTableName();
    $query = "SELECT contact_id FROM $table WHERE type_id = %1";
    $params = [1 => [$id, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    while($dao->fetch()) {
      $ids[] = $dao->contact_id;
    }

    return $ids;
  }

  public static function removeReceiversFromAbsenceType($id)
  {
    $id = (int)$id;
    $table = self::getTableName();
    $query = "DELETE FROM $table WHERE type_id = %1";
    $params = [1 => [$id, 'Integer']];
    CRM_Core_DAO::executeQuery($query, $params);
  }

}
