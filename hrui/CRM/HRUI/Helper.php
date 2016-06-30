<?php

/**
 * Collection of methods to be used in this extension
 */
class CRM_HRUI_Helper {

  /**
   * return an array containing the list of line managers
   * for specific contact
   *
   * @param int $contactID contact ID
   * @return array
   */
  public static function getLineManagersList($contactID)  {
    try {
      $result = civicrm_api3('RelationshipType', 'getsingle', array(
        'sequential' => 1,
        'return' => array("id"),
        'name_a_b' => "Line Manager is",
      ));
      $relationshipTypeID = $result['id'];
      $queryParam =
        array(
          1 => array($contactID, 'Integer'),
          2 => array($relationshipTypeID, 'Integer')
        );
      $query = "SELECT cc.id, cc.display_name
            FROM civicrm_relationship cr
            LEFT JOIN civicrm_contact cc
            ON cr.contact_id_b = cc.id
            WHERE cr.contact_id_a = %1
            AND cr.relationship_type_id = %2
            AND (cr.end_date IS NULL OR cr.end_date >= CURDATE())
            AND (cr.start_date IS NULL OR cr.start_date <= CURDATE())
            AND cr.is_active = 1
            AND cc.is_deleted = 0";
      $response = CRM_Core_DAO::executeQuery($query, $queryParam);
      $result = array();
      while($response->fetch())  {
        $result[$response->id] = $response->display_name;
      }
      return $result;
    }
    catch (CiviCRM_API3_Exception $ex){
      return array();
    }
  }
}
