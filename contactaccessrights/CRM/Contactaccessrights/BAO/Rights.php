<?php

class CRM_Contactaccessrights_BAO_Rights extends CRM_Contactaccessrights_DAO_Rights {
  /**
   * Rights grouped by type.
   *
   * @var array
   */
  private $rights = [];

  /**
   * Create a new Rights based on array-data.
   *
   * @param array $params key-value pairs
   *
   * @return CRM_Contactaccessrights_DAO_Rights|NULL
   *
   */
  public static function create($params) {
    $entityName = 'Rights';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

    $instance = new static();
    $instance->copyValues($params);
    $instance->save();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * @param \CRM_Contactaccessrights_Utils_RightType_RightTypeInterface $rightType
   * @param int|null                                                    $contactId
   *
   * @return array
   */
  public function getRightsByType(CRM_Contactaccessrights_Utils_RightType_RightTypeInterface $rightType, $contactId = NULL) {
    $contactId = $contactId ?: CRM_Core_Session::singleton()->get('userID');

    $sql = "
    SELECT
      rights.id id,
      rights.contact_id contact_id,
      rights.entity_type entity_type,
      rights.entity_id entity_id,
      ov.label label,
      ov.value value

    FROM civicrm_contactaccessrights_rights rights

    INNER JOIN civicrm_option_group og
    ON og.name = rights.entity_type AND og.name = %1

    INNER JOIN civicrm_option_value ov
    ON ov.id = rights.entity_id

    WHERE rights.contact_id = %2";

    $queryParams = array(1 => array($rightType->getEntityType(), 'String'), 2 => array($contactId, 'Integer'));

    $bao = static::executeQuery($sql, $queryParams);

    while ($bao->fetch()) {
      $this->addRight($bao->toArray(), $rightType->getEntityType());
    }

    return isset($this->rights[$rightType->getEntityType()])
      ? $this->rights[$rightType->getEntityType()]
      : [];
  }

  /**
   * @param $right
   * @param $entityType
   */
  private function addRight($right, $entityType) {
    if (!isset($this->rights[$entityType])) {
      $this->rights[$entityType] = [];
    }

    $this->rights[$entityType][$right['id']] = $right;
  }
}
