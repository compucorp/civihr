<?php

use CRM_Contactaccessrights_Utils_RightType_RightTypeInterface as RightTypeInterface;
use CRM_Contactaccessrights_Utils_RightType_Region as RegionRightType;
use CRM_Contactaccessrights_Utils_RightType_Location as LocationRightType;

class CRM_Contactaccessrights_BAO_Rights extends CRM_Contactaccessrights_DAO_Rights {

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
   * Gets the regions that the contact has access to.
   *
   * @param int $contactID
   *
   * @return array
   */
  public function getContactRightsByLocations($contactID) {
    $rightType = new LocationRightType();

    return $this->getRightsByType($rightType, $contactID);
  }

  /**
   * Gets the locations that the contact has access to.
   *
   * @param int $contactID
   *
   * @return array
   */
  public function getContactRightsByRegions($contactID) {
    $rightType = new RegionRightType();

    return $this->getRightsByType($rightType, $contactID);
  }

  /**
   * Gets the Entity that the given contact has access to depending on
   * the right type. (Locations and Regions for now)
   *
   * @param RightTypeInterface $rightType
   * @param int $contactID
   *
   * @return array
   */
  private function getRightsByType(RightTypeInterface $rightType, $contactID) {
    $contactID = $contactID ?: CRM_Core_Session::getLoggedInContactID();

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

    $entityType = $rightType->getEntityType();
    $queryParams = [
      1 => [$entityType, 'String'],
      2 => [$contactID, 'Integer']
    ];

    $bao = CRM_Core_DAO::executeQuery($sql, $queryParams);

    $rights = [$entityType => []];

    while ($bao->fetch()) {
      $rights[$entityType][$bao->id] = $bao->toArray();
    }

    return !empty($rights[$entityType]) ? $rights[$entityType] : [];
  }
}
