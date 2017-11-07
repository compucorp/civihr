<?php

class CRM_Hremergency_DAO_EmergencyContact extends CRM_Core_DAO {

  /**
   * @inheritdoc
   */
  public function __construct() {
    $this->__table = self::getTableName();
    parent::__construct();
  }

  /**
   * @inheritdoc
   */
  public static function &fields() {
    return [
        'id' => array(
        'name' => 'id',
        'type' => CRM_Utils_Type::T_INT,
        'title' => ts('Emergency Contact ID'),
      ) ,
      'entity_id' => array(
        'name' => 'entity_id',
        'type' => CRM_Utils_Type::T_INT,
        'title' => ts('Entity ID') ,
        'required' => false,
      )
    ];
  }

  /**
   * @inheritdoc
   */
  public static function getTableName() {
    return 'civicrm_value_emergency_contacts_21';
  }

}
