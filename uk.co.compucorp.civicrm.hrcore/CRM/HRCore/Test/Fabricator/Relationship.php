<?php

class CRM_HRCore_Test_Fabricator_Relationship {

  private static $defaultParams = [
    'sequential' => 1,
    'name_a_b' => "test AB",
    'name_b_a' => "test BA",
    'contact_type_a' => "Individual",
    'contact_type_b' => "Individual",
  ];

  public static function fabricateRelationshipType($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3(
      'Activity',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
