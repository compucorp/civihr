<?php

/**
 * Fabricates rights for tests.
 */
class CRM_Contactaccessrights_Test_Fabricator_Rights {

  /**
   * Fabricates Rights, given specific values for contact_id, entity_id and 
   * entity_type.
   * 
   * @param array $params
   *   Associative array of parameters to be used to store rights information in 
   *   database.
   * @return array
   *   Holds values stored in database for created access right
   */
  public static function fabricate($params) {
    $result = civicrm_api3('Rights', 'create', [
      'sequential' => 1,
      'contact_id' => $params['contact_id'],
      'entity_id' => $params['entity_id'],
      'entity_type' => $params['entity_type']
    ]);

    return array_shift($result['values']);
  }

}
